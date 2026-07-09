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
        if (Schema::hasColumn('timetables', 'short_break_after_lunch_minutes')) {
            return;
        }

        Schema::table('timetables', function (Blueprint $table) {
            $table->unsignedInteger('short_break_after_lunch_minutes')->default(0)->after('lunch_break_minutes');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (! Schema::hasColumn('timetables', 'short_break_after_lunch_minutes')) {
            return;
        }

        Schema::table('timetables', function (Blueprint $table) {
            $table->dropColumn('short_break_after_lunch_minutes');
        });
    }
};
