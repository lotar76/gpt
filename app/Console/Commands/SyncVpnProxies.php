<?php

namespace App\Console\Commands;

use App\Services\ProxyManagerService;
use Illuminate\Console\Command;


class SyncVpnProxies extends Command
{
    protected $signature = 'vpn:sync';
    protected $description = 'Загрузка и обновление списка VPN/прокси-серверов';

    public function handle(\App\Services\ProxyManagerService $service)
    {
        $count = $service->fetchAndStore();
        $this->info("Синхронизировано $count прокси.");

        $service->validateAll();
        $this->info("Проверка завершена.");
    }
}

