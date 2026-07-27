<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('leave_applications')) {
            Schema::create('leave_applications', function (Blueprint $table): void {
                $table->id();
                $table->date('applied_date');
                $table->string('application_no', 30)->unique();
                $table->foreignId('applied_by')->nullable()->constrained('users')->nullOnDelete();
                $table->enum('applicant_type', ['teacher', 'user'])->default('teacher');
                $table->foreignId('teacher_id')->nullable()->constrained()->nullOnDelete();
                $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
                $table->foreignId('role_id')->nullable()->constrained('roles')->nullOnDelete();
                $table->foreignId('leave_type_id')->constrained()->restrictOnDelete();
                $table->date('from_date');
                $table->date('to_date');
                $table->decimal('days', 6, 1);
                $table->boolean('is_half_day')->default(false);
                $table->text('reason');
                $table->enum('status', ['Pending', 'Approved', 'Rejected'])->default('Pending');
                $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamp('approved_at')->nullable();
                $table->text('remarks')->nullable();
                $table->timestamps();
            });

            return;
        }

        if (! Schema::hasColumn('leave_applications', 'applicant_type')) {
            Schema::table('leave_applications', function (Blueprint $table): void {
                $table->enum('applicant_type', ['teacher', 'user'])->default('teacher')->after('application_no');
                $table->foreignId('user_id')->nullable()->after('teacher_id')->constrained('users')->nullOnDelete();
                $table->foreignId('role_id')->nullable()->after('user_id')->constrained('roles')->nullOnDelete();
                $table->boolean('is_half_day')->default(false)->after('days');
            });
        }

        Schema::table('leave_applications', function (Blueprint $table): void {
            $table->foreignId('teacher_id')->nullable()->change();
            $table->decimal('days', 6, 1)->change();
        });

        DB::table('leave_applications')->whereNull('applicant_type')->update(['applicant_type' => 'teacher']);

        if (Schema::hasTable('teacher_leave_balances')) {
            Schema::table('teacher_leave_balances', function (Blueprint $table): void {
                $table->decimal('allocated_days', 6, 1)->change();
                $table->decimal('used_days', 6, 1)->change();
                $table->decimal('remaining_days', 6, 1)->change();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('leave_applications', 'user_id')) {
            Schema::table('leave_applications', function (Blueprint $table): void {
                $table->dropConstrainedForeignId('user_id');
                $table->dropConstrainedForeignId('role_id');
                $table->dropColumn(['applicant_type', 'is_half_day']);
            });
        }
    }
};
