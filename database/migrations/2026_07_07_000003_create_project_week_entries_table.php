<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('project_weeks', 'source_timetable_id')) {
            Schema::table('project_weeks', function (Blueprint $table) {
                $table->foreignId('source_timetable_id')
                    ->nullable()
                    ->after('created_by_id')
                    ->constrained('timetables')
                    ->nullOnDelete();
            });
        }

        Schema::create('project_week_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_week_id')->constrained()->cascadeOnDelete();
            $table->foreignId('timetable_entry_id')->constrained()->cascadeOnDelete();
            $table->string('day');
            $table->unsignedInteger('period_no');
            $table->foreignId('teacher_1_id')->nullable()->constrained('teachers')->nullOnDelete();
            $table->foreignId('teacher_2_id')->nullable()->constrained('teachers')->nullOnDelete();
            $table->timestamps();

            $table->unique(['project_week_id', 'timetable_entry_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('project_week_entries');

        if (Schema::hasColumn('project_weeks', 'source_timetable_id')) {
            Schema::table('project_weeks', function (Blueprint $table) {
                $table->dropConstrainedForeignId('source_timetable_id');
            });
        }
    }
};
