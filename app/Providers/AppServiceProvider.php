<?php

namespace App\Providers;

use App\Contracts\RukaProvider;
use App\Services\Ruka\FakeRukaProvider;
use App\Services\Ruka\GoogleAiStudioRukaProvider;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(RukaProvider::class, function (): RukaProvider {
            $provider = strtolower(trim((string) config('rumusin.ai.provider', 'fake')));

            if (in_array($provider, ['google', 'gemini', 'google_ai_studio', 'aistudio'], true)) {
                return new GoogleAiStudioRukaProvider(
                    apiKey: (string) config('rumusin.ai.api_key', ''),
                    model: (string) config('rumusin.ai.model', ''),
                );
            }

            return new FakeRukaProvider();
        });
    }

    public function boot(): void
    {
        //
    }
}
