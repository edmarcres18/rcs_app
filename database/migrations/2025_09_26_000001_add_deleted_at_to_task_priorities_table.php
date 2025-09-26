<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (Schema::hasTable('task_priorities') && !Schema::hasColumn('task_priorities', 'deleted_at')) {
            Schema::table('task_priorities', function (Blueprint $table) {
                $table->softDeletes();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('task_priorities') && Schema::hasColumn('task_priorities', 'deleted_at')) {
            Schema::table('task_priorities', function (Blueprint $table) {
                $table->dropSoftDeletes();
            });
        }
    }
};
