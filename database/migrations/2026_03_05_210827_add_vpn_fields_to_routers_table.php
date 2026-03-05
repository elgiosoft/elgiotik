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
            $table->boolean('vpn_enabled')->default(false)->after('is_active');
            $table->string('vpn_ip')->nullable()->after('vpn_enabled');
            $table->string('vpn_public_key')->nullable()->after('vpn_ip');
            $table->text('vpn_private_key')->nullable()->after('vpn_public_key');
            $table->string('vpn_endpoint')->nullable()->after('vpn_private_key')->comment('Server endpoint for router config');
            $table->integer('vpn_listen_port')->default(51820)->after('vpn_endpoint');
            $table->timestamp('vpn_last_handshake')->nullable()->after('vpn_listen_port');
            $table->text('vpn_config_script')->nullable()->after('vpn_last_handshake')->comment('Generated MikroTik config');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('routers', function (Blueprint $table) {
            $table->dropColumn([
                'vpn_enabled',
                'vpn_ip',
                'vpn_public_key',
                'vpn_private_key',
                'vpn_endpoint',
                'vpn_listen_port',
                'vpn_last_handshake',
                'vpn_config_script',
            ]);
        });
    }
};
