<template>
  <div class="common-layout">
    <el-container class="full-height">
      <el-main class="main-center">
        <Menu />
        <el-row :gutter="30" class="user-profile">
          <!-- Profile Picture & Upload -->
          <el-col :span="6">
            <ProfilePicture 
              :existing-image="existingImage"
              :user-type="user_type"
              @image-uploaded="handleImageUpload"
            />
          </el-col>

          <!-- Profile Data Presentation -->
          <el-col :span="18">
            <ProfileInformation 
              :profile-data="profileData"
              :status-info="statusInfo"
              :plan-type="plan_type"
              :show-cancel-subscription="cancelSubscription"
              :cancel-loading="cancelLoading"
              @cancel-subscription="handleCancelSubscription"
            />
            
            <SubscriptionStatus 
              :status-value="statusValue"
              :subscription-end-date="subscription_end_date"
            />

            <ProfileAdjusterDetails 
              v-if="user_type === 'adjuster'"
              :adjuster-data="adjusterDetails"
            />

            <CompanyDetails 
              v-else-if="user_type === 'company'"
              :company-data="companyDetails"
              :img-url="img_url"
            />
          </el-col>
        </el-row>
      </el-main>
    </el-container>
  </div>
</template>

<script>
import { onMounted, ref, computed } from 'vue';
import { useAppHelper } from '../../Composable/appHelper';
import AlertMessage from '../../Composable/AlertMessage';
import { loader } from '../../Composable/Loader';
import Menu from './Menu.vue';
import ProfilePicture from './ProfilePicture.vue';
import ProfileInformation from './ProfileInformation.vue';
import SubscriptionStatus from './SubscriptionStatus.vue';
import ProfileAdjusterDetails from './ProfileAdjusterDetails.vue';
import CompanyDetails from './CompanyDetails.vue';
// Import Element Plus icons
import { CircleCloseFilled, CircleCheckFilled, Clock, WarningFilled, InfoFilled, RemoveFilled } from '@element-plus/icons-vue';

export default {
  name: 'MyProfile',
  components: { 
    Menu, 
    ProfilePicture,
    ProfileInformation,
    SubscriptionStatus,
    ProfileAdjusterDetails,
    CompanyDetails,
    CircleCloseFilled, 
    CircleCheckFilled, 
    Clock, 
    WarningFilled, 
    InfoFilled, 
    RemoveFilled 
  },
  setup() {
    const { get, post, getStatusLabel, getStatus } = useAppHelper();
    const { error, success } = AlertMessage();
    const { startLoading, stopLoading } = loader();
    const app_vars = window.adjuster_forge_app_vars;
    const user_type = app_vars.user_data?.user_type || '';
    const subscriptionData = app_vars?.subscription_data || {};
    const subscription_end_date = app_vars?.subscription_data?.subscription_expire_at || '';
    const img_url = `${app_vars.image_url}/`;
    const existingImage = ref('');
    const status = ref('');
    const plan_type = app_vars?.plan_type || '';
    const cancelLoading = ref(false);

    // Profile data
    const profileData = ref({
      first_name: '',
      last_name: '',
      email: '',
      city: '',
      state: '',
    });

    // Adjuster details
    const adjusterDetails = ref({
      phone: '',
      bio: '',
      years_experience: 0,
      cat_deployments: 0,
      availability: [],
      licenses: [],
      badges: [],
      carrier_experience: [],
      employers_ia_firms: '',
      resume: '',
      w9: '',
      insurance_proof: '',
      references: [],
    });

    // Company details
    const companyDetails = ref({
      phone: '',
      company_name: '',
      dot_mc: '',
      website: '',
      address: '',
      logo: '',
      about: '',
    });

    const cancelSubscription = computed(() => {
      if( plan_type === 'free_trial') { 
        return false;
      } else if (plan_type === 'standard' || plan_type === 'premium') {
        return statusValue.value === 'active' && statusValue.value !== 'cancelled' && statusValue.value !== 'expire' && statusValue.value !== 'due_subscription';
      }
      return statusValue.value === 'active' && statusValue !== 'cancelled' && statusValue !== 'expire' && statusValue !== 'due_subscription' && statusValue.value !== 'pending';
    });

    const statusValue = computed(() => {
      return getStatus(status.value, user_type);
    });

    // Status info object for ProfileInformation component
    const statusInfo = computed(() => {
      const value = statusValue.value;
      const label = getStatusLabel(value, user_type);
      let icon;
      switch (getStatus(value, user_type)) {
        case 'pending': icon = Clock; break;
        case 'completed': icon = CircleCheckFilled; break;
        case 'submitted': icon = InfoFilled; break;
        case 'rejected': icon = CircleCloseFilled; break;
        case 'active': icon = CircleCheckFilled; break;
        case 'expire': icon = RemoveFilled; break;
        default: icon = WarningFilled;
      }
      return { value, label, icon };
    });

    // Handle image upload from ProfilePicture component
    const handleImageUpload = (response) => {
      if (user_type === 'adjuster' && response.profile_picture) {
        existingImage.value = response.profile_picture;
      } else if (user_type === 'company' && response.logo) {
        existingImage.value = response.logo;
        companyDetails.value.logo = response.logo;
      }
    };

    // Fetch profile info
    const fetchProfileInfo = async () => {
      startLoading();
      try {
        const response = await get('get-profile');
        if (response) {
          profileData.value.first_name = response.first_name || '';
          profileData.value.last_name = response.last_name || '';
          profileData.value.email = response.email || '';
          profileData.value.city = response.city || '';
          profileData.value.state = response.state || '';
          status.value = response.status || '';
          existingImage.value = response.profile_picture || '';

          if (user_type === 'adjuster') {
            const adjuster_data = response.adjuster_data || {};
            
            // Handle references - could be from user_data or direct
            let references = adjuster_data.references || adjuster_data.user_data?.references || [];
            if (typeof references === 'string') {
              references = references.replace(/\\"/g, '"');
              try {
                references = JSON.parse(references);
              } catch (e) {
                references = [];
              }
            }
            
            Object.assign(adjusterDetails.value, {
              phone: adjuster_data.phone || adjuster_data.user_data?.phone || '',
              bio: adjuster_data.bio || adjuster_data.user_data?.bio || '',
              years_experience: adjuster_data.user_data?.years_experience || 0,
              cat_deployments: adjuster_data.cat_deployments || adjuster_data.user_data?.cat_deployments || 0,
              availability: Array.isArray(adjuster_data.availability) ? adjuster_data.availability : [],
              licenses: Array.isArray(adjuster_data.licenses) ? adjuster_data.licenses : [],
              badges: Array.isArray(adjuster_data.badges) ? adjuster_data.badges : [],
              carrier_experience: Array.isArray(adjuster_data.carrier_experience) ? adjuster_data.carrier_experience : [],
              employers_ia_firms: adjuster_data.employers_ia_firms || '',
              resume: adjuster_data.resume || adjuster_data.user_data?.resume || '',
              w9: adjuster_data.w9 || adjuster_data.user_data?.w9 || '',
              insurance_proof: adjuster_data.insurance_proof || adjuster_data.user_data?.insurance_proof || '',
              references: Array.isArray(references) ? references : [],
            });
          } else if (user_type === 'company') {
            const company_data = response.company_data || {};
            Object.assign(companyDetails.value, {
              phone: company_data.phone || '',
              address: company_data.address || '',
              logo: company_data.logo || '',
              company_name: company_data.company_name || '',
              dot_mc: company_data.dot_mc || '',
              website: company_data.website || '',
              about: company_data.about || ''
            });
          }
        }
      } catch (err) {
        error(err.message);
      } finally {
        stopLoading();
      }
    };

    // Cancel subscription
    const handleCancelSubscription = async () => {
      cancelLoading.value = true;
      startLoading();
      try {
        const transection_id = subscriptionData.transaction_id || '';
        if (!transection_id) {
          error('No transaction ID found for cancellation.');
          cancelLoading.value = false;
          stopLoading();
          return;
        }
        const response = await post('cancel-subscription', { subscription_id: transection_id });
        if (response && response.status === 'success') {
          success('Subscription cancelled successfully.');
          status.value = 'expire';
          await fetchProfileInfo();
        } else {
          error(response?.message || 'Failed to cancel subscription.');
        }
      } catch (err) {
        error(err.message);
      } finally {
        cancelLoading.value = false;
        stopLoading();
      }
    };

    onMounted(() => {
      fetchProfileInfo();
    });

    return {
      user_type,
      img_url,
      existingImage,
      profileData,
      adjusterDetails,
      companyDetails,
      status,
      statusValue,
      statusInfo,
      cancelSubscription,
      handleCancelSubscription,
      cancelLoading,
      subscription_end_date,
      plan_type,
      handleImageUpload
    };
  },
};
</script>

<style scoped>
.common-layout {
  background: #f7f8fa;
  min-height: 100vh;
}

.user-profile {
  margin-top: 40px;
}

.profile-image-preview {
  text-align: center;
  margin-bottom: 10px;
}

.profile-image-preview img {
  box-shadow: 0 2px 8px rgba(0,0,0,0.08);
  border: 2px solid #e4e7ed;
}

.upload-demo {
  margin-top: 10px;
}

.el-upload__text {
  color: #909399;
  font-size: 15px;
}

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

ul {
  padding-left: 18px;
  margin: 0;
}

li {
  margin-bottom: 6px;
  color: #606266;
}

.el-col {
  min-height: 100%;
}

@media (max-width: 900px) {
  .el-row.user-profile {
    flex-direction: column;
  }
  .el-col {
    width: 100% !important;
    max-width: 100%;
  }
  .profile-image-preview img {
    max-width: 180px;
    max-height: 180px;
  }
}

.status-label {
  font-weight: 500;
  font-size: 15px;
  vertical-align: middle;
  display: inline-flex;
  align-items: center;
  /* Default color */
  color: #606266;
}

/* Status color overrides */
.status-label.rejected {
  color: #f56c6c;
}
.status-label.active {
  color: #67c23a;
}
.status-label.completed {
  color: #409EFF;
}
.status-label.pending {
  color: #e6a23c;
}
.status-label.submitted {
  color: #909399;
}
.status-label.expire {
  color: #909399;
}

/* New improved styles */
.tag-container {
  display: flex;
  flex-wrap: wrap;
  gap: 8px;
  align-items: center;
}

.detail-tag {
  margin: 2px 4px 2px 0;
  font-size: 13px;
  padding: 4px 8px;
  border-radius: 6px;
}

.no-data {
  color: #909399;
  font-style: italic;
  font-size: 14px;
}

.document-item {
  display: flex;
  align-items: center;
  gap: 8px;
}

.document-item .el-button {
  border-radius: 6px;
  font-weight: 500;
}

/* Better responsive design */
@media (max-width: 768px) {
  .el-descriptions {
    --el-descriptions-table-border: none;
  }
  
  .tag-container {
    flex-direction: column;
    align-items: flex-start;
  }
  
  .detail-tag {
    margin: 2px 0;
  }
  
  .document-item {
    flex-direction: column;
    align-items: flex-start;
  }
}
</style>