<?php

declare(strict_types=1);

namespace Modules\WorkflowEngine\Exceptions;

class CycleDetectedException extends WorkflowValidationException
{
    /**
     * @param  list<string>  $cyclePath
     */
    public function __construct(public readonly array $cyclePath)
    {
        parent::__construct(
            message: 'A cycle was detected in the workflow graph.',
            errors: [
                'cycle' => $cyclePath,
            ],
        );
    }
}
