<template>
  <el-header>
    <div class="header-content">
      <el-menu :default-active="activeIndex" class="el-menu-demo" mode="horizontal" @select="handleSelect"
        :router="true">
        <el-menu-item index="general" :to="{ path: '/general' }">General Settings</el-menu-item>
        <el-menu-item index="payment" :to="{ path: '/payment' }">Stripe Settings</el-menu-item>
        <el-menu-item index="drivers" :to="{ path: '/drivers' }">Drivers</el-menu-item>
        <el-menu-item index="companies" :to="{ path: '/companies' }">Companies</el-menu-item>
      </el-menu>
      <div class="h-6" />
    </div>
  </el-header>
</template>

<script>
import { ref, watch, onMounted } from 'vue';
import { useRoute, useRouter } from 'vue-router';

export default {
  name: 'Header',
  setup() {
    const route = useRoute(); // Get the current route
    const router = useRouter();
    const activeIndex = ref('general');

    // Set the active menu item based on the current route path
    const setActiveMenu = (path) => {
      if (path.includes('/general')) {
        activeIndex.value = 'general';
      } else if (path.includes('/payment')) {
        activeIndex.value = 'payment';
      } else if (path.includes('/subscriber')) {
        activeIndex.value = 'subscriber';
      } else if (path.includes('/purchased')) {
        activeIndex.value = 'purchased';
      } else if (path.includes('/drivers')) {
        activeIndex.value = 'drivers';
      } else if (path.includes('/driver-details')) {
        activeIndex.value = 'drivers';
      } else if (path.includes('/companies')) {
        activeIndex.value = 'companies';
      } else {
        activeIndex.value = 'general';
      }
    };

    // Call setActiveMenu on component mount
    onMounted(() => {
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

    return {
      activeIndex,
      handleSelect,
    };
  },
};
</script>
