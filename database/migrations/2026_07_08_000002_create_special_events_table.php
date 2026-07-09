<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('special_events', function (Blueprint $table) {
            $table->id();
            $table->string('event_code')->unique();
            $table->string('event_title');
            $table->foreignId('event_type_id')->constrained()->restrictOnDelete();
            $table->foreignId('academic_year_id')->constrained()->restrictOnDelete();
            $table->date('event_start_date');
            $table->date('event_end_date');
            $table->unsignedInteger('days')->default(1);
            $table->boolean('media_coverable')->default(false);
            $table->string('venue')->nullable();
            $table->string('organized_by')->nullable();
            $table->string('incharge')->nullable();
            $table->string('contact_no')->nullable();
            $table->json('participants')->nullable();
            $table->boolean('outside_candidates')->default(false);
            $table->string('objective')->nullable();
            $table->text('event_details')->nullable();
            $table->string('banner_image')->nullable();
            $table->string('status')->default('draft');
            $table->foreignId('created_by_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('special_event_timings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('special_event_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('day_number');
            $table->date('event_date');
            $table->string('day_label');
            $table->time('start_time');
            $table->time('end_time');
            $table->timestamps();
        });

        Schema::create('special_event_attachments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('special_event_id')->constrained()->cascadeOnDelete();
            $table->string('file_path');
            $table->string('file_name');
            $table->string('mime_type')->nullable();
            $table->unsignedBigInteger('file_size')->nullable();
            $table->timestamps();
        });

        Schema::create('grade_special_event', function (Blueprint $table) {
            $table->id();
            $table->foreignId('grade_id')->constrained()->cascadeOnDelete();
            $table->foreignId('special_event_id')->constrained()->cascadeOnDelete();
            $table->timestamps();
            $table->unique(['grade_id', 'special_event_id'], 'grade_special_event_unique');
        });

        Schema::create('division_special_event', function (Blueprint $table) {
            $table->id();
            $table->foreignId('division_id')->constrained()->cascadeOnDelete();
            $table->foreignId('special_event_id')->constrained()->cascadeOnDelete();
            $table->timestamps();
            $table->unique(['division_id', 'special_event_id'], 'division_special_event_unique');
        });

        Schema::create('special_event_staff_coordinator', function (Blueprint $table) {
            $table->id();
            $table->foreignId('special_event_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->timestamps();
            $table->unique(['special_event_id', 'user_id'], 'special_event_staff_unique');
        });

        Schema::create('special_event_teacher_coordinator', function (Blueprint $table) {
            $table->id();
            $table->foreignId('special_event_id')->constrained()->cascadeOnDelete();
            $table->foreignId('teacher_id')->constrained()->cascadeOnDelete();
            $table->timestamps();
            $table->unique(['special_event_id', 'teacher_id'], 'special_event_teacher_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('special_event_teacher_coordinator');
        Schema::dropIfExists('special_event_staff_coordinator');
        Schema::dropIfExists('division_special_event');
        Schema::dropIfExists('grade_special_event');
        Schema::dropIfExists('special_event_attachments');
        Schema::dropIfExists('special_event_timings');
        Schema::dropIfExists('special_events');
    }
};
