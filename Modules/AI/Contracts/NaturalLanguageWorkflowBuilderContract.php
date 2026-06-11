<?php

declare(strict_types=1);

namespace Modules\AI\Contracts;

use Modules\AI\DTOs\BuildWorkflowFromPromptDTO;
use Modules\AI\DTOs\GeneratedWorkflowResultDTO;

interface NaturalLanguageWorkflowBuilderContract
{
    public function build(BuildWorkflowFromPromptDTO $command): GeneratedWorkflowResultDTO;
}
