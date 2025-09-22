<template>
  <div class="inactive-account-box">
    <el-empty :description="`Upgrade to ${plan_type === 'free_trial' ? 'Standard or Premium' : 'Premium'} to unlock all features!`" />
    <div class="inactive-message">
      <el-icon style="font-size: 32px; color: #409eff; margin-bottom: 10px;">
        <i class="el-icon-star-on"></i>
      </el-icon>
      <h2>Upgrade to {{ plan_type === 'free_trial' ? 'Standard or Premium' : 'Premium' }}</h2>
      <p>
        You are currently on a {{ plan_type === 'free_trial' ? 'Free Trial' : 'Standard' }} plan.<br>
        You need to Upgrade to {{ plan_type === 'free_trial' ? 'Standard or Premium' : 'Premium' }} to access this feature.
      </p>
      <el-button type="primary" size="large" style="margin-top: 18px;" @click="$router.push('/subscription')">
        Upgrade Now
      </el-button>
    </div>
  </div>
</template>

<script>
export default {
  name: 'UpgradeToPro',
  setup() {
    const app_vars = window.driver_forge_app_vars;
    const user_status = `${app_vars.user_status ? app_vars.user_status : ''}`;
    const user_type = `${app_vars.user_data? app_vars.user_data.user_type : ''}`;
    const plan_type = `${app_vars.plan_type ? app_vars.plan_type : ''}`;
    return { 
      user_status, 
      user_type, 
      plan_type 
    };
  }
};
</script>

<style scoped>
.inactive-account-box {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  min-height: 350px;
  background: #f8fafc;
  border-radius: 16px;
  box-shadow: 0 4px 24px rgba(60,120,200,0.08);
  padding: 40px 20px;
  margin: 40px auto;
  max-width: 500px;
}
.inactive-message {
  text-align: center;
  margin-top: 10px;
}
.inactive-message h2 {
  font-size: 1.5rem;
  color: #409eff;
  margin-bottom: 8px;
}
.inactive-message p {
  color: #606266;
  font-size: 1.08em;
  margin-bottom: 0;
}
</style>