<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('special_event_timetable_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('special_event_id')->constrained()->cascadeOnDelete();
            $table->foreignId('grade_id')->constrained()->cascadeOnDelete();
            $table->foreignId('division_id')->constrained()->cascadeOnDelete();
            $table->foreignId('project_week_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('timetable_entry_id')->nullable()->constrained()->nullOnDelete();
            $table->string('day');
            $table->unsignedInteger('period_no');
            $table->string('entry_type')->default('period');
            $table->string('subject_name')->nullable();
            $table->json('teacher_names')->nullable();
            $table->time('start_time');
            $table->time('end_time');
            $table->unsignedInteger('duration_minutes');
            $table->boolean('is_event_period')->default(false);
            $table->timestamps();
            $table->unique(['special_event_id', 'grade_id', 'division_id', 'day', 'period_no', 'entry_type'], 'special_event_tt_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('special_event_timetable_entries');
    }
};
