<?php

use Tests\TestCase;

pest()->extend(TestCase::class)
    ->in('Feature');

pest()->extend(TestCase::class)
    ->in('../Modules/WorkflowEngine/Tests');
