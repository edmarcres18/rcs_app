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
        Schema::table('users', function (Blueprint $table) {
            $table->string('telegram_chat_id')->nullable()->after('email');
            $table->string('telegram_username')->nullable()->after('telegram_chat_id');
            $table->boolean('telegram_notifications_enabled')->default(false)->after('telegram_username');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('telegram_chat_id');
            $table->dropColumn('telegram_username');
            $table->dropColumn('telegram_notifications_enabled');
        });
    }
};
