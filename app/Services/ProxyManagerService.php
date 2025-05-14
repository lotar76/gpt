<?php

namespace App\Services;

use App\Models\VpnProxy;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ProxyManagerService
{
    public function syncFromWebshare(): int
    {
        $apiKey = config('services.webshare.token');

        // бновим прокси на Webshare
//        $this->reloadProxies();
//        sleep(2);

        // 📥 Загружаем новый список
        $response = Http::withHeaders([
            'Authorization' => 'Token ' . $apiKey,
        ])->get('https://proxy.webshare.io/api/v2/proxy/list/', [
            'mode' => 'direct',
        ]);

        if (!$response->ok()) {
            throw new \RuntimeException('Ошибка получения списка прокси с Webshare');
        }

        $data = $response->json('results');

        // 🧠 Сравнение с текущей базой
        if (!$this->hasProxyListChanged($data)) {
            Log::info('[vpn:sync] Список прокси не изменился — база не обновляется');
            return 0;
        }

        // Чистим базу и сохраняем новые
        VpnProxy::truncate();
        $saved = 0;

        foreach ($data as $item) {
            if (empty($item['proxy_address']) || empty($item['port'])) continue;

            VpnProxy::create([
                'ip' => $item['proxy_address'],
                'port' => $item['port'],
                'protocol' => 'http',
                'username' => $item['username'] ?? null,
                'password' => $item['password'] ?? null,
                'country' => $item['country_code'] ?? null,
                'is_working' => false,
                'openai_compatible' => false,
                'last_checked_at' => now(),
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
        try {
            $url = 'http://httpbin.org/ip';

            $options = [
                'proxy' => "{$proxy->protocol}://{$proxy->ip}:{$proxy->port}",
                'timeout' => 5,
                'verify' => false,
            ];

            if ($proxy->username && $proxy->password) {
                $auth = base64_encode("{$proxy->username}:{$proxy->password}");
                $headers = ['Proxy-Authorization' => "Basic {$auth}"];
            } else {
                $headers = [];
            }

            $response = Http::withHeaders($headers)
                ->withOptions($options)
                ->get($url);

            return $response->ok() && isset($response->json()['origin']);
        } catch (\Throwable $e) {
            return false;
        }
    }

    public function reloadProxies(): void
    {
        $apiKey = config('services.webshare.token');

        $response = Http::withHeaders([
            'Authorization' => 'Token ' . $apiKey,
        ])->post('https://proxy.webshare.io/api/v2/proxy/list/refresh/');

        dump($response->status(), $response->body());


        if (!$response->ok()) {
            throw new \RuntimeException('Ошибка обновления списка прокси на Webshare');
        }

        Log::info('[webshare] Прокси обновлены', ['time' => now()]);
    }

    private function hasProxyListChanged(array $newProxies): bool
    {
        $existing = VpnProxy::pluck('ip')->toArray();
        $incoming = array_column($newProxies, 'proxy_address');

        sort($existing);
        sort($incoming);

        return $existing !== $incoming;
    }


}
