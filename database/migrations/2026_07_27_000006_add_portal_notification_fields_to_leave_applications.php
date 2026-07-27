<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('leave_applications', function (Blueprint $table): void {
            $table->boolean('submitted_by_applicant')->default(false)->after('applied_by')->index();
            $table->timestamp('admin_viewed_at')->nullable()->after('submitted_by_applicant')->index();
        });
    }

    public function down(): void
    {
        Schema::table('leave_applications', function (Blueprint $table): void {
            $table->dropColumn(['submitted_by_applicant', 'admin_viewed_at']);
        });
    }
};
