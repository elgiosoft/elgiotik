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
        Schema::table('hotspot_users', function (Blueprint $table) {
            // Update status to include pending/paid for payment tracking
            $table->dropColumn('status');
        });

        Schema::table('hotspot_users', function (Blueprint $table) {
            $table->enum('status', ['pending', 'paid', 'active', 'disabled', 'expired'])->default('pending')->after('password');

            // Add transaction tracking
            $table->string('transaction_id')->nullable()->after('status');

            // Add MikroTik sync tracking
            $table->string('mikrotik_user_id')->nullable()->after('transaction_id');
            $table->boolean('synced_to_router')->default(false)->after('mikrotik_user_id');
            $table->text('sync_error')->nullable()->after('synced_to_router');

            // Add sold_by and sold_at
            $table->foreignId('sold_by')->nullable()->after('created_by')->constrained('users')->nullOnDelete();
            $table->timestamp('sold_at')->nullable()->after('sold_by');
            $table->timestamp('activated_at')->nullable()->after('sold_at');
            $table->text('notes')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('hotspot_users', function (Blueprint $table) {
            $table->dropColumn(['transaction_id', 'mikrotik_user_id', 'synced_to_router', 'sync_error', 'sold_at', 'activated_at', 'notes']);
            $table->dropForeign(['sold_by']);
            $table->dropColumn('sold_by');
            $table->dropColumn('status');
        });

        Schema::table('hotspot_users', function (Blueprint $table) {
            $table->enum('status', ['active', 'disabled', 'expired'])->default('active');
        });
    }
};
