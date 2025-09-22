<template>
  <div class="inactive-account-box">
    <el-empty description="Your profile is under review." />
    <div class="inactive-message">
      <el-icon style="font-size: 32px; color: #e6a23c; margin-bottom: 10px;">
        <i class="el-icon-warning"></i>
      </el-icon>
      <h2>Your profile is being reviewed</h2>
      <p>
        Please wait until your profile is approved by our team.<br>
        You will be notified once your account is active and you can access all features.
      </p>
    </div>
  </div>
</template>

<script>
export default {
  name: 'UnderReview',
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
  color: #e6a23c;
  margin-bottom: 8px;
}
.inactive-message p {
  color: #606266;
  font-size: 1.08em;
  margin-bottom: 0;
}
</style>