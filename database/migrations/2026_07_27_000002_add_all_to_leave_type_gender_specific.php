<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('leave_types') && Schema::hasColumn('leave_types', 'gender_specific')) {
            Schema::table('leave_types', function (Blueprint $table): void {
                $table->enum('gender_specific', ['all', 'male', 'female'])->nullable()->change();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('leave_types') && Schema::hasColumn('leave_types', 'gender_specific')) {
            DB::table('leave_types')->where('gender_specific', 'all')->update(['gender_specific' => 'male']);

            Schema::table('leave_types', function (Blueprint $table): void {
                $table->enum('gender_specific', ['male', 'female'])->nullable()->change();
            });
        }
    }
};
