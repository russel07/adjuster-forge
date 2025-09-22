<template>
  <div>
    <h3 style="margin-top:30px;">Company Details</h3>
    <el-descriptions border :column="2">
      <el-descriptions-item label="Company Name">{{ companyData.company_name }}</el-descriptions-item>
      <el-descriptions-item label="Phone">{{ companyData.phone }}</el-descriptions-item>
      <el-descriptions-item label="Address">{{ companyData.address }}</el-descriptions-item>
      <el-descriptions-item label="DOT/MC">{{ companyData.dot_mc }}</el-descriptions-item>
      <el-descriptions-item label="Website">{{ companyData.website }}</el-descriptions-item>
      <el-descriptions-item label="Logo">
        <template v-if="companyData.logo">
          <img :src="imgUrl + companyData.logo" alt="Company Logo" style="max-width: 120px; border-radius: 6px;" />
        </template>
        <template v-else>Not uploaded</template>
      </el-descriptions-item>
      <el-descriptions-item label="About">{{ companyData.about }}</el-descriptions-item>
    </el-descriptions>
  </div>
</template>

<script>
export default {
  name: 'CompanyDetails',
  props: {
    companyData: {
      type: Object,
      required: true
    },
    imgUrl: {
      type: String,
      required: true
    }
  }
};
</script>

<style scoped>
h3 {
  font-weight: 600;
  color: #303133;
  margin-bottom: 18px;
}

.el-descriptions {
  background: #fff;
  border-radius: 8px;
  box-shadow: 0 2px 8px rgba(0,0,0,0.04);
  margin-bottom: 24px;
}

.el-descriptions__label {
  font-weight: 500;
  color: #606266;
}

.el-descriptions__content {
  color: #303133;
}
</style>
