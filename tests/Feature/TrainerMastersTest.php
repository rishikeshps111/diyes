<?php

namespace Tests\Feature;

use App\Models\TrainerCategory;
use App\Models\TrainerType;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class TrainerMastersTest extends TestCase
{
    use RefreshDatabase;

    public function test_trainer_type_and_category_support_authorized_crud_actions(): void
    {
        $user = User::factory()->create();

        foreach (['trainer-type', 'trainer-category'] as $master) {
            foreach (['view', 'create', 'edit', 'delete'] as $action) {
                Permission::findOrCreate($action.'.'.$master, 'web');
            }
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();
        $user->givePermissionTo(Permission::all());
        $this->actingAs($user);

        $masters = [
            ['route' => 'trainer-types', 'model' => TrainerType::class, 'prefix' => 'TRT'],
            ['route' => 'trainer-categories', 'model' => TrainerCategory::class, 'prefix' => 'TRC'],
        ];

        foreach ($masters as $master) {
            $this->get(route($master['route'].'.index'))->assertOk();

            $this->post(route($master['route'].'.store'), [
                'title' => 'Internal',
                'is_active' => true,
            ])->assertRedirect(route($master['route'].'.index'));

            $record = $master['model']::query()->sole();
            $this->assertStringStartsWith($master['prefix'], $record->code);

            $this->put(route($master['route'].'.update', $record), [
                'title' => 'External',
                'is_active' => true,
            ])->assertRedirect(route($master['route'].'.index'));

            $this->patchJson(route($master['route'].'.toggle-status', $record))
                ->assertOk()
                ->assertJsonPath('is_active', false);

            $this->deleteJson(route($master['route'].'.destroy', $record))
                ->assertOk();

            $this->assertDatabaseMissing($record->getTable(), ['id' => $record->getKey()]);
        }
    }

    public function test_trainer_masters_require_their_view_permissions(): void
    {
        $this->actingAs(User::factory()->create());

        $this->get(route('trainer-types.index'))->assertForbidden();
        $this->get(route('trainer-categories.index'))->assertForbidden();
    }
}
