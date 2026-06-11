import WebhookTriggerController from './WebhookTriggerController'
import CronTriggerController from './CronTriggerController'
import WorkflowTriggerController from './WorkflowTriggerController'
import ManualTriggerController from './ManualTriggerController'
const Controllers = {
    WebhookTriggerController: Object.assign(WebhookTriggerController, WebhookTriggerController),
CronTriggerController: Object.assign(CronTriggerController, CronTriggerController),
WorkflowTriggerController: Object.assign(WorkflowTriggerController, WorkflowTriggerController),
ManualTriggerController: Object.assign(ManualTriggerController, ManualTriggerController),
}

export default Controllers