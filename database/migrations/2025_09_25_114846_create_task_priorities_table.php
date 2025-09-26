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
        Schema::create('task_priorities', function (Blueprint $table) {
            $table->id();
            // Grouping key so a single Task Priority can contain multiple items managed together
            $table->uuid('group_key')->index();
            $table->unsignedBigInteger('instruction_id');
            $table->unsignedBigInteger('instruction_sender_id');
            $table->string('priority_title');
            $table->enum('priority_level', ['high', 'normal', 'low'])->default('normal');
            $table->date('start_date');
            $table->date('target_deadline');
            $table->tinyInteger('week_range')->unsigned();
            $table->enum('status', ['Not Started', 'Processing', 'Accomplished'])->default('Not Started');
            $table->text('notes')->nullable();
            $table->timestamps();

            // Foreign key constraints
            $table->foreign('instruction_id')->references('id')->on('instructions')->onDelete('cascade');
            $table->foreign('instruction_sender_id')->references('id')->on('users')->onDelete('cascade');

            // Indexes for better performance
            $table->index(['instruction_id', 'instruction_sender_id']);
            $table->index(['priority_level', 'status']);
            $table->index('target_deadline');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('task_priorities');
    }
};
