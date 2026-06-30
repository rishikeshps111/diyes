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
        Schema::create('classrooms', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('room_name');
            $table->string('building');
            $table->string('floor');
            $table->enum('room_type', [
                'Smart Classroom',
                'Laboratory',
                'Lecture Hall',
                'Computer Lab',
                'Library Room',
                'Activity Room',
                'Seminar Hall',
            ]);
            $table->unsignedInteger('seating_capacity');
            $table->foreignId('department_id')->constrained()->cascadeOnDelete();
            $table->json('equipment')->nullable();
            $table->boolean('is_active')->default(true);
            $table->text('remarks')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('classrooms');
    }
};
