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
        // Check if columns already exist
        if (Schema::hasColumn('vouchers', 'code')) {
            Schema::table('vouchers', function (Blueprint $table) {
                // Remove the code field (vouchers are now profiles, not codes)
                $table->dropUnique(['code']);
                $table->dropColumn(['code', 'password']);
            });
        }

        if (!Schema::hasColumn('vouchers', 'mikrotik_profile_id')) {
            Schema::table('vouchers', function (Blueprint $table) {
                // Add mikrotik_profile_id to track the profile created on router
                $table->string('mikrotik_profile_id')->nullable();

                // Add user_capacity - how many hotspot users can be generated
                $table->integer('user_capacity')->default(1);

                // Add counter for generated users
                $table->integer('users_generated')->default(0);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('vouchers', function (Blueprint $table) {
            // Restore code and password
            $table->string('code')->unique();
            $table->string('password')->nullable();

            // Remove new fields
            $table->dropColumn(['mikrotik_profile_id', 'user_capacity', 'users_generated']);
        });
    }
};
