<template>
  <div class="common-layout">
    <el-container class="full-height">
      <el-main class="main-center">
        <Menu />
        <div class="dashboard-container">
          <AdjusterProfileSection
            v-if="user_type === 'adjuster'"
            :t_c_page_url="t_c_page_url"
            :loading="loading"
            :error="error"
            @submit-profile="submitProfile"
          />
          <CompanyProfileSection
            v-else-if="user_type === 'company'"
            :t_c_page_url="t_c_page_url"
            :loading="loading"
            :error="error"
            @submit-profile="submitProfile"
          />
        </div>
      </el-main>
    </el-container>
  </div>
</template>

<script>
import Menu from './Menu.vue';
import AdjusterProfileSection from './AdjusterProfileSection.vue';
import CompanyProfileSection from './CompanyProfileSection.vue';
import { useRouter } from 'vue-router';
import { useAppHelper } from '../../Composable/appHelper';
import AlertMessage from '../../Composable/AlertMessage';
import { loader } from '../../Composable/Loader';
import { ref } from 'vue';

export default {
  name: 'CompleteProfile',
  components: {
    Menu,
    AdjusterProfileSection,
    CompanyProfileSection
  },
  setup() {
    const router = useRouter();
    const { post } = useAppHelper();
    const { success, error } = AlertMessage();
    const { startLoading, stopLoading } = loader();
    const loading = ref(false);
    const app_vars = window.adjuster_forge_app_vars || {};
    const user_type = app_vars.user_data?.user_type || '';
    const { t_c_page: t_c_page_url } = app_vars;
    const { profile_page: profilePageURL } = app_vars;

    const submitProfile = async (formData) => {
      loading.value = true;
      startLoading();
      try {
        const response = await post('complete-profile', formData, {
          headers: { 'Content-Type': 'multipart/form-data' },
        });
        if (response.status === 'success') {
          success('Profile updated successfully');
          setTimeout(() => {
            window.location.href = profilePageURL;
          }, 1200);
        } else {
          error(response.message || 'Something went wrong');
        }
      } catch (err) {
        error(err.message || 'Unexpected error occurred');
      } finally {
        loading.value = false;
        stopLoading();
      }
    };

    return {
      t_c_page_url,
      user_type,
      submitProfile,
      loading,
      error
    };
  },
};
</script>

<style scoped>
</style>