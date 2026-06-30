<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call(RolePermissionSeeder::class);
        $this->call(AcademicYearSeeder::class);
        $this->call(GradeSeeder::class);
        $this->call(DivisionSeeder::class);
        $this->call(DepartmentSeeder::class);
        $this->call(DesignationSeeder::class);
        $this->call(ClassroomSeeder::class);
        $this->call(VenueSeeder::class);
        $this->call(HolidaySeeder::class);

        $user = User::query()->updateOrCreate(
            ['email' => 'admin@gmail.com'],
            [
                'name' => 'Admin',
                'password' => 'admin@123',
            ],
        );

        $user->assignRole('admin');
    }
}
