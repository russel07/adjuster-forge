import { createWebHashHistory, createRouter } from "vue-router";
import Settings from '../components/Admin/Settings.vue';
import PaymentSettings from '../components/Admin/PaymentSettings.vue';
import CompaniesList from '../components/Admin/CompaniesList.vue';
import AdjustersList from '../components/Admin/AdjustersList.vue';
import PurchaseHistory from '../components/Admin/PurchaseHistory.vue';
import AdjusterDetails from "../components/Admin/AdjusterDetails.vue";

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
        path: '/adjusters',
        name: 'AdjustersList',
        component: AdjustersList
    },
    {
        path: '/adjuster-details/:user_id',
        name: 'adjuster-details',
        component: AdjusterDetails,
        props: true
    },
];

const router = createRouter({
    history: createWebHashHistory(),
    routes
});

export default router;
