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
        Schema::create('timetables', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('timetable_name')->nullable()->default('Regular Timetable');
            $table->foreignId('timetable_category_id')->constrained('time_table_categories')->cascadeOnDelete();
            $table->date('applicable_from');
            $table->date('applicable_to');
            $table->foreignId('academic_year_id')->constrained()->cascadeOnDelete();
            $table->foreignId('grade_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('total_periods_per_day');
            $table->unsignedInteger('period_duration_minutes');
            $table->unsignedInteger('short_break_minutes');
            $table->unsignedInteger('lunch_break_minutes');
            $table->foreignId('timetable_incharge_id')->constrained('users')->cascadeOnDelete();
            $table->text('description')->nullable();
            $table->foreignId('prepared_by_id')->constrained('users')->cascadeOnDelete();
            $table->timestamp('prepared_at');
            $table->enum('status', ['draft', 'published'])->default('draft');
            $table->timestamps();
        });

        Schema::create('timetable_division', function (Blueprint $table) {
            $table->id();
            $table->foreignId('timetable_id')->constrained()->cascadeOnDelete();
            $table->foreignId('division_id')->constrained()->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['timetable_id', 'division_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('timetable_division');
        Schema::dropIfExists('timetables');
    }
};
