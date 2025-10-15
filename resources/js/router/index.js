import { createWebHashHistory, createRouter } from "vue-router";
import Settings from '../components/Admin/Settings.vue';
import PaymentSettings from '../components/Admin/PaymentSettings.vue';
import CompaniesList from '../components/Admin/CompaniesList.vue';
import CompanyDetails from "../components/Admin/CompanyDetails.vue";
import AdjustersList from '../components/Admin/AdjustersList.vue';
import AdjusterDetails from "../components/Admin/AdjusterDetails.vue";
import ManualPayment from "../components/Admin/ManualPayment.vue";
import FreeAccessSettings from "../components/Admin/FreeAccessSettings.vue";
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
        path: '/free-access',
        name: 'FreeAccessSettings',
        component: FreeAccessSettings
    },
    {
        path: '/companies',
        name: 'CompaniesList',
        component: CompaniesList
    },
    {
        path: '/company-details/:user_id',
        name: 'company-details',
        component: CompanyDetails,
        props: true
    },
    {
        path: '/manual-payment/:user_id',
        name: 'ManualPayment',
        component: ManualPayment
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
