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
        Schema::table('routers', function (Blueprint $table) {
            $table->string('routeros_version')->nullable()->after('vpn_config_script')->comment('RouterOS version (e.g., 6.49, 7.13)');
            $table->string('vpn_type')->default('wireguard')->after('routeros_version')->comment('VPN type: wireguard or openvpn');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('routers', function (Blueprint $table) {
            $table->dropColumn(['routeros_version', 'vpn_type']);
        });
    }
};
