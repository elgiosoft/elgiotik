<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Modify the role ENUM to include 'owner'
        DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('admin', 'owner', 'staff', 'cashier') NOT NULL DEFAULT 'owner'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remove 'owner' from the ENUM
        DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('admin', 'staff', 'cashier') NOT NULL DEFAULT 'staff'");
    }
};
