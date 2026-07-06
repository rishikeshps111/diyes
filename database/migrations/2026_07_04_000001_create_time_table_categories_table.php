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
        if (Schema::hasTable('time_table_categories')) {
            return;
        }

        if (Schema::hasTable('time_table_types')) {
            Schema::rename('time_table_types', 'time_table_categories');

            return;
        }

        Schema::create('time_table_categories', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('title');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('time_table_categories') && ! Schema::hasTable('time_table_types')) {
            Schema::rename('time_table_categories', 'time_table_types');

            return;
        }

        Schema::dropIfExists('time_table_categories');
    }
};
