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
        Schema::table('instruction_replies', function (Blueprint $table) {
            $table->string('attachment_filename')->nullable()->after('content');
            $table->string('attachment_original_name')->nullable()->after('attachment_filename');
            $table->string('attachment_path')->nullable()->after('attachment_original_name');
            $table->string('attachment_mime_type')->nullable()->after('attachment_path');
            $table->unsignedBigInteger('attachment_size')->nullable()->after('attachment_mime_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('instruction_replies', function (Blueprint $table) {
            $table->dropColumn([
                'attachment_filename',
                'attachment_original_name',
                'attachment_path',
                'attachment_mime_type',
                'attachment_size'
            ]);
        });
    }
};
