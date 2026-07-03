<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::statement('ALTER TABLE holidays MODIFY holiday_type VARCHAR(50) NOT NULL');

        DB::table('holidays')->where('holiday_type', 'National')->update(['holiday_type' => 'Public']);
        DB::table('holidays')->where('holiday_type', 'Festival')->update(['holiday_type' => 'festival']);
        DB::table('holidays')->whereIn('holiday_type', ['School Event', 'Local Holiday', 'Exam Break', 'Vacation', 'Other'])->update(['holiday_type' => 'Others']);

        DB::statement("ALTER TABLE holidays MODIFY holiday_type ENUM('Public','festival','Optional','Others') NOT NULL");

        Schema::table('holidays', function (Blueprint $table) {
            $table->string('applicable_for')->nullable()->after('end_date');
        });

        DB::table('holidays')->update([
            'applicable_for' => 'All',
        ]);

        Schema::table('holidays', function (Blueprint $table) {
            $table->dropColumn(['applicable_branch', 'applicable_classes']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('holidays', function (Blueprint $table) {
            $table->string('applicable_branch')->nullable()->after('end_date');
            $table->enum('applicable_classes', [
                'All Classes',
                'Primary',
                'Middle School',
                'High School',
                'Higher Secondary',
                'Selected Classes',
            ])->nullable()->after('applicable_branch');
        });

        DB::table('holidays')->update([
            'applicable_classes' => 'All Classes',
        ]);

        Schema::table('holidays', function (Blueprint $table) {
            $table->dropColumn('applicable_for');
        });

        DB::statement('ALTER TABLE holidays MODIFY holiday_type VARCHAR(50) NOT NULL');

        DB::table('holidays')->where('holiday_type', 'Public')->update(['holiday_type' => 'National']);
        DB::table('holidays')->where('holiday_type', 'festival')->update(['holiday_type' => 'Festival']);
        DB::table('holidays')->whereIn('holiday_type', ['Optional', 'Others'])->update(['holiday_type' => 'Other']);

        DB::statement("ALTER TABLE holidays MODIFY holiday_type ENUM('National','Festival','School Event','Local Holiday','Exam Break','Vacation','Other') NOT NULL");
    }
};
