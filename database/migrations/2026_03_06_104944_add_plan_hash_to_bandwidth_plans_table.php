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
        Schema::table('bandwidth_plans', function (Blueprint $table) {
            $table->string('plan_hash', 64)->unique()->nullable()->after('id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bandwidth_plans', function (Blueprint $table) {
            $table->dropColumn('plan_hash');
        });
    }
};
