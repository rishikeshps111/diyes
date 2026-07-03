<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::table('teacher_documents as duplicate')
            ->join('teacher_documents as original', function ($join): void {
                $join->on('duplicate.teacher_id', '=', 'original.teacher_id')
                    ->on('duplicate.document_type', '=', 'original.document_type')
                    ->whereColumn('duplicate.id', '>', 'original.id');
            })
            ->delete();

        Schema::table('teacher_documents', function (Blueprint $table) {
            $table->unique(['teacher_id', 'document_type'], 'teacher_documents_teacher_type_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('teacher_documents', function (Blueprint $table) {
            $table->dropUnique('teacher_documents_teacher_type_unique');
        });
    }
};
