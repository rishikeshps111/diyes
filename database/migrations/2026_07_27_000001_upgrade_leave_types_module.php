<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('leave_types')) {
            Schema::create('leave_types', function (Blueprint $table): void {
                $table->id();
                $table->string('code', 30)->unique();
                $table->string('leave_name', 100)->unique();
                $table->enum('leave_type', ['paid', 'unpaid']);
                $table->unsignedInteger('max_leaves_per_year');
                $table->unsignedInteger('total_days');
                $table->boolean('is_lop')->default(false);
                $table->boolean('carry_forward_allowed');
                $table->unsignedInteger('max_carry_forward_limit')->nullable();
                $table->enum('applicable_for', ['all', 'teachers', 'role']);
                $table->foreignId('role_id')->nullable()->constrained('roles')->nullOnDelete();
                $table->enum('gender_specific', ['all', 'male', 'female']);
                $table->unsignedInteger('max_leave_days_per_request');
                $table->unsignedInteger('advance_notice_days');
                $table->boolean('allow_half_day');
                $table->boolean('requires_approval');
                $table->boolean('encashment_allowed');
                $table->boolean('status')->default(true);
                $table->text('description');
                $table->timestamps();
            });

            return;
        }

        $columns = [
            'leave_type' => fn (Blueprint $table) => $table->enum('leave_type', ['paid', 'unpaid'])->nullable()->after('leave_name'),
            'max_leaves_per_year' => fn (Blueprint $table) => $table->unsignedInteger('max_leaves_per_year')->nullable()->after('leave_type'),
            'carry_forward_allowed' => fn (Blueprint $table) => $table->boolean('carry_forward_allowed')->default(false)->after('total_days'),
            'max_carry_forward_limit' => fn (Blueprint $table) => $table->unsignedInteger('max_carry_forward_limit')->nullable()->after('carry_forward_allowed'),
            'applicable_for' => fn (Blueprint $table) => $table->enum('applicable_for', ['all', 'teachers', 'role'])->nullable()->after('max_carry_forward_limit'),
            'gender_specific' => fn (Blueprint $table) => $table->enum('gender_specific', ['all', 'male', 'female'])->nullable()->after('role_id'),
            'max_leave_days_per_request' => fn (Blueprint $table) => $table->unsignedInteger('max_leave_days_per_request')->nullable()->after('gender_specific'),
            'advance_notice_days' => fn (Blueprint $table) => $table->unsignedInteger('advance_notice_days')->nullable()->after('max_leave_days_per_request'),
            'allow_half_day' => fn (Blueprint $table) => $table->boolean('allow_half_day')->default(false)->after('advance_notice_days'),
            'requires_approval' => fn (Blueprint $table) => $table->boolean('requires_approval')->default(true)->after('allow_half_day'),
            'encashment_allowed' => fn (Blueprint $table) => $table->boolean('encashment_allowed')->default(false)->after('requires_approval'),
            'description' => fn (Blueprint $table) => $table->text('description')->nullable()->after('status'),
        ];

        foreach ($columns as $column => $definition) {
            if (! Schema::hasColumn('leave_types', $column)) {
                Schema::table('leave_types', $definition);
            }
        }

        DB::table('leave_types')
            ->orderBy('id')
            ->get()
            ->each(function ($leaveType): void {
                DB::table('leave_types')->where('id', $leaveType->id)->update([
                    'leave_type' => $leaveType->leave_type ?: ($leaveType->is_lop ? 'unpaid' : 'paid'),
                    'max_leaves_per_year' => $leaveType->max_leaves_per_year ?? $leaveType->total_days ?? 0,
                    'applicable_for' => $leaveType->applicable_for ?: ($leaveType->role_id ? 'role' : 'all'),
                    'gender_specific' => $leaveType->gender_specific ?: 'all',
                    'max_leave_days_per_request' => $leaveType->max_leave_days_per_request ?? max(1, (int) ($leaveType->total_days ?? 1)),
                    'advance_notice_days' => $leaveType->advance_notice_days ?? 0,
                    'description' => $leaveType->description ?: 'Legacy leave type.',
                ]);
            });
    }

    public function down(): void
    {
        $columns = [
            'leave_type', 'max_leaves_per_year', 'carry_forward_allowed',
            'max_carry_forward_limit', 'applicable_for', 'gender_specific',
            'max_leave_days_per_request', 'advance_notice_days', 'allow_half_day',
            'requires_approval', 'encashment_allowed', 'description',
        ];

        $existing = array_values(array_filter(
            $columns,
            fn (string $column): bool => Schema::hasColumn('leave_types', $column),
        ));

        if ($existing !== []) {
            Schema::table('leave_types', fn (Blueprint $table) => $table->dropColumn($existing));
        }
    }
};
