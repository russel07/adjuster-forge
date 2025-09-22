<template>
  <el-container class="full-height">
    <el-main class="main-center">
      <Header />
      <div class="dashboard-container">
        <h1>General Settings</h1>

        <el-card>
          <el-form :model="form" :rules="rules" ref="formRef" label-width="200px"
            class="dashboard-setting-form-container">
            <el-row :gutter="20">
              <el-col :span="12">
                <el-form-item label="Auth Page" prop="auth_page_id">
                  <el-select v-model="form.auth_page_id" placeholder="Select Auth Page" style="width: 100%">
                    <el-option v-for="item in wp_pages" :key="item.id" :label="item.title" :value="item.id"></el-option>
                  </el-select>
                </el-form-item>
                
                <el-form-item label="Profile Page" prop="profile_page_id">
                  <el-select v-model="form.profile_page_id" placeholder="Select Profile Page" style="width: 100%">
                    <el-option v-for="item in wp_pages" :key="item.id" :label="item.title" :value="item.id"></el-option>
                  </el-select>
                </el-form-item>
                
                <el-form-item label="Driver Subscription Amount" prop="driver_subscription_amount">
                  <el-input v-model="form.driver_subscription_amount" type="number" :min="0" step="0.01" placeholder="Enter driver subscription amount"></el-input>
                </el-form-item>

                <el-form-item label="Driver Subscription ID" prop="driver_subscription_id">
                  <el-input v-model="form.driver_subscription_id" placeholder="Enter driver subscription Stripe ID"></el-input>
                </el-form-item>

                <el-form-item label="Driver Yearly Subscription Amount" prop="driver_yearly_subscription_amount">
                  <el-input v-model="form.driver_yearly_subscription_amount" type="number" :min="0" step="0.01" placeholder="Enter driver yearly subscription amount"></el-input>
                </el-form-item>

                <el-form-item label="Driver Yearly Subscription ID" prop="driver_yearly_subscription_id">
                  <el-input v-model="form.driver_yearly_subscription_id" placeholder="Enter driver yearly subscription Stripe ID"></el-input>
                </el-form-item>

                <el-form-item label="Allow Free Verification" prop="allow_free_verification">
                  <el-switch v-model="form.allow_free_verification" active-text="Yes" inactive-text="No"></el-switch>
                </el-form-item>

                <el-form-item label="Currency" prop="selected_currency">
                  <el-select v-model="form.selected_currency" placeholder="Select Currency" style="width: 100%">
                    <el-option v-for="item in currencies" :key="item" :label="item" :value="item"></el-option>
                  </el-select>
                </el-form-item>
              </el-col>
              
              <el-col :span="12">
                <el-form-item label="Standard Plan Monthly" prop="standard_membership_amount">
                  <el-input v-model="form.standard_membership_amount" type="number" :min="0" step="0.01" placeholder="Enter standard membership amount"></el-input>
                </el-form-item>

                <el-form-item label="Standard Plan ID" prop="standard_membership_id">
                  <el-input v-model="form.standard_membership_id" placeholder="Enter standard membership Stripe ID"></el-input>
                </el-form-item>

                <el-form-item label="Standard Yearly Plan Amount" prop="standard_yearly_membership_amount">
                  <el-input v-model="form.standard_yearly_membership_amount" type="number" :min="0" step="0.01" placeholder="Enter standard yearly membership amount"></el-input>
                </el-form-item>
                <el-form-item label="Standard Yearly Plan ID" prop="standard_yearly_membership_id">
                  <el-input v-model="form.standard_yearly_membership_id" placeholder="Enter standard yearly membership Stripe ID"></el-input>
                </el-form-item>

                <el-form-item label="Premium Plan Monthly" prop="premium_membership_amount">
                  <el-input v-model="form.premium_membership_amount" type="number" :min="0" step="0.01" placeholder="Enter premium membership amount"></el-input>
                </el-form-item>

                <el-form-item label="Premium Plan ID" prop="premium_membership_id">
                  <el-input v-model="form.premium_membership_id" placeholder="Enter premium membership Stripe ID"></el-input>
                </el-form-item>
                <el-form-item label="Premium Yearly Plan Amount" prop="premium_yearly_membership_amount">
                  <el-input v-model="form.premium_yearly_membership_amount" type="number" :min="0" step="0.01" placeholder="Enter premium yearly membership amount"></el-input>
                </el-form-item>
                <el-form-item label="Premium Yearly Plan ID" prop="premium_yearly_membership_id">
                  <el-input v-model="form.premium_yearly_membership_id" placeholder="Enter premium yearly membership Stripe ID"></el-input>
                </el-form-item>
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
import { onMounted, ref } from "vue";
import AlertMessage from "../../Composable/AlertMessage";
import { useAppHelper } from "../../Composable/appHelper";
import { loader } from '../../Composable/Loader';
import Header from "../Header";
import Footer from "../Footer";
export default {
  name: "Settings",
  components: {
    Header,
    Footer
  },
  setup() {
    const { get, post } = useAppHelper();
    const { success, error } = AlertMessage();
    const { startLoading, stopLoading } = loader();
    const formRef = ref(null);
    const form = ref({
      auth_page_id: '',
      profile_page_id: '',
      driver_subscription_amount: '',
      driver_subscription_id: '',
      driver_yearly_subscription_amount: '',
      driver_yearly_subscription_id: '',
      standard_membership_amount: '',
      standard_membership_id: '',
      standard_yearly_membership_amount: '',
      standard_yearly_membership_id: '',
      premium_membership_amount: '',
      premium_membership_id: '',
      premium_yearly_membership_amount: '',
      premium_yearly_membership_id: '',
      allow_free_verification: false,
      selected_currency: '',
    });

    // Validation rules
    const rules = {
      auth_page_id: [
        { required: true, message: 'Auth page is required, trigger: "change"'}
      ],
      profile_page_id: [
        { required: true, message: "Profile Page is required", trigger: "change" },
      ],
      driver_subscription_amount: [
        { required: true, message: "Driver Subscription Amount is required", trigger: "blur" },
      ],
      driver_subscription_id: [
        { required: true, message: "Driver Subscription ID is required", trigger: "blur" },
      ],
      driver_yearly_subscription_amount: [
        { required: true, message: "Driver Yearly Subscription Amount is required", trigger: "blur" },
      ],
      driver_yearly_subscription_id: [
        { required: true, message: "Driver Yearly Subscription ID is required", trigger: "blur" },
      ],
      standard_membership_amount: [
        { required: true, message: "Standard Membership Amount is required", trigger: "blur" },
      ],
      standard_membership_id: [
        { required: true, message: "Standard Membership ID is required", trigger: "blur" },
      ],
      standard_yearly_membership_amount: [
        { required: true, message: "Standard Yearly Membership Amount is required", trigger: "blur" },
      ],
      standard_yearly_membership_id: [
        { required: true, message: "Standard Yearly Membership ID is required", trigger: "blur" },
      ],
      premium_membership_amount: [
        { required: true, message: "Premium Membership Amount is required", trigger: "blur" },
      ],
      premium_membership_id: [
        { required: true, message: "Premium Membership ID is required", trigger: "blur" },
      ],
      premium_yearly_membership_amount: [
        { required: true, message: "Premium Yearly Membership Amount is required", trigger: "blur" },
      ],
      premium_yearly_membership_id: [
        { required: true, message: "Premium Yearly Membership ID is required", trigger: "blur" },
      ],
      allow_free_verification: [
        { required: true, message: "Allow Free Verification is required", trigger: "change" },
      ],
      selected_currency: [
        { required: true, message: "Currency is required", trigger: "change" },
      ],
    };

    const wp_pages = ref([]);
    const currencies = ref([]);

    const fetchSettings = async () => {
      startLoading();
      try {
        const response = await get("admin-settings");
        if (response.status === 'success') {
          const settings = response.settings;
          wp_pages.value = settings.wp_pages;
          currencies.value = settings.currencies;
          form.value.auth_page_id = String(settings.auth_page_id);
          form.value.profile_page_id = String(settings.profile_page_id);
          form.value.driver_subscription_amount = settings.driver_subscription_amount;
          form.value.driver_subscription_id = settings.driver_subscription_id;
          form.value.driver_yearly_subscription_amount = settings.driver_yearly_subscription_amount;
          form.value.driver_yearly_subscription_id = settings.driver_yearly_subscription_id;
          form.value.standard_membership_amount = settings.standard_membership_amount;
          form.value.standard_membership_id = settings.standard_membership_id;
          form.value.standard_yearly_membership_amount = settings.standard_yearly_membership_amount;
          form.value.standard_yearly_membership_id = settings.standard_yearly_membership_id;
          form.value.premium_membership_amount = settings.premium_membership_amount;
          form.value.premium_membership_id = settings.premium_membership_id;
          form.value.premium_yearly_membership_amount = settings.premium_yearly_membership_amount;
          form.value.premium_yearly_membership_id = settings.premium_yearly_membership_id;
          form.value.allow_free_verification = settings.allow_free_verification === 'yes';
          form.value.selected_currency = settings.selected_currency;
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
            const response = await post('update-settings', form.value, {
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

    return {
      form,
      rules,
      wp_pages,
      currencies,
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
</style>
