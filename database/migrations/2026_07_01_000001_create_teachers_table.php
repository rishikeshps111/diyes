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
        Schema::create('teachers', function (Blueprint $table) {
            $table->id();

            $table->string('employee_id')->unique();
            $table->string('teacher_image')->nullable();

            $table->string('name');
            $table->enum('gender', ['Male', 'Female', 'Others']);
            $table->date('date_of_birth');

            $table->string('phone_country_code', 10)->default('+91');
            $table->string('phone');

            $table->string('alternative_phone_country_code', 10)->default('+91');
            $table->string('alternative_phone')->nullable();

            $table->string('email')->unique();
            $table->string('qualification');
            $table->unsignedInteger('experience')->default(0);
            $table->date('date_of_joining');

            $table->foreignId('department_id')->constrained()->restrictOnDelete();
            $table->foreignId('designation_id')->constrained()->restrictOnDelete();

            $table->string('subject')->nullable();

            $table->foreignId('class_in_charge_id')
                ->nullable()
                ->constrained('grades')
                ->nullOnDelete();

            $table->foreignId('country_id')->constrained()->restrictOnDelete();
            $table->foreignId('state_id')->constrained()->restrictOnDelete();
            $table->foreignId('district_id')->constrained()->restrictOnDelete();

            $table->text('address');
            $table->string('pincode', 10);

            $table->enum('employment_type', ['permanent', 'temporary']);
            $table->decimal('salary', 12, 2);

            $table->enum('status', ['active', 'on leave', 'Training', 'suspended'])
                ->default('active');

            $table->boolean('is_verified')->default(false);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('teachers');
    }
};
