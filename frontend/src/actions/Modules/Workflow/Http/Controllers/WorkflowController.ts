import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition, applyUrlDefaults } from './../../../../../wayfinder'
/**
* @see \Modules\Workflow\Http\Controllers\WorkflowController::index
 * @see Modules/Workflow/Http/Controllers/WorkflowController.php:24
 * @route '/api/v1/workflows'
 */
export const index = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
})

index.definition = {
    methods: ["get","head"],
    url: '/api/v1/workflows',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \Modules\Workflow\Http\Controllers\WorkflowController::index
 * @see Modules/Workflow/Http/Controllers/WorkflowController.php:24
 * @route '/api/v1/workflows'
 */
index.url = (options?: RouteQueryOptions) => {
    return index.definition.url + queryParams(options)
}

/**
* @see \Modules\Workflow\Http\Controllers\WorkflowController::index
 * @see Modules/Workflow/Http/Controllers/WorkflowController.php:24
 * @route '/api/v1/workflows'
 */
index.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
})
/**
* @see \Modules\Workflow\Http\Controllers\WorkflowController::index
 * @see Modules/Workflow/Http/Controllers/WorkflowController.php:24
 * @route '/api/v1/workflows'
 */
index.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: index.url(options),
    method: 'head',
})

    /**
* @see \Modules\Workflow\Http\Controllers\WorkflowController::index
 * @see Modules/Workflow/Http/Controllers/WorkflowController.php:24
 * @route '/api/v1/workflows'
 */
    const indexForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: index.url(options),
        method: 'get',
    })

            /**
* @see \Modules\Workflow\Http\Controllers\WorkflowController::index
 * @see Modules/Workflow/Http/Controllers/WorkflowController.php:24
 * @route '/api/v1/workflows'
 */
        indexForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: index.url(options),
            method: 'get',
        })
            /**
* @see \Modules\Workflow\Http\Controllers\WorkflowController::index
 * @see Modules/Workflow/Http/Controllers/WorkflowController.php:24
 * @route '/api/v1/workflows'
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
* @see \Modules\Workflow\Http\Controllers\WorkflowController::show
 * @see Modules/Workflow/Http/Controllers/WorkflowController.php:39
 * @route '/api/v1/workflows/{workflow}'
 */
export const show = (args: { workflow: string | { id: string } } | [workflow: string | { id: string } ] | string | { id: string }, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: show.url(args, options),
    method: 'get',
})

show.definition = {
    methods: ["get","head"],
    url: '/api/v1/workflows/{workflow}',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \Modules\Workflow\Http\Controllers\WorkflowController::show
 * @see Modules/Workflow/Http/Controllers/WorkflowController.php:39
 * @route '/api/v1/workflows/{workflow}'
 */
show.url = (args: { workflow: string | { id: string } } | [workflow: string | { id: string } ] | string | { id: string }, options?: RouteQueryOptions) => {
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

    return show.definition.url
            .replace('{workflow}', parsedArgs.workflow.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \Modules\Workflow\Http\Controllers\WorkflowController::show
 * @see Modules/Workflow/Http/Controllers/WorkflowController.php:39
 * @route '/api/v1/workflows/{workflow}'
 */
show.get = (args: { workflow: string | { id: string } } | [workflow: string | { id: string } ] | string | { id: string }, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: show.url(args, options),
    method: 'get',
})
/**
* @see \Modules\Workflow\Http\Controllers\WorkflowController::show
 * @see Modules/Workflow/Http/Controllers/WorkflowController.php:39
 * @route '/api/v1/workflows/{workflow}'
 */
show.head = (args: { workflow: string | { id: string } } | [workflow: string | { id: string } ] | string | { id: string }, options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: show.url(args, options),
    method: 'head',
})

    /**
* @see \Modules\Workflow\Http\Controllers\WorkflowController::show
 * @see Modules/Workflow/Http/Controllers/WorkflowController.php:39
 * @route '/api/v1/workflows/{workflow}'
 */
    const showForm = (args: { workflow: string | { id: string } } | [workflow: string | { id: string } ] | string | { id: string }, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: show.url(args, options),
        method: 'get',
    })

            /**
* @see \Modules\Workflow\Http\Controllers\WorkflowController::show
 * @see Modules/Workflow/Http/Controllers/WorkflowController.php:39
 * @route '/api/v1/workflows/{workflow}'
 */
        showForm.get = (args: { workflow: string | { id: string } } | [workflow: string | { id: string } ] | string | { id: string }, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: show.url(args, options),
            method: 'get',
        })
            /**
* @see \Modules\Workflow\Http\Controllers\WorkflowController::show
 * @see Modules/Workflow/Http/Controllers/WorkflowController.php:39
 * @route '/api/v1/workflows/{workflow}'
 */
        showForm.head = (args: { workflow: string | { id: string } } | [workflow: string | { id: string } ] | string | { id: string }, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: show.url(args, {
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    show.form = showForm
/**
* @see \Modules\Workflow\Http\Controllers\WorkflowController::store
 * @see Modules/Workflow/Http/Controllers/WorkflowController.php:47
 * @route '/api/v1/workflows'
 */
export const store = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: store.url(options),
    method: 'post',
})

store.definition = {
    methods: ["post"],
    url: '/api/v1/workflows',
} satisfies RouteDefinition<["post"]>

/**
* @see \Modules\Workflow\Http\Controllers\WorkflowController::store
 * @see Modules/Workflow/Http/Controllers/WorkflowController.php:47
 * @route '/api/v1/workflows'
 */
store.url = (options?: RouteQueryOptions) => {
    return store.definition.url + queryParams(options)
}

/**
* @see \Modules\Workflow\Http\Controllers\WorkflowController::store
 * @see Modules/Workflow/Http/Controllers/WorkflowController.php:47
 * @route '/api/v1/workflows'
 */
store.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: store.url(options),
    method: 'post',
})

    /**
* @see \Modules\Workflow\Http\Controllers\WorkflowController::store
 * @see Modules/Workflow/Http/Controllers/WorkflowController.php:47
 * @route '/api/v1/workflows'
 */
    const storeForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: store.url(options),
        method: 'post',
    })

            /**
* @see \Modules\Workflow\Http\Controllers\WorkflowController::store
 * @see Modules/Workflow/Http/Controllers/WorkflowController.php:47
 * @route '/api/v1/workflows'
 */
        storeForm.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: store.url(options),
            method: 'post',
        })
    
    store.form = storeForm
/**
* @see \Modules\Workflow\Http\Controllers\WorkflowController::update
 * @see Modules/Workflow/Http/Controllers/WorkflowController.php:58
 * @route '/api/v1/workflows/{workflow}'
 */
export const update = (args: { workflow: string | { id: string } } | [workflow: string | { id: string } ] | string | { id: string }, options?: RouteQueryOptions): RouteDefinition<'put'> => ({
    url: update.url(args, options),
    method: 'put',
})

update.definition = {
    methods: ["put"],
    url: '/api/v1/workflows/{workflow}',
} satisfies RouteDefinition<["put"]>

/**
* @see \Modules\Workflow\Http\Controllers\WorkflowController::update
 * @see Modules/Workflow/Http/Controllers/WorkflowController.php:58
 * @route '/api/v1/workflows/{workflow}'
 */
update.url = (args: { workflow: string | { id: string } } | [workflow: string | { id: string } ] | string | { id: string }, options?: RouteQueryOptions) => {
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

    return update.definition.url
            .replace('{workflow}', parsedArgs.workflow.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \Modules\Workflow\Http\Controllers\WorkflowController::update
 * @see Modules/Workflow/Http/Controllers/WorkflowController.php:58
 * @route '/api/v1/workflows/{workflow}'
 */
update.put = (args: { workflow: string | { id: string } } | [workflow: string | { id: string } ] | string | { id: string }, options?: RouteQueryOptions): RouteDefinition<'put'> => ({
    url: update.url(args, options),
    method: 'put',
})

    /**
* @see \Modules\Workflow\Http\Controllers\WorkflowController::update
 * @see Modules/Workflow/Http/Controllers/WorkflowController.php:58
 * @route '/api/v1/workflows/{workflow}'
 */
    const updateForm = (args: { workflow: string | { id: string } } | [workflow: string | { id: string } ] | string | { id: string }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: update.url(args, {
                    [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                        _method: 'PUT',
                        ...(options?.query ?? options?.mergeQuery ?? {}),
                    }
                }),
        method: 'post',
    })

            /**
* @see \Modules\Workflow\Http\Controllers\WorkflowController::update
 * @see Modules/Workflow/Http/Controllers/WorkflowController.php:58
 * @route '/api/v1/workflows/{workflow}'
 */
        updateForm.put = (args: { workflow: string | { id: string } } | [workflow: string | { id: string } ] | string | { id: string }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
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
* @see \Modules\Workflow\Http\Controllers\WorkflowController::destroy
 * @see Modules/Workflow/Http/Controllers/WorkflowController.php:68
 * @route '/api/v1/workflows/{workflow}'
 */
export const destroy = (args: { workflow: string | { id: string } } | [workflow: string | { id: string } ] | string | { id: string }, options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: destroy.url(args, options),
    method: 'delete',
})

destroy.definition = {
    methods: ["delete"],
    url: '/api/v1/workflows/{workflow}',
} satisfies RouteDefinition<["delete"]>

/**
* @see \Modules\Workflow\Http\Controllers\WorkflowController::destroy
 * @see Modules/Workflow/Http/Controllers/WorkflowController.php:68
 * @route '/api/v1/workflows/{workflow}'
 */
destroy.url = (args: { workflow: string | { id: string } } | [workflow: string | { id: string } ] | string | { id: string }, options?: RouteQueryOptions) => {
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

    return destroy.definition.url
            .replace('{workflow}', parsedArgs.workflow.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \Modules\Workflow\Http\Controllers\WorkflowController::destroy
 * @see Modules/Workflow/Http/Controllers/WorkflowController.php:68
 * @route '/api/v1/workflows/{workflow}'
 */
destroy.delete = (args: { workflow: string | { id: string } } | [workflow: string | { id: string } ] | string | { id: string }, options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: destroy.url(args, options),
    method: 'delete',
})

    /**
* @see \Modules\Workflow\Http\Controllers\WorkflowController::destroy
 * @see Modules/Workflow/Http/Controllers/WorkflowController.php:68
 * @route '/api/v1/workflows/{workflow}'
 */
    const destroyForm = (args: { workflow: string | { id: string } } | [workflow: string | { id: string } ] | string | { id: string }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: destroy.url(args, {
                    [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                        _method: 'DELETE',
                        ...(options?.query ?? options?.mergeQuery ?? {}),
                    }
                }),
        method: 'post',
    })

            /**
* @see \Modules\Workflow\Http\Controllers\WorkflowController::destroy
 * @see Modules/Workflow/Http/Controllers/WorkflowController.php:68
 * @route '/api/v1/workflows/{workflow}'
 */
        destroyForm.delete = (args: { workflow: string | { id: string } } | [workflow: string | { id: string } ] | string | { id: string }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: destroy.url(args, {
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'DELETE',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'post',
        })
    
    destroy.form = destroyForm
const WorkflowController = { index, show, store, update, destroy }

export default WorkflowController