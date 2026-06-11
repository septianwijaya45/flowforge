import Auth from './Auth'
import Workflow from './Workflow'
import WorkflowVersioning from './WorkflowVersioning'
import Trigger from './Trigger'
import Monitoring from './Monitoring'
import Scheduler from './Scheduler'
const Modules = {
    Auth: Object.assign(Auth, Auth),
Workflow: Object.assign(Workflow, Workflow),
WorkflowVersioning: Object.assign(WorkflowVersioning, WorkflowVersioning),
Trigger: Object.assign(Trigger, Trigger),
Monitoring: Object.assign(Monitoring, Monitoring),
Scheduler: Object.assign(Scheduler, Scheduler),
}

export default Modules