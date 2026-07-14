<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call(ModulePrefixSeeder::class);
        $this->call(RolePermissionSeeder::class);
        $this->call(AcademicYearSeeder::class);
        $this->call(GradeSeeder::class);
        $this->call(SubjectSeeder::class);
        $this->call(DivisionSeeder::class);
        $this->call(DepartmentSeeder::class);
        $this->call(DesignationSeeder::class);
        $this->call(CountrySeeder::class);
        $this->call(StateSeeder::class);
        $this->call(DistrictSeeder::class);
        $this->call(TeacherSeeder::class);
        $this->call(ClassroomSeeder::class);
        $this->call(VenueSeeder::class);
        $this->call(HolidaySeeder::class);
        $this->call(TimeTableCategorySeeder::class);
        $this->call(ProjectCategorySeeder::class);
        $this->call(EventTypeSeeder::class);
        $this->call(TrainerTypeSeeder::class);
        $this->call(TrainerCategorySeeder::class);
        $this->call(TrainingScheduleSeeder::class);
        $this->call(UserSeeder::class);
        $this->call(SpecialEventSeeder::class);
        $this->call(TimetableSeeder::class);
        $this->call(TimetableEntrySeeder::class);
        $this->call(ProjectSeeder::class);
        $this->call(ProjectWeekSeeder::class);
        $this->call(ProjectWeekEntrySeeder::class);
        $this->call(SpecialEventTimetableEntrySeeder::class);

        $adminRole = Role::query()->where('name', 'admin')->where('guard_name', 'web')->first();
        $adminDepartmentId = \App\Models\Department::query()->orderBy('id')->value('id');
        $adminDesignationId = \App\Models\Designation::query()->orderBy('id')->value('id');

        $user = User::query()->updateOrCreate(
            ['email' => 'admin@gmail.com'],
            [
                'employee_code' => app(\App\Services\PrefixCodeService::class)->format('user', 1),
                'username' => 'admin',
                'name' => 'Admin',
                'phone_country_code' => '+91',
                'phone' => '9999999999',
                'department_id' => $adminDepartmentId,
                'designation_id' => $adminDesignationId,
                'role_id' => $adminRole?->id,
                'password' => 'admin@123',
                'is_active' => true,
                'is_two_factor_enabled' => false,
            ],
        );

        $user->assignRole('admin');
    }
}
