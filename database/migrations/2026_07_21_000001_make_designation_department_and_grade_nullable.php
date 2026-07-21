<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('designations', function (Blueprint $table) {
            $table->foreignId('department_id')->nullable()->change();
            $table->foreignId('grade_id')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('designations', function (Blueprint $table) {
            $table->foreignId('department_id')->nullable(false)->change();
            $table->foreignId('grade_id')->nullable(false)->change();
        });
    }
};
