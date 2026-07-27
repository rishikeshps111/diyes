<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('leave_applications') && ! Schema::hasColumn('leave_applications', 'applied_by')) {
            Schema::table('leave_applications', function (Blueprint $table): void {
                $table->foreignId('applied_by')
                    ->nullable()
                    ->after('application_no')
                    ->constrained('users')
                    ->nullOnDelete();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('leave_applications') && Schema::hasColumn('leave_applications', 'applied_by')) {
            Schema::table('leave_applications', function (Blueprint $table): void {
                $table->dropConstrainedForeignId('applied_by');
            });
        }
    }
};
