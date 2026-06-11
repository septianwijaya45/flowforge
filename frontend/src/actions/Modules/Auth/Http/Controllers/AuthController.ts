import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition } from './../../../../../wayfinder'
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
/**
* @see \Modules\Auth\Http\Controllers\AuthController::login
 * @see Modules/Auth/Http/Controllers/AuthController.php:25
 * @route '/api/v1/auth/login'
 */
export const login = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: login.url(options),
    method: 'post',
})

login.definition = {
    methods: ["post"],
    url: '/api/v1/auth/login',
} satisfies RouteDefinition<["post"]>

/**
* @see \Modules\Auth\Http\Controllers\AuthController::login
 * @see Modules/Auth/Http/Controllers/AuthController.php:25
 * @route '/api/v1/auth/login'
 */
login.url = (options?: RouteQueryOptions) => {
    return login.definition.url + queryParams(options)
}

/**
* @see \Modules\Auth\Http\Controllers\AuthController::login
 * @see Modules/Auth/Http/Controllers/AuthController.php:25
 * @route '/api/v1/auth/login'
 */
login.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: login.url(options),
    method: 'post',
})

    /**
* @see \Modules\Auth\Http\Controllers\AuthController::login
 * @see Modules/Auth/Http/Controllers/AuthController.php:25
 * @route '/api/v1/auth/login'
 */
    const loginForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: login.url(options),
        method: 'post',
    })

            /**
* @see \Modules\Auth\Http\Controllers\AuthController::login
 * @see Modules/Auth/Http/Controllers/AuthController.php:25
 * @route '/api/v1/auth/login'
 */
        loginForm.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: login.url(options),
            method: 'post',
        })
    
    login.form = loginForm
/**
* @see \Modules\Auth\Http\Controllers\AuthController::refresh
 * @see Modules/Auth/Http/Controllers/AuthController.php:39
 * @route '/api/v1/auth/refresh'
 */
export const refresh = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: refresh.url(options),
    method: 'post',
})

refresh.definition = {
    methods: ["post"],
    url: '/api/v1/auth/refresh',
} satisfies RouteDefinition<["post"]>

/**
* @see \Modules\Auth\Http\Controllers\AuthController::refresh
 * @see Modules/Auth/Http/Controllers/AuthController.php:39
 * @route '/api/v1/auth/refresh'
 */
refresh.url = (options?: RouteQueryOptions) => {
    return refresh.definition.url + queryParams(options)
}

/**
* @see \Modules\Auth\Http\Controllers\AuthController::refresh
 * @see Modules/Auth/Http/Controllers/AuthController.php:39
 * @route '/api/v1/auth/refresh'
 */
refresh.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: refresh.url(options),
    method: 'post',
})

    /**
* @see \Modules\Auth\Http\Controllers\AuthController::refresh
 * @see Modules/Auth/Http/Controllers/AuthController.php:39
 * @route '/api/v1/auth/refresh'
 */
    const refreshForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: refresh.url(options),
        method: 'post',
    })

            /**
* @see \Modules\Auth\Http\Controllers\AuthController::refresh
 * @see Modules/Auth/Http/Controllers/AuthController.php:39
 * @route '/api/v1/auth/refresh'
 */
        refreshForm.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: refresh.url(options),
            method: 'post',
        })
    
    refresh.form = refreshForm
/**
* @see \Modules\Auth\Http\Controllers\AuthController::logout
 * @see Modules/Auth/Http/Controllers/AuthController.php:52
 * @route '/api/v1/auth/logout'
 */
export const logout = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: logout.url(options),
    method: 'post',
})

logout.definition = {
    methods: ["post"],
    url: '/api/v1/auth/logout',
} satisfies RouteDefinition<["post"]>

/**
* @see \Modules\Auth\Http\Controllers\AuthController::logout
 * @see Modules/Auth/Http/Controllers/AuthController.php:52
 * @route '/api/v1/auth/logout'
 */
logout.url = (options?: RouteQueryOptions) => {
    return logout.definition.url + queryParams(options)
}

/**
* @see \Modules\Auth\Http\Controllers\AuthController::logout
 * @see Modules/Auth/Http/Controllers/AuthController.php:52
 * @route '/api/v1/auth/logout'
 */
logout.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: logout.url(options),
    method: 'post',
})

    /**
* @see \Modules\Auth\Http\Controllers\AuthController::logout
 * @see Modules/Auth/Http/Controllers/AuthController.php:52
 * @route '/api/v1/auth/logout'
 */
    const logoutForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: logout.url(options),
        method: 'post',
    })

            /**
* @see \Modules\Auth\Http\Controllers\AuthController::logout
 * @see Modules/Auth/Http/Controllers/AuthController.php:52
 * @route '/api/v1/auth/logout'
 */
        logoutForm.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: logout.url(options),
            method: 'post',
        })
    
    logout.form = logoutForm
const AuthController = { sessionToken, login, refresh, logout }

export default AuthController