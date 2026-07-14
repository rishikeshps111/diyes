<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('training_schedules')) {
            Schema::create('training_schedules', function (Blueprint $table) {
                $table->id();
                $table->string('code')->unique();
                $table->string('title');
                $table->foreignId('trainer_type_id')->constrained('trainer_types')->restrictOnDelete();
                $table->foreignId('trainer_category_id')->constrained('trainer_categories')->restrictOnDelete();
                $table->enum('conducted_by', ['diyes', 'others']);
                $table->string('resource_person_trainer');
                $table->date('start_date');
                $table->date('end_date');
                $table->decimal('per_day_hours', 5, 2);
                $table->enum('mode', ['online', 'offline']);
                $table->string('venue');
                $table->unsignedInteger('total_count');
                $table->enum('applicable', ['teachers', 'student', 'staff']);
                $table->text('training_objectives');
                $table->text('training_description');
                $table->text('remarks')->nullable();
                $table->enum('status', ['draft', 'published'])->default('draft');
                $table->foreignId('created_by_id')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('subject_training_schedule')) {
            Schema::create('subject_training_schedule', function (Blueprint $table) {
                $table->id();
                $table->foreignId('training_schedule_id')->constrained()->cascadeOnDelete();
                $table->foreignId('subject_id')->constrained()->restrictOnDelete();
                $table->timestamps();
                $table->unique(['training_schedule_id', 'subject_id'], 'training_schedule_subject_unique');
            });
        }

        if (! Schema::hasTable('training_schedule_sessions')) {
            Schema::create('training_schedule_sessions', function (Blueprint $table) {
                $table->id();
                $table->foreignId('training_schedule_id')->constrained()->cascadeOnDelete();
                $table->unsignedInteger('session_no');
                $table->date('session_date');
                $table->time('time_from');
                $table->time('time_to');
                $table->string('topic_module');
                $table->decimal('duration_hours', 5, 2);
                $table->timestamps();
                $table->unique(['training_schedule_id', 'session_no'], 'training_schedule_session_unique');
            });
        } elseif (! Schema::hasIndex('training_schedule_sessions', 'training_schedule_session_unique')) {
            Schema::table('training_schedule_sessions', function (Blueprint $table) {
                $table->unique(['training_schedule_id', 'session_no'], 'training_schedule_session_unique');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('training_schedule_sessions');
        Schema::dropIfExists('subject_training_schedule');
        Schema::dropIfExists('training_schedules');
    }
};
