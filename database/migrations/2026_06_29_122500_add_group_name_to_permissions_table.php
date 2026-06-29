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
        $tableName = config('permission.table_names.permissions', 'permissions');

        if (! Schema::hasColumn($tableName, 'group_name')) {
            Schema::table($tableName, static function (Blueprint $table): void {
                $table->string('group_name')->nullable()->after('guard_name')->index();
            });

            return;
        }

        Schema::table($tableName, static function (Blueprint $table): void {
            $table->index('group_name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $tableName = config('permission.table_names.permissions', 'permissions');

        if (Schema::hasColumn($tableName, 'group_name')) {
            Schema::table($tableName, static function (Blueprint $table): void {
                $table->dropIndex(['group_name']);
            });
        }
    }
};
