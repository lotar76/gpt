<?php
namespace App\Services\AIChart;

use App\Models\VpnProxy;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class OpenAiChatService implements AIChatInterface
{
    public function sendPrompt(string $prompt, ?string $imageBase64 = null): string
    {
        $proxy = $this->getWorkingProxy();
//        $proxyUrl = $proxy ? "{$proxy->protocol}://{$proxy->ip}:{$proxy->port}" : null;
        $proxyUrl = 'https://api.proxyapi.ru/openai/v1/chat/completions';

        $headers = [
            'Authorization' => 'Bearer ' . config('services.openai.key'),
            'Content-Type' => 'application/json',
        ];

        $body = [
            'model' => config('services.openai.model'),
            'messages' => [
                [
                    'role' => 'user',
                    'content' => $this->buildMessage($prompt, $imageBase64),
                ],
            ],
            'max_tokens' => 1000,
        ];

        try {
            $response = Http::withHeaders($headers)
                ->withOptions([
                    'verify' => false,
                    'proxy' => $proxyUrl,
                    'timeout' => 30,
                ])
                ->post(config('services.openai.url'), $body);

            if (! $response->successful()) {
                throw new \RuntimeException('OpenAI API error: ' . $response->body());
            }

            return $response->json('choices.0.message.content');
        } catch (\Throwable $e) {
            if ($proxy) {
                $proxy->is_working = false;
                $proxy->save();
                Log::warning("Прокси {$proxyUrl} отключён: " . $e->getMessage());
            }

            throw new \RuntimeException('Ошибка при отправке запроса через прокси: ' . $e->getMessage(), 0, $e);
        }
    }

    private function getWorkingProxy(): ?VpnProxy
    {
        return VpnProxy::where('is_working', true)->inRandomOrder()->first();
    }

    private function buildMessage(string $prompt, ?string $imageBase64): array
    {
        if ($imageBase64) {
            return [
                [
                    'type' => 'text',
                    'text' => $prompt,
                ],
                [
                    'type' => 'image_url',
                    'image_url' => [
                        'url' => 'data:image/jpeg;base64,' . $imageBase64,
                    ],
                ],
            ];
        }

        return [
            [
                'type' => 'text',
                'text' => $prompt,
            ]
        ];
    }
}
