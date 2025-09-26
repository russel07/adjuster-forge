<template>
  <el-header>
    <div class="header-content">
      <el-menu :default-active="activeIndex" class="el-menu-demo" mode="horizontal" @select="handleSelect"
        :router="true">
        <!-- Menu icon as a menu item -->
        <el-menu-item index="menu-icon" @click="handleMenuIconClick" style="padding: 0 16px;">
          <el-image :src="logo_url" style="width:32px;height:auto;vertical-align:middle;padding-bottom:20px;" fit="contain" />
        </el-menu-item>
        <!-- Common menu items for all users -->
        <el-menu-item index="my-profile" :to="{ path: '/my-profile' }">My Profile</el-menu-item>
        <el-menu-item index="inbox" :to="{ path: '/inbox' }">
          <el-icon v-if="user_status !== 'active' || plan_type === 'free_trial'"><Lock /></el-icon> Inbox</el-menu-item>

        <!-- Adjuster specific menu items -->
        <template v-if="user_type === 'adjuster' ">
          <el-menu-item index="my-jobs" :to="{ path: '/my-jobs' }">
            <el-icon v-if="user_status !== 'active'"><Lock /></el-icon>My Jobs
          </el-menu-item>
          <el-menu-item index="all-jobs" :to="{ path: '/all-jobs' }">
            <el-icon v-if="user_status !== 'active'"><Lock /></el-icon>All Jobs
          </el-menu-item>
        </template>
        
        <!-- Company specific menu items -->
        <template v-if="user_type === 'company' ">
          <el-menu-item index="jobs" :to="{ path: '/my-jobs' }">
            <el-icon v-if="user_status !== 'active' || plan_type !== 'premium'"><Lock /></el-icon> My Jobs
          </el-menu-item>
          <el-menu-item index="post-job" :to="{ path: '/post-job' }">
            <el-icon v-if="user_status !== 'active' || plan_type !== 'premium'"><Lock /></el-icon> Post Job
          </el-menu-item>
          <el-menu-item index="adjusters" :to="{ path: '/adjusters' }">
            <el-icon v-if="user_status !== 'active' "><Lock /></el-icon> Adjusters
          </el-menu-item>
        </template>
        
        <!-- Common menu items continued -->
        <el-menu-item index="payment-history" :to="{ path: '/payment-history' }">Payment History</el-menu-item>
        <el-sub-menu index="profile">
          <template #title>{{ user_name }}</template>
          <el-menu-item index="change-password" :to="{ path: '/change-password' }">Change Password</el-menu-item>
          <el-menu-item index="update-profile" :to="{ path: '/update-profile' }">Update Profile</el-menu-item>
          <el-menu-item index="logout" @click="handleLogout">Logout({{ user_name }})</el-menu-item>
        </el-sub-menu>        
      </el-menu>
      <div class="h-6" />
    </div>
  </el-header>
</template>

<script>
import { onMounted, ref, watch } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { Lock } from '@element-plus/icons-vue';

export default {
  name: 'Menu',
  components: {
    Lock
  },
  setup() {
    const route = useRoute(); // Get the current route
    const router = useRouter();
    const activeIndex = ref('my-profile');
    const app_vars = window.adjuster_forge_app_vars;
    const is_logged_in = `${app_vars.is_logged_in}`;
    const logo_url = `${app_vars.app_logo}`;
    const home_url = `${app_vars.home_url}`;
    const user_status = `${app_vars.user_status ? app_vars.user_status : ''}`;
    const user_type = `${app_vars.user_data? app_vars.user_data.user_type : ''}`;
    const plan_type = `${app_vars.plan_type ? app_vars.plan_type : ''}`;
    const logout_url = `${app_vars.logout_url}`;
    const user_name = `${app_vars.user_data? app_vars.user_data.user_name : ''}`;
    // Set the active menu item based on the current route path
    const setActiveMenu = (path) => {
      if ( path.includes('/inbox') ) {
        activeIndex.value = 'inbox';
      } else if ( path.includes('my-jobs') ) {
        activeIndex.value = 'my-jobs';
      } else if ( path.includes('all-jobs') ) {
        activeIndex.value = 'all-jobs';
      } else if ( path.includes('job-details') ) {
        activeIndex.value = 'all-jobs';
      }  else if ( path.includes('jobs') ) {
        activeIndex.value = 'jobs';
      } else if ( path.includes('post-job') ) {
        activeIndex.value = 'post-job';
      }  else if ( path.includes('adjusters') ) {
        activeIndex.value = 'adjusters';
      } else if ( path.includes('/payment-history') ) {
        activeIndex.value = 'payment-history';
      }  else if ( path.includes('/change-password') ) {
        activeIndex.value = 'change-password';
      } else {
        activeIndex.value = 'my-profile';
      }
    };

    const handleLogout = () => {
      window.location.href = logout_url;
    };

    // Call setActiveMenu on component mount
    onMounted(() => {
      if( ! is_logged_in ){
        window.location.href = `${app_vars.auth_page}`;
      }
      setActiveMenu(route.path);
    });

    // Watch the route for changes and update the active menu item accordingly
    watch(route, (newRoute) => {
      setActiveMenu(newRoute.path);
    });

    // Handle manual tab selection from the menu
    const handleSelect = (key) => {
      activeIndex.value = key;
      // Navigate to the selected route when a menu item is clicked
      router.push(`/${key}`);
    };

    // Handler for menu icon click
    const handleMenuIconClick = () => {
      window.location.href = home_url;
    };

    return {
      activeIndex,
      handleSelect,
      handleLogout,
      user_type,
      logout_url,
      user_name,
      user_status,
      plan_type,
      logo_url,
      handleMenuIconClick,
    };
  },
};
</script>
<style scoped>
.el-menu-item .el-image__inner {
  vertical-align: middle !important;
}
</style>
