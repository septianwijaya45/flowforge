import AuthController from './AuthController'
import Settings from './Settings'
const Controllers = {
    AuthController: Object.assign(AuthController, AuthController),
Settings: Object.assign(Settings, Settings),
}

export default Controllers