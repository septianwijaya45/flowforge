<?php

declare(strict_types=1);

return [

    /*
    |--------------------------------------------------------------------------
    | Database Connection
    |--------------------------------------------------------------------------
    |
    | Connection name defined in config/database.php for append-only logs.
    |
    */
    'connection' => env('EXECUTION_LOG_DB_CONNECTION', 'execution_logs'),

    /*
    |--------------------------------------------------------------------------
    | Write Batching
    |--------------------------------------------------------------------------
    |
    | Buffered logs are flushed when the buffer reaches this size or at the
    | end of the request lifecycle.
    |
    */
    'write_batch_size' => (int) env('EXECUTION_LOG_WRITE_BATCH_SIZE', 100),

    /*
    |--------------------------------------------------------------------------
    | Retention
    |--------------------------------------------------------------------------
    |
    | Logs older than retention_days are eligible for purge. Purge runs in
    | batches to avoid long-running locks on high-volume tables.
    |
    */
    'retention_days' => (int) env('EXECUTION_LOG_RETENTION_DAYS', 30),

    'purge_batch_size' => (int) env('EXECUTION_LOG_PURGE_BATCH_SIZE', 1000),

];
