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
        Schema::create('venues', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('venue_name');
            $table->enum('venue_type', [
                'Auditorium',
                'Hall',
                'Conference Room',
                'Sports Ground',
                'Open Air Theatre',
                'Multipurpose Room',
                'Meeting Room',
            ]);
            $table->string('building');
            $table->unsignedInteger('capacity');
            $table->json('facilities')->nullable();
            $table->string('contact_person');
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
        Schema::dropIfExists('venues');
    }
};
