import ElementPlus from 'element-plus';
import 'element-plus/dist/index.css';
import { createApp } from 'vue';
import router from './router/AdjusterForgeProfile';

import AdjusterForgeProfile from './components/Profile/AdjusterForgeProfile.vue';
const app = createApp(AdjusterForgeProfile);

app.use(router);
app.use(ElementPlus);

app.mount('#adjuster_forge_profile_all_tabs');