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
        Schema::create('holidays', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('holiday_name');
            $table->enum('holiday_type', [
                'Public',
                'festival',
                'Optional',
                'Others',
            ]);
            $table->foreignId('academic_year_id')->constrained()->cascadeOnDelete();
            $table->date('holiday_date');
            $table->date('start_date');
            $table->date('end_date');
            $table->string('applicable_for')->nullable();
            $table->boolean('is_active')->default(true);
            $table->text('description')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('holidays');
    }
};
