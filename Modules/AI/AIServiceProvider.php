<?php

declare(strict_types=1);

namespace Modules\AI;

use App\Support\Modules\ModuleServiceProvider;
use Modules\AI\Contracts\LlmClientContract;
use Modules\AI\Contracts\NaturalLanguageWorkflowBuilderContract;
use Modules\AI\Contracts\WorkflowDefinitionPromptTemplateContract;
use Modules\AI\Services\GeminiLlmClient;
use Modules\AI\Services\LlmClientFactory;
use Modules\AI\Services\LlmJsonResponseParser;
use Modules\AI\Services\NaturalLanguageWorkflowBuilder;
use Modules\AI\Services\OpenAiLlmClient;
use Modules\AI\Services\WorkflowDefinitionAiValidator;
use Modules\AI\Services\WorkflowDefinitionPromptTemplate;
use Modules\AI\Services\WorkflowDefinitionSanitizer;

class AIServiceProvider extends ModuleServiceProvider
{
    public function moduleName(): string
    {
        return 'AI';
    }

    public function register(): void
    {
        $this->app->singleton(WorkflowDefinitionPromptTemplateContract::class, WorkflowDefinitionPromptTemplate::class);
        $this->app->singleton(LlmJsonResponseParser::class);
        $this->app->singleton(WorkflowDefinitionSanitizer::class);
        $this->app->singleton(WorkflowDefinitionAiValidator::class);
        $this->app->singleton(LlmClientFactory::class);
        $this->app->singleton(OpenAiLlmClient::class);
        $this->app->singleton(GeminiLlmClient::class);

        $this->app->singleton(NaturalLanguageWorkflowBuilderContract::class, NaturalLanguageWorkflowBuilder::class);

        $this->app->bind(LlmClientContract::class, function ($app): LlmClientContract {
            return $app->make(LlmClientFactory::class)->make();
        });
    }
}
