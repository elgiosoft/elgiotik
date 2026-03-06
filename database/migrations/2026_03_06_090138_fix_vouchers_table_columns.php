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
        Schema::table('vouchers', function (Blueprint $table) {
            // Rename bandwidth_profile_id to bandwidth_plan_id
            $table->renameColumn('bandwidth_profile_id', 'bandwidth_plan_id');
            // Drop profile_name column as we're using bandwidth_plan_id relationship
            $table->dropColumn('profile_name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('vouchers', function (Blueprint $table) {
            // Reverse the changes
            $table->renameColumn('bandwidth_plan_id', 'bandwidth_profile_id');
            $table->string('profile_name')->nullable()->after('bandwidth_profile_id');
        });
    }
};
