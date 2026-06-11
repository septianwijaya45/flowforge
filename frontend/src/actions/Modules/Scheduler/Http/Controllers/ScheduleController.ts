import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition, applyUrlDefaults } from './../../../../../wayfinder'
/**
* @see \Modules\Scheduler\Http\Controllers\ScheduleController::index
 * @see Modules/Scheduler/Http/Controllers/ScheduleController.php:24
 * @route '/api/v1/schedules'
 */
export const index = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
})

index.definition = {
    methods: ["get","head"],
    url: '/api/v1/schedules',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \Modules\Scheduler\Http\Controllers\ScheduleController::index
 * @see Modules/Scheduler/Http/Controllers/ScheduleController.php:24
 * @route '/api/v1/schedules'
 */
index.url = (options?: RouteQueryOptions) => {
    return index.definition.url + queryParams(options)
}

/**
* @see \Modules\Scheduler\Http\Controllers\ScheduleController::index
 * @see Modules/Scheduler/Http/Controllers/ScheduleController.php:24
 * @route '/api/v1/schedules'
 */
index.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
})
/**
* @see \Modules\Scheduler\Http\Controllers\ScheduleController::index
 * @see Modules/Scheduler/Http/Controllers/ScheduleController.php:24
 * @route '/api/v1/schedules'
 */
index.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: index.url(options),
    method: 'head',
})

    /**
* @see \Modules\Scheduler\Http\Controllers\ScheduleController::index
 * @see Modules/Scheduler/Http/Controllers/ScheduleController.php:24
 * @route '/api/v1/schedules'
 */
    const indexForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: index.url(options),
        method: 'get',
    })

            /**
* @see \Modules\Scheduler\Http\Controllers\ScheduleController::index
 * @see Modules/Scheduler/Http/Controllers/ScheduleController.php:24
 * @route '/api/v1/schedules'
 */
        indexForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: index.url(options),
            method: 'get',
        })
            /**
* @see \Modules\Scheduler\Http\Controllers\ScheduleController::index
 * @see Modules/Scheduler/Http/Controllers/ScheduleController.php:24
 * @route '/api/v1/schedules'
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
* @see \Modules\Scheduler\Http\Controllers\ScheduleController::show
 * @see Modules/Scheduler/Http/Controllers/ScheduleController.php:33
 * @route '/api/v1/schedules/{schedule}'
 */
export const show = (args: { schedule: string | { id: string } } | [schedule: string | { id: string } ] | string | { id: string }, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: show.url(args, options),
    method: 'get',
})

show.definition = {
    methods: ["get","head"],
    url: '/api/v1/schedules/{schedule}',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \Modules\Scheduler\Http\Controllers\ScheduleController::show
 * @see Modules/Scheduler/Http/Controllers/ScheduleController.php:33
 * @route '/api/v1/schedules/{schedule}'
 */
show.url = (args: { schedule: string | { id: string } } | [schedule: string | { id: string } ] | string | { id: string }, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { schedule: args }
    }

            if (typeof args === 'object' && !Array.isArray(args) && 'id' in args) {
            args = { schedule: args.id }
        }
    
    if (Array.isArray(args)) {
        args = {
                    schedule: args[0],
                }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
                        schedule: typeof args.schedule === 'object'
                ? args.schedule.id
                : args.schedule,
                }

    return show.definition.url
            .replace('{schedule}', parsedArgs.schedule.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \Modules\Scheduler\Http\Controllers\ScheduleController::show
 * @see Modules/Scheduler/Http/Controllers/ScheduleController.php:33
 * @route '/api/v1/schedules/{schedule}'
 */
show.get = (args: { schedule: string | { id: string } } | [schedule: string | { id: string } ] | string | { id: string }, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: show.url(args, options),
    method: 'get',
})
/**
* @see \Modules\Scheduler\Http\Controllers\ScheduleController::show
 * @see Modules/Scheduler/Http/Controllers/ScheduleController.php:33
 * @route '/api/v1/schedules/{schedule}'
 */
show.head = (args: { schedule: string | { id: string } } | [schedule: string | { id: string } ] | string | { id: string }, options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: show.url(args, options),
    method: 'head',
})

    /**
* @see \Modules\Scheduler\Http\Controllers\ScheduleController::show
 * @see Modules/Scheduler/Http/Controllers/ScheduleController.php:33
 * @route '/api/v1/schedules/{schedule}'
 */
    const showForm = (args: { schedule: string | { id: string } } | [schedule: string | { id: string } ] | string | { id: string }, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: show.url(args, options),
        method: 'get',
    })

            /**
* @see \Modules\Scheduler\Http\Controllers\ScheduleController::show
 * @see Modules/Scheduler/Http/Controllers/ScheduleController.php:33
 * @route '/api/v1/schedules/{schedule}'
 */
        showForm.get = (args: { schedule: string | { id: string } } | [schedule: string | { id: string } ] | string | { id: string }, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: show.url(args, options),
            method: 'get',
        })
            /**
* @see \Modules\Scheduler\Http\Controllers\ScheduleController::show
 * @see Modules/Scheduler/Http/Controllers/ScheduleController.php:33
 * @route '/api/v1/schedules/{schedule}'
 */
        showForm.head = (args: { schedule: string | { id: string } } | [schedule: string | { id: string } ] | string | { id: string }, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
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
* @see \Modules\Scheduler\Http\Controllers\ScheduleController::store
 * @see Modules/Scheduler/Http/Controllers/ScheduleController.php:41
 * @route '/api/v1/schedules'
 */
export const store = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: store.url(options),
    method: 'post',
})

store.definition = {
    methods: ["post"],
    url: '/api/v1/schedules',
} satisfies RouteDefinition<["post"]>

/**
* @see \Modules\Scheduler\Http\Controllers\ScheduleController::store
 * @see Modules/Scheduler/Http/Controllers/ScheduleController.php:41
 * @route '/api/v1/schedules'
 */
store.url = (options?: RouteQueryOptions) => {
    return store.definition.url + queryParams(options)
}

/**
* @see \Modules\Scheduler\Http\Controllers\ScheduleController::store
 * @see Modules/Scheduler/Http/Controllers/ScheduleController.php:41
 * @route '/api/v1/schedules'
 */
store.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: store.url(options),
    method: 'post',
})

    /**
* @see \Modules\Scheduler\Http\Controllers\ScheduleController::store
 * @see Modules/Scheduler/Http/Controllers/ScheduleController.php:41
 * @route '/api/v1/schedules'
 */
    const storeForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: store.url(options),
        method: 'post',
    })

            /**
* @see \Modules\Scheduler\Http\Controllers\ScheduleController::store
 * @see Modules/Scheduler/Http/Controllers/ScheduleController.php:41
 * @route '/api/v1/schedules'
 */
        storeForm.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: store.url(options),
            method: 'post',
        })
    
    store.form = storeForm
/**
* @see \Modules\Scheduler\Http\Controllers\ScheduleController::update
 * @see Modules/Scheduler/Http/Controllers/ScheduleController.php:56
 * @route '/api/v1/schedules/{schedule}'
 */
export const update = (args: { schedule: string | { id: string } } | [schedule: string | { id: string } ] | string | { id: string }, options?: RouteQueryOptions): RouteDefinition<'put'> => ({
    url: update.url(args, options),
    method: 'put',
})

update.definition = {
    methods: ["put"],
    url: '/api/v1/schedules/{schedule}',
} satisfies RouteDefinition<["put"]>

/**
* @see \Modules\Scheduler\Http\Controllers\ScheduleController::update
 * @see Modules/Scheduler/Http/Controllers/ScheduleController.php:56
 * @route '/api/v1/schedules/{schedule}'
 */
update.url = (args: { schedule: string | { id: string } } | [schedule: string | { id: string } ] | string | { id: string }, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { schedule: args }
    }

            if (typeof args === 'object' && !Array.isArray(args) && 'id' in args) {
            args = { schedule: args.id }
        }
    
    if (Array.isArray(args)) {
        args = {
                    schedule: args[0],
                }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
                        schedule: typeof args.schedule === 'object'
                ? args.schedule.id
                : args.schedule,
                }

    return update.definition.url
            .replace('{schedule}', parsedArgs.schedule.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \Modules\Scheduler\Http\Controllers\ScheduleController::update
 * @see Modules/Scheduler/Http/Controllers/ScheduleController.php:56
 * @route '/api/v1/schedules/{schedule}'
 */
update.put = (args: { schedule: string | { id: string } } | [schedule: string | { id: string } ] | string | { id: string }, options?: RouteQueryOptions): RouteDefinition<'put'> => ({
    url: update.url(args, options),
    method: 'put',
})

    /**
* @see \Modules\Scheduler\Http\Controllers\ScheduleController::update
 * @see Modules/Scheduler/Http/Controllers/ScheduleController.php:56
 * @route '/api/v1/schedules/{schedule}'
 */
    const updateForm = (args: { schedule: string | { id: string } } | [schedule: string | { id: string } ] | string | { id: string }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: update.url(args, {
                    [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                        _method: 'PUT',
                        ...(options?.query ?? options?.mergeQuery ?? {}),
                    }
                }),
        method: 'post',
    })

            /**
* @see \Modules\Scheduler\Http\Controllers\ScheduleController::update
 * @see Modules/Scheduler/Http/Controllers/ScheduleController.php:56
 * @route '/api/v1/schedules/{schedule}'
 */
        updateForm.put = (args: { schedule: string | { id: string } } | [schedule: string | { id: string } ] | string | { id: string }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
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
* @see \Modules\Scheduler\Http\Controllers\ScheduleController::destroy
 * @see Modules/Scheduler/Http/Controllers/ScheduleController.php:70
 * @route '/api/v1/schedules/{schedule}'
 */
export const destroy = (args: { schedule: string | { id: string } } | [schedule: string | { id: string } ] | string | { id: string }, options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: destroy.url(args, options),
    method: 'delete',
})

destroy.definition = {
    methods: ["delete"],
    url: '/api/v1/schedules/{schedule}',
} satisfies RouteDefinition<["delete"]>

/**
* @see \Modules\Scheduler\Http\Controllers\ScheduleController::destroy
 * @see Modules/Scheduler/Http/Controllers/ScheduleController.php:70
 * @route '/api/v1/schedules/{schedule}'
 */
destroy.url = (args: { schedule: string | { id: string } } | [schedule: string | { id: string } ] | string | { id: string }, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { schedule: args }
    }

            if (typeof args === 'object' && !Array.isArray(args) && 'id' in args) {
            args = { schedule: args.id }
        }
    
    if (Array.isArray(args)) {
        args = {
                    schedule: args[0],
                }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
                        schedule: typeof args.schedule === 'object'
                ? args.schedule.id
                : args.schedule,
                }

    return destroy.definition.url
            .replace('{schedule}', parsedArgs.schedule.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \Modules\Scheduler\Http\Controllers\ScheduleController::destroy
 * @see Modules/Scheduler/Http/Controllers/ScheduleController.php:70
 * @route '/api/v1/schedules/{schedule}'
 */
destroy.delete = (args: { schedule: string | { id: string } } | [schedule: string | { id: string } ] | string | { id: string }, options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: destroy.url(args, options),
    method: 'delete',
})

    /**
* @see \Modules\Scheduler\Http\Controllers\ScheduleController::destroy
 * @see Modules/Scheduler/Http/Controllers/ScheduleController.php:70
 * @route '/api/v1/schedules/{schedule}'
 */
    const destroyForm = (args: { schedule: string | { id: string } } | [schedule: string | { id: string } ] | string | { id: string }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: destroy.url(args, {
                    [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                        _method: 'DELETE',
                        ...(options?.query ?? options?.mergeQuery ?? {}),
                    }
                }),
        method: 'post',
    })

            /**
* @see \Modules\Scheduler\Http\Controllers\ScheduleController::destroy
 * @see Modules/Scheduler/Http/Controllers/ScheduleController.php:70
 * @route '/api/v1/schedules/{schedule}'
 */
        destroyForm.delete = (args: { schedule: string | { id: string } } | [schedule: string | { id: string } ] | string | { id: string }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: destroy.url(args, {
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'DELETE',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'post',
        })
    
    destroy.form = destroyForm
const ScheduleController = { index, show, store, update, destroy }

export default ScheduleController