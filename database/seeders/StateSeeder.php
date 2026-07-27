<?php

namespace Database\Seeders;

use App\Models\Country;
use App\Models\State;
use Illuminate\Database\Seeder;

class StateSeeder extends Seeder
{
    public function run(): void
    {
        $country = Country::query()->where('code', 'IN')->firstOrFail();

        $states = [
          'kerala'
        ];

        foreach ($states as $state) {
            State::query()->updateOrCreate(
                ['country_id' => $country->id, 'name' => $state],
                []
            );
        }
    }
}
