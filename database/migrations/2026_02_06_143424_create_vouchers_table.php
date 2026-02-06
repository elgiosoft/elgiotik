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
        Schema::create('vouchers', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('password')->nullable();
            $table->foreignId('bandwidth_plan_id')->constrained()->cascadeOnDelete();
            $table->foreignId('router_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('customer_id')->nullable()->constrained()->nullOnDelete();
            $table->enum('status', ['active', 'used', 'expired', 'disabled'])->default('active');
            $table->decimal('price', 10, 2);
            $table->timestamp('activated_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->foreignId('sold_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('sold_at')->nullable();
            $table->string('mac_address')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vouchers');
    }
};
