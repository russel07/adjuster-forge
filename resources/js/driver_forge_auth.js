import ElementPlus from 'element-plus';
import 'element-plus/dist/index.css';
import { createApp } from 'vue';
import router from './router/DriverForgeAuth';

import Auth from './components/Auth/Auth.vue';
const app = createApp( Auth );

app.use(router);
app.use(ElementPlus);

app.mount('#driver_forge_profile_auth');