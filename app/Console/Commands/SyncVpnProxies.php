<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\ProxyManagerService;
use Illuminate\Support\Facades\Log;


class SyncVpnProxies extends Command
{
    protected $signature = 'vpn:sync';
    protected $description = 'Синхронизирует список прокси с Webshare.io и проверяет их рабочесть';

    public function handle(ProxyManagerService $service): int
    {
        Log::info('[vpn:sync] Запуск команды', ['time' => now()]);

        try {
            $count = $service->syncFromWebshare();
            $this->info("Синхронизировано {$count} прокси.");

            $this->info("Проверяем рабочесть...");
            $service->validateAll();
            $this->info("Проверка завершена.");
        } catch (\Throwable $e) {
            $this->error('Ошибка: ' . $e->getMessage());
            return 1;
        }

        Log::info('[vpn:sync] Проверка прокси завершена', [
            'time' => now(),
            'total' => \App\Models\VpnProxy::count(),
        ]);


        return 0;
    }
}
