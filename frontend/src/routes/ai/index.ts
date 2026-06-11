import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition } from './../../wayfinder'
/**
* @see \Inertia\Controller::__invoke
 * @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
 * @route '/ai/assistant'
 */
export const assistant = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: assistant.url(options),
    method: 'get',
})

assistant.definition = {
    methods: ["get","head"],
    url: '/ai/assistant',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \Inertia\Controller::__invoke
 * @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
 * @route '/ai/assistant'
 */
assistant.url = (options?: RouteQueryOptions) => {
    return assistant.definition.url + queryParams(options)
}

/**
* @see \Inertia\Controller::__invoke
 * @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
 * @route '/ai/assistant'
 */
assistant.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: assistant.url(options),
    method: 'get',
})
/**
* @see \Inertia\Controller::__invoke
 * @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
 * @route '/ai/assistant'
 */
assistant.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: assistant.url(options),
    method: 'head',
})

    /**
* @see \Inertia\Controller::__invoke
 * @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
 * @route '/ai/assistant'
 */
    const assistantForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: assistant.url(options),
        method: 'get',
    })

            /**
* @see \Inertia\Controller::__invoke
 * @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
 * @route '/ai/assistant'
 */
        assistantForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: assistant.url(options),
            method: 'get',
        })
            /**
* @see \Inertia\Controller::__invoke
 * @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
 * @route '/ai/assistant'
 */
        assistantForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: assistant.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    assistant.form = assistantForm
const ai = {
    assistant: Object.assign(assistant, assistant),
}

export default ai