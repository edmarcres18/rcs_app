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
        Schema::create('instruction_user', function (Blueprint $table) {
            $table->id();
            $table->foreignId('instruction_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->boolean('is_read')->default(false);
            $table->foreignId('forwarded_by_id')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();

            // Prevent duplicate assignments
            $table->unique(['instruction_id', 'user_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('instruction_user');
    }
};
