<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('substitute_allocations', function (Blueprint $table) {
            $table->foreignId('teacher_id')->nullable()->after('training_schedule_trainer_id')->constrained('teachers')->restrictOnDelete();
            $table->foreignId('subject_id')->nullable()->after('teacher_id')->constrained('subjects')->restrictOnDelete();
            $table->foreignId('training_schedule_trainer_id')->nullable()->change();
        });

        DB::table('substitute_allocations')->whereNotNull('training_schedule_trainer_id')->orderBy('id')->each(function ($row): void {
            $trainer = DB::table('training_schedule_trainers')->find($row->training_schedule_trainer_id);
            if ($trainer) {
                DB::table('substitute_allocations')->where('id', $row->id)->update([
                    'teacher_id' => $trainer->teacher_id,
                    'subject_id' => $trainer->subject_id,
                ]);
            }
        });
    }

    public function down(): void
    {
        Schema::table('substitute_allocations', function (Blueprint $table) {
            $table->dropConstrainedForeignId('subject_id');
            $table->dropConstrainedForeignId('teacher_id');
        });
    }
};
