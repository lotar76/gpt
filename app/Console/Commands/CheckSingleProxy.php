<?php
namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\VpnProxy;
use App\Services\ProxyManagerService;

class CheckSingleProxy extends Command
{
    protected $signature = 'vpn:check-one {id : ID прокси}';
    protected $description = 'Проверка одного прокси по ID';

    public function handle(ProxyManagerService $proxyService)
    {
        $id = $this->argument('id');
        $proxy = VpnProxy::find($id);

        if (! $proxy) {
            $this->error("Прокси с ID {$id} не найден.");
            return 1;
        }

        $this->info("Проверяю {$proxy->ip}:{$proxy->port} ...");

        $result = $proxyService->isWorking($proxy);
        $proxy->is_working = $result;
        $proxy->last_checked_at = now();
        $proxy->save();

        if ($result) {
            $this->info("✅ Прокси РАБОТАЕТ");
        } else {
            $this->warn("❌ Прокси НЕ работает");
        }

        return 0;
    }
}
