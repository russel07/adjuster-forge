<template>
  <div class="adjuster-forge-profile-register">
    <el-form :model="registerForm" ref="registerFormRef" :rules="rules" label-position="top" label-width="auto"
      class="register-form" validate-on-blur validate-on-change>
      <el-row>
        <div class="form-section-header">
          <h1>Your Details</h1>
          <p>We just need a few details from you to get you started</p>
        </div>
      </el-row>

      <el-form-item label="I am a" prop="user_type">
        <el-radio-group v-model="registerForm.user_type">
          <el-radio label="adjuster">Adjuster</el-radio>
          <el-radio label="company">Company</el-radio>
        </el-radio-group>
      </el-form-item>

      <el-row :gutter="20">
        <el-col :span="12">
          <el-form-item label="First Name" prop="first_name" placeholder="Enter Your First Name Here">
            <el-input v-model="registerForm.first_name"></el-input>
          </el-form-item>
        </el-col>
        <el-col :span="12">
          <el-form-item label="Last Name" prop="last_name">
            <el-input v-model="registerForm.last_name"></el-input>
          </el-form-item>
        </el-col>
      </el-row>

      <el-form-item label="Email" prop="email">
        <el-input v-model="registerForm.email"></el-input>
      </el-form-item>

      <el-form-item label="Password" prop="password">
        <el-input type="password" v-model="registerForm.password"></el-input>
      </el-form-item>

      <el-row :gutter="20">
        <el-col :span="12">
          <el-form-item label="City" prop="city">
            <el-input v-model="registerForm.city"></el-input>
          </el-form-item>
        </el-col>
        <el-col :span="12">
          <el-form-item label="State" prop="state">
            <el-input v-model="registerForm.state"></el-input>
          </el-form-item>
        </el-col>
      </el-row>

      <el-form-item>
        <el-button type="primary" class="register-button" @click="handleRegister">Register Your Account</el-button>
      </el-form-item>

      <el-form-item prop="term_condition">
        <el-checkbox v-model="registerForm.term_condition">I confirm that I have read and accepted the <br />
          <a :href="t_c_page_url" target="_blank" class="terms-conditions">Terms and Conditions.</a></el-checkbox>
      </el-form-item>

      <el-form-item>
        <span>You already have an account?
          <span class="login-link" @click="navigateToLogin">Login Now</span></span>
      </el-form-item>
    </el-form>
  </div>
</template>

<script>
import { onMounted, ref } from 'vue';
import { useRouter } from 'vue-router';
import AlertMessage from '../../Composable/AlertMessage';
import { useAppHelper } from '../../Composable/appHelper';
import { loader } from '../../Composable/Loader';

export default {
  name: 'Register',
  components: {},
  setup() {
    const router = useRouter();
    const { post } = useAppHelper();
    const { error, success } = AlertMessage();
    const { startLoading, stopLoading } = loader();
    const app_vars = window.adjuster_forge_app_vars;
    const is_logged_in = app_vars.is_logged_in;
    const profile_page_url = app_vars.profile_page;
    const t_c_page_url = app_vars.t_c_page;
    const registerForm = ref({
      first_name: '',
      last_name: '',
      email: '',
      password: '',
      user_type: '',
      city: '',
      state: '',
      term_condition: '',
    });
    const registerFormRef = ref(null);
    const rules = {
      first_name: [{ required: true, message: 'Please enter your first name', trigger: 'blur' }],
      last_name: [{ required: true, message: 'Please enter your last name', trigger: 'blur' }],
      email: [
        { required: true, message: 'Please enter your email address', trigger: 'blur' },
        { type: 'email', message: 'Please enter a valid email address', trigger: 'blur' },
      ],
      password: [{ required: true, message: 'Please enter your password', trigger: 'blur' }],
      user_type: [{ required: true, message: 'Please select your user type', trigger: 'change' }],
      city: [{ required: true, message: 'Please enter your city', trigger: 'blur' }],
      state: [{ required: true, message: 'Please enter your state', trigger: 'blur' }],
      term_condition: [{ required: true, message: 'Please accept the terms and conditions', trigger: 'change' }],
    };


    const handleRegister = async () => {
      registerFormRef.value.validate((validData) => {
        if (validData) {
          handleRegisterSubmission();
        } else {
          return false;
        }
      });
    };

    const handleRegisterSubmission = async () => {
      startLoading();
      try {
        const formData = {
          ...registerForm.value,
        };
        const response = await post('register', formData);

        if (response.status === 'success') {
          success('Account created successfully');
          setTimeout(() => {
            //router.push({ name: 'complete-profile' });
            window.location.href = profile_page_url;
          }, 2000);
          
        } else {
          error(response.message);
        }
      } catch (err) {
        error(err.message);
      } finally {
        stopLoading();
      }
    };

    const navigateToLogin = () => {
      router.push('/sign-in');
    };

    onMounted(() => {
      if ( is_logged_in ) {
        window.location.href = profile_page_url;
      }
    });

    return {
      registerForm,
      registerFormRef,
      rules,
      handleRegister,
      navigateToLogin,
      t_c_page_url
    };
  },
};
</script>

<style scoped>
.register-form {
  max-width: 500px;
  margin: 0 auto;
  padding: 20px;
  border: 1px solid #dcdcdc;
  border-radius: 4px;
  background-color: #fff;
}

.form-actions {
  display: flex;
  justify-content: space-between;
  align-items: center;
  gap: 100px;
}

.login-link {
  margin-top: 10px;
  color: #409eff;
  text-decoration: none;
  cursor: pointer;
}

.login-link:hover {
  text-decoration: underline;
}

.forgot-password {
  color: #409eff;
  cursor: pointer;
}

.forgot-password:hover {
  text-decoration: underline;
}
</style>
