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
        Schema::create('system_notifications', function (Blueprint $table) {
            $table->id();
            $table->string('title'); // Short headline of the notification
            $table->text('message'); // Main body text/content of the notification
            $table->enum('status', ['active', 'inactive', 'archived'])->default('active'); // Visibility status
            $table->enum('type', ['update', 'maintenance', 'alert', 'info'])->nullable(); // Classification type
            $table->datetime('date_start')->nullable(); // When notification becomes visible
            $table->datetime('date_end')->nullable(); // When notification expires
            $table->unsignedBigInteger('created_by'); // Who created it (System Admin)
            $table->timestamps();

            // Foreign key constraint
            $table->foreign('created_by')->references('id')->on('users')->onDelete('cascade');
            
            // Indexes for better performance
            $table->index(['status', 'date_start', 'date_end']);
            $table->index('created_by');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('system_notifications');
    }
};
