import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition } from './../../wayfinder'
/**
* @see \Modules\Auth\Http\Controllers\AuthController::sessionToken
 * @see Modules/Auth/Http/Controllers/AuthController.php:68
 * @route '/api/v1/auth/session-token'
 */
export const sessionToken = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: sessionToken.url(options),
    method: 'post',
})

sessionToken.definition = {
    methods: ["post"],
    url: '/api/v1/auth/session-token',
} satisfies RouteDefinition<["post"]>

/**
* @see \Modules\Auth\Http\Controllers\AuthController::sessionToken
 * @see Modules/Auth/Http/Controllers/AuthController.php:68
 * @route '/api/v1/auth/session-token'
 */
sessionToken.url = (options?: RouteQueryOptions) => {
    return sessionToken.definition.url + queryParams(options)
}

/**
* @see \Modules\Auth\Http\Controllers\AuthController::sessionToken
 * @see Modules/Auth/Http/Controllers/AuthController.php:68
 * @route '/api/v1/auth/session-token'
 */
sessionToken.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: sessionToken.url(options),
    method: 'post',
})

    /**
* @see \Modules\Auth\Http\Controllers\AuthController::sessionToken
 * @see Modules/Auth/Http/Controllers/AuthController.php:68
 * @route '/api/v1/auth/session-token'
 */
    const sessionTokenForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: sessionToken.url(options),
        method: 'post',
    })

            /**
* @see \Modules\Auth\Http\Controllers\AuthController::sessionToken
 * @see Modules/Auth/Http/Controllers/AuthController.php:68
 * @route '/api/v1/auth/session-token'
 */
        sessionTokenForm.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: sessionToken.url(options),
            method: 'post',
        })
    
    sessionToken.form = sessionTokenForm
const auth = {
    sessionToken: Object.assign(sessionToken, sessionToken),
}

export default auth