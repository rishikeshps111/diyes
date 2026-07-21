<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('teacher_subjects', function (Blueprint $table) {
            $table->index('teacher_id');
        });

        Schema::table('teacher_subjects', function (Blueprint $table) {
            $table->dropUnique(['teacher_id', 'subject_id']);
        });

        Schema::table('teacher_subjects', function (Blueprint $table) {
            $table->foreignId('grade_id')->nullable()->after('teacher_id')->constrained()->cascadeOnDelete();
            $table->unique(['teacher_id', 'grade_id', 'subject_id']);
        });

        DB::table('teacher_subjects')->orderBy('id')->each(function (object $assignment): void {
            $gradeId = DB::table('subjects')->where('id', $assignment->subject_id)->value('grade_id');
            DB::table('teacher_subjects')->where('id', $assignment->id)->update(['grade_id' => $gradeId]);
        });
    }

    public function down(): void
    {
        Schema::table('teacher_subjects', function (Blueprint $table) {
            $table->dropUnique(['teacher_id', 'grade_id', 'subject_id']);
            $table->dropConstrainedForeignId('grade_id');
            $table->unique(['teacher_id', 'subject_id']);
            $table->dropIndex(['teacher_id']);
        });
    }
};
