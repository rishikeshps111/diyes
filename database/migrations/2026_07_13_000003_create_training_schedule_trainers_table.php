<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('training_schedule_trainers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('training_schedule_id')->constrained()->cascadeOnDelete();
            $table->foreignId('designation_id')->constrained()->restrictOnDelete();
            $table->foreignId('teacher_id')->constrained()->restrictOnDelete();
            $table->foreignId('subject_id')->constrained()->restrictOnDelete();
            $table->timestamps();

            $table->unique(
                ['training_schedule_id', 'teacher_id', 'subject_id'],
                'training_schedule_teacher_subject_unique',
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('training_schedule_trainers');
    }
};
