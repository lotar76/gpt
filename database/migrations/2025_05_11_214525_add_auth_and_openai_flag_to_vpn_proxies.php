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
		Schema::table('vpn_proxies', function (Blueprint $table) {
			$table->string('username')->nullable()->after('protocol');
			$table->string('password')->nullable()->after('username');
			$table->boolean('openai_compatible')->default(false)->after('is_working');
		});
	}

	public function down(): void
	{
		Schema::table('vpn_proxies', function (Blueprint $table) {
			$table->dropColumn(['username', 'password', 'openai_compatible']);
		});
	}
};
