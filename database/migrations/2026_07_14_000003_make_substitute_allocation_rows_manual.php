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
            $table->foreignId('grade_id')->nullable()->after('subject_id')->constrained()->restrictOnDelete();
            $table->foreignId('division_id')->nullable()->after('grade_id')->constrained()->restrictOnDelete();
            $table->unsignedInteger('period_no')->nullable()->after('division_id');
            $table->foreignId('timetable_entry_id')->nullable()->change();
        });

        DB::table('substitute_allocations')->whereNotNull('timetable_entry_id')->orderBy('id')->each(function ($row): void {
            $entry = DB::table('timetable_entries')->find($row->timetable_entry_id);
            $timetable = $entry ? DB::table('timetables')->find($entry->timetable_id) : null;
            $division = $timetable ? DB::table('timetable_division')->where('timetable_id', $timetable->id)->value('division_id') : null;
            DB::table('substitute_allocations')->where('id', $row->id)->update([
                'grade_id' => $timetable?->grade_id,
                'division_id' => $division,
                'period_no' => $entry?->period_no,
            ]);
        });
    }

    public function down(): void
    {
        Schema::table('substitute_allocations', function (Blueprint $table) {
            $table->dropColumn('period_no');
            $table->dropConstrainedForeignId('division_id');
            $table->dropConstrainedForeignId('grade_id');
        });
    }
};
