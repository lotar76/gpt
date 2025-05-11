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
        $imagePath = storage_path('app/public/img-gpt/2.JPG');
//        $imagePath = storage_path('app/public/img-gpt/46.JPG');

        if (!file_exists($imagePath)) {
            $this->error('Файл не найден: ' . $imagePath);
            return;
        }

        $imageBase64 = base64_encode(file_get_contents($imagePath));

        $prompt = "Проанализируй пропорции тела человека на изображении, опираясь только на силуэт и геометрию. Выбери один тип фигуры из следующих: Прямоугольник, Треугольник, Песочные часы, Яблоко, Груша. Ответь одним словом, без пояснений.";


        try {
            $response = $this->chat->sendPrompt($prompt, $imageBase64);
            $this->info($response);
        } catch (\Throwable $e) {
            $this->error("Ошибка: " . $e->getMessage());
        }
    }
}
