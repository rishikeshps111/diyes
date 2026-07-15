<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('substitute_allocations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('training_schedule_id')->constrained()->cascadeOnDelete();
            $table->foreignId('training_schedule_trainer_id')->constrained()->cascadeOnDelete();
            $table->foreignId('timetable_entry_id')->constrained()->cascadeOnDelete();
            $table->foreignId('substitute_teacher_id')->constrained('teachers')->restrictOnDelete();
            $table->date('allocation_date');
            $table->timestamps();

            $table->unique(
                ['training_schedule_id', 'timetable_entry_id', 'allocation_date'],
                'substitute_allocation_entry_date_unique',
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('substitute_allocations');
    }
};
