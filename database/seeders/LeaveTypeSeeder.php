<?php

namespace Database\Seeders;

use App\Models\LeaveType;
use App\Services\PrefixCodeService;
use Illuminate\Database\Seeder;

class LeaveTypeSeeder extends Seeder
{
    public function run(): void
    {
        $leaveTypes = [
            [
                'leave_name' => 'Casual Leave',
                'leave_type' => 'paid',
                'max_leaves_per_year' => 12,
                'carry_forward_allowed' => false,
                'max_carry_forward_limit' => null,
                'applicable_for' => 'all',
                'gender_specific' => 'all',
                'max_leave_days_per_request' => 3,
                'advance_notice_days' => 1,
                'allow_half_day' => true,
                'requires_approval' => true,
                'encashment_allowed' => false,
                'status' => true,
                'description' => 'Paid casual leave for short personal requirements.',
            ],
            [
                'leave_name' => 'Earned Leave',
                'leave_type' => 'paid',
                'max_leaves_per_year' => 18,
                'carry_forward_allowed' => true,
                'max_carry_forward_limit' => 30,
                'applicable_for' => 'all',
                'gender_specific' => 'all',
                'max_leave_days_per_request' => 10,
                'advance_notice_days' => 7,
                'allow_half_day' => false,
                'requires_approval' => true,
                'encashment_allowed' => true,
                'status' => true,
                'description' => 'Paid earned leave with carry-forward and encashment benefits.',
            ],
            [
                'leave_name' => 'Leave Without Pay',
                'leave_type' => 'unpaid',
                'max_leaves_per_year' => 30,
                'carry_forward_allowed' => false,
                'max_carry_forward_limit' => null,
                'applicable_for' => 'teachers',
                'gender_specific' => 'all',
                'max_leave_days_per_request' => 15,
                'advance_notice_days' => 3,
                'allow_half_day' => false,
                'requires_approval' => true,
                'encashment_allowed' => false,
                'status' => true,
                'description' => 'Unpaid leave available when paid leave balances are insufficient.',
            ],
        ];

        foreach ($leaveTypes as $index => $data) {
            LeaveType::query()->updateOrCreate(
                ['leave_name' => $data['leave_name']],
                [
                    ...$data,
                    'code' => app(PrefixCodeService::class)->format('leave_type', $index + 1),
                    'total_days' => $data['max_leaves_per_year'],
                    'is_lop' => $data['leave_type'] === 'unpaid',
                    'role_id' => null,
                ],
            );
        }
    }
}
