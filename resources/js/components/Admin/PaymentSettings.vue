<template>
  <el-container class="full-height">
    <el-main class="main-center">
      <Header />
      <div class="dashboard-container">
        <el-card>
          <template #header>
            <div class="card-header">
              <h1>Stripe Settings</h1>
            </div>
          </template>
          <template #default>
            <el-card>
              <el-form :model="form" :rules="rules" ref="formRef" label-width="200px"
                class="dashboard-setting-form-container">
                <el-row>
                  <el-col :span="24">      
                    <div class="stripe-key-container">
                      <el-form-item label="Stripe Public Key" prop="stripe_public_key">
                        <el-input v-model="form.stripe_public_key"></el-input>
                      </el-form-item>

                      <el-form-item label="Stripe Secret Key" prop="stripe_secret_key">
                        <el-input v-model="form.stripe_secret_key"></el-input>
                      </el-form-item>
                    </div>
                  </el-col>
                </el-row>
                <el-row>
                  <el-col :span="24">
                    <el-button type="primary" @click="onSubmit" class="setting-button">Save</el-button>
                  </el-col>
                </el-row>
              </el-form>
            </el-card>

            <el-card class="mt-20">
              <h2>Webhooks for Stripe</h2>
              <p>Use the following URLs to set up webhooks in your Stripe dashboard:</p>
              <ul class="webhook-list">
                <li v-for="(url, event) in webhooks" :key="event" class="webhook-item">
                  <div class="webhook-content">
                    <strong>{{ event }}:</strong> 
                    <code class="webhook-url">{{ url }}</code>
                    <el-button 
                      type="primary" 
                      size="small" 
                      :icon="CopyDocument"
                      @click="copyToClipboard(url, event)"
                      class="copy-button"
                      title="Copy URL"
                    >
                      Copy
                    </el-button>
                  </div>
                </li>
              </ul>
            </el-card>
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
import { CopyDocument } from '@element-plus/icons-vue';
import AlertMessage from '../../Composable/AlertMessage';
import { useAppHelper } from '../../Composable/appHelper';
import { loader } from '../../Composable/Loader';
import Footer from '../Footer.vue';
import Header from '../Header';
export default {
  name: 'PaymentSettings',
  components: {
    Header,
    Footer,
  },
  setup() {
    const { get, post } = useAppHelper();
    const { success, error } = AlertMessage();
    const { startLoading, stopLoading } = loader();
    const formRef = ref(null);
    const app_vars = window.adjuster_forge_app_vars || {};
    const rest_url = app_vars.rest_info?.rest_url || '';
    const form = ref({
      payment_gateway: '',
      stripe_public_key: '',
      stripe_secret_key: '',
      paypal_client_id: '',
      paypal_secret_key: '',
    });

    const webhooks = {
      'Subscribed': `${rest_url}/webhook-subscribe-to-stripe`,
      'Subscription Canceled': `${rest_url}/webhook-cancel-subscription-from-stripe`,
      'Refund': `${rest_url}/webhook-refund-from-stripe`,
    };

    const validateGatewayKey = (rule, value, callback) => {
      if (form.value.payment_gateway.includes('Stripe') || form.value.payment_gateway.includes('Both')) {
        if (!value) {
          return callback(new Error(`The ${rule.field.replace('_', ' ')} is required`));
        }
      }
      callback();
    };

    // Validation rules
    const rules = {
      payment_gateway: [
        { required: true, message: "Please select at least one payment gateway", trigger: "change" },
      ],
      stripe_public_key: [
        { validator: validateGatewayKey, trigger: 'blur' },
      ],
      stripe_secret_key: [
        { validator: validateGatewayKey, trigger: 'blur' },
      ],
      paypal_client_id: [
        { validator: validateGatewayKey, trigger: 'blur' },
      ],
      paypal_secret_key: [
        { validator: validateGatewayKey, trigger: 'blur' },
      ],
    };

    const fetchSettings = async () => {
      startLoading();
      try {
        const response = await get('get-payment-settings');
        if (response.status === 'success') {
          form.value.payment_gateway = response.data.payment_gateway;
          form.value.stripe_public_key = response.data.stripe_public_key;
          form.value.stripe_secret_key = response.data.stripe_secret_key;
          form.value.paypal_client_id = response.data.paypal_client_id;
          form.value.paypal_secret_key = response.data.paypal_secret_key;
        } else {
          error(response.message);
        }
      } catch (err) {
        error(err.message);
      } finally {
        stopLoading();
      }
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

    const copyToClipboard = async (url, eventName) => {
      try {
        await navigator.clipboard.writeText(url);
        success(`${eventName} webhook URL copied to clipboard!`);
      } catch (err) {
        // Fallback for older browsers
        const textArea = document.createElement('textarea');
        textArea.value = url;
        document.body.appendChild(textArea);
        textArea.select();
        document.execCommand('copy');
        document.body.removeChild(textArea);
        success(`${eventName} webhook URL copied to clipboard!`);
      }
    };

    onMounted(() => {
      fetchSettings();
    });

    return {
      form,
      rules,
      onSubmit,
      formRef,
      webhooks,
      copyToClipboard,
      CopyDocument,
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
