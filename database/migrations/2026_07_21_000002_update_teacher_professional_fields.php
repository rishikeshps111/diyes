<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('teachers', function (Blueprint $table) {
            $table->foreignId('department_id')->nullable()->change();
            $table->foreignId('designation_id')->nullable()->change();
            $table->boolean('is_class_in_charge')->default(false)->after('class_in_charge_id');
        });

        DB::table('teachers')
            ->whereNotNull('class_in_charge_id')
            ->update(['is_class_in_charge' => true]);
    }

    public function down(): void
    {
        Schema::table('teachers', function (Blueprint $table) {
            $table->dropColumn('is_class_in_charge');
            $table->foreignId('department_id')->nullable(false)->change();
            $table->foreignId('designation_id')->nullable(false)->change();
        });
    }
};
