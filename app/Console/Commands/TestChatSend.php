<?php

namespace App\Console\Commands;

use App\Services\AIChart\AIChatInterface;
use App\Services\AIChart\PromptResolver;
use Illuminate\Console\Command;

class TestChatSend extends Command
{
    protected $signature = 'chat:test {prompt?}';

    public function __construct(
        private readonly AIChatInterface $chat,
        private readonly PromptResolver $promptResolver,
    ) {
        parent::__construct();
    }

    public function handle()
    {
        // Получаем ключ или текст промпта из аргумента
        $input = $this->argument('prompt');

        if (!$input){
            $this->error('Промпт не задан...');
            return;
        }

        // Резолвим промпт через PromptResolver
        $prompt = $this->promptResolver->resolve($input);

        // === Изображение ===
        $imagePath = null;
//        $imagePath = storage_path('app/public/img-gpt/2.png');
//        $imagePath = storage_path('app/public/img-gpt/46.JPG');

        $imageBase64 = $imagePath && file_exists($imagePath)
            ? base64_encode(file_get_contents($imagePath))
            : null;

        // === Запрос к AI ===
        try {
            $response = $this->chat->sendPrompt($prompt, $imageBase64);
            $this->info($response);
        } catch (\Throwable $e) {
            $this->error("Ошибка: " . $e->getMessage());
        }
    }
}
