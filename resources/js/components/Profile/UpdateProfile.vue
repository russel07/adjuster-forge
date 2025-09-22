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
              <template v-if="user_type === 'driver'">
                <el-divider content-position="left">Driver Specific Details</el-divider>
                <el-row :gutter="20">
                  <el-col :span="12">
                    <el-form-item label="Availability" prop="availability">
                        <el-select v-model="form.availability" multiple placeholder="Select availability">
                        <el-option label="Full Time" value="Full Time" />
                        <el-option label="Part Time" value="Part Time" />
                        <el-option label="Weekends" value="Weekends" />
                        <el-option label="Nights" value="Nights" />
                        <el-option label="Seasonal" value="Seasonal" />
                        </el-select>
                    </el-form-item>
                  </el-col>
                  <el-col :span="12">
                    <el-form-item label="License Class(es)" prop="license_classes">
                      <el-select v-model="form.license_classes" multiple placeholder="Select license class(es)">
                          <el-option label="Class A" value="CDL-Class-A" />
                          <el-option label="Class B" value="CDL-Class-B" />
                          <el-option label="Class C" value="CDL-Class-C" />
                          <el-option label="Non-CDL" value="Non-CDL" />
                      </el-select>
                    </el-form-item>
                  </el-col>
                </el-row>
                <el-row :gutter="20">
                  <el-col :xs="24" :sm="12">
                    <el-form-item label="Endorsements" prop="endorsements">
                      <el-select v-model="form.endorsements" multiple placeholder="Select endorsements">
                          <el-option label="Hazmat (H)" value="Hazmat" />
                          <el-option label="Tank Vehicles (N)" value="Tank-Vehicles" />
                          <el-option label="Passenger (P)" value="Passenger" />
                          <el-option label="Double/Triple Trailers (T)" value="Double-Triple-Trailers" />
                          <el-option label="Combination Hazmat/Tank (X)" value="Combination-Hazmat-Tank" />
                          <el-option label="School Bus (S)" value="School-Bus" />
                      </el-select>
                    </el-form-item>
                  </el-col>
                </el-row>
                <el-form-item label="Vehicle/Equipment Experience">
                  <template v-for="(eq, idx) in form.equipment_experience" :key="idx">
                    <div class="equipment-row">
                      <el-row :gutter="10" align="middle">
                        <el-col :xs="24" :sm="10">
                          <el-form-item :label="idx === 0 ? 'Equipment Type' : ''" :prop="`equipment_experience.${idx}.type`" style="margin-bottom: 0;">
                            <el-select v-model="eq.type" placeholder="Select equipment type" style="width: 100%">
                              <el-option label="18-Wheeler / Tractor-Trailer" value="18-Wheeler-Tractor-Trailer" />
                              <el-option label="Doubles / Triples" value="Doubles-Triples" />
                              <el-option label="Straight Truck / Box Truck" value="Straight-Truck-Box-Truck" />
                              <el-option label="Flatbed" value="Flatbed" />
                              <el-option label="Tanker" value="Tanker" />
                              <el-option label="Refrigerated (Reefer)" value="Refrigerated-Reefer" />
                              <el-option label="Dry Van" value="Dry-Van" />
                              <el-option label="Dump Truck" value="Dump-Truck" />
                              <el-option label="Heavy Haul / Oversize Loads" value="Heavy-Haul-Oversize-Loads" />
                            </el-select>
                          </el-form-item>
                        </el-col>
                        <el-col :xs="24" :sm="8">
                          <el-form-item :label="idx === 0 ? 'Years of Experience' : ''" :prop="`equipment_experience.${idx}.years`" style="margin-bottom: 0;">
                            <el-input-number v-model="eq.years" :min="1" placeholder="Years" style="width: 100%" />
                          </el-form-item>
                        </el-col>
                        <el-col :xs="24" :sm="6" class="equipment-actions" style="display: flex; align-items: center;">
                          <el-tooltip content="Remove Equipment" placement="top">
                            <el-icon v-if="form.equipment_experience.length > 1" @click="removeEquipment(idx)" class="remove-equipment-button"><Delete /></el-icon>
                          </el-tooltip>
                        </el-col>
                      </el-row>
                    </div>
                  </template>
                  <el-button type="primary" @click="addEquipment" style="margin-top: 10px;">
                    + Add More
                  </el-button>
                </el-form-item>

                <el-form-item label="Professional References">
                  <template v-for="(ref, idx) in form.references" :key="idx">
                    <div class="reference-entry">
                      <el-row :gutter="10" align="middle">
                        <el-col :xs="24" :sm="8">
                          <el-form-item :label="idx === 0 ? 'Reference Name' : ''" :prop="`references.${idx}.name`" style="margin-bottom: 0;">
                            <el-input v-model="ref.name" placeholder="Full name" style="width: 100%" />
                          </el-form-item>
                        </el-col>
                        <el-col :xs="24" :sm="8">
                          <el-form-item :label="idx === 0 ? 'Phone' : ''" :prop="`references.${idx}.phone`" style="margin-bottom: 0;">
                            <el-input v-model="ref.phone" placeholder="Phone number" style="width: 100%" />
                          </el-form-item>
                        </el-col>
                        <el-col :xs="24" :sm="6">
                          <el-form-item :label="idx === 0 ? 'Years Known' : ''" :prop="`references.${idx}.years_known`" style="margin-bottom: 0;">
                            <el-input v-model="ref.years_known" placeholder="Years known" style="width: 100%" />
                          </el-form-item>
                        </el-col>
                        <el-col :xs="24" :sm="2" class="reference-actions" style="display: flex; align-items: center; justify-content: center;">
                          <el-tooltip content="Remove Reference" placement="top">
                            <el-icon v-if="form.references.length > 1" @click="removeReference(idx)" class="remove-reference-button"><Delete /></el-icon>
                          </el-tooltip>
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
    const app_vars = window.driver_forge_app_vars;
    const user_type = app_vars.user_data?.user_type || '';
    const loading = ref(false);
    const formRef = ref(null);
    const form = ref({
      first_name: '',
      last_name: '',
      city: '',
      state: '',
      country: '',
      phone: '',
      ...(user_type === 'driver'
    ? {
        bio: '',
        availability: [],
        license_classes: [],
        endorsements: [],
        equipment_experience: [
          { type: '', years: null }
        ],
        references: [
          { name: '', phone: '', years_known: '' }
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
      ...(user_type === 'driver'
      ? {
          availability: [{ required: false, message: 'Please select at least one availability option', trigger: 'change' }],
          license_classes: [{ required: true, message: 'Please select at least one license class', trigger: 'change' }],
          endorsements: [{ required: false, message: 'Please select at least one endorsement', trigger: 'change' }],
          equipment_experience: [{
            validator: (rule, value, callback) => {
                if (!Array.isArray(value) || value.length < 1) {
                    return callback(new Error('Please add at least one equipment experience row'));
                }
                for (let i = 0; i < value.length; i++) {
                    const eq = value[i];
                    if (!eq.type || eq.years === null || eq.years === undefined) {
                        return callback(new Error(`Equipment row #${i + 1} is incomplete`));
                    }
                }
                return callback();
            },
            trigger: 'blur',
        }],
        references: [{
            validator: (rule, value, callback) => {
                if (!Array.isArray(value) || value.length < 1) {
                    return callback(new Error('Please add at least one professional reference'));
                }
                for (let i = 0; i < value.length; i++) {
                    const ref = value[i];
                    if (!ref.name || !ref.years_known || !ref.phone) {
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

          if (user_type === 'driver') {
            form.value.about = response.bio || '';
            const driver_data = response.driver_data || {};
            // Set driver-specific form data
            form.value.availability = Array.isArray(driver_data.availability) 
              ? driver_data.availability.map(item => item.name || item) 
              : [];
            
            form.value.license_classes = Array.isArray(driver_data.licenses) 
              ? driver_data.licenses.map(item => item.name || item) 
              : [];
            
            form.value.endorsements = Array.isArray(driver_data.endorsements) 
              ? driver_data.endorsements.map(item => item.name || item) 
              : [];
            
            // Set equipment experience
            if (Array.isArray(driver_data.experience) && driver_data.experience.length > 0) {
              form.value.equipment_experience = driver_data.experience.map(item => ({
                type: item.name || item.type || '',
                years: item.years || null
              }));
            } else {
              form.value.equipment_experience = [{ type: '', years: null }];
            }

            // Set references
            let references = driver_data.user_data?.references || [];
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
                years_known: ref.years_known || '',
                phone: ref.phone || ''
              }));
            } else {
              form.value.references = [{ name: '', years_known: '', phone: '' }];
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

    const addReference = () => {
      form.value.references.push({ name: '', name: '', phone: '' });
    };
    const removeReference = (idx) => {
      if (form.value.references.length > 1) {
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
