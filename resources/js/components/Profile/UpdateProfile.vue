<template>
  <div class="common-layout">
    <el-container class="full-height">
      <el-main class="main-center">
        <Menu />
        <el-row :gutter="30" class="user-profile">
          <el-col :span="24">
            <h3>Update Profile</h3>
            <el-form :model="form" ref="formRef" :rules="rules" label-position="top" class="profile-update-form">
              <div class="form-section">
                <el-divider content-position="left">Personal Information</el-divider>
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
              </div>
              <template v-if="user_type === 'adjuster'">
                <div class="form-section">
                  <el-divider content-position="left">Adjuster Specific Details</el-divider>
                  
                  <!-- Experience & Eligibility -->
                  <el-row :gutter="20">
                    <el-col :span="12">
                      <el-form-item label="Years of Licensed Experience" prop="years_experience">
                        <el-input-number v-model="form.years_experience" :min="3" :max="60" placeholder="e.g., 5" style="width: 100%"></el-input-number>
                      </el-form-item>
                    </el-col>
                    <el-col :span="12">
                      <el-form-item label="Current Availability" prop="availability">
                        <el-select v-model="form.availability" multiple placeholder="Select availability">
                          <el-option label="Available" value="available" />
                          <el-option label="Contractual" value="contractual" />
                          <el-option label="Not Available" value="not-available" />
                          <el-option label="Permanent" value="permanent" />
                        </el-select>
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
                          <el-option label="Auto" value="Auto" />
                          <el-option label="CAT" value="CAT" />
                          <el-option label="Commercial" value="Commercial" />
                          <el-option label="Contents" value="Contents" />
                          <el-option label="Desk Adjuster" value="Desk Adjuster" />
                          <el-option label="Field Adjuster" value="Field Adjuster" />
                          <el-option label="Large Loss" value="Large Loss" />
                          <el-option label="Property" value="Property" />
                          <el-option label="Worker Comp" value="Worker Comp" />
                        </el-select>
                      </el-form-item>
                    </el-col>
                  </el-row>
                </div>

                <el-divider content-position="left">Certifications & Badges</el-divider>
                <!-- Certifications & Badges -->
                <div class="form-section">
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

                    <!-- Badge Proof Files -->
                    <div v-if="form.badges.length" class="badge-proofs">
                        <div class="preview-label">Upload Proof Files for Selected Badges:</div>
                        <div v-for="badgeId in form.badges" :key="'proof-' + badgeId" class="badge-proof-entry">
                            <div class="badge-proof-header">
                                <span class="badge-proof-label">{{ getBadgeName(badgeId) }} Certificate/Proof</span>
                                <span class="required-indicator">*</span>
                            </div>
                            <el-upload
                                :on-change="(file, files) => badgeProofFileChange(file, files, badgeId)"
                                :on-remove="(file, files) => badgeProofFileRemove(file, files, badgeId)"
                                :file-list="getBadgeProofFileList(badgeId)"
                                :auto-upload="false"
                                accept=".pdf,.jpg,.png"
                                :limit="1"
                                drag
                                class="upload-box"
                            >
                                <el-icon><Document /></el-icon>
                                <div v-if="getBadgeProofFileList(badgeId).length === 0">
                                    Click or drag {{ getBadgeName(badgeId) }} certificate to upload
                                </div>
                                <div v-else>
                                    Replace {{ getBadgeName(badgeId) }} certificate
                                </div>
                                <template #tip>
                                    <div class="helper-text">
                                        <span v-if="getBadgeProofFileList(badgeId).length === 0">
                                            Upload certificate or proof document (PDF, JPG, PNG)
                                        </span>
                                        <span v-else>
                                            Current file will be replaced with new upload
                                        </span>
                                    </div>
                                </template>
                            </el-upload>
                        </div>
                    </div>
                </div>

                <el-divider content-position="left">Carrier & IA Experience</el-divider>
                <!-- Carrier & IA Experience -->
                <div class="form-section">
                  <el-row :gutter="20">
                    <el-col :span="12">
                      <el-form-item label="Carrier Experience" prop="carrier_experience">
                        <el-select v-model="form.carrier_experience" multiple placeholder="Select carriers" style="width: 100%">
                          <el-option label="Allstate" value="Allstate" />
                          <el-option label="Citizens" value="Citizens" />
                          <el-option label="Other" value="Other" />
                          <el-option label="Pilot" value="Pilot" />
                          <el-option label="Renfroe" value="Renfroe" />
                          <el-option label="State Farm" value="State Farm" />
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
                        />
                      </el-form-item>
                    </el-col>
                  </el-row>
                </div>

                <el-divider content-position="left">Professional References</el-divider>
                <!-- Professional References -->
                <div class="form-section">
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
                </div>
              </template>
              <template v-else> 
                <el-divider content-position="left">Company Specific Details</el-divider>
                <div class="form-section">
                  <el-row :gutter="20">
                    <el-col :span="12">
                      <el-form-item label="Company Name" prop="company_name">
                          <el-input v-model="form.company_name" placeholder="Enter your company name" />
                      </el-form-item>
                    </el-col>
                    <el-col :span="12">
                      <el-form-item label="Website" prop="website">
                        <el-input v-model="form.website" placeholder="Enter company website" />
                      </el-form-item>
                    </el-col>
                  </el-row>
                  <el-row :gutter="20">
                    <el-col :span="12">
                      <el-form-item label="DOT / MC Number" prop="dot_mc">
                        <el-input v-model="form.dot_mc" placeholder="Enter DOT Or MC Number" />
                      </el-form-item>
                    </el-col>
                    <el-col :span="12">
                      <el-form-item label="Address" prop="address">
                        <el-input v-model="form.address" type="textarea" :rows="4" placeholder="Enter company address" />
                      </el-form-item>
                    </el-col>
                  </el-row>
                </div>
              </template>
              
              <div class="form-section">
                <el-form-item>
                  <div class="button-container">
                    <el-button type="primary" size="large" @click="handleSubmit" :loading="loading">
                      Update Profile
                    </el-button>
                  </div>
                </el-form-item>
              </div>
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
      { id: 'drone', name: 'Drone Part 107' },
      { id: 'flood', name: 'Flood Certified' },
      { id: 'haag', name: 'HAAG' },
      { id: 'multistate', name: 'Multi-State Licensed' },
      { id: 'nfip', name: 'NFIP' },
      { id: 'rope', name: 'Rope & Harness' },
      { id: 'xactimate', name: 'Xactimate Level 2+' },
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
        badge_proofs: {},
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
    
    const BadgeProofFileLists = ref({});

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
            const adjuster_data = response.adjuster_data || {};
            form.value.first_name = response.first_name || adjuster_data.user_data?.first_name || '';
            form.value.last_name = response.last_name || adjuster_data.user_data?.last_name || '';
            form.value.city = response.city || adjuster_data.user_data?.city || '';
            form.value.state = response.state || adjuster_data.user_data?.state || '';
            form.value.country = response.country || adjuster_data.user_data?.country || '';
            form.value.phone = response.phone || adjuster_data.user_data?.phone || '';
            form.value.about = response?.bio || adjuster_data.user_data?.bio || '';
            form.value.phone = response.phone || adjuster_data.user_data?.phone || '';
            // Set adjuster-specific form data
            form.value.availability = Array.isArray(adjuster_data.availability) 
              ? adjuster_data.availability.map(item => item.name || item) 
              : [];
            
            form.value.years_experience = adjuster_data.user_data?.years_experience || null;
            form.value.cat_deployments = adjuster_data.user_data?.cat_deployments || null;
            
            form.value.experience_types = Array.isArray(adjuster_data.experience_types) 
              ? adjuster_data.experience_types.map(item => item.name || item) 
              : [];
            
            form.value.badges = Array.isArray(adjuster_data.badges) 
              ? adjuster_data.badges.map(item => item.badge || item.name || item) 
              : [];
            
            // Populate existing badge proof files
            if (Array.isArray(adjuster_data.badges)) {
              adjuster_data.badges.forEach(badge => {
                if (badge.proof_file_url && (badge.badge || badge.name)) {
                  const badgeId = badge.badge || badge.name;
                  // Create file object for display in upload component
                  const existingFile = {
                    name: badge.proof_file_url.split('/').pop() || 'Badge Proof',
                    url: `${window.location.origin}/download/${badge.proof_file_url}`,
                    status: 'success',
                    uid: Date.now() + Math.random()
                  };
                  BadgeProofFileLists.value[badgeId] = [existingFile];
                }
              });
            }
            
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
        delete form.value.badge_proofs[badgeId];
        delete BadgeProofFileLists.value[badgeId];
      } else {
        form.value.badges.push(badgeId);
        BadgeProofFileLists.value[badgeId] = [];
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

    const badgeProofFileChange = (file, uploadedFiles, badgeId) => {
      const allowedTypes = ['application/pdf', 'image/png', 'image/jpg', 'image/jpeg'];
      const allowedExtensions = ['.pdf', '.png', '.jpg', '.jpeg'];
      if (!allowedTypes.includes(file.raw.type) && !allowedExtensions.some(ext => file.raw.name.toLowerCase().endsWith(ext))) {
        // Remove only new files, keep existing ones
        BadgeProofFileLists.value[badgeId] = BadgeProofFileLists.value[badgeId]?.filter(f => f.status === 'success' && f.url) || [];
        error('Invalid file type. Please upload a PDF or image file (PNG, JPG, JPEG).');
        return false;
      }
      const maxSize = 2 * 1024 * 1024;
      if (file.raw.size > maxSize) {
        // Remove only new files, keep existing ones
        BadgeProofFileLists.value[badgeId] = BadgeProofFileLists.value[badgeId]?.filter(f => f.status === 'success' && f.url) || [];
        error('File size must be less than 2MB');
        return false;
      }
      // Keep existing files and add new one (replace any new uploads)
      const existingFiles = BadgeProofFileLists.value[badgeId]?.filter(f => f.status === 'success' && f.url) || [];
      BadgeProofFileLists.value[badgeId] = [...existingFiles, ...uploadedFiles.slice(-1)];
      form.value.badge_proofs[badgeId] = file.raw;
    };
    
    // Handle badge proof file removal
    const badgeProofFileRemove = (file, fileList, badgeId) => {
      BadgeProofFileLists.value[badgeId] = fileList;
      // If it's a new file being removed, clear it from badge_proofs
      if (!file.url) {
        delete form.value.badge_proofs[badgeId];
      }
    };
    
    // Badge proof file management
    const getBadgeProofFileList = (badgeId) => {
      return BadgeProofFileLists.value[badgeId] || [];
    };

    const handleSubmit = () => {
      formRef.value.validate(async (valid) => {
        if (!valid) return;
        loading.value = true;
        startLoading();
        try {
          let submitData;
          
          if (user_type === 'adjuster') {
            // Create FormData for file uploads
            const formData = new FormData();
            
            // Append basic fields
            Object.keys(form.value).forEach(key => {
              if (key === 'badge_proofs') {
                // Handle badge proof files separately
                return;
              }
              
              const value = form.value[key];
              if (Array.isArray(value)) {
                formData.append(key, JSON.stringify(value));
              } else if (value !== null && value !== undefined) {
                formData.append(key, value);
              }
            });
            
            // Append badge proof files
            if (form.value.badges && form.value.badges.length > 0) {
              form.value.badges.forEach((badgeId, index) => {
                if (form.value.badge_proofs[badgeId]) {
                  formData.append(`badge_proof_${badgeId}`, form.value.badge_proofs[badgeId]);
                }
              });
            }
            
            submitData = formData;
          } else {
            // For company users, use regular JSON
            submitData = form.value;
          }
          
          const response = await post('update-profile', submitData);
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
      removeReference,
      getBadgeProofFileList,
      badgeProofFileChange,
      badgeProofFileRemove,
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
.form-section {
  margin-bottom: 24px;
  padding: 20px;
  background-color: #fafbfc;
  border: 1px solid #e4e7ed;
  border-radius: 8px;
  transition: box-shadow 0.3s ease;
}

.form-section:hover {
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
}

.el-divider {
  margin: 32px 0 20px 0;
  font-weight: 600;
  color: #303133;
}

.el-form-item {
  margin-bottom: 18px;
}

.el-input, .el-select {
  width: 100%;
}

.el-input-number {
  width: 100%;
}

.el-input-number .el-input__wrapper {
  height: 40px;
}
.el-input-number__increase,
.el-input-number__decrease {
  height: 48px!important;
  line-height: 48px;
  display: flex;
  align-items: center;
  justify-content: center;
}

.el-input-number .el-input-number__increase {
  border-bottom: 1px solid #dcdfe6;
}

.el-input__wrapper {
  border-radius: 6px;
  border: 1px solid #dcdfe6;
  transition: border-color 0.3s ease;
  height: 40px;
  display: flex;
  align-items: center;
}

.el-input__wrapper:hover {
  border-color: #c0c4cc;
}

.el-input__wrapper.is-focus {
  border-color: #409eff;
}

.el-textarea__inner {
  border-radius: 6px;
  border: 1px solid #dcdfe6;
  transition: border-color 0.3s ease;
}

.el-textarea__inner:hover {
  border-color: #c0c4cc;
}

.el-textarea__inner:focus {
  border-color: #409eff;
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

/* Badge Proof Files */
.badge-proofs {
  margin-top: 16px;
  padding: 12px;
  border: 1px solid #e4e7ed;
  border-radius: 8px;
  background-color: #fafbfc;
}

.badge-proof-entry {
  margin-bottom: 16px;
}

.badge-proof-entry:last-child {
  margin-bottom: 0;
}

.badge-proof-header {
  display: flex;
  align-items: center;
  margin-bottom: 8px;
}

.badge-proof-label {
  font-weight: 600;
  color: #303133;
  font-size: 14px;
}

.required-indicator {
  color: #f56c6c;
  margin-left: 4px;
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
  border: 2px dashed #dcdfe6;
  border-radius: 8px;
  text-align: center;
  padding: 20px 10px;
  background: #fafbfc;
  transition: all 0.3s ease;
}

.upload-box:hover {
  border-color: #409eff;
  background: #f0f9ff;
}

.upload-box .el-icon {
  font-size: 28px;
  color: #8c939d;
  margin-bottom: 8px;
}

.helper-text {
  font-size: 12px;
  color: #8c939d;
  margin-top: 8px;
  line-height: 1.4;
}

/* Badge System Improvements */
.badges-grid {
  display: flex;
  flex-wrap: wrap;
  gap: 10px;
  margin-bottom: 16px;
  padding: 16px;
  background: #f8f9fa;
  border-radius: 8px;
  border: 1px solid #e9ecef;
}

.badge-preview {
  margin-top: 16px;
  padding: 12px;
  background: #f0f9ff;
  border: 1px solid #bfdbfe;
  border-radius: 6px;
}

.preview-label {
  font-size: 14px;
  font-weight: 600;
  color: #374151;
  margin-bottom: 8px;
}

.badge-pills {
  display: flex;
  flex-wrap: wrap;
  gap: 8px;
}

/* Badge Proof Files */
.badge-proofs {
  margin-top: 20px;
  padding: 16px;
  border: 1px solid #e4e7ed;
  border-radius: 8px;
  background-color: #fafbfc;
}

.badge-proof-entry {
  margin-bottom: 20px;
  padding: 16px;
  background: white;
  border: 1px solid #e9ecef;
  border-radius: 6px;
}

.badge-proof-entry:last-child {
  margin-bottom: 0;
}

.badge-proof-header {
  display: flex;
  align-items: center;
  margin-bottom: 12px;
}

.badge-proof-label {
  font-weight: 600;
  color: #374151;
  font-size: 14px;
}

.required-indicator {
  color: #ef4444;
  margin-left: 4px;
  font-weight: bold;
}

.button-container {
  display: flex;
  justify-content: center;
  width: 100%;
  padding: 20px 0;
}

.button-container .el-button {
  min-width: 200px;
  padding: 12px 32px;
  font-size: 16px;
  font-weight: 600;
}

h3 {
  font-weight: 600;
  color: #303133;
  margin-bottom: 24px;
  font-size: 24px;
}

.field-error {
  color: #ef4444;
  font-size: 12px;
  margin-top: 4px;
}

/* Equipment and Reference Entries */
.equipment-row, .reference-entry {
  margin-bottom: 16px;
  padding: 16px;
  border: 1px solid #e4e7ed;
  border-radius: 8px;
  background-color: white;
  transition: box-shadow 0.3s ease;
}

.equipment-row:hover, .reference-entry:hover {
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
}

.reference-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 12px;
}

.reference-label {
  font-weight: 600;
  color: #374151;
  font-size: 14px;
}

.remove-button {
  color: #ef4444;
  cursor: pointer;
  transition: color 0.3s ease;
  font-size: 18px;
}

.remove-button:hover {
  color: #dc2626;
}

.remove-reference-button,
.remove-equipment-button {
  background-color: transparent;
  border: none;
  color: #ef4444;
  cursor: pointer;
  transition: background-color 0.3s ease;
  padding: 4px;
  border-radius: 4px;
}

.remove-reference-button:hover,
.remove-equipment-button:hover {
  background-color: #fef2f2;
}

/* Responsive Styles */
@media (max-width: 900px) {
  .el-row.user-profile {
    flex-direction: column;
  }
  .el-col {
    width: 100% !important;
    max-width: 100%;
  }
}

@media (max-width: 768px) {
  .profile-update-form {
    padding: 15px;
    margin: 10px;
  }
  
  .form-section {
    padding: 15px;
  }
  
  .reference-entry, .equipment-row {
    padding: 12px;
  }
  
  .badges-grid {
    gap: 8px;
    padding: 12px;
  }
  
  .badge-proof-entry {
    padding: 12px;
  }
  
  h3 {
    font-size: 20px;
  }
}

@media (max-width: 480px) {
  .profile-update-form {
    padding: 10px;
    margin: 5px;
  }
  
  .form-section {
    padding: 12px;
  }
  
  .reference-entry .el-row {
    gap: 8px;
  }
  
  .reference-entry .el-col {
    margin-bottom: 8px;
  }
  
  .badges-grid {
    flex-direction: column;
    gap: 6px;
  }
  
  .button-container .el-button {
    width: 100%;
    min-width: auto;
  }
  
  h3 {
    font-size: 18px;
    text-align: center;
  }
}
</style>
