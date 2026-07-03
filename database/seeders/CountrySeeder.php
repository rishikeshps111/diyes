<?php

namespace Database\Seeders;

use App\Models\Country;
use Illuminate\Database\Seeder;

class CountrySeeder extends Seeder
{
    public function run(): void
    {
        Country::query()->updateOrCreate(
            ['code' => 'IN'],
            [
                'name' => 'India',
                'phone_code' => '+91',
            ]
        );
    }
}
