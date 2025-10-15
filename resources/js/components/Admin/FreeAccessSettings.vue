<template>
  <el-container class="full-height">
    <el-main class="main-center">
      <Header />
      <div class="dashboard-container">
        <el-card>
          <template #header>
            <div class="card-header">
              <h1>Allow Free access to company and adjuster</h1>
            </div>
          </template>
          <template #default>
            <el-form :model="form" :rules="rules" ref="formRef" label-width="180px"
              class="dashboard-setting-form-container">
              <el-row :gutter="24">
                <el-col :lg="12" :md="24" :sm="24">
                  <div class="settings-card">
                    <div class="card-section-header">
                      <h3>adjuster Free Access Settings</h3>
                    </div>
                    <div class="settings-content">
                      <el-form-item label="Enable Free Access" prop="allow_free_adjuster">
                        <el-switch 
                          v-model="form.allow_free_adjuster" 
                          active-text="Enabled" 
                          inactive-text="Disabled"
                          size="large"
                        ></el-switch>
                      </el-form-item>
                      
                      <el-form-item 
                        label="Access Interval" 
                        prop="adjuster_free_interval"
                        :class="{ 'disabled-field': !form.allow_free_adjuster }"
                      >
                        <el-select 
                          v-model="form.adjuster_free_interval" 
                          placeholder="Select Interval" 
                          style="width: 100%"
                          :disabled="!form.allow_free_adjuster"
                        >
                          <el-option label="Monthly" value="month"></el-option>
                          <el-option label="Yearly" value="year"></el-option>
                        </el-select>
                      </el-form-item>

                      <el-form-item 
                        label="Free Access Duration" 
                        prop="adjuster_interval_amount"
                        :class="{ 'disabled-field': !form.allow_free_adjuster }"
                      >
                        <el-input-number
                          v-model="form.adjuster_interval_amount"
                          :precision="0"
                          :step="1"
                          :min="1"
                          :max="365"
                          placeholder="Enter duration"
                          style="width: 100%"
                          :disabled="!form.allow_free_adjuster"
                        ></el-input-number>
                        <div class="field-help-text">
                          Duration in {{ form.adjuster_free_interval === 'month' ? 'months' : 'years' }}
                        </div>
                      </el-form-item>
                    </div>
                  </div>
                </el-col>

                <el-col :lg="12" :md="24" :sm="24">
                  <div class="settings-card">
                    <div class="card-section-header">
                      <h3>Company Free Access Settings</h3>
                    </div>
                    <div class="settings-content">
                      <el-form-item label="Enable Free Access" prop="allow_free_company">
                        <el-switch 
                          v-model="form.allow_free_company" 
                          active-text="Enabled" 
                          inactive-text="Disabled"
                          size="large"
                        ></el-switch>
                      </el-form-item>
                      
                      <el-form-item 
                        label="Access Interval" 
                        prop="company_free_interval"
                        :class="{ 'disabled-field': !form.allow_free_company }"
                      >
                        <el-select 
                          v-model="form.company_free_interval" 
                          placeholder="Select Interval" 
                          style="width: 100%"
                          :disabled="!form.allow_free_company"
                        >
                          <el-option label="Monthly" value="month"></el-option>
                          <el-option label="Yearly" value="year"></el-option>
                        </el-select>
                      </el-form-item>

                      <el-form-item 
                        label="Free Access Duration" 
                        prop="company_interval_amount"
                        :class="{ 'disabled-field': !form.allow_free_company }"
                      >
                        <el-input-number
                          v-model="form.company_interval_amount"
                          :precision="0"
                          :step="1"
                          :min="1"
                          :max="365"
                          placeholder="Enter duration"
                          style="width: 100%"
                          :disabled="!form.allow_free_company"
                        ></el-input-number>
                        <div class="field-help-text">
                          Duration in {{ form.company_free_interval === 'month' ? 'months' : 'years' }}
                        </div>
                      </el-form-item>
                    </div>
                  </div>
                </el-col>
              </el-row>
              
              <el-row>
                <el-col :span="24">
                  <div class="form-actions">
                    <el-button type="primary" @click="onSubmit" class="setting-button" size="large">
                      <el-icon><Check /></el-icon>
                      Save Settings
                    </el-button>
                  </div>
                </el-col>
              </el-row>
            </el-form>
          </template>
        </el-card>
      </div>
    </el-main>
    <el-footer>
      <Footer />
    </el-footer>
  </el-container>
</template>

<script>
import { onMounted, ref } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { Check } from '@element-plus/icons-vue';
import AlertMessage from '../../Composable/AlertMessage';
import { useAppHelper } from '../../Composable/appHelper';
import { loader } from '../../Composable/Loader';
import Footer from '../Footer.vue';
import Header from '../Header.vue';
export default {
  name: 'FreeAccessSettings',
  components: {
    Header,
    Footer,
    Check,
  },
  setup() {
    const route = useRoute();
    const router = useRouter();
    const { get, post } = useAppHelper();
    const { success, error } = AlertMessage();
    const { startLoading, stopLoading } = loader();
    const formRef = ref(null);
    const app_vars = window.adjuster_forge_app_vars || {};
    const form = ref({
      allow_free_adjuster: false,
      adjuster_free_interval: '',
      adjuster_interval_amount: 1,
      allow_free_company: false,
      company_free_interval: '',
      company_interval_amount: 1,
    });

    // Validation rules
    const rules = {
      allow_free_adjuster: [
        { required: true, message: "Allow free adjuster is required", trigger: "change" },
      ],
      adjuster_free_interval: [
        { 
          validator: (rule, value, callback) => {
            if (form.value.allow_free_adjuster === true || form.value.allow_free_adjuster === 'true') {
              if (!value) {
                callback(new Error('adjuster free interval is required when free adjuster is enabled'));
              } else {
                callback();
              }
            } else {
              callback();
            }
          }, 
          trigger: "blur" 
        },
      ],
      adjuster_interval_amount: [
        { 
          validator: (rule, value, callback) => {
            if (form.value.allow_free_adjuster === true || form.value.allow_free_adjuster === 'true') {
              if (!value) {
                callback(new Error('adjuster interval amount is required when free adjuster is enabled'));
              } else {
                callback();
              }
            } else {
              callback();
            }
          }, 
          trigger: "blur" 
        },
      ],
      allow_free_company: [
        { required: true, message: "Allow free company is required", trigger: "change" },
      ],
      company_free_interval: [
        { 
          validator: (rule, value, callback) => {
            if (form.value.allow_free_company === true || form.value.allow_free_company === 'true') {
              if (!value) {
                callback(new Error('Company free interval is required when free company is enabled'));
              } else {
                callback();
              }
            } else {
              callback();
            }
          }, 
          trigger: "blur" 
        },
      ],
      company_interval_amount: [
        { 
          validator: (rule, value, callback) => {
            if (form.value.allow_free_company === true || form.value.allow_free_company === 'true') {
              if (!value) {
                callback(new Error('Company free interval is required when free company is enabled'));
              } else {
                callback();
              }
            } else {
              callback();
            }
          }, 
          trigger: "blur" 
        },
      ],
    };

    const fetchSettings = async () => {
      startLoading();
      try {
        const response = await get("admin-settings");
        if (response.status === 'success') {
          const settings = response.settings;
          
          form.value.allow_free_adjuster = settings.allow_free_adjuster === 'yes';
          form.value.adjuster_free_interval = settings.adjuster_free_interval;
          form.value.adjuster_interval_amount = settings.adjuster_interval_amount;
          form.value.allow_free_company = settings.allow_free_company === 'yes';
          form.value.company_free_interval = settings.company_free_interval;
          form.value.company_interval_amount = settings.company_interval_amount;
        } else {
          error(response.message);
        }
      } catch (err) {
        error('Error fetching settings:' + err.response);
      }
      stopLoading();
    };

    // Validate and submit the form
    const onSubmit = async () => {
      formRef.value.validate(async (valid) => {
        if (valid) {
          startLoading();
          try {
            const response = await post('update-free-access-settings', form.value, {
              headers: { "Content-Type": "multipart/form-data" }
            });
            success(response.message);

            if (response.status === 'success') {
              success(response.message);
            } else {
              error(response.message);
            }
          } catch (error) {
            error('Error updating settings:' + error.response.data.message);
          }
          stopLoading();
        } else {
          error('Please fill in all required fields.');
          return false;
        }
      });
    };

    onMounted(() => {
      fetchSettings();
    });

    const updatePaymentSettings = async () => {
      startLoading();
      try {
        const formData = {
          ...form.value,
        }
        const response = await post('save-payment-settings', formData);

        if (response.status === 'success') {
          success(response.message);
        } else {
          error(response.message);
        }
      } catch (error) {
        error('Error updating payment settings:');
      }
      stopLoading();
    };

    onMounted(() => {
      fetchSettings();
    });

    return {
      form,
      rules,
      onSubmit,
      formRef,
    };
  },
};
</script>
<style scoped>
.dashboard-container {
  padding: 20px;
  max-width: 100%;
}

.full-height {
  min-height: 100vh;
}

.el-col {
  padding: 10px;
}

.form-actions {
  display: flex;
  gap: 16px;
  justify-content: center;
  margin-top: 20px;
}

.setting-button, .cancel-button {
  min-width: 140px;
  font-weight: 500;
}

.dashboard-setting-form-container {
  padding: 20px 0;
}

/* Settings Card Styling */
.settings-card {
  height: 100%;
  border-radius: 12px;
  box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
  border: 1px solid #e4e7ed;
  background: #fff;
}

.card-section-header {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  color: white;
  padding: 20px 24px;
  border-radius: 12px 12px 0 0;
  border-bottom: 3px solid #5a67d8;
  position: relative;
  overflow: hidden;
}

.card-section-header::before {
  content: '';
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: linear-gradient(45deg, rgba(255,255,255,0.1) 0%, rgba(255,255,255,0.05) 100%);
  pointer-events: none;
}

.card-section-header h3 {
  margin: 0;
  font-size: 1.3rem;
  font-weight: 600;
  text-align: center;
  position: relative;
  z-index: 1;
  text-shadow: 0 1px 2px rgba(0, 0, 0, 0.1);
}

.settings-content {
  padding: 24px;
}

/* Form Items */
.el-form-item {
  margin-bottom: 24px;
}

.el-form-item__label {
  font-weight: 500;
  color: #5a6c7d;
}

/* Disabled Fields */
.disabled-field .el-form-item__label {
  color: #c0c4cc;
}

.disabled-field .el-select,
.disabled-field .el-input-number {
  opacity: 0.6;
}

/* Help Text */
.field-help-text {
  font-size: 12px;
  color: #8492a6;
  margin-top: 4px;
  font-style: italic;
}

/* Switch Styling */
.el-switch {
  --el-switch-on-color: #67c23a;
  --el-switch-off-color: #dcdfe6;
}

</style>
