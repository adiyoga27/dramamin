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
        Schema::create('episodes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('movie_id')->constrained()->onDelete('cascade');
            $table->string('external_id');
            $table->string('title');
            $table->string('download_url')->nullable();
            $table->string('local_path')->nullable();
            $table->enum('status', ['pending', 'downloading', 'completed', 'failed'])->default('pending');
            $table->timestamps();

            $table->unique(['movie_id', 'external_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('episodes');
    }
};
