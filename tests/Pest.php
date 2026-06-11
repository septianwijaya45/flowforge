<?php

use Tests\TestCase;

pest()->extend(TestCase::class)
    ->in('Feature');

pest()->extend(TestCase::class)
    ->in('../Modules/WorkflowEngine/Tests');

pest()->extend(TestCase::class)
    ->in('../Modules/Retry/Tests');

pest()->extend(TestCase::class)
    ->in('../Modules/Tenant/Tests');

pest()->extend(TestCase::class)
    ->in('../Modules/Auth/Tests');

pest()->extend(TestCase::class)
    ->in('../Modules/Workflow/Tests');

pest()->extend(TestCase::class)
    ->in('../Modules/WorkflowVersioning/Tests');

pest()->extend(TestCase::class)
    ->in('../Modules/Trigger/Tests');

pest()->extend(TestCase::class)
    ->in('../Modules/Monitoring/Tests');

pest()->extend(TestCase::class)
    ->in('../Modules/ExecutionLog/Tests');

pest()->extend(TestCase::class)
    ->in('../Modules/AI/Tests');
