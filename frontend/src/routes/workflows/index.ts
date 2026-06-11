import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition, applyUrlDefaults } from './../../wayfinder'
/**
* @see \Inertia\Controller::__invoke
 * @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
 * @route '/workflows'
 */
export const index = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
})

index.definition = {
    methods: ["get","head"],
    url: '/workflows',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \Inertia\Controller::__invoke
 * @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
 * @route '/workflows'
 */
index.url = (options?: RouteQueryOptions) => {
    return index.definition.url + queryParams(options)
}

/**
* @see \Inertia\Controller::__invoke
 * @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
 * @route '/workflows'
 */
index.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
})
/**
* @see \Inertia\Controller::__invoke
 * @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
 * @route '/workflows'
 */
index.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: index.url(options),
    method: 'head',
})

    /**
* @see \Inertia\Controller::__invoke
 * @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
 * @route '/workflows'
 */
    const indexForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: index.url(options),
        method: 'get',
    })

            /**
* @see \Inertia\Controller::__invoke
 * @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
 * @route '/workflows'
 */
        indexForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: index.url(options),
            method: 'get',
        })
            /**
* @see \Inertia\Controller::__invoke
 * @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
 * @route '/workflows'
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
 * @see routes/web.php:16
 * @route '/workflows/{workflow}/builder'
 */
export const builder = (args: { workflow: string | { id: string } } | [workflow: string | { id: string } ] | string | { id: string }, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: builder.url(args, options),
    method: 'get',
})

builder.definition = {
    methods: ["get","head"],
    url: '/workflows/{workflow}/builder',
} satisfies RouteDefinition<["get","head"]>

/**
 * @see routes/web.php:16
 * @route '/workflows/{workflow}/builder'
 */
builder.url = (args: { workflow: string | { id: string } } | [workflow: string | { id: string } ] | string | { id: string }, options?: RouteQueryOptions) => {
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

    return builder.definition.url
            .replace('{workflow}', parsedArgs.workflow.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
 * @see routes/web.php:16
 * @route '/workflows/{workflow}/builder'
 */
builder.get = (args: { workflow: string | { id: string } } | [workflow: string | { id: string } ] | string | { id: string }, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: builder.url(args, options),
    method: 'get',
})
/**
 * @see routes/web.php:16
 * @route '/workflows/{workflow}/builder'
 */
builder.head = (args: { workflow: string | { id: string } } | [workflow: string | { id: string } ] | string | { id: string }, options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: builder.url(args, options),
    method: 'head',
})

    /**
 * @see routes/web.php:16
 * @route '/workflows/{workflow}/builder'
 */
    const builderForm = (args: { workflow: string | { id: string } } | [workflow: string | { id: string } ] | string | { id: string }, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: builder.url(args, options),
        method: 'get',
    })

            /**
 * @see routes/web.php:16
 * @route '/workflows/{workflow}/builder'
 */
        builderForm.get = (args: { workflow: string | { id: string } } | [workflow: string | { id: string } ] | string | { id: string }, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: builder.url(args, options),
            method: 'get',
        })
            /**
 * @see routes/web.php:16
 * @route '/workflows/{workflow}/builder'
 */
        builderForm.head = (args: { workflow: string | { id: string } } | [workflow: string | { id: string } ] | string | { id: string }, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: builder.url(args, {
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    builder.form = builderForm
/**
 * @see routes/web.php:22
 * @route '/workflows/{workflow}/triggers'
 */
export const triggers = (args: { workflow: string | { id: string } } | [workflow: string | { id: string } ] | string | { id: string }, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: triggers.url(args, options),
    method: 'get',
})

triggers.definition = {
    methods: ["get","head"],
    url: '/workflows/{workflow}/triggers',
} satisfies RouteDefinition<["get","head"]>

/**
 * @see routes/web.php:22
 * @route '/workflows/{workflow}/triggers'
 */
triggers.url = (args: { workflow: string | { id: string } } | [workflow: string | { id: string } ] | string | { id: string }, options?: RouteQueryOptions) => {
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

    return triggers.definition.url
            .replace('{workflow}', parsedArgs.workflow.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
 * @see routes/web.php:22
 * @route '/workflows/{workflow}/triggers'
 */
triggers.get = (args: { workflow: string | { id: string } } | [workflow: string | { id: string } ] | string | { id: string }, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: triggers.url(args, options),
    method: 'get',
})
/**
 * @see routes/web.php:22
 * @route '/workflows/{workflow}/triggers'
 */
triggers.head = (args: { workflow: string | { id: string } } | [workflow: string | { id: string } ] | string | { id: string }, options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: triggers.url(args, options),
    method: 'head',
})

    /**
 * @see routes/web.php:22
 * @route '/workflows/{workflow}/triggers'
 */
    const triggersForm = (args: { workflow: string | { id: string } } | [workflow: string | { id: string } ] | string | { id: string }, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: triggers.url(args, options),
        method: 'get',
    })

            /**
 * @see routes/web.php:22
 * @route '/workflows/{workflow}/triggers'
 */
        triggersForm.get = (args: { workflow: string | { id: string } } | [workflow: string | { id: string } ] | string | { id: string }, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: triggers.url(args, options),
            method: 'get',
        })
            /**
 * @see routes/web.php:22
 * @route '/workflows/{workflow}/triggers'
 */
        triggersForm.head = (args: { workflow: string | { id: string } } | [workflow: string | { id: string } ] | string | { id: string }, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: triggers.url(args, {
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    triggers.form = triggersForm
const workflows = {
    index: Object.assign(index, index),
builder: Object.assign(builder, builder),
triggers: Object.assign(triggers, triggers),
}

export default workflows