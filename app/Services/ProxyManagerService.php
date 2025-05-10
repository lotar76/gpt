<?php

namespace App\Services;

use App\Models\VpnProxy;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ProxyManagerService
{
    public function fetchAndStore(): int
    {
        $url = config('services.vpn_proxy.source_url');

        $response = Http::withOptions([
            'verify' => false,
            'timeout' => 10,
        ])->withHeaders([
            'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/123.0.0.0 Safari/537.36',
            'Referer' => 'https://www.google.com',
            'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,*/*;q=0.8',
            'Accept-Language' => 'en-US,en;q=0.5',
            'Upgrade-Insecure-Requests' => '1',
        ])->get($url);



        dump($response->status(), $response->body());


        if (! $response->ok()) {
            throw new \RuntimeException('Ошибка загрузки прокси');
        }

        $lines = explode("\n", trim($response->body()));
        if (empty($lines)) {
            throw new \RuntimeException('Пустой список прокси');
        }

        VpnProxy::truncate();

        $saved = 0;

        foreach ($lines as $line) {
            $line = trim($line);
            if (empty($line) || !str_contains($line, ':')) continue;

            [$ip, $port] = explode(':', $line);

            VpnProxy::create([
                'ip' => $ip,
                'port' => $port,
                'protocol' => 'http',
                'country' => null,
                'last_checked_at' => now(),
                'is_working' => false,
            ]);

            $saved++;
        }

        return $saved;
    }

    public function validateAll(): void
    {
        VpnProxy::all()->each(function (VpnProxy $proxy) {
            $proxy->is_working = $this->isWorking($proxy);
            $proxy->last_checked_at = now();
            $proxy->save();
        });
    }

    public function isWorking(VpnProxy $proxy): bool
    {
        $ip = $proxy->ip;
        $port = $proxy->port;
        $protocol = $proxy->protocol;

        // 🔌 Быстрая проверка доступности TCP-порта (3 секунды таймаут)


        // 🌍 Попробуем HTTP-запрос
        try {
            $response = Http::withOptions([
                'proxy' => "{$protocol}://{$ip}:{$port}",
                'timeout' => 5,
                'verify' => false,
            ])->get('http://httpbin.org/ip');

            if ($response->ok()) {
                $json = $response->json();
                return isset($json['origin']); // получили IP → прокси работает
            }
        } catch (\Throwable $e) {
            return false;
        }

        return false;
    }
}
