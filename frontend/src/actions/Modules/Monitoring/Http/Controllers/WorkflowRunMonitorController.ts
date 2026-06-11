import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition, applyUrlDefaults } from './../../../../../wayfinder'
/**
* @see \Modules\Monitoring\Http\Controllers\WorkflowRunMonitorController::metrics
 * @see Modules/Monitoring/Http/Controllers/WorkflowRunMonitorController.php:22
 * @route '/api/v1/monitoring/metrics'
 */
export const metrics = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: metrics.url(options),
    method: 'get',
})

metrics.definition = {
    methods: ["get","head"],
    url: '/api/v1/monitoring/metrics',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \Modules\Monitoring\Http\Controllers\WorkflowRunMonitorController::metrics
 * @see Modules/Monitoring/Http/Controllers/WorkflowRunMonitorController.php:22
 * @route '/api/v1/monitoring/metrics'
 */
metrics.url = (options?: RouteQueryOptions) => {
    return metrics.definition.url + queryParams(options)
}

/**
* @see \Modules\Monitoring\Http\Controllers\WorkflowRunMonitorController::metrics
 * @see Modules/Monitoring/Http/Controllers/WorkflowRunMonitorController.php:22
 * @route '/api/v1/monitoring/metrics'
 */
metrics.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: metrics.url(options),
    method: 'get',
})
/**
* @see \Modules\Monitoring\Http\Controllers\WorkflowRunMonitorController::metrics
 * @see Modules/Monitoring/Http/Controllers/WorkflowRunMonitorController.php:22
 * @route '/api/v1/monitoring/metrics'
 */
metrics.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: metrics.url(options),
    method: 'head',
})

    /**
* @see \Modules\Monitoring\Http\Controllers\WorkflowRunMonitorController::metrics
 * @see Modules/Monitoring/Http/Controllers/WorkflowRunMonitorController.php:22
 * @route '/api/v1/monitoring/metrics'
 */
    const metricsForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: metrics.url(options),
        method: 'get',
    })

            /**
* @see \Modules\Monitoring\Http\Controllers\WorkflowRunMonitorController::metrics
 * @see Modules/Monitoring/Http/Controllers/WorkflowRunMonitorController.php:22
 * @route '/api/v1/monitoring/metrics'
 */
        metricsForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: metrics.url(options),
            method: 'get',
        })
            /**
* @see \Modules\Monitoring\Http\Controllers\WorkflowRunMonitorController::metrics
 * @see Modules/Monitoring/Http/Controllers/WorkflowRunMonitorController.php:22
 * @route '/api/v1/monitoring/metrics'
 */
        metricsForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: metrics.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    metrics.form = metricsForm
/**
* @see \Modules\Monitoring\Http\Controllers\WorkflowRunMonitorController::index
 * @see Modules/Monitoring/Http/Controllers/WorkflowRunMonitorController.php:32
 * @route '/api/v1/monitoring/runs'
 */
export const index = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
})

index.definition = {
    methods: ["get","head"],
    url: '/api/v1/monitoring/runs',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \Modules\Monitoring\Http\Controllers\WorkflowRunMonitorController::index
 * @see Modules/Monitoring/Http/Controllers/WorkflowRunMonitorController.php:32
 * @route '/api/v1/monitoring/runs'
 */
index.url = (options?: RouteQueryOptions) => {
    return index.definition.url + queryParams(options)
}

/**
* @see \Modules\Monitoring\Http\Controllers\WorkflowRunMonitorController::index
 * @see Modules/Monitoring/Http/Controllers/WorkflowRunMonitorController.php:32
 * @route '/api/v1/monitoring/runs'
 */
index.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
})
/**
* @see \Modules\Monitoring\Http\Controllers\WorkflowRunMonitorController::index
 * @see Modules/Monitoring/Http/Controllers/WorkflowRunMonitorController.php:32
 * @route '/api/v1/monitoring/runs'
 */
index.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: index.url(options),
    method: 'head',
})

    /**
* @see \Modules\Monitoring\Http\Controllers\WorkflowRunMonitorController::index
 * @see Modules/Monitoring/Http/Controllers/WorkflowRunMonitorController.php:32
 * @route '/api/v1/monitoring/runs'
 */
    const indexForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: index.url(options),
        method: 'get',
    })

            /**
* @see \Modules\Monitoring\Http\Controllers\WorkflowRunMonitorController::index
 * @see Modules/Monitoring/Http/Controllers/WorkflowRunMonitorController.php:32
 * @route '/api/v1/monitoring/runs'
 */
        indexForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: index.url(options),
            method: 'get',
        })
            /**
* @see \Modules\Monitoring\Http\Controllers\WorkflowRunMonitorController::index
 * @see Modules/Monitoring/Http/Controllers/WorkflowRunMonitorController.php:32
 * @route '/api/v1/monitoring/runs'
 */
        indexForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: index.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    index.form = indexForm
/**
* @see \Modules\Monitoring\Http\Controllers\WorkflowRunMonitorController::show
 * @see Modules/Monitoring/Http/Controllers/WorkflowRunMonitorController.php:54
 * @route '/api/v1/monitoring/runs/{run}'
 */
export const show = (args: { run: string | { id: string } } | [run: string | { id: string } ] | string | { id: string }, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: show.url(args, options),
    method: 'get',
})

show.definition = {
    methods: ["get","head"],
    url: '/api/v1/monitoring/runs/{run}',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \Modules\Monitoring\Http\Controllers\WorkflowRunMonitorController::show
 * @see Modules/Monitoring/Http/Controllers/WorkflowRunMonitorController.php:54
 * @route '/api/v1/monitoring/runs/{run}'
 */
show.url = (args: { run: string | { id: string } } | [run: string | { id: string } ] | string | { id: string }, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { run: args }
    }

            if (typeof args === 'object' && !Array.isArray(args) && 'id' in args) {
            args = { run: args.id }
        }
    
    if (Array.isArray(args)) {
        args = {
                    run: args[0],
                }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
                        run: typeof args.run === 'object'
                ? args.run.id
                : args.run,
                }

    return show.definition.url
            .replace('{run}', parsedArgs.run.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \Modules\Monitoring\Http\Controllers\WorkflowRunMonitorController::show
 * @see Modules/Monitoring/Http/Controllers/WorkflowRunMonitorController.php:54
 * @route '/api/v1/monitoring/runs/{run}'
 */
show.get = (args: { run: string | { id: string } } | [run: string | { id: string } ] | string | { id: string }, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: show.url(args, options),
    method: 'get',
})
/**
* @see \Modules\Monitoring\Http\Controllers\WorkflowRunMonitorController::show
 * @see Modules/Monitoring/Http/Controllers/WorkflowRunMonitorController.php:54
 * @route '/api/v1/monitoring/runs/{run}'
 */
show.head = (args: { run: string | { id: string } } | [run: string | { id: string } ] | string | { id: string }, options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: show.url(args, options),
    method: 'head',
})

    /**
* @see \Modules\Monitoring\Http\Controllers\WorkflowRunMonitorController::show
 * @see Modules/Monitoring/Http/Controllers/WorkflowRunMonitorController.php:54
 * @route '/api/v1/monitoring/runs/{run}'
 */
    const showForm = (args: { run: string | { id: string } } | [run: string | { id: string } ] | string | { id: string }, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: show.url(args, options),
        method: 'get',
    })

            /**
* @see \Modules\Monitoring\Http\Controllers\WorkflowRunMonitorController::show
 * @see Modules/Monitoring/Http/Controllers/WorkflowRunMonitorController.php:54
 * @route '/api/v1/monitoring/runs/{run}'
 */
        showForm.get = (args: { run: string | { id: string } } | [run: string | { id: string } ] | string | { id: string }, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: show.url(args, options),
            method: 'get',
        })
            /**
* @see \Modules\Monitoring\Http\Controllers\WorkflowRunMonitorController::show
 * @see Modules/Monitoring/Http/Controllers/WorkflowRunMonitorController.php:54
 * @route '/api/v1/monitoring/runs/{run}'
 */
        showForm.head = (args: { run: string | { id: string } } | [run: string | { id: string } ] | string | { id: string }, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: show.url(args, {
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    show.form = showForm
const WorkflowRunMonitorController = { metrics, index, show }

export default WorkflowRunMonitorController