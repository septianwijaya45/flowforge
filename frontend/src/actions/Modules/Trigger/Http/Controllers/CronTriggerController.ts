import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition } from './../../../../../wayfinder'
/**
* @see \Modules\Trigger\Http\Controllers\CronTriggerController::process
 * @see Modules/Trigger/Http/Controllers/CronTriggerController.php:19
 * @route '/api/v1/triggers/cron/process'
 */
export const process = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: process.url(options),
    method: 'post',
})

process.definition = {
    methods: ["post"],
    url: '/api/v1/triggers/cron/process',
} satisfies RouteDefinition<["post"]>

/**
* @see \Modules\Trigger\Http\Controllers\CronTriggerController::process
 * @see Modules/Trigger/Http/Controllers/CronTriggerController.php:19
 * @route '/api/v1/triggers/cron/process'
 */
process.url = (options?: RouteQueryOptions) => {
    return process.definition.url + queryParams(options)
}

/**
* @see \Modules\Trigger\Http\Controllers\CronTriggerController::process
 * @see Modules/Trigger/Http/Controllers/CronTriggerController.php:19
 * @route '/api/v1/triggers/cron/process'
 */
process.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: process.url(options),
    method: 'post',
})

    /**
* @see \Modules\Trigger\Http\Controllers\CronTriggerController::process
 * @see Modules/Trigger/Http/Controllers/CronTriggerController.php:19
 * @route '/api/v1/triggers/cron/process'
 */
    const processForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: process.url(options),
        method: 'post',
    })

            /**
* @see \Modules\Trigger\Http\Controllers\CronTriggerController::process
 * @see Modules/Trigger/Http/Controllers/CronTriggerController.php:19
 * @route '/api/v1/triggers/cron/process'
 */
        processForm.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: process.url(options),
            method: 'post',
        })
    
    process.form = processForm
const CronTriggerController = { process }

export default CronTriggerController