import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition, applyUrlDefaults } from './../../../../../wayfinder'
/**
* @see \Modules\WorkflowVersioning\Http\Controllers\WorkflowVersionController::current
 * @see Modules/WorkflowVersioning/Http/Controllers/WorkflowVersionController.php:27
 * @route '/api/v1/workflows/{workflow}/versions/current'
 */
export const current = (args: { workflow: string | { id: string } } | [workflow: string | { id: string } ] | string | { id: string }, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: current.url(args, options),
    method: 'get',
})

current.definition = {
    methods: ["get","head"],
    url: '/api/v1/workflows/{workflow}/versions/current',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \Modules\WorkflowVersioning\Http\Controllers\WorkflowVersionController::current
 * @see Modules/WorkflowVersioning/Http/Controllers/WorkflowVersionController.php:27
 * @route '/api/v1/workflows/{workflow}/versions/current'
 */
current.url = (args: { workflow: string | { id: string } } | [workflow: string | { id: string } ] | string | { id: string }, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { workflow: args }
    }

            if (typeof args === 'object' && !Array.isArray(args) && 'id' in args) {
            args = { workflow: args.id }
        }
    
    if (Array.isArray(args)) {
        args = {
                    workflow: args[0],
                }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
                        workflow: typeof args.workflow === 'object'
                ? args.workflow.id
                : args.workflow,
                }

    return current.definition.url
            .replace('{workflow}', parsedArgs.workflow.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \Modules\WorkflowVersioning\Http\Controllers\WorkflowVersionController::current
 * @see Modules/WorkflowVersioning/Http/Controllers/WorkflowVersionController.php:27
 * @route '/api/v1/workflows/{workflow}/versions/current'
 */
current.get = (args: { workflow: string | { id: string } } | [workflow: string | { id: string } ] | string | { id: string }, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: current.url(args, options),
    method: 'get',
})
/**
* @see \Modules\WorkflowVersioning\Http\Controllers\WorkflowVersionController::current
 * @see Modules/WorkflowVersioning/Http/Controllers/WorkflowVersionController.php:27
 * @route '/api/v1/workflows/{workflow}/versions/current'
 */
current.head = (args: { workflow: string | { id: string } } | [workflow: string | { id: string } ] | string | { id: string }, options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: current.url(args, options),
    method: 'head',
})

    /**
* @see \Modules\WorkflowVersioning\Http\Controllers\WorkflowVersionController::current
 * @see Modules/WorkflowVersioning/Http/Controllers/WorkflowVersionController.php:27
 * @route '/api/v1/workflows/{workflow}/versions/current'
 */
    const currentForm = (args: { workflow: string | { id: string } } | [workflow: string | { id: string } ] | string | { id: string }, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: current.url(args, options),
        method: 'get',
    })

            /**
* @see \Modules\WorkflowVersioning\Http\Controllers\WorkflowVersionController::current
 * @see Modules/WorkflowVersioning/Http/Controllers/WorkflowVersionController.php:27
 * @route '/api/v1/workflows/{workflow}/versions/current'
 */
        currentForm.get = (args: { workflow: string | { id: string } } | [workflow: string | { id: string } ] | string | { id: string }, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: current.url(args, options),
            method: 'get',
        })
            /**
* @see \Modules\WorkflowVersioning\Http\Controllers\WorkflowVersionController::current
 * @see Modules/WorkflowVersioning/Http/Controllers/WorkflowVersionController.php:27
 * @route '/api/v1/workflows/{workflow}/versions/current'
 */
        currentForm.head = (args: { workflow: string | { id: string } } | [workflow: string | { id: string } ] | string | { id: string }, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: current.url(args, {
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    current.form = currentForm
/**
* @see \Modules\WorkflowVersioning\Http\Controllers\WorkflowVersionController::index
 * @see Modules/WorkflowVersioning/Http/Controllers/WorkflowVersionController.php:41
 * @route '/api/v1/workflows/{workflow}/versions'
 */
export const index = (args: { workflow: string | { id: string } } | [workflow: string | { id: string } ] | string | { id: string }, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(args, options),
    method: 'get',
})

index.definition = {
    methods: ["get","head"],
    url: '/api/v1/workflows/{workflow}/versions',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \Modules\WorkflowVersioning\Http\Controllers\WorkflowVersionController::index
 * @see Modules/WorkflowVersioning/Http/Controllers/WorkflowVersionController.php:41
 * @route '/api/v1/workflows/{workflow}/versions'
 */
index.url = (args: { workflow: string | { id: string } } | [workflow: string | { id: string } ] | string | { id: string }, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { workflow: args }
    }

            if (typeof args === 'object' && !Array.isArray(args) && 'id' in args) {
            args = { workflow: args.id }
        }
    
    if (Array.isArray(args)) {
        args = {
                    workflow: args[0],
                }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
                        workflow: typeof args.workflow === 'object'
                ? args.workflow.id
                : args.workflow,
                }

    return index.definition.url
            .replace('{workflow}', parsedArgs.workflow.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \Modules\WorkflowVersioning\Http\Controllers\WorkflowVersionController::index
 * @see Modules/WorkflowVersioning/Http/Controllers/WorkflowVersionController.php:41
 * @route '/api/v1/workflows/{workflow}/versions'
 */
index.get = (args: { workflow: string | { id: string } } | [workflow: string | { id: string } ] | string | { id: string }, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(args, options),
    method: 'get',
})
/**
* @see \Modules\WorkflowVersioning\Http\Controllers\WorkflowVersionController::index
 * @see Modules/WorkflowVersioning/Http/Controllers/WorkflowVersionController.php:41
 * @route '/api/v1/workflows/{workflow}/versions'
 */
index.head = (args: { workflow: string | { id: string } } | [workflow: string | { id: string } ] | string | { id: string }, options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: index.url(args, options),
    method: 'head',
})

    /**
* @see \Modules\WorkflowVersioning\Http\Controllers\WorkflowVersionController::index
 * @see Modules/WorkflowVersioning/Http/Controllers/WorkflowVersionController.php:41
 * @route '/api/v1/workflows/{workflow}/versions'
 */
    const indexForm = (args: { workflow: string | { id: string } } | [workflow: string | { id: string } ] | string | { id: string }, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: index.url(args, options),
        method: 'get',
    })

            /**
* @see \Modules\WorkflowVersioning\Http\Controllers\WorkflowVersionController::index
 * @see Modules/WorkflowVersioning/Http/Controllers/WorkflowVersionController.php:41
 * @route '/api/v1/workflows/{workflow}/versions'
 */
        indexForm.get = (args: { workflow: string | { id: string } } | [workflow: string | { id: string } ] | string | { id: string }, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: index.url(args, options),
            method: 'get',
        })
            /**
* @see \Modules\WorkflowVersioning\Http\Controllers\WorkflowVersionController::index
 * @see Modules/WorkflowVersioning/Http/Controllers/WorkflowVersionController.php:41
 * @route '/api/v1/workflows/{workflow}/versions'
 */
        indexForm.head = (args: { workflow: string | { id: string } } | [workflow: string | { id: string } ] | string | { id: string }, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: index.url(args, {
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    index.form = indexForm
/**
* @see \Modules\WorkflowVersioning\Http\Controllers\WorkflowVersionController::store
 * @see Modules/WorkflowVersioning/Http/Controllers/WorkflowVersionController.php:63
 * @route '/api/v1/workflows/{workflow}/versions'
 */
export const store = (args: { workflow: string | { id: string } } | [workflow: string | { id: string } ] | string | { id: string }, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: store.url(args, options),
    method: 'post',
})

store.definition = {
    methods: ["post"],
    url: '/api/v1/workflows/{workflow}/versions',
} satisfies RouteDefinition<["post"]>

/**
* @see \Modules\WorkflowVersioning\Http\Controllers\WorkflowVersionController::store
 * @see Modules/WorkflowVersioning/Http/Controllers/WorkflowVersionController.php:63
 * @route '/api/v1/workflows/{workflow}/versions'
 */
store.url = (args: { workflow: string | { id: string } } | [workflow: string | { id: string } ] | string | { id: string }, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { workflow: args }
    }

            if (typeof args === 'object' && !Array.isArray(args) && 'id' in args) {
            args = { workflow: args.id }
        }
    
    if (Array.isArray(args)) {
        args = {
                    workflow: args[0],
                }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
                        workflow: typeof args.workflow === 'object'
                ? args.workflow.id
                : args.workflow,
                }

    return store.definition.url
            .replace('{workflow}', parsedArgs.workflow.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \Modules\WorkflowVersioning\Http\Controllers\WorkflowVersionController::store
 * @see Modules/WorkflowVersioning/Http/Controllers/WorkflowVersionController.php:63
 * @route '/api/v1/workflows/{workflow}/versions'
 */
store.post = (args: { workflow: string | { id: string } } | [workflow: string | { id: string } ] | string | { id: string }, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: store.url(args, options),
    method: 'post',
})

    /**
* @see \Modules\WorkflowVersioning\Http\Controllers\WorkflowVersionController::store
 * @see Modules/WorkflowVersioning/Http/Controllers/WorkflowVersionController.php:63
 * @route '/api/v1/workflows/{workflow}/versions'
 */
    const storeForm = (args: { workflow: string | { id: string } } | [workflow: string | { id: string } ] | string | { id: string }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: store.url(args, options),
        method: 'post',
    })

            /**
* @see \Modules\WorkflowVersioning\Http\Controllers\WorkflowVersionController::store
 * @see Modules/WorkflowVersioning/Http/Controllers/WorkflowVersionController.php:63
 * @route '/api/v1/workflows/{workflow}/versions'
 */
        storeForm.post = (args: { workflow: string | { id: string } } | [workflow: string | { id: string } ] | string | { id: string }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: store.url(args, options),
            method: 'post',
        })
    
    store.form = storeForm
/**
* @see \Modules\WorkflowVersioning\Http\Controllers\WorkflowVersionController::rollback
 * @see Modules/WorkflowVersioning/Http/Controllers/WorkflowVersionController.php:78
 * @route '/api/v1/workflows/{workflow}/versions/{version}/rollback'
 */
export const rollback = (args: { workflow: string | { id: string }, version: string | { id: string } } | [workflow: string | { id: string }, version: string | { id: string } ], options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: rollback.url(args, options),
    method: 'post',
})

rollback.definition = {
    methods: ["post"],
    url: '/api/v1/workflows/{workflow}/versions/{version}/rollback',
} satisfies RouteDefinition<["post"]>

/**
* @see \Modules\WorkflowVersioning\Http\Controllers\WorkflowVersionController::rollback
 * @see Modules/WorkflowVersioning/Http/Controllers/WorkflowVersionController.php:78
 * @route '/api/v1/workflows/{workflow}/versions/{version}/rollback'
 */
rollback.url = (args: { workflow: string | { id: string }, version: string | { id: string } } | [workflow: string | { id: string }, version: string | { id: string } ], options?: RouteQueryOptions) => {
    if (Array.isArray(args)) {
        args = {
                    workflow: args[0],
                    version: args[1],
                }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
                        workflow: typeof args.workflow === 'object'
                ? args.workflow.id
                : args.workflow,
                                version: typeof args.version === 'object'
                ? args.version.id
                : args.version,
                }

    return rollback.definition.url
            .replace('{workflow}', parsedArgs.workflow.toString())
            .replace('{version}', parsedArgs.version.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \Modules\WorkflowVersioning\Http\Controllers\WorkflowVersionController::rollback
 * @see Modules/WorkflowVersioning/Http/Controllers/WorkflowVersionController.php:78
 * @route '/api/v1/workflows/{workflow}/versions/{version}/rollback'
 */
rollback.post = (args: { workflow: string | { id: string }, version: string | { id: string } } | [workflow: string | { id: string }, version: string | { id: string } ], options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: rollback.url(args, options),
    method: 'post',
})

    /**
* @see \Modules\WorkflowVersioning\Http\Controllers\WorkflowVersionController::rollback
 * @see Modules/WorkflowVersioning/Http/Controllers/WorkflowVersionController.php:78
 * @route '/api/v1/workflows/{workflow}/versions/{version}/rollback'
 */
    const rollbackForm = (args: { workflow: string | { id: string }, version: string | { id: string } } | [workflow: string | { id: string }, version: string | { id: string } ], options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: rollback.url(args, options),
        method: 'post',
    })

            /**
* @see \Modules\WorkflowVersioning\Http\Controllers\WorkflowVersionController::rollback
 * @see Modules/WorkflowVersioning/Http/Controllers/WorkflowVersionController.php:78
 * @route '/api/v1/workflows/{workflow}/versions/{version}/rollback'
 */
        rollbackForm.post = (args: { workflow: string | { id: string }, version: string | { id: string } } | [workflow: string | { id: string }, version: string | { id: string } ], options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: rollback.url(args, options),
            method: 'post',
        })
    
    rollback.form = rollbackForm
const WorkflowVersionController = { current, index, store, rollback }

export default WorkflowVersionController