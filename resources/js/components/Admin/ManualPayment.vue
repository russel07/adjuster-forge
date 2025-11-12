<template>
  <el-container class="full-height">
    <el-main class="main-center">
      <Header />
      <div class="dashboard-container">
        <el-card>
          <template #header>
            <div class="card-header">
              <h1>Update User Payment Manually</h1>
            </div>
          </template>
          <template #default>
            <el-form :model="form" :rules="rules" ref="formRef" label-width="200px"
              class="dashboard-setting-form-container">
              <el-row :gutter="20">
                <el-col :span="24">
                  <el-form-item label="Customer ID" prop="customer_id">
                    <el-input v-model="form.customer_id" placeholder="Enter Stripe customer ID"></el-input>
                  </el-form-item>

                  <el-form-item label="Transaction ID" prop="transaction_id">
                    <el-input v-model="form.transaction_id" placeholder="Enter transaction ID"></el-input>
                  </el-form-item>
                  
                  <el-form-item label="Plan Type" prop="plan_type">
                    <el-select v-model="form.plan_type" placeholder="Select plan type" style="width: 100%">
                      <template v-if="form.user_type === 'adjuster'">
                        <el-option label="adjuster Monthly Fees" value="adjuster Monthly Fees"></el-option>
                        <el-option label="adjuster Yearly Fees" value="adjuster Yearly Fees"></el-option>
                      </template>
                      <template v-else-if="form.user_type === 'company'">
                        <el-option label="Premium" value="Premium"></el-option>
                        <el-option label="Standard" value="Standard"></el-option>
                      </template>
                      <template v-else>
                        <el-option label="Basic" value="Basic"></el-option>
                        <el-option label="Company Premium" value="Company Premium"></el-option>
                        <el-option label="Premium" value="Premium"></el-option>
                      </template>
                    </el-select>
                  </el-form-item>

                  <el-form-item label="Payment Interval" prop="payment_interval">
                    <el-select v-model="form.payment_interval" placeholder="Select payment interval" style="width: 100%">
                      <el-option label="Monthly" value="monthly"></el-option>
                      <el-option label="Yearly" value="yearly"></el-option>
                    </el-select>
                  </el-form-item>

                  <el-form-item label="Amount" prop="amount">
                    <el-input-number 
                      v-model="form.amount" 
                      :precision="2" 
                      :step="5" 
                      :min="0" 
                      placeholder="Enter amount"
                      style="width: 100%"
                    ></el-input-number>
                  </el-form-item>

                  <el-form-item label="Paid Date" prop="paid_date">
                    <el-date-picker
                      v-model="form.paid_date"
                      type="datetime"
                      placeholder="Select paid date and time"
                      style="width: 100%"
                      format="YYYY-MM-DD HH:mm:ss"
                      value-format="YYYY-MM-DD HH:mm:ss"
                    ></el-date-picker>
                  </el-form-item>
                </el-col>
              </el-row>
              <el-row>
                <el-col :span="24">
                  <div class="form-actions">
                    <el-button type="primary" @click="onSubmit" class="setting-button">
                      Save Payment Information
                    </el-button>
                    <el-button @click="goBack" class="cancel-button">
                      Cancel
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
import { CopyDocument } from '@element-plus/icons-vue';
import AlertMessage from '../../Composable/AlertMessage';
import { useAppHelper } from '../../Composable/appHelper';
import { loader } from '../../Composable/Loader';
import Footer from '../Footer.vue';
import Header from '../Header';
export default {
  name: 'ManualPayment',
  components: {
    Header,
    Footer,
  },
  setup() {
    const route = useRoute();
    const router = useRouter();
    const { get, post } = useAppHelper();
    const { success, error } = AlertMessage();
    const { startLoading, stopLoading } = loader();
    const user_id  = ref(`${route.params.user_id}`)
    const formRef = ref(null);
    const app_vars = window.adjuster_forge_app_vars || {};
    const form = ref({
      user_id:'',
      user_type: '',
      customer_id: '',
      transaction_id: '',
      plan_type: '',
      payment_interval: '',
      amount: 0,
      paid_date: new Date().toISOString().slice(0, 19).replace('T', ' ')
    });

    // Validation rules
    const rules = {
      user_id: [
        { required: true, message: "User ID is required", trigger: "blur" },
      ],
      user_type: [
        { required: true, message: "User type is required", trigger: "blur" },
      ],
      customer_id: [
        { required: true, message: "Customer ID is required", trigger: "blur" },
      ],
      transaction_id: [
        { required: true, message: "Transaction ID is required", trigger: "blur" },
      ],
      plan_type: [
        { required: true, message: "Plan type is required", trigger: "blur" },
      ],
      payment_interval: [
        { required: true, message: "Payment interval is required", trigger: "blur" },
      ],
      amount: [
        { required: true, message: "Amount is required", trigger: "blur" },
        { type: 'number', message: 'Amount must be a valid number', trigger: 'blur' },
        { validator: (rule, value, callback) => {
            if (value !== null && value < 0) {
              callback(new Error('Amount cannot be negative'));
            } else {
              callback();
            }
          }, trigger: 'blur' 
        },
      ],
      paid_date: [
        { required: true, message: "Paid date is required", trigger: "blur" },
        { type: 'date', message: 'Please select a valid date', trigger: 'change' },
      ],
    };

    const fetchUser = async () => {
      startLoading();
      try {
        const response = await get(`user/${user_id.value}`);
        if (response.status === 'success') {
          form.value.user_id = response.data.user_id;
          form.value.user_type = response.data.user_type;
        } else {
          error(response.message);
        }
      } catch (err) {
        error(err.message);
      } finally {
        stopLoading();
      }
    };

    const goBack = () => {
      router.go(-1); // Go back to previous page
    };

    // Validate and submit the form
    const onSubmit = async () => {
      formRef.value.validate((valid) => {
        if (valid) {
          updatePaymentSettings();
        } else {
          return false;
        }
      });
    };

    const updatePaymentSettings = async () => {
      startLoading();
      try {
        const formData = {
          ...form.value,
        }
        const response = await post('update-manual-payment', formData);

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
      fetchUser();
    });

    return {
      form,
      rules,
      onSubmit,
      formRef,
      CopyDocument,
      goBack,
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
  max-width: 50%;
}

.webhook-list {
  list-style: none;
  padding: 0;
  margin: 0;
}

.webhook-item {
  margin-bottom: 15px;
  padding: 12px;
  background: #f8f9fa;
  border-radius: 6px;
  border: 1px solid #e9ecef;
}

.webhook-content {
  display: flex;
  align-items: center;
  gap: 10px;
  flex-wrap: wrap;
}

.webhook-url {
  background: #ffffff;
  padding: 6px 10px;
  border-radius: 4px;
  border: 1px solid #d0d7de;
  font-family: 'Monaco', 'Menlo', 'Ubuntu Mono', monospace;
  font-size: 13px;
  color: #24292f;
  flex: 1;
  min-width: 300px;
  word-break: break-all;
  margin: 0 10px;
}

.copy-button {
  margin-left: auto;
  flex-shrink: 0;
}

@media (max-width: 768px) {
  .webhook-content {
    flex-direction: column;
    align-items: flex-start;
  }
  
  .webhook-url {
    min-width: 100%;
    margin: 5px 0;
  }
  
  .copy-button {
    margin-left: 0;
    align-self: flex-end;
  }
}
</style>
