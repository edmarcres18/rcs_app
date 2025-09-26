<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (Schema::hasTable('task_priorities') && !Schema::hasColumn('task_priorities', 'created_by_user_id')) {
            Schema::table('task_priorities', function (Blueprint $table) {
                $table->unsignedBigInteger('created_by_user_id')->nullable()->after('instruction_sender_id');
                $table->foreign('created_by_user_id')
                    ->references('id')->on('users')
                    ->cascadeOnUpdate()
                    ->nullOnDelete();
                $table->index('created_by_user_id');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('task_priorities') && Schema::hasColumn('task_priorities', 'created_by_user_id')) {
            Schema::table('task_priorities', function (Blueprint $table) {
                $table->dropForeign(['created_by_user_id']);
                $table->dropColumn('created_by_user_id');
            });
        }
    }
};
