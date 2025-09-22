<template>
  <el-container class="full-height">
    <el-main class="main-center">
      <Header />
      <div class="dashboard-container">
        <h1>Stripe Settings</h1>

        <el-card>
          <el-form :model="form" :rules="rules" ref="formRef" label-width="200px"
            class="dashboard-setting-form-container">
            <el-row>
              <el-col :span="12">
  
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
      </div>
    </el-main>
    <el-footer>
      <Footer />
    </el-footer>
  </el-container>
</template>

<script>
import { onMounted, ref } from 'vue';
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

    const form = ref({
      payment_gateway: '',
      stripe_public_key: '',
      stripe_secret_key: '',
      paypal_client_id: '',
      paypal_secret_key: '',
    });

    const validateGatewayKey = (rule, value, callback) => {
      if (form.value.payment_gateway.includes('Stripe') || form.value.payment_gateway.includes('Both')) {
        if (!value) {
          return callback(new Error(`The ${rule.field.replace('_', ' ')} is required`));
        }
      }
      callback();
    };

    /*watch(() => form.value.payment_gateway, (newValue) => {
      if (newValue === 'Stripe') {
        form.value.paypal_client_id = '';
        form.value.paypal_secret_key = '';
      } else if (newValue === 'PayPal') {
        form.value.stripe_public_key = '';
        form.value.stripe_secret_key = '';
      }
    });*/

    const showThis = (gateway) => {
      return form.value.payment_gateway.includes(gateway);
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

    onMounted(() => {
      fetchSettings();
    });

    return {
      form,
      rules,
      onSubmit,
      formRef,
      showThis,
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
</style>
