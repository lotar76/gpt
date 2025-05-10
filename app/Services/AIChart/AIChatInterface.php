<?php

namespace App\Services\AIChart;

interface AIChatInterface
{
    /**
     * Отправить текст и изображение и получить ответ.
     *
     * @param string $prompt
     * @param string|null $imageBase64
     * @return string Ответ от AI
     */
    public function sendPrompt(string $prompt, ?string $imageBase64 = null): string;
}
