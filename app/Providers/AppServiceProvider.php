<?php

namespace App\Providers;

use App\Services\AIChart\AIChatInterface;
use App\Services\AIChart\OpenAiChatService;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(AIChatInterface::class, OpenAiChatService::class);

    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
