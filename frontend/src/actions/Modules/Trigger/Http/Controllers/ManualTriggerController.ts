import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition, applyUrlDefaults } from './../../../../../wayfinder'
/**
* @see \Modules\Trigger\Http\Controllers\ManualTriggerController::fire
 * @see Modules/Trigger/Http/Controllers/ManualTriggerController.php:24
 * @route '/api/v1/workflows/{workflow}/trigger/manual'
 */
export const fire = (args: { workflow: string | { id: string } } | [workflow: string | { id: string } ] | string | { id: string }, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: fire.url(args, options),
    method: 'post',
})

fire.definition = {
    methods: ["post"],
    url: '/api/v1/workflows/{workflow}/trigger/manual',
} satisfies RouteDefinition<["post"]>

/**
* @see \Modules\Trigger\Http\Controllers\ManualTriggerController::fire
 * @see Modules/Trigger/Http/Controllers/ManualTriggerController.php:24
 * @route '/api/v1/workflows/{workflow}/trigger/manual'
 */
fire.url = (args: { workflow: string | { id: string } } | [workflow: string | { id: string } ] | string | { id: string }, options?: RouteQueryOptions) => {
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

    return fire.definition.url
            .replace('{workflow}', parsedArgs.workflow.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \Modules\Trigger\Http\Controllers\ManualTriggerController::fire
 * @see Modules/Trigger/Http/Controllers/ManualTriggerController.php:24
 * @route '/api/v1/workflows/{workflow}/trigger/manual'
 */
fire.post = (args: { workflow: string | { id: string } } | [workflow: string | { id: string } ] | string | { id: string }, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: fire.url(args, options),
    method: 'post',
})

    /**
* @see \Modules\Trigger\Http\Controllers\ManualTriggerController::fire
 * @see Modules/Trigger/Http/Controllers/ManualTriggerController.php:24
 * @route '/api/v1/workflows/{workflow}/trigger/manual'
 */
    const fireForm = (args: { workflow: string | { id: string } } | [workflow: string | { id: string } ] | string | { id: string }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: fire.url(args, options),
        method: 'post',
    })

            /**
* @see \Modules\Trigger\Http\Controllers\ManualTriggerController::fire
 * @see Modules/Trigger/Http/Controllers/ManualTriggerController.php:24
 * @route '/api/v1/workflows/{workflow}/trigger/manual'
 */
        fireForm.post = (args: { workflow: string | { id: string } } | [workflow: string | { id: string } ] | string | { id: string }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: fire.url(args, options),
            method: 'post',
        })
    
    fire.form = fireForm
const ManualTriggerController = { fire }

export default ManualTriggerController