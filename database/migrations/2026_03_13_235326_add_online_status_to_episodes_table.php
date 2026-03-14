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
        // Add 'online' to enum and set as default
        \Illuminate\Support\Facades\DB::statement("ALTER TABLE episodes MODIFY COLUMN status ENUM('online', 'pending', 'downloading', 'completed', 'failed') NOT NULL DEFAULT 'online'");
        
        // Update current sessions/data: anything 'pending' is now 'online'
        \Illuminate\Support\Facades\DB::table('episodes')->where('status', 'pending')->update(['status' => 'online']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        \Illuminate\Support\Facades\DB::table('episodes')->where('status', 'online')->update(['status' => 'pending']);
        \Illuminate\Support\Facades\DB::statement("ALTER TABLE episodes MODIFY COLUMN status ENUM('pending', 'downloading', 'completed', 'failed') NOT NULL DEFAULT 'pending'");
    }
};
