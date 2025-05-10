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
        Schema::create('vpn_proxies', function (Blueprint $table) {
            $table->id();
            $table->string('ip');
            $table->string('port');
            $table->string('protocol')->default('http'); // http, https, socks5 и т.д.
            $table->string('country')->nullable();
            $table->timestamp('last_checked_at')->nullable();
            $table->boolean('is_working')->default(false);
            $table->timestamps();

            $table->unique(['ip', 'port', 'protocol']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vpn_proxies');
    }
};
