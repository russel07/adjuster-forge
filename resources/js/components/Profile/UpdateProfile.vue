<template>
  <div class="common-layout">
    <el-container class="full-height">
      <el-main class="main-center">
        <Menu />
        <el-row :gutter="30" class="user-profile">
          <el-col :span="24">
            <h3>Update Profile</h3>
            <el-form :model="form" ref="formRef" :rules="rules" label-position="top" class="profile-update-form">
              <el-row :gutter="20">
                <el-col :xs="24" :sm="12">
                  <el-form-item label="First Name" prop="first_name">
                    <el-input v-model="form.first_name" placeholder="Enter your first name" />
                  </el-form-item>
                </el-col>
                <el-col :xs="24" :sm="12">
                  <el-form-item label="Last Name" prop="last_name">
                    <el-input v-model="form.last_name" placeholder="Enter your last name" />
                  </el-form-item>
                </el-col>
              </el-row>
              <el-row :gutter="20">
                <el-col :xs="24" :sm="12">
                  <el-form-item label="City" prop="city">
                    <el-input v-model="form.city" placeholder="Enter your city" />
                  </el-form-item>
                </el-col>
                <el-col :xs="24" :sm="12">
                  <el-form-item label="State" prop="state">
                    <el-input v-model="form.state" placeholder="Enter your state" />
                  </el-form-item>
                </el-col>
              </el-row>
              <el-row :gutter="20">
                <el-col :xs="24" :sm="12">
                  <el-form-item label="Country" prop="country">
                    <el-input v-model="form.country" placeholder="Enter your country" />
                  </el-form-item>
                </el-col>
                <el-col :xs="24" :sm="12">
                  <el-form-item label="Mobile Number" prop="phone">
                    <el-input v-model="form.phone" placeholder="Enter your mobile number" />
                  </el-form-item>
                </el-col>
              </el-row>
              
              <el-row :gutter="20">
                <el-col :span="24">
                  <el-form-item label="About" prop="about">
                    <el-input v-model="form.about" type="textarea" :rows="4" placeholder="Enter company about"></el-input>
                  </el-form-item>
                </el-col>
              </el-row>
              <template v-if="user_type === 'adjuster'">
                <el-divider content-position="left">Adjuster Specific Details</el-divider>
                
                <!-- Experience & Eligibility -->
                <el-row :gutter="20">
                  <el-col :span="12">
                    <el-form-item label="Current Availability" prop="availability">
                      <el-select v-model="form.availability" multiple placeholder="Select availability">
                        <el-option label="Available" value="available" />
                        <el-option label="Contractual" value="contractual" />
                        <el-option label="Permanent" value="permanent" />
                        <el-option label="Not Available" value="not-available" />
                      </el-select>
                    </el-form-item>
                  </el-col>
                  <el-col :span="12">
                    <el-form-item label="Years of Licensed Experience" prop="years_experience">
                      <el-input-number v-model="form.years_experience" :min="3" :max="60" placeholder="e.g., 5" style="width: 100%"></el-input-number>
                    </el-form-item>
                  </el-col>
                </el-row>
                
                <el-row :gutter="20">
                  <el-col :span="12">
                    <el-form-item label="CAT Deployments" prop="cat_deployments">
                      <el-input-number v-model="form.cat_deployments" :min="0" placeholder="Number of CAT deployments" style="width: 100%"></el-input-number>
                    </el-form-item>
                  </el-col>
                  <el-col :span="12">
                    <el-form-item label="Experience Types" prop="experience_types">
                      <el-select v-model="form.experience_types" multiple placeholder="Select your experience types" style="width: 100%">
                        <el-option label="Contents" value="Contents" />
                        <el-option label="Auto" value="Auto" />
                        <el-option label="Property" value="Property" />
                        <el-option label="Large Loss" value="Large Loss" />
                        <el-option label="Commercial" value="Commercial" />
                        <el-option label="Worker Comp" value="Worker Comp" />
                        <el-option label="CAT" value="CAT" />
                        <el-option label="Desk Adjuster" value="Desk Adjuster" />
                        <el-option label="Field Adjuster" value="Field Adjuster" />
                      </el-select>
                    </el-form-item>
                  </el-col>
                </el-row>

                <!-- Certifications & Badges -->
                <el-form-item label="Certifications & Badges">
                  <div class="badges-grid">
                    <el-tag
                      v-for="badge in availableBadges" 
                      :key="badge.id"
                      :type="form.badges.includes(badge.id) ? 'primary' : undefined"
                      :effect="form.badges.includes(badge.id) ? 'dark' : 'plain'"
                      size="large"
                      style="cursor: pointer; margin: 4px;"
                      @click="toggleBadge(badge.id)"
                    >
                      {{ badge.name }}
                    </el-tag>
                  </div>
                  <div class="badge-preview" v-if="form.badges.length">
                    <div class="preview-label">Selected Badges:</div>
                    <div class="badge-pills">
                      <el-tag
                        v-for="badgeId in form.badges" 
                        :key="badgeId" 
                        type="success"
                        effect="dark"
                        size="small"
                      >
                        {{ getBadgeName(badgeId) }}
                      </el-tag>
                    </div>
                  </div>
                </el-form-item>

                <!-- Carrier & IA Experience -->
                <el-row :gutter="20">
                  <el-col :span="12">
                    <el-form-item label="Carrier Experience" prop="carrier_experience">
                      <el-select v-model="form.carrier_experience" multiple placeholder="Select carriers">
                        <el-option label="State Farm" value="State Farm" />
                        <el-option label="Allstate" value="Allstate" />
                        <el-option label="Citizens" value="Citizens" />
                        <el-option label="Renfroe" value="Renfroe" />
                        <el-option label="Pilot" value="Pilot" />
                        <el-option label="Other" value="Other" />
                      </el-select>
                    </el-form-item>
                  </el-col>
                  <el-col :span="12">
                    <el-form-item label="Employers / IA Firms" prop="employers_ia_firms">
                      <el-input 
                        v-model="form.employers_ia_firms" 
                        type="textarea" 
                        :rows="3"
                        placeholder="List prior firms, roles, and years"
                      ></el-input>
                    </el-form-item>
                  </el-col>
                </el-row>

                <el-form-item label="Professional References">
                  <template v-for="(ref, idx) in form.references" :key="idx">
                    <div class="reference-entry">
                      <div class="reference-header">
                        <span class="reference-label">Reference {{ idx + 1 }}</span>
                        <el-tooltip v-if="form.references.length > 2" content="Remove Reference" placement="top">
                          <el-icon @click="removeReference(idx)" class="remove-button"><Delete /></el-icon>
                        </el-tooltip>
                      </div>
                      <el-row :gutter="10">
                        <el-col :xs="24" :sm="8">
                          <el-input v-model="ref.name" placeholder="Full name" />
                        </el-col>
                        <el-col :xs="24" :sm="8">
                          <el-input v-model="ref.phone" placeholder="Phone or email" />
                        </el-col>
                        <el-col :xs="24" :sm="8">
                          <el-input v-model="ref.relationship" placeholder="E.g., Manager at Company X" />
                        </el-col>
                      </el-row>
                    </div>
                  </template>
                  <el-button type="primary" @click="addReference" style="margin-top: 10px;">
                    + Add Reference
                  </el-button>
                </el-form-item>
              </template>
              <template v-else> 
                <el-divider content-position="left">Company Specific Details</el-divider>
                <el-row :gutter="20">
                  <el-col :span="12">
                    <el-form-item label="Company Name" prop="company_name">
                        <el-input v-model="form.company_name" placeholder="Enter your company name"></el-input>
                    </el-form-item>
                  </el-col>
                  <el-col :span="12">
                    <el-form-item label="Website" prop="website">
                      <el-input v-model="form.website" placeholder="Enter company website"></el-input>
                    </el-form-item>
                  </el-col>
                  <el-col :span="12">
                    <el-form-item label="DOT / MC Number" prop="dot_mc">
                      <el-input v-model="form.dot_mc" placeholder="Enter DOT Or MC Number"></el-input>
                    </el-form-item>
                  </el-col>
                  <el-col :span="12">
                    <el-form-item label="Address" prop="address">
                      <el-input v-model="form.address" type="textarea" :rows="4" placeholder="Enter company address"></el-input>
                    </el-form-item>
                  </el-col>
                </el-row>
              </template>
              <el-form-item>
                <div class="button-container">
                  <el-button type="primary" @click="handleSubmit" :loading="loading">Update Profile</el-button>
                </div>
              </el-form-item>
            </el-form>
          </el-col>
        </el-row>
      </el-main>
    </el-container>
  </div>
</template>

<script>
import { ref, onMounted } from 'vue';
import { useAppHelper } from '../../Composable/appHelper';
import AlertMessage from '../../Composable/AlertMessage';
import { loader } from '../../Composable/Loader';
import Menu from './Menu.vue';
import { Delete, Document, Picture, CircleCheck, CirclePlus } from '@element-plus/icons-vue';

export default {
  name: 'UpdateProfile',
  components: { Menu, Delete, Document, Picture, CircleCheck, CirclePlus },
  setup() {
    const { get, post } = useAppHelper();
    const { error, success } = AlertMessage();
    const { startLoading, stopLoading } = loader();
    const app_vars = window.adjuster_forge_app_vars;
    const user_type = app_vars.user_data?.user_type || '';
    const loading = ref(false);
    const formRef = ref(null);
    
    // Available badges for adjusters
    const availableBadges = [
      { id: 'xactimate', name: 'Xactimate Level 2+' },
      { id: 'haag', name: 'HAAG' },
      { id: 'nfip', name: 'NFIP' },
      { id: 'rope', name: 'Rope & Harness' },
      { id: 'drone', name: 'Drone Part 107' },
      { id: 'multistate', name: 'Multi-State Licensed' },
      { id: 'flood', name: 'Flood Certified' },
    ];
    const form = ref({
      first_name: '',
      last_name: '',
      city: '',
      state: '',
      country: '',
      phone: '',
      ...(user_type === 'adjuster'
    ? {
        about: '',
        availability: [],
        years_experience: null,
        cat_deployments: null,
        experience_types: [],
        badges: [],
        carrier_experience: [],
        employers_ia_firms: '',
        references: [
          { name: '', phone: '', relationship: '' },
          { name: '', phone: '', relationship: '' }
        ],
      }
    : {
      company_name: '',
      website: '',
      address: '',
      dot_mc: '',
      about: '',
    })
    });
    const rules = {
      first_name: [{ required: true, message: 'First name is required', trigger: 'blur' }],
      last_name: [{ required: true, message: 'Last name is required', trigger: 'blur' }],
      city: [{ required: true, message: 'City is required', trigger: 'blur' }],
      state: [{ required: true, message: 'State is required', trigger: 'blur' }],
      country: [{ required: true, message: 'Country is required', trigger: 'blur' }],
      phone: [
        { required: true, message: 'Mobile number is required', trigger: 'blur' },
        { pattern: /^[0-9+\-\s()]{7,20}$/, message: 'Please enter a valid mobile number', trigger: 'blur' }
      ],
      ...(user_type === 'adjuster'
      ? {
          availability: [{ type: 'array', required: false, message: 'Please select at least one availability option', trigger: 'change' }],
          years_experience: [
            { required: true, message: 'Please enter years of experience', trigger: 'blur' },
            { type: 'number', min: 0, message: 'Years of experience must be a positive number', trigger: 'blur' }
          ],
          experience_types: [{ type: 'array', required: false, message: 'Please select at least one experience type', trigger: 'change' }],
          references: [{
            validator: (rule, value, callback) => {
                if (!Array.isArray(value) || value.length < 2) {
                    return callback(new Error('Please provide at least 2 references'));
                }
                for (let i = 0; i < value.length; i++) {
                    const ref = value[i];
                    if (!ref.name || !ref.phone) {
                        return callback(new Error(`Reference #${i + 1} is incomplete`));
                    }
                }
                return callback();
            },
            trigger: 'blur',
        }],
        }
      : {
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
        })
    };

    // Fetch profile info
    const fetchProfileInfo = async () => {
      startLoading();
      try {
        const response = await get('get-profile');
        if (response) {
          // Set basic profile data
          form.value.first_name = response.first_name || '';
          form.value.last_name = response.last_name || '';
          form.value.city = response.city || '';
          form.value.state = response.state || '';
          form.value.country = response.country || '';
          form.value.phone = response.phone || '';

          if (user_type === 'adjuster') {
            form.value.about = response.bio || '';
            const adjuster_data = response.adjuster_data || {};
            
            // Set adjuster-specific form data
            form.value.availability = Array.isArray(adjuster_data.availability) 
              ? adjuster_data.availability.map(item => item.name || item) 
              : [];
            
            form.value.years_experience = adjuster_data.years_experience || null;
            form.value.cat_deployments = adjuster_data.cat_deployments || null;
            
            form.value.experience_types = Array.isArray(adjuster_data.experience_types) 
              ? adjuster_data.experience_types.map(item => item.name || item) 
              : [];
            
            form.value.badges = Array.isArray(adjuster_data.badges) 
              ? adjuster_data.badges.map(item => item.badge || item.name || item) 
              : [];
            
            form.value.carrier_experience = Array.isArray(adjuster_data.carrier_experience) 
              ? adjuster_data.carrier_experience.map(item => item.name || item) 
              : [];
            
            form.value.employers_ia_firms = adjuster_data.employers_ia_firms || '';

            // Set references
            let references = adjuster_data.user_data?.references || adjuster_data.references || [];
            if (typeof references === 'string') {
              references = references.replace(/\\"/g, '"');
              try {
                references = JSON.parse(references);
              } catch (e) {
                references = [];
              }
            }
            
            if (Array.isArray(references) && references.length > 0) {
              form.value.references = references.map(ref => ({
                name: ref.name || '',
                phone: ref.phone || '',
                relationship: ref.relationship || ''
              }));
            } else {
              form.value.references = [
                { name: '', phone: '', relationship: '' },
                { name: '', phone: '', relationship: '' }
              ];
            }

          } else if (user_type === 'company') {
            const company_data = response.company_data || {};
            // Set company-specific form data
            form.value.phone = company_data.phone || '';
            form.value.company_name = company_data.company_name || '';
            form.value.website = company_data.website || '';
            form.value.address = company_data.address || '';
            form.value.dot_mc = company_data.dot_mc || '';
            form.value.about = company_data.about || '';
          }
        }
      } catch (err) {
        error(err.message || 'Unexpected error occurred');
      } finally {
        stopLoading();
      }
    };

    const addEquipment = () => {
      form.value.equipment_experience.push({ type: '', years: null });
    };
    const removeEquipment = (idx) => {
      if (form.value.equipment_experience.length > 1) {
        form.value.equipment_experience.splice(idx, 1);
      }
    };

    // Badge management functions
    const toggleBadge = (badgeId) => {
      const index = form.value.badges.indexOf(badgeId);
      if (index > -1) {
        form.value.badges.splice(index, 1);
      } else {
        form.value.badges.push(badgeId);
      }
    };

    const getBadgeName = (badgeId) => {
      const badge = availableBadges.find(b => b.id === badgeId);
      return badge ? badge.name : badgeId;
    };

    const addReference = () => {
      form.value.references.push({ name: '', phone: '', relationship: '' });
    };
    const removeReference = (idx) => {
      if (form.value.references.length > 2) {
        form.value.references.splice(idx, 1);
      }
    };

    const handleSubmit = () => {
      formRef.value.validate(async (valid) => {
        if (!valid) return;
        loading.value = true;
        startLoading();
        try {
          const response = await post('update-profile', form.value);
          if ( response ) {
            success('Profile updated successfully');
          } else {
            error(response?.message || 'Failed to update profile');
          }
        } catch (err) {
          error(err.message || 'Unexpected error occurred');
        } finally {
          loading.value = false;
          stopLoading();
        }
      });
    };

    onMounted(() => {
      fetchProfileInfo();
    });

    return {
      form,
      formRef,
      rules,
      handleSubmit,
      loading,
      user_type,
      availableBadges,
      toggleBadge,
      getBadgeName,
      addEquipment,
      removeEquipment,
      addReference,
      removeReference
    };
  }
};
</script>

<style scoped>
.user-profile {
  margin-top: 40px;
}
.profile-update-form {
  max-width: 90%;
  margin: 0 auto;
  margin-top: 10px;
  padding: 30px 20px;
  border: 1px solid #dcdcdc;
  border-radius: 12px;
  background-color: #fff;
  box-shadow: 0 4px 24px rgba(102,126,234,0.08);
}
.form-section-header {
  text-align: center;
  margin-bottom: 20px;
}
.el-divider {
  margin: 32px 0 16px 0;
}
.equipment-row {
  margin-bottom: 10px;
  padding: 10px;
  border: 1px solid #e4e7ed;
  border-radius: 6px;
  background-color: #fafafa;
  width: 100%;
}
.reference-entry {
  margin-bottom: 10px;
  padding: 10px;
  border: 1px solid #e4e7ed;
  border-radius: 6px;
  background-color: #fafafa;
  width: 100%;
}

.reference-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 10px;
}

.reference-label {
  font-weight: 600;
  color: #303133;
  font-size: 14px;
}

.remove-button {
  color: #f56c6c;
  cursor: pointer;
  transition: color 0.3s;
}

.remove-button:hover {
  color: #ff4d4f;
}

/* Badge System */
.badges-grid {
  display: flex;
  flex-wrap: wrap;
  gap: 8px;
  margin-bottom: 12px;
}

.badge-preview {
  margin-top: 12px;
}

.preview-label {
  font-size: 13px;
  color: #606266;
  margin-bottom: 8px;
}

.badge-pills {
  display: flex;
  flex-wrap: wrap;
  gap: 8px;
}
.field-error {
  color: #f56c6c;
  font-size: 0.85em;
  margin-top: 4px;
}
.remove-reference-button,
.remove-equipment-button {
  background-color: transparent;
  border: none;
  color: #f56c6c;
  cursor: pointer;
  transition: background-color 0.3s;
}
.upload-box {
  width: 100%;
  border: 1px dashed #d9d9d9;
  border-radius: 8px;
  text-align: center;
  padding: 10px 0;
  background: #f8fafc;
}
.terms-link {
  font-size: 0.95em;
}
.terms-conditions {
  color: #409eff;
  text-decoration: underline;
}
.button-container {
  display: flex;
  justify-content: center;
  width: 100%;
}
.submit-button {
  min-width: 200px;
  padding: 12px 24px;
}
h3 {
  font-weight: 600;
  color: #303133;
  margin-bottom: 18px;
}
@media (max-width: 900px) {
  .el-row.user-profile {
    flex-direction: column;
  }
  .el-col {
    width: 100% !important;
    max-width: 100%;
  }
}

/* Responsive Styles */
@media (max-width: 768px) {
  .profile-update-form {
    padding: 15px;
    margin: 10px;
  }
  .reference-entry {
    padding: 8px;
  }
  .remove-equipment-button,
  .remove-reference-button {
    margin-top: 10px;
  }
}
@media (max-width: 480px) {
  .profile-update-form {
    padding: 10px;
    margin: 5px;
  }
  .form-section-header h1 {
    font-size: 1.5em;
  }
  .reference-entry .el-row {
    gap: 5px;
  }
  .reference-entry .el-col {
    margin-bottom: 10px;
  }
}
</style>
