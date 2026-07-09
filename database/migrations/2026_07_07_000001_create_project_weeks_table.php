<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('project_weeks', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->foreignId('project_id')->constrained()->cascadeOnDelete();
            $table->date('applicable_from');
            $table->date('applicable_to');
            $table->foreignId('academic_year_id')->constrained()->cascadeOnDelete();
            $table->foreignId('grade_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('total_periods');
            $table->text('description')->nullable();
            $table->enum('status', ['draft', 'publish'])->default('draft');
            $table->foreignId('created_by_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('division_project_week', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_week_id')->constrained()->cascadeOnDelete();
            $table->foreignId('division_id')->constrained()->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['project_week_id', 'division_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('division_project_week');
        Schema::dropIfExists('project_weeks');
    }
};
