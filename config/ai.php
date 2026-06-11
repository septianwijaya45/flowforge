<?php

declare(strict_types=1);

return [

    /*
  |--------------------------------------------------------------------------
  | Default LLM Provider
  |--------------------------------------------------------------------------
  |
  | Supported: "openai", "gemini"
  |
  */

    'provider' => env('AI_PROVIDER', 'openai'),

    'max_retry_attempts' => (int) env('AI_MAX_RETRY_ATTEMPTS', 3),

    'openai' => [
        'api_key' => env('OPENAI_API_KEY'),
        'model' => env('OPENAI_MODEL', 'gpt-4o-mini'),
        'base_url' => env('OPENAI_BASE_URL', 'https://api.openai.com/v1'),
        'temperature' => (float) env('OPENAI_TEMPERATURE', 0.2),
    ],

    'gemini' => [
        'api_key' => env('GEMINI_API_KEY'),
        'model' => env('GEMINI_MODEL', 'gemini-2.0-flash'),
        'base_url' => env('GEMINI_BASE_URL', 'https://generativelanguage.googleapis.com/v1beta'),
        'temperature' => (float) env('GEMINI_TEMPERATURE', 0.2),
    ],

];
