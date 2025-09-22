<template>
  <div class="inactive-account-box">
    <el-empty description="Your profile has been rejected." />
    <div class="inactive-message">
      <el-icon style="font-size: 32px; color: #f56c6c; margin-bottom: 10px;">
        <i class="el-icon-close"></i>
      </el-icon>
      <h2>Your profile was not approved</h2>
      <p>
        Unfortunately, your account has been rejected.<br>
        If you believe this is a mistake, please contact support or review your profile and try again.
      </p>
    </div>
  </div>
</template>

<script>
export default {
  name: 'RejectedAccount',
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
  color: #f56c6c;
  margin-bottom: 8px;
}
.inactive-message {
  text-align: center;
  margin-top: 10px;
}
.inactive-message h2 {
  font-size: 1.5rem;
  color: #f56c6c;
  margin-bottom: 8px;
}
.inactive-message p {
  color: #606266;
  font-size: 1.08em;
  margin-bottom: 0;
}
</style>