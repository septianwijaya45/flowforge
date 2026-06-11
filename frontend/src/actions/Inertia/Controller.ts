import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition } from './../../wayfinder'
/**
* @see \Inertia\Controller::__invoke
 * @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
 * @route '/settings/appearance'
 */
const Controllere19ee86e9cf603ce1a59a1ec5d21dec5 = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: Controllere19ee86e9cf603ce1a59a1ec5d21dec5.url(options),
    method: 'get',
})

Controllere19ee86e9cf603ce1a59a1ec5d21dec5.definition = {
    methods: ["get","head"],
    url: '/settings/appearance',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \Inertia\Controller::__invoke
 * @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
 * @route '/settings/appearance'
 */
Controllere19ee86e9cf603ce1a59a1ec5d21dec5.url = (options?: RouteQueryOptions) => {
    return Controllere19ee86e9cf603ce1a59a1ec5d21dec5.definition.url + queryParams(options)
}

/**
* @see \Inertia\Controller::__invoke
 * @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
 * @route '/settings/appearance'
 */
Controllere19ee86e9cf603ce1a59a1ec5d21dec5.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: Controllere19ee86e9cf603ce1a59a1ec5d21dec5.url(options),
    method: 'get',
})
/**
* @see \Inertia\Controller::__invoke
 * @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
 * @route '/settings/appearance'
 */
Controllere19ee86e9cf603ce1a59a1ec5d21dec5.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: Controllere19ee86e9cf603ce1a59a1ec5d21dec5.url(options),
    method: 'head',
})

    /**
* @see \Inertia\Controller::__invoke
 * @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
 * @route '/settings/appearance'
 */
    const Controllere19ee86e9cf603ce1a59a1ec5d21dec5Form = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: Controllere19ee86e9cf603ce1a59a1ec5d21dec5.url(options),
        method: 'get',
    })

            /**
* @see \Inertia\Controller::__invoke
 * @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
 * @route '/settings/appearance'
 */
        Controllere19ee86e9cf603ce1a59a1ec5d21dec5Form.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: Controllere19ee86e9cf603ce1a59a1ec5d21dec5.url(options),
            method: 'get',
        })
            /**
* @see \Inertia\Controller::__invoke
 * @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
 * @route '/settings/appearance'
 */
        Controllere19ee86e9cf603ce1a59a1ec5d21dec5Form.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: Controllere19ee86e9cf603ce1a59a1ec5d21dec5.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    Controllere19ee86e9cf603ce1a59a1ec5d21dec5.form = Controllere19ee86e9cf603ce1a59a1ec5d21dec5Form
    /**
* @see \Inertia\Controller::__invoke
 * @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
 * @route '/'
 */
const Controller980bb49ee7ae63891f1d891d2fbcf1c9 = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: Controller980bb49ee7ae63891f1d891d2fbcf1c9.url(options),
    method: 'get',
})

Controller980bb49ee7ae63891f1d891d2fbcf1c9.definition = {
    methods: ["get","head"],
    url: '/',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \Inertia\Controller::__invoke
 * @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
 * @route '/'
 */
Controller980bb49ee7ae63891f1d891d2fbcf1c9.url = (options?: RouteQueryOptions) => {
    return Controller980bb49ee7ae63891f1d891d2fbcf1c9.definition.url + queryParams(options)
}

/**
* @see \Inertia\Controller::__invoke
 * @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
 * @route '/'
 */
Controller980bb49ee7ae63891f1d891d2fbcf1c9.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: Controller980bb49ee7ae63891f1d891d2fbcf1c9.url(options),
    method: 'get',
})
/**
* @see \Inertia\Controller::__invoke
 * @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
 * @route '/'
 */
Controller980bb49ee7ae63891f1d891d2fbcf1c9.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: Controller980bb49ee7ae63891f1d891d2fbcf1c9.url(options),
    method: 'head',
})

    /**
* @see \Inertia\Controller::__invoke
 * @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
 * @route '/'
 */
    const Controller980bb49ee7ae63891f1d891d2fbcf1c9Form = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: Controller980bb49ee7ae63891f1d891d2fbcf1c9.url(options),
        method: 'get',
    })

            /**
* @see \Inertia\Controller::__invoke
 * @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
 * @route '/'
 */
        Controller980bb49ee7ae63891f1d891d2fbcf1c9Form.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: Controller980bb49ee7ae63891f1d891d2fbcf1c9.url(options),
            method: 'get',
        })
            /**
* @see \Inertia\Controller::__invoke
 * @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
 * @route '/'
 */
        Controller980bb49ee7ae63891f1d891d2fbcf1c9Form.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: Controller980bb49ee7ae63891f1d891d2fbcf1c9.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    Controller980bb49ee7ae63891f1d891d2fbcf1c9.form = Controller980bb49ee7ae63891f1d891d2fbcf1c9Form
    /**
* @see \Inertia\Controller::__invoke
 * @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
 * @route '/dashboard'
 */
const Controller42a740574ecbfbac32f8cc353fc32db9 = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: Controller42a740574ecbfbac32f8cc353fc32db9.url(options),
    method: 'get',
})

Controller42a740574ecbfbac32f8cc353fc32db9.definition = {
    methods: ["get","head"],
    url: '/dashboard',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \Inertia\Controller::__invoke
 * @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
 * @route '/dashboard'
 */
Controller42a740574ecbfbac32f8cc353fc32db9.url = (options?: RouteQueryOptions) => {
    return Controller42a740574ecbfbac32f8cc353fc32db9.definition.url + queryParams(options)
}

/**
* @see \Inertia\Controller::__invoke
 * @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
 * @route '/dashboard'
 */
Controller42a740574ecbfbac32f8cc353fc32db9.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: Controller42a740574ecbfbac32f8cc353fc32db9.url(options),
    method: 'get',
})
/**
* @see \Inertia\Controller::__invoke
 * @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
 * @route '/dashboard'
 */
Controller42a740574ecbfbac32f8cc353fc32db9.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: Controller42a740574ecbfbac32f8cc353fc32db9.url(options),
    method: 'head',
})

    /**
* @see \Inertia\Controller::__invoke
 * @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
 * @route '/dashboard'
 */
    const Controller42a740574ecbfbac32f8cc353fc32db9Form = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: Controller42a740574ecbfbac32f8cc353fc32db9.url(options),
        method: 'get',
    })

            /**
* @see \Inertia\Controller::__invoke
 * @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
 * @route '/dashboard'
 */
        Controller42a740574ecbfbac32f8cc353fc32db9Form.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: Controller42a740574ecbfbac32f8cc353fc32db9.url(options),
            method: 'get',
        })
            /**
* @see \Inertia\Controller::__invoke
 * @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
 * @route '/dashboard'
 */
        Controller42a740574ecbfbac32f8cc353fc32db9Form.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: Controller42a740574ecbfbac32f8cc353fc32db9.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    Controller42a740574ecbfbac32f8cc353fc32db9.form = Controller42a740574ecbfbac32f8cc353fc32db9Form
    /**
* @see \Inertia\Controller::__invoke
 * @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
 * @route '/workflows'
 */
const Controller6e09a40e2d8757ab8158a51107e91f73 = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: Controller6e09a40e2d8757ab8158a51107e91f73.url(options),
    method: 'get',
})

Controller6e09a40e2d8757ab8158a51107e91f73.definition = {
    methods: ["get","head"],
    url: '/workflows',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \Inertia\Controller::__invoke
 * @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
 * @route '/workflows'
 */
Controller6e09a40e2d8757ab8158a51107e91f73.url = (options?: RouteQueryOptions) => {
    return Controller6e09a40e2d8757ab8158a51107e91f73.definition.url + queryParams(options)
}

/**
* @see \Inertia\Controller::__invoke
 * @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
 * @route '/workflows'
 */
Controller6e09a40e2d8757ab8158a51107e91f73.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: Controller6e09a40e2d8757ab8158a51107e91f73.url(options),
    method: 'get',
})
/**
* @see \Inertia\Controller::__invoke
 * @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
 * @route '/workflows'
 */
Controller6e09a40e2d8757ab8158a51107e91f73.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: Controller6e09a40e2d8757ab8158a51107e91f73.url(options),
    method: 'head',
})

    /**
* @see \Inertia\Controller::__invoke
 * @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
 * @route '/workflows'
 */
    const Controller6e09a40e2d8757ab8158a51107e91f73Form = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: Controller6e09a40e2d8757ab8158a51107e91f73.url(options),
        method: 'get',
    })

            /**
* @see \Inertia\Controller::__invoke
 * @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
 * @route '/workflows'
 */
        Controller6e09a40e2d8757ab8158a51107e91f73Form.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: Controller6e09a40e2d8757ab8158a51107e91f73.url(options),
            method: 'get',
        })
            /**
* @see \Inertia\Controller::__invoke
 * @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
 * @route '/workflows'
 */
        Controller6e09a40e2d8757ab8158a51107e91f73Form.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: Controller6e09a40e2d8757ab8158a51107e91f73.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    Controller6e09a40e2d8757ab8158a51107e91f73.form = Controller6e09a40e2d8757ab8158a51107e91f73Form
    /**
* @see \Inertia\Controller::__invoke
 * @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
 * @route '/monitoring'
 */
const Controllerdc6808bf543b24a6626dfdb2a2997554 = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: Controllerdc6808bf543b24a6626dfdb2a2997554.url(options),
    method: 'get',
})

Controllerdc6808bf543b24a6626dfdb2a2997554.definition = {
    methods: ["get","head"],
    url: '/monitoring',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \Inertia\Controller::__invoke
 * @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
 * @route '/monitoring'
 */
Controllerdc6808bf543b24a6626dfdb2a2997554.url = (options?: RouteQueryOptions) => {
    return Controllerdc6808bf543b24a6626dfdb2a2997554.definition.url + queryParams(options)
}

/**
* @see \Inertia\Controller::__invoke
 * @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
 * @route '/monitoring'
 */
Controllerdc6808bf543b24a6626dfdb2a2997554.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: Controllerdc6808bf543b24a6626dfdb2a2997554.url(options),
    method: 'get',
})
/**
* @see \Inertia\Controller::__invoke
 * @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
 * @route '/monitoring'
 */
Controllerdc6808bf543b24a6626dfdb2a2997554.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: Controllerdc6808bf543b24a6626dfdb2a2997554.url(options),
    method: 'head',
})

    /**
* @see \Inertia\Controller::__invoke
 * @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
 * @route '/monitoring'
 */
    const Controllerdc6808bf543b24a6626dfdb2a2997554Form = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: Controllerdc6808bf543b24a6626dfdb2a2997554.url(options),
        method: 'get',
    })

            /**
* @see \Inertia\Controller::__invoke
 * @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
 * @route '/monitoring'
 */
        Controllerdc6808bf543b24a6626dfdb2a2997554Form.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: Controllerdc6808bf543b24a6626dfdb2a2997554.url(options),
            method: 'get',
        })
            /**
* @see \Inertia\Controller::__invoke
 * @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
 * @route '/monitoring'
 */
        Controllerdc6808bf543b24a6626dfdb2a2997554Form.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: Controllerdc6808bf543b24a6626dfdb2a2997554.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    Controllerdc6808bf543b24a6626dfdb2a2997554.form = Controllerdc6808bf543b24a6626dfdb2a2997554Form
    /**
* @see \Inertia\Controller::__invoke
 * @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
 * @route '/schedules'
 */
const Controllere1f9edf590e2d1f4bd9768a922a3f602 = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: Controllere1f9edf590e2d1f4bd9768a922a3f602.url(options),
    method: 'get',
})

Controllere1f9edf590e2d1f4bd9768a922a3f602.definition = {
    methods: ["get","head"],
    url: '/schedules',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \Inertia\Controller::__invoke
 * @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
 * @route '/schedules'
 */
Controllere1f9edf590e2d1f4bd9768a922a3f602.url = (options?: RouteQueryOptions) => {
    return Controllere1f9edf590e2d1f4bd9768a922a3f602.definition.url + queryParams(options)
}

/**
* @see \Inertia\Controller::__invoke
 * @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
 * @route '/schedules'
 */
Controllere1f9edf590e2d1f4bd9768a922a3f602.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: Controllere1f9edf590e2d1f4bd9768a922a3f602.url(options),
    method: 'get',
})
/**
* @see \Inertia\Controller::__invoke
 * @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
 * @route '/schedules'
 */
Controllere1f9edf590e2d1f4bd9768a922a3f602.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: Controllere1f9edf590e2d1f4bd9768a922a3f602.url(options),
    method: 'head',
})

    /**
* @see \Inertia\Controller::__invoke
 * @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
 * @route '/schedules'
 */
    const Controllere1f9edf590e2d1f4bd9768a922a3f602Form = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: Controllere1f9edf590e2d1f4bd9768a922a3f602.url(options),
        method: 'get',
    })

            /**
* @see \Inertia\Controller::__invoke
 * @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
 * @route '/schedules'
 */
        Controllere1f9edf590e2d1f4bd9768a922a3f602Form.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: Controllere1f9edf590e2d1f4bd9768a922a3f602.url(options),
            method: 'get',
        })
            /**
* @see \Inertia\Controller::__invoke
 * @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
 * @route '/schedules'
 */
        Controllere1f9edf590e2d1f4bd9768a922a3f602Form.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: Controllere1f9edf590e2d1f4bd9768a922a3f602.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    Controllere1f9edf590e2d1f4bd9768a922a3f602.form = Controllere1f9edf590e2d1f4bd9768a922a3f602Form
    /**
* @see \Inertia\Controller::__invoke
 * @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
 * @route '/ai/assistant'
 */
const Controller931c15d406e7dff66adf42ebe23169bc = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: Controller931c15d406e7dff66adf42ebe23169bc.url(options),
    method: 'get',
})

Controller931c15d406e7dff66adf42ebe23169bc.definition = {
    methods: ["get","head"],
    url: '/ai/assistant',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \Inertia\Controller::__invoke
 * @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
 * @route '/ai/assistant'
 */
Controller931c15d406e7dff66adf42ebe23169bc.url = (options?: RouteQueryOptions) => {
    return Controller931c15d406e7dff66adf42ebe23169bc.definition.url + queryParams(options)
}

/**
* @see \Inertia\Controller::__invoke
 * @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
 * @route '/ai/assistant'
 */
Controller931c15d406e7dff66adf42ebe23169bc.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: Controller931c15d406e7dff66adf42ebe23169bc.url(options),
    method: 'get',
})
/**
* @see \Inertia\Controller::__invoke
 * @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
 * @route '/ai/assistant'
 */
Controller931c15d406e7dff66adf42ebe23169bc.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: Controller931c15d406e7dff66adf42ebe23169bc.url(options),
    method: 'head',
})

    /**
* @see \Inertia\Controller::__invoke
 * @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
 * @route '/ai/assistant'
 */
    const Controller931c15d406e7dff66adf42ebe23169bcForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: Controller931c15d406e7dff66adf42ebe23169bc.url(options),
        method: 'get',
    })

            /**
* @see \Inertia\Controller::__invoke
 * @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
 * @route '/ai/assistant'
 */
        Controller931c15d406e7dff66adf42ebe23169bcForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: Controller931c15d406e7dff66adf42ebe23169bc.url(options),
            method: 'get',
        })
            /**
* @see \Inertia\Controller::__invoke
 * @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
 * @route '/ai/assistant'
 */
        Controller931c15d406e7dff66adf42ebe23169bcForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: Controller931c15d406e7dff66adf42ebe23169bc.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    Controller931c15d406e7dff66adf42ebe23169bc.form = Controller931c15d406e7dff66adf42ebe23169bcForm

/**
* Multiple routes resolve to \Inertia\Controller::Controller, so this export is a
* dictionary keyed by URI rather than a callable. Call a specific route with `Controller['<uri>'](...)`,
* or import the route by name from your generated `routes/` directory.
*/
const Controller = {
    '/settings/appearance': Controllere19ee86e9cf603ce1a59a1ec5d21dec5,
    '/': Controller980bb49ee7ae63891f1d891d2fbcf1c9,
    '/dashboard': Controller42a740574ecbfbac32f8cc353fc32db9,
    '/workflows': Controller6e09a40e2d8757ab8158a51107e91f73,
    '/monitoring': Controllerdc6808bf543b24a6626dfdb2a2997554,
    '/schedules': Controllere1f9edf590e2d1f4bd9768a922a3f602,
    '/ai/assistant': Controller931c15d406e7dff66adf42ebe23169bc,
}

export default Controller