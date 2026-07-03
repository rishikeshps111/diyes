<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('employee_code')->nullable()->unique()->after('id');
            $table->string('username')->nullable()->unique()->after('employee_code');
            $table->string('phone_country_code', 10)->default('+91')->after('email');
            $table->string('phone', 20)->nullable()->after('phone_country_code');
            $table->foreignId('department_id')->nullable()->after('phone')->constrained('departments')->nullOnDelete();
            $table->foreignId('designation_id')->nullable()->after('department_id')->constrained('designations')->nullOnDelete();
            $table->foreignId('role_id')->nullable()->after('designation_id')->constrained('roles')->nullOnDelete();
            $table->string('profile_image')->nullable()->after('password');
            $table->boolean('is_active')->default(true)->after('profile_image');
            $table->boolean('is_two_factor_enabled')->default(false)->after('is_active');
            $table->timestamp('last_login_at')->nullable()->after('is_two_factor_enabled');
            $table->text('remarks')->nullable()->after('last_login_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['department_id']);
            $table->dropForeign(['designation_id']);
            $table->dropForeign(['role_id']);
            $table->dropUnique(['employee_code']);
            $table->dropUnique(['username']);
            $table->dropColumn([
                'employee_code',
                'username',
                'phone_country_code',
                'phone',
                'department_id',
                'designation_id',
                'role_id',
                'profile_image',
                'is_active',
                'is_two_factor_enabled',
                'last_login_at',
                'remarks',
            ]);
        });
    }
};
