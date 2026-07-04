<?php

namespace Database\Seeders;

use App\Models\ModulePrefix;
use Illuminate\Database\Seeder;

class ModulePrefixSeeder extends Seeder
{
    /**
     * Seed editable prefixes for generated module codes.
     */
    public function run(): void
    {
        $prefixes = [
            'academic_year' => 'AT',
            'grade' => 'GRD',
            'subject' => 'SUB',
            'division' => 'DIV',
            'department' => 'DEP',
            'designation' => 'DSG',
            'classroom' => 'CLS',
            'venue' => 'VEN',
            'holiday' => 'HOL',
            'teacher' => 'EMP',
            'user' => 'EMP',
        ];

        foreach ($prefixes as $module => $prefix) {
            ModulePrefix::query()->updateOrCreate(
                ['module' => $module],
                ['prefix' => $prefix],
            );
        }
    }
}
