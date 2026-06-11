<?php

declare(strict_types=1);

namespace Modules\WorkflowEngine\Contracts;

interface WorkflowParallelExecutorContract
{
    /**
     * Execute tasks concurrently and return results in input order.
     *
     * @template T
     *
     * @param  list<callable(): T>  $tasks
     * @return list<T>
     */
    public function run(array $tasks): array;
}
