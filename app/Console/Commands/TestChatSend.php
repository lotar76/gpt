<?php

namespace App\Console\Commands;
use App\Services\AIChart\AIChatInterface;
use Illuminate\Console\Command;

class TestChatSend extends Command
{
    protected $signature = 'chat:test';
    public function __construct(private readonly AIChatInterface $chat)
    {
        parent::__construct();
    }

    public function handle()
    {
//        $imagePath = storage_path('app/public/img-gpt/1.png');
        $imagePath = storage_path('app/public/img-gpt/2.png');

        if (!file_exists($imagePath)) {
            $this->error('Файл не найден: ' . $imagePath);
            return;
        }

        $imageBase64 = base64_encode(file_get_contents($imagePath));

        $prompt = 'ообязательно предели тип фигуры на картинке - Прямоугольник, треугольник, Песочные часы, Яблоко, Груша. ответь просто название и все';

        try {
            $response = $this->chat->sendPrompt($prompt, $imageBase64);
            $this->info($response);
        } catch (\Throwable $e) {
            $this->error("Ошибка: " . $e->getMessage());
        }
    }
}
