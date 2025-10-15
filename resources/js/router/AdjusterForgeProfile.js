import { createRouter, createWebHashHistory } from "vue-router"

import ChangePassword from '../components/Profile/ChangePassword.vue'
import Inbox from '../components/Profile/Inbox.vue'
import MyProfile from '../components/Profile/MyProfile.vue'
import CompleteProfile from '../components/Profile/CompleteProfile.vue'
import UpdateProfile from '../components/Profile/UpdateProfile.vue'
import Subscription from "../components/Profile/Subscription.vue"
import CreateJob from '../components/Jobs/Create.vue'
import Jobs from "../components/Jobs/Jobs.vue"
import AllJobs from "../components/Jobs/AllJobs.vue";
import JobDetails from "../components/Jobs/JobDetails.vue"
import AppliedJobs from "../components/Jobs/AppliedJobs.vue"
import JobApplicants from "../components/Jobs/JobApplicants.vue"
import AdjustersList from "../components/Profile/AdjustersList.vue";
import AdjusterDetails from "../components/Profile/AdjusterDetails.vue";
import PaymentHistory from "../components/Profile/PaymentHistory.vue"
import VerificationFee from "../components/Profile/VerificationFee.vue";

const routes = [
    {
        path: '/',
        redirect: '/my-profile',

    },
    {
        path: '/my-profile',
        name: 'my-profile',
        component: MyProfile
    },
    {
        path: '/complete-profile',
        name: 'complete-profile',
        component: CompleteProfile
    },
    {
        path: '/update-profile',
        name: 'update-profile',
        component: UpdateProfile
    },
    {
        path: '/all-jobs',
        name: 'all-jobs',
        component: AllJobs
    },
    {
        path: '/my-jobs',
        name: 'my-jobs',
        component: AppliedJobs
    },
    {
        path: '/jobs',
        name: 'jobs',
        component: Jobs
    },
    {
        path: '/job-applicants/:jobId',
        name: 'job-applicants',
        component: JobApplicants,
        meta: { requiresAuth: true }
    },
    {
        path: '/post-job',
        name: 'post-job',
        component: CreateJob
    },
    {
        path: '/subscription',
        name: 'subscription',
        component: Subscription
    },
    {
        path: '/inbox',
        name: 'inbox',
        component: Inbox
    },
    {
        path: '/payment-history',
        name: 'payment-history',
        component: PaymentHistory
    },
    {
        path: '/change-password',
        name: 'change-password',
        component: ChangePassword
    },
    {
        path: '/verification-fee',
        name: 'verification-fee',
        component: VerificationFee
    },
    {
        path: '/job-details/:id',
        name: 'job-details',
        component: JobDetails,
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
]

const router = createRouter({
    history: createWebHashHistory(),
    routes
})

router.beforeEach((to, from, next) => {
    const app_vars = window.adjuster_forge_app_vars;
    const is_remaining_days = `${app_vars.remaining_days}`;
    const is_logged_in = `${app_vars.is_logged_in}`;
    const auth_page = `${app_vars.auth_page}`;
    let user_type = '';
    const subscription_data = app_vars.subscription_data;
    const is_expired = `${app_vars.is_expired}`;
    if( ! is_logged_in ) {
        window.location.href = auth_page;
    } else {
        user_type = `${app_vars.user_data?.user_type}`;
    }

    if ( 'adjuster' === user_type ) {
        //Did not complete the profile? force to complete
        if( ! subscription_data ||  ! subscription_data.profile_completed ) {
            if ( to.path !== '/complete-profile' ) {
                next({ path: '/complete-profile' })
            } else {
                next()
            }
        } else {
            // did not paid verification fees, force to pay
            if( ! subscription_data.paid_verification_fee ) {
                if ( to.path !== '/verification-fee' ) {
                    next({ path: '/verification-fee' })
                } else {
                    next()
                }
            } else {
                //Verification fees paid, force to redirect profile page
                if ( to.path === '/verification-fee' ) {
                    next({ path: '/my-profile' })
                } else {
                    next()
                }
            }
        }
    }
    else {
        if( ! subscription_data ||  ! subscription_data.profile_completed ) {
            if ( to.path !== '/complete-profile' ) {
                next({ path: '/complete-profile' })
            } else {
                next()
            }
        } else {
            next()
        }
    }
})

export default router
