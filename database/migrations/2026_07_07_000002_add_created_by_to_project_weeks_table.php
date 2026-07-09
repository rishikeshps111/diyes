<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('project_weeks', 'created_by_id')) {
            return;
        }

        Schema::table('project_weeks', function (Blueprint $table) {
            $table->foreignId('created_by_id')
                ->nullable()
                ->after('status')
                ->constrained('users')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        if (! Schema::hasColumn('project_weeks', 'created_by_id')) {
            return;
        }

        Schema::table('project_weeks', function (Blueprint $table) {
            $table->dropConstrainedForeignId('created_by_id');
        });
    }
};
