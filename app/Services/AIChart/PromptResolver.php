<?php

namespace App\Services\AIChart;

class PromptResolver
{
    public function resolve(string $keyOrRaw): string
    {
        $predefined = config('prompts');

        return $predefined[$keyOrRaw] ?? $keyOrRaw;
    }
}
