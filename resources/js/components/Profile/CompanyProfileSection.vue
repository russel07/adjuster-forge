<template>
    <el-form
        :model="profileForm"
        ref="profileFormRef"
        :rules="rules"
        label-position="top"
        class="profile-complete-form"
        validate-on-blur
        validate-on-change
        >
        <div class="form-section-header">
            <h1>Your Details</h1>
            <p>We just need a few details from you to get you started</p>
        </div>

        <el-divider content-position="left">Company Details</el-divider>
        <el-row :gutter="20">
          <el-col :span="12">
            <el-form-item label="Phone Number" prop="phone">
                <el-input v-model="profileForm.phone" placeholder="Enter your phone number"></el-input>
            </el-form-item>
          </el-col>
          <el-col :span="12">
            <el-form-item label="Company Name" prop="company_name">
                <el-input v-model="profileForm.company_name" placeholder="Enter your company name"></el-input>
            </el-form-item>
          </el-col>
          <el-col :span="12">
            <el-form-item label="Website" prop="website">
              <el-input v-model="profileForm.website" placeholder="Enter company website"></el-input>
            </el-form-item>
          </el-col>
          <el-col :span="12">
            <el-form-item label="DOT / MC Number" prop="dot_mc">
              <el-input v-model="profileForm.dot_mc" placeholder="Enter DOT Or MC Number"></el-input>
            </el-form-item>
          </el-col>
        </el-row>

        <el-row :gutter="20">
          <el-col :xs="24" :sm="12">
            <el-form-item label="Address" prop="address">
              <el-input v-model="profileForm.address" type="textarea" :rows="4" placeholder="Enter company address"></el-input>
            </el-form-item>
          </el-col>
          <el-col :xs="24" :sm="12">
            <el-form-item label="Upload Company Logo" prop="logo">
              <el-upload
                class="upload-box"
                :on-change="logoFileChange"
                :file-list="LogoFileList"
                :auto-upload="false"
                accept=".png,.jpg,.jpeg"
                :limit="1"
                drag
              >
                <i class="el-icon-upload"></i>
                <div>Click or drag image to upload</div>
                <template #tip>
                  <span v-if="LogoFileList.length">
                    <el-icon><Picture /></el-icon>
                    {{ LogoFileList[0].name }} ({{ (LogoFileList[0].size/1024/1024).toFixed(2) }} MB)
                  </span>
                </template>
              </el-upload>
            </el-form-item>
          </el-col>
        </el-row>
        <el-row :gutter="20">
          <el-col :span="24">
            <el-form-item label="About" prop="about">
              <el-input v-model="profileForm.about" type="textarea" :rows="4" placeholder="Enter company about"></el-input>
            </el-form-item>
          </el-col>
        </el-row>
        <el-divider content-position="left">Information Confirmation</el-divider>
        <el-form-item prop="declaration_agreed" :required="true">
            <el-checkbox v-model="profileForm.declaration_agreed">
            I confirm that all the provided information is correct.
            </el-checkbox>
            <span class="terms-link">
            &nbsp;(<a :href="t_c_page_url" target="_blank" class="terms-conditions">View Terms & Conditions</a>)
            </span>
        </el-form-item>

        <el-form-item>
            <div class="button-container">
            <el-button type="primary" class="submit-button" @click="handleSubmit" :loading="loading">
                Submit
            </el-button>
            </div>
        </el-form-item>
    </el-form>
</template>
<script>
import { ref } from 'vue';
import { Picture } from '@element-plus/icons-vue';
export default {
  name: 'CompanyProfileSection',
  props: {
    error: Function,
    t_c_page_url: String,
    loading: {
      type: Boolean,
      default: false
    }
  },
  components: { Picture },
  setup(props, { emit }) {
    const LogoFileList = ref([]);
    const profileForm = ref({
      phone: '',
      company_name: '',
      website: '',
      dot_mc: '',
      address: '',
      logo: null,
      about: ''
    });
    const profileFormRef = ref(null);
    const rules = {
      phone: [
        { required: true, message: 'Please enter your phone number', trigger: 'blur' },
        { pattern: /^[0-9+\-\s()]{7,20}$/, message: 'Please enter a valid phone number', trigger: 'blur' },
      ],
      company_name: [
        { required: true, message: 'Please enter your company name', trigger: 'blur' },
        { min: 5, max: 100, message: 'Company name must be between 5 and 100 characters', trigger: 'blur' },
      ],
      website: [
        { type: 'url', message: 'Please enter a valid URL', trigger: 'blur' },
      ],
      dot_mc: [
        { required: true, message: 'Please enter your DOT / MC Number', trigger: 'blur' },
      ],
      address: [
        { required: true, message: 'Please enter your address', trigger: 'blur' },
      ],
      logo: [
        { required: true, message: 'Please upload your company logo', trigger: 'change' },
      ],
      about: [
        { required: true, message: 'Please enter information about your company', trigger: 'blur' },
      ],
    }

    const logoFileChange = (file, uploadedFiles) => {
      const allowedTypes = ['image/png', 'image/jpg', 'image/jpeg'];
      const allowedExtensions = ['.png', '.jpg', '.jpeg'];
      if (!allowedTypes.includes(file.raw.type) && !allowedExtensions.some(ext => file.raw.name.toLowerCase().endsWith(ext))) {
        LogoFileList.value = [];
        props.error('Invalid file type. Please upload a PNG, JPG, or JPEG image.');
        return false;
      }
      const maxSize = 2 * 1024 * 1024;
      if (file.raw.size > maxSize) {
        LogoFileList.value = [];
        props.error('Logo size must be less than 2MB');
        return false;
      }
      LogoFileList.value = uploadedFiles.slice(-1);
      profileForm.value.logo = file.raw;
    };

    // Submit handler
    const handleSubmit = () => {
      profileFormRef.value.validate(async (valid) => {
        if (!valid) return;

        const formData = new FormData();
        for (const key in profileForm.value) {
          formData.append(key, profileForm.value[key]);
        }

        emit('submit-profile', formData);
      });
    };

    return {
      profileForm,
      profileFormRef,
      rules,
      LogoFileList,
      logoFileChange,
      t_c_page_url: props.t_c_page_url,
      loading: props.loading,
      handleSubmit,
      Picture,
    }
  }
};
</script>
<style scoped>
.upload-box {
  width: 100%;
  border: 1px dashed #d9d9d9;
  border-radius: 8px;
  text-align: center;
  padding: 10px 0;
  background: #f8fafc;
}
</style>
