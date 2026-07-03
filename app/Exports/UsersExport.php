<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class UsersExport implements FromCollection, ShouldAutoSize, WithHeadings, WithMapping
{
    public function __construct(private readonly Collection $users)
    {
    }

    public function collection(): Collection
    {
        return $this->users;
    }

    public function headings(): array
    {
        return [
            'Employee ID',
            'Username',
            'Name',
            'Role',
            'Department',
            'Email',
            'Phone',
            'Last Login',
            'Status',
            'Two-Factor Authentication',
            'Remarks',
        ];
    }

    public function map($user): array
    {
        return [
            $user->employee_code,
            $user->username,
            $user->name,
            $user->role?->name ? ucfirst($user->role->name) : '-',
            $user->department?->department_name ?? '-',
            $user->email,
            trim($user->phone_country_code.' '.$user->phone),
            $user->last_login_at?->format('d M Y h:i A') ?? '-',
            $user->is_active ? 'Active' : 'Inactive',
            $user->is_two_factor_enabled ? 'Enabled' : 'Disabled',
            $user->remarks,
        ];
    }
}
