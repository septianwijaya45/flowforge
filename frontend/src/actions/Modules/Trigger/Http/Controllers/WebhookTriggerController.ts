import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition, applyUrlDefaults } from './../../../../../wayfinder'
/**
* @see \Modules\Trigger\Http\Controllers\WebhookTriggerController::handle
 * @see Modules/Trigger/Http/Controllers/WebhookTriggerController.php:22
 * @route '/api/v1/webhooks/{token}'
 */
export const handle = (args: { token: string | number } | [token: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: handle.url(args, options),
    method: 'post',
})

handle.definition = {
    methods: ["post"],
    url: '/api/v1/webhooks/{token}',
} satisfies RouteDefinition<["post"]>

/**
* @see \Modules\Trigger\Http\Controllers\WebhookTriggerController::handle
 * @see Modules/Trigger/Http/Controllers/WebhookTriggerController.php:22
 * @route '/api/v1/webhooks/{token}'
 */
handle.url = (args: { token: string | number } | [token: string | number ] | string | number, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { token: args }
    }

    
    if (Array.isArray(args)) {
        args = {
                    token: args[0],
                }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
                        token: args.token,
                }

    return handle.definition.url
            .replace('{token}', parsedArgs.token.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \Modules\Trigger\Http\Controllers\WebhookTriggerController::handle
 * @see Modules/Trigger/Http/Controllers/WebhookTriggerController.php:22
 * @route '/api/v1/webhooks/{token}'
 */
handle.post = (args: { token: string | number } | [token: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: handle.url(args, options),
    method: 'post',
})

    /**
* @see \Modules\Trigger\Http\Controllers\WebhookTriggerController::handle
 * @see Modules/Trigger/Http/Controllers/WebhookTriggerController.php:22
 * @route '/api/v1/webhooks/{token}'
 */
    const handleForm = (args: { token: string | number } | [token: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: handle.url(args, options),
        method: 'post',
    })

            /**
* @see \Modules\Trigger\Http\Controllers\WebhookTriggerController::handle
 * @see Modules/Trigger/Http/Controllers/WebhookTriggerController.php:22
 * @route '/api/v1/webhooks/{token}'
 */
        handleForm.post = (args: { token: string | number } | [token: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: handle.url(args, options),
            method: 'post',
        })
    
    handle.form = handleForm
const WebhookTriggerController = { handle }

export default WebhookTriggerController