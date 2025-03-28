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
        Schema::create('issues', function (Blueprint $table) {
            $table->id();
            $table->string('title')->nullable();
            $table->text('description')->nullable();
            $table->dateTime('startDate')->nullable();
            $table->dateTime('endDate')->nullable();
            $table->integer('duration')->nullable();
            $table->foreignId('statusID')->constrained('statuses');
            $table->foreignId('sprintID')->nullable()->constrained('sprints');
            $table->foreignId('projectID')->constrained('projects');
            $table->foreignId('userID')->constrained('users');
            $table->foreignId('assigneeID')->nullable()->constrained('users');
            $table->foreignId('assignerID')->constrained('users');
            $table->string('priority')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('issues');
    }
};
