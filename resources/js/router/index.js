import { createWebHashHistory, createRouter } from "vue-router";
import Settings from '../components/Admin/Settings.vue';
import PaymentSettings from '../components/Admin/PaymentSettings.vue';
import CompaniesList from '../components/Admin/CompaniesList.vue';
import DriversList from '../components/Admin/DriversList.vue';
import PurchaseHistory from '../components/Admin/PurchaseHistory.vue';
import DriverDetails from "../components/Admin/DriverDetails.vue";

const routes = [
    {
        path: '/',
        redirect: '/general'
    },
    {
        path: '/general',
        name: 'GeneralSettings',
        component: Settings
    },
    {
        path: '/payment',
        name: 'PaymentSettings',
        component: PaymentSettings
    },
    {
        path: '/purchased',
        name: 'PurchaseHistory',
        component: PurchaseHistory
    },
    {
        path: '/companies',
        name: 'CompaniesList',
        component: CompaniesList
    },
    {
        path: '/drivers',
        name: 'DriversList',
        component: DriversList
    },
    {
        path: '/driver-details/:user_id',
        name: 'driver-details',
        component: DriverDetails,
        props: true
    },
];

const router = createRouter({
    history: createWebHashHistory(),
    routes
});

export default router;
