<?php

namespace Database\Seeders;

use App\Models\District;
use App\Models\State;
use Illuminate\Database\Seeder;

class DistrictSeeder extends Seeder
{
    public function run(): void
    {
        $districtsByState = [
            'Kerala' => ['Thiruvananthapuram', 'Kollam', 'Pathanamthitta', 'Alappuzha', 'Kottayam', 'Idukki', 'Ernakulam', 'Thrissur', 'Palakkad', 'Malappuram', 'Kozhikode', 'Wayanad', 'Kannur', 'Kasaragod'],
            'Tamil Nadu' => ['Chennai', 'Coimbatore', 'Madurai', 'Salem', 'Tiruchirappalli'],
            'Karnataka' => ['Bengaluru Urban', 'Mysuru', 'Mangaluru', 'Belagavi', 'Hubballi-Dharwad'],
            'Maharashtra' => ['Mumbai', 'Pune', 'Nagpur', 'Nashik', 'Thane'],
            'Delhi' => ['Central Delhi', 'New Delhi', 'North Delhi', 'South Delhi', 'West Delhi'],
        ];

        foreach ($districtsByState as $stateName => $districts) {
            $state = State::query()->where('name', $stateName)->first();

            if (! $state) {
                continue;
            }

            foreach ($districts as $district) {
                District::query()->updateOrCreate(
                    ['state_id' => $state->id, 'name' => $district],
                    []
                );
            }
        }
    }
}
