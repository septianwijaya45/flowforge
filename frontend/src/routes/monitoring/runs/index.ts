import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition, applyUrlDefaults } from './../../../wayfinder'
/**
 * @see routes/web.php:29
 * @route '/monitoring/runs/{run}'
 */
export const show = (args: { run: string | { id: string } } | [run: string | { id: string } ] | string | { id: string }, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: show.url(args, options),
    method: 'get',
})

show.definition = {
    methods: ["get","head"],
    url: '/monitoring/runs/{run}',
} satisfies RouteDefinition<["get","head"]>

/**
 * @see routes/web.php:29
 * @route '/monitoring/runs/{run}'
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
 * @see routes/web.php:29
 * @route '/monitoring/runs/{run}'
 */
show.get = (args: { run: string | { id: string } } | [run: string | { id: string } ] | string | { id: string }, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: show.url(args, options),
    method: 'get',
})
/**
 * @see routes/web.php:29
 * @route '/monitoring/runs/{run}'
 */
show.head = (args: { run: string | { id: string } } | [run: string | { id: string } ] | string | { id: string }, options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: show.url(args, options),
    method: 'head',
})

    /**
 * @see routes/web.php:29
 * @route '/monitoring/runs/{run}'
 */
    const showForm = (args: { run: string | { id: string } } | [run: string | { id: string } ] | string | { id: string }, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: show.url(args, options),
        method: 'get',
    })

            /**
 * @see routes/web.php:29
 * @route '/monitoring/runs/{run}'
 */
        showForm.get = (args: { run: string | { id: string } } | [run: string | { id: string } ] | string | { id: string }, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: show.url(args, options),
            method: 'get',
        })
            /**
 * @see routes/web.php:29
 * @route '/monitoring/runs/{run}'
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
const runs = {
    show: Object.assign(show, show),
}

export default runs