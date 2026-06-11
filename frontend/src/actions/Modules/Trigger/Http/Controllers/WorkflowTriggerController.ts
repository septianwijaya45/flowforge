import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition, applyUrlDefaults } from './../../../../../wayfinder'
/**
* @see \Modules\Trigger\Http\Controllers\WorkflowTriggerController::index
 * @see Modules/Trigger/Http/Controllers/WorkflowTriggerController.php:25
 * @route '/api/v1/workflows/{workflow}/triggers'
 */
export const index = (args: { workflow: string | { id: string } } | [workflow: string | { id: string } ] | string | { id: string }, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(args, options),
    method: 'get',
})

index.definition = {
    methods: ["get","head"],
    url: '/api/v1/workflows/{workflow}/triggers',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \Modules\Trigger\Http\Controllers\WorkflowTriggerController::index
 * @see Modules/Trigger/Http/Controllers/WorkflowTriggerController.php:25
 * @route '/api/v1/workflows/{workflow}/triggers'
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
* @see \Modules\Trigger\Http\Controllers\WorkflowTriggerController::index
 * @see Modules/Trigger/Http/Controllers/WorkflowTriggerController.php:25
 * @route '/api/v1/workflows/{workflow}/triggers'
 */
index.get = (args: { workflow: string | { id: string } } | [workflow: string | { id: string } ] | string | { id: string }, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(args, options),
    method: 'get',
})
/**
* @see \Modules\Trigger\Http\Controllers\WorkflowTriggerController::index
 * @see Modules/Trigger/Http/Controllers/WorkflowTriggerController.php:25
 * @route '/api/v1/workflows/{workflow}/triggers'
 */
index.head = (args: { workflow: string | { id: string } } | [workflow: string | { id: string } ] | string | { id: string }, options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: index.url(args, options),
    method: 'head',
})

    /**
* @see \Modules\Trigger\Http\Controllers\WorkflowTriggerController::index
 * @see Modules/Trigger/Http/Controllers/WorkflowTriggerController.php:25
 * @route '/api/v1/workflows/{workflow}/triggers'
 */
    const indexForm = (args: { workflow: string | { id: string } } | [workflow: string | { id: string } ] | string | { id: string }, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: index.url(args, options),
        method: 'get',
    })

            /**
* @see \Modules\Trigger\Http\Controllers\WorkflowTriggerController::index
 * @see Modules/Trigger/Http/Controllers/WorkflowTriggerController.php:25
 * @route '/api/v1/workflows/{workflow}/triggers'
 */
        indexForm.get = (args: { workflow: string | { id: string } } | [workflow: string | { id: string } ] | string | { id: string }, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: index.url(args, options),
            method: 'get',
        })
            /**
* @see \Modules\Trigger\Http\Controllers\WorkflowTriggerController::index
 * @see Modules/Trigger/Http/Controllers/WorkflowTriggerController.php:25
 * @route '/api/v1/workflows/{workflow}/triggers'
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
* @see \Modules\Trigger\Http\Controllers\WorkflowTriggerController::store
 * @see Modules/Trigger/Http/Controllers/WorkflowTriggerController.php:34
 * @route '/api/v1/workflows/{workflow}/triggers'
 */
export const store = (args: { workflow: string | { id: string } } | [workflow: string | { id: string } ] | string | { id: string }, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: store.url(args, options),
    method: 'post',
})

store.definition = {
    methods: ["post"],
    url: '/api/v1/workflows/{workflow}/triggers',
} satisfies RouteDefinition<["post"]>

/**
* @see \Modules\Trigger\Http\Controllers\WorkflowTriggerController::store
 * @see Modules/Trigger/Http/Controllers/WorkflowTriggerController.php:34
 * @route '/api/v1/workflows/{workflow}/triggers'
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
* @see \Modules\Trigger\Http\Controllers\WorkflowTriggerController::store
 * @see Modules/Trigger/Http/Controllers/WorkflowTriggerController.php:34
 * @route '/api/v1/workflows/{workflow}/triggers'
 */
store.post = (args: { workflow: string | { id: string } } | [workflow: string | { id: string } ] | string | { id: string }, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: store.url(args, options),
    method: 'post',
})

    /**
* @see \Modules\Trigger\Http\Controllers\WorkflowTriggerController::store
 * @see Modules/Trigger/Http/Controllers/WorkflowTriggerController.php:34
 * @route '/api/v1/workflows/{workflow}/triggers'
 */
    const storeForm = (args: { workflow: string | { id: string } } | [workflow: string | { id: string } ] | string | { id: string }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: store.url(args, options),
        method: 'post',
    })

            /**
* @see \Modules\Trigger\Http\Controllers\WorkflowTriggerController::store
 * @see Modules/Trigger/Http/Controllers/WorkflowTriggerController.php:34
 * @route '/api/v1/workflows/{workflow}/triggers'
 */
        storeForm.post = (args: { workflow: string | { id: string } } | [workflow: string | { id: string } ] | string | { id: string }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: store.url(args, options),
            method: 'post',
        })
    
    store.form = storeForm
/**
* @see \Modules\Trigger\Http\Controllers\WorkflowTriggerController::update
 * @see Modules/Trigger/Http/Controllers/WorkflowTriggerController.php:49
 * @route '/api/v1/workflows/{workflow}/triggers/{trigger}'
 */
export const update = (args: { workflow: string | { id: string }, trigger: string | { id: string } } | [workflow: string | { id: string }, trigger: string | { id: string } ], options?: RouteQueryOptions): RouteDefinition<'put'> => ({
    url: update.url(args, options),
    method: 'put',
})

update.definition = {
    methods: ["put"],
    url: '/api/v1/workflows/{workflow}/triggers/{trigger}',
} satisfies RouteDefinition<["put"]>

/**
* @see \Modules\Trigger\Http\Controllers\WorkflowTriggerController::update
 * @see Modules/Trigger/Http/Controllers/WorkflowTriggerController.php:49
 * @route '/api/v1/workflows/{workflow}/triggers/{trigger}'
 */
update.url = (args: { workflow: string | { id: string }, trigger: string | { id: string } } | [workflow: string | { id: string }, trigger: string | { id: string } ], options?: RouteQueryOptions) => {
    if (Array.isArray(args)) {
        args = {
                    workflow: args[0],
                    trigger: args[1],
                }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
                        workflow: typeof args.workflow === 'object'
                ? args.workflow.id
                : args.workflow,
                                trigger: typeof args.trigger === 'object'
                ? args.trigger.id
                : args.trigger,
                }

    return update.definition.url
            .replace('{workflow}', parsedArgs.workflow.toString())
            .replace('{trigger}', parsedArgs.trigger.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \Modules\Trigger\Http\Controllers\WorkflowTriggerController::update
 * @see Modules/Trigger/Http/Controllers/WorkflowTriggerController.php:49
 * @route '/api/v1/workflows/{workflow}/triggers/{trigger}'
 */
update.put = (args: { workflow: string | { id: string }, trigger: string | { id: string } } | [workflow: string | { id: string }, trigger: string | { id: string } ], options?: RouteQueryOptions): RouteDefinition<'put'> => ({
    url: update.url(args, options),
    method: 'put',
})

    /**
* @see \Modules\Trigger\Http\Controllers\WorkflowTriggerController::update
 * @see Modules/Trigger/Http/Controllers/WorkflowTriggerController.php:49
 * @route '/api/v1/workflows/{workflow}/triggers/{trigger}'
 */
    const updateForm = (args: { workflow: string | { id: string }, trigger: string | { id: string } } | [workflow: string | { id: string }, trigger: string | { id: string } ], options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: update.url(args, {
                    [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                        _method: 'PUT',
                        ...(options?.query ?? options?.mergeQuery ?? {}),
                    }
                }),
        method: 'post',
    })

            /**
* @see \Modules\Trigger\Http\Controllers\WorkflowTriggerController::update
 * @see Modules/Trigger/Http/Controllers/WorkflowTriggerController.php:49
 * @route '/api/v1/workflows/{workflow}/triggers/{trigger}'
 */
        updateForm.put = (args: { workflow: string | { id: string }, trigger: string | { id: string } } | [workflow: string | { id: string }, trigger: string | { id: string } ], options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: update.url(args, {
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'PUT',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'post',
        })
    
    update.form = updateForm
/**
* @see \Modules\Trigger\Http\Controllers\WorkflowTriggerController::destroy
 * @see Modules/Trigger/Http/Controllers/WorkflowTriggerController.php:70
 * @route '/api/v1/workflows/{workflow}/triggers/{trigger}'
 */
export const destroy = (args: { workflow: string | { id: string }, trigger: string | { id: string } } | [workflow: string | { id: string }, trigger: string | { id: string } ], options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: destroy.url(args, options),
    method: 'delete',
})

destroy.definition = {
    methods: ["delete"],
    url: '/api/v1/workflows/{workflow}/triggers/{trigger}',
} satisfies RouteDefinition<["delete"]>

/**
* @see \Modules\Trigger\Http\Controllers\WorkflowTriggerController::destroy
 * @see Modules/Trigger/Http/Controllers/WorkflowTriggerController.php:70
 * @route '/api/v1/workflows/{workflow}/triggers/{trigger}'
 */
destroy.url = (args: { workflow: string | { id: string }, trigger: string | { id: string } } | [workflow: string | { id: string }, trigger: string | { id: string } ], options?: RouteQueryOptions) => {
    if (Array.isArray(args)) {
        args = {
                    workflow: args[0],
                    trigger: args[1],
                }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
                        workflow: typeof args.workflow === 'object'
                ? args.workflow.id
                : args.workflow,
                                trigger: typeof args.trigger === 'object'
                ? args.trigger.id
                : args.trigger,
                }

    return destroy.definition.url
            .replace('{workflow}', parsedArgs.workflow.toString())
            .replace('{trigger}', parsedArgs.trigger.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \Modules\Trigger\Http\Controllers\WorkflowTriggerController::destroy
 * @see Modules/Trigger/Http/Controllers/WorkflowTriggerController.php:70
 * @route '/api/v1/workflows/{workflow}/triggers/{trigger}'
 */
destroy.delete = (args: { workflow: string | { id: string }, trigger: string | { id: string } } | [workflow: string | { id: string }, trigger: string | { id: string } ], options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: destroy.url(args, options),
    method: 'delete',
})

    /**
* @see \Modules\Trigger\Http\Controllers\WorkflowTriggerController::destroy
 * @see Modules/Trigger/Http/Controllers/WorkflowTriggerController.php:70
 * @route '/api/v1/workflows/{workflow}/triggers/{trigger}'
 */
    const destroyForm = (args: { workflow: string | { id: string }, trigger: string | { id: string } } | [workflow: string | { id: string }, trigger: string | { id: string } ], options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: destroy.url(args, {
                    [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                        _method: 'DELETE',
                        ...(options?.query ?? options?.mergeQuery ?? {}),
                    }
                }),
        method: 'post',
    })

            /**
* @see \Modules\Trigger\Http\Controllers\WorkflowTriggerController::destroy
 * @see Modules/Trigger/Http/Controllers/WorkflowTriggerController.php:70
 * @route '/api/v1/workflows/{workflow}/triggers/{trigger}'
 */
        destroyForm.delete = (args: { workflow: string | { id: string }, trigger: string | { id: string } } | [workflow: string | { id: string }, trigger: string | { id: string } ], options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: destroy.url(args, {
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'DELETE',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'post',
        })
    
    destroy.form = destroyForm
const WorkflowTriggerController = { index, store, update, destroy }

export default WorkflowTriggerController