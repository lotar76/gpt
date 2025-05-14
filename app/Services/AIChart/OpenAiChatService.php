<?php

namespace App\Services\AIChart;

use App\Models\VpnProxy;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class OpenAiChatService implements AIChatInterface
{
    public function __construct(private readonly PromptResolver $resolver) {}

    public function sendPrompt(string $prompt, ?string $imageBase64 = null): string
    {
        $proxy = $this->getWorkingProxy();
        $resolvedPrompt = $this->resolver->resolve($prompt);


        $headers = [
            'Authorization' => 'Bearer ' . config('services.openai.key'),
            'Content-Type' => 'application/json',
        ];

        $body = [
            'model' => config('services.openai.model'),
            'messages' => [
                [
                    'role' => 'user',
                    'content' => $this->buildMessage($resolvedPrompt, $imageBase64),
                ],
            ],
            'max_tokens' => 1000,
        ];

//        dump($body);
        $options = [
            'verify' => false,
            'timeout' => 30,
        ];

        if ($proxy) {
            $proxyUrl = "{$proxy->protocol}://{$proxy->ip}:{$proxy->port}";

            if ($proxy->username && $proxy->password) {
                $auth = "{$proxy->username}:{$proxy->password}@";
                $proxyUrl = "{$proxy->protocol}://{$auth}{$proxy->ip}:{$proxy->port}";
                $headers['Proxy-Authorization'] = 'Basic ' . base64_encode("{$proxy->username}:{$proxy->password}");

            }

            $options['proxy'] = $proxyUrl;
//            dump($proxyUrl); // Для дебага
        }

        try {
            $response = Http::withHeaders($headers)
                ->withOptions($options)
                ->post(config('services.openai.url'), $body);

            if (!$response->successful()) {
                throw new \RuntimeException('OpenAI API error: ' . $response->body());
            }

            return $response->json('choices.0.message.content');
        } catch (\Throwable $e) {
            if ($proxy) {
                $proxy->is_working = false;
                $proxy->save();
                Log::warning("Прокси {$proxyUrl} отключён: " . $e->getMessage());
            }
//            dump($proxyUrl);
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
