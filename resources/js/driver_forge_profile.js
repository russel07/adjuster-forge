import ElementPlus from 'element-plus';
import 'element-plus/dist/index.css';
import { createApp } from 'vue';
import router from './router/DriverForgeProfile';

import DriverForgeProfile from './components/Profile/DriverForgeProfile.vue';
const app = createApp(DriverForgeProfile);

app.use(router);
app.use(ElementPlus);

app.mount('#driver_forge_profile_all_tabs');