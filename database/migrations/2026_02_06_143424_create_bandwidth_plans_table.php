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
        Schema::create('bandwidth_plans', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('rate_limit'); // e.g., "512k/2M"
            $table->string('download_speed'); // e.g., "2M"
            $table->string('upload_speed'); // e.g., "512k"
            $table->decimal('price', 10, 2);
            $table->integer('validity_days')->nullable(); // null for unlimited
            $table->integer('validity_hours')->nullable();
            $table->bigInteger('data_limit')->nullable(); // in bytes, null for unlimited
            $table->integer('session_timeout')->nullable(); // in seconds
            $table->integer('idle_timeout')->nullable(); // in seconds
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bandwidth_plans');
    }
};
