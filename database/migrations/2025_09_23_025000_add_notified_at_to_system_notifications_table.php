<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('system_notifications', function (Blueprint $table) {
            if (!Schema::hasColumn('system_notifications', 'notified_at')) {
                $table->timestamp('notified_at')->nullable()->after('date_end');
            }
        });
    }

    public function down(): void
    {
        Schema::table('system_notifications', function (Blueprint $table) {
            if (Schema::hasColumn('system_notifications', 'notified_at')) {
                $table->dropColumn('notified_at');
            }
        });
    }
};
