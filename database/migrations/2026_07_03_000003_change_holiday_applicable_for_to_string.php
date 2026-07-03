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
        Schema::table('holidays', function (Blueprint $table) {
            $table->string('applicable_for')->nullable()->after('end_date')->change();
        });

        DB::table('holidays')
            ->whereRaw("JSON_VALID(applicable_for)")
            ->update([
                'applicable_for' => DB::raw("JSON_UNQUOTE(JSON_EXTRACT(applicable_for, '$[0]'))"),
            ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('holidays')
            ->whereNotNull('applicable_for')
            ->update([
                'applicable_for' => DB::raw("JSON_ARRAY(applicable_for)"),
            ]);

        Schema::table('holidays', function (Blueprint $table) {
            $table->json('applicable_for')->nullable()->change();
        });
    }
};
