<template>
    <el-form
        :model="profileForm"
        ref="profileFormRef"
        :rules="rules"
        label-position="top"
        class="profile-complete-form"
        validate-on-blur
        validate-on-change
        >
        <div class="form-section-header">
            <h1>Your Details</h1>
            <p>We just need a few details from you to get you started</p>
        </div>

        <el-divider content-position="left">Contact Information</el-divider>
        <el-row :gutter="20">
            <el-col :span="12">
            <el-form-item label="Phone Number" prop="phone">
                <el-input v-model="profileForm.phone" placeholder="Enter your phone number"></el-input>
            </el-form-item>
            </el-col>
            <el-col :span="12">
            <el-form-item label="Availability" prop="availability">
                <el-select v-model="profileForm.availability" multiple placeholder="Select availability">
                <el-option label="Full Time" value="Full Time" />
                <el-option label="Part Time" value="Part Time" />
                <el-option label="Weekends" value="Weekends" />
                <el-option label="Nights" value="Nights" />
                <el-option label="Seasonal" value="Seasonal" />
                </el-select>
            </el-form-item>
            </el-col>
        </el-row>
        <el-divider content-position="left">Experience & Documents</el-divider>
        <el-row :gutter="20">
        <el-col :xs="24" :sm="12">
            <el-form-item label="License Class(es)" prop="license_classes">
            <el-select v-model="profileForm.license_classes" multiple placeholder="Select license class(es)">
                <el-option label="Class A" value="CDL-Class-A" />
                <el-option label="Class B" value="CDL-Class-B" />
                <el-option label="Class C" value="CDL-Class-C" />
                <el-option label="Non-CDL" value="Non-CDL" />
            </el-select>
            </el-form-item>
        </el-col>
        <el-col :xs="24" :sm="12">
            <el-form-item label="Endorsements" prop="endorsements">
            <el-select v-model="profileForm.endorsements" multiple placeholder="Select endorsements">
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
        <div v-for="(eq, idx) in profileForm.equipment_experience" :key="idx" class="equipment-row">
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
                <el-icon v-if="profileForm.equipment_experience.length > 1" @click="removeEquipment(idx)" class="remove-equipment-button"><Delete /></el-icon>
                </el-tooltip>
            </el-col>
            </el-row>
        </div>
        <el-button type="primary" @click="addEquipment" style="margin-top: 10px;">
            + Add More
        </el-button>
        </el-form-item>
        <el-row :gutter="20">
          <el-col :xs="24" :sm="8">
              <el-form-item label="Upload Resume (PDF)" prop="resume">
                  <el-upload
                      class="upload-box"
                      :on-change="resumeFileChange"
                      :file-list="ResumeFileList"
                      :auto-upload="false"
                      accept=".pdf"
                      :limit="1"
                      drag
                  >
                      <i class="el-icon-upload"></i>
                      <div>Click or drag PDF to upload</div>
                      <template #tip>
                      <span v-if="ResumeFileList.length">
                          <el-icon><Document /></el-icon>
                          {{ ResumeFileList[0].name }} ({{ (ResumeFileList[0].size/1024/1024).toFixed(2) }} MB)
                      </span>
                      </template>
                  </el-upload>
              </el-form-item>
          </el-col>
          <el-col :xs="24" :sm="8">
              <el-form-item label="Upload Medical Card (PDF/Image)" prop="medical_card">
                  <el-upload
                      class="upload-box"
                      :on-change="medicalCardFileChange"
                      :file-list="MedicalCardFileList"
                      :auto-upload="false"
                      accept=".pdf,.png,.jpg,.jpeg"
                      :limit="1"
                      drag
                  >
                      <i class="el-icon-upload"></i>
                      <div>Click or drag to upload</div>
                      <template #tip>
                      <span v-if="MedicalCardFileList.length">
                          <el-icon><Picture /></el-icon>
                          {{ MedicalCardFileList[0].name }} ({{ (MedicalCardFileList[0].size/1024/1024).toFixed(2) }} MB)
                      </span>
                      </template>
                  </el-upload>
              </el-form-item>
          </el-col>

          <el-col :xs="24" :sm="8">
              <el-form-item label="Upload Motor Vehicle Record (MVR)" prop="mvr">
                  <el-upload
                      class="upload-box"
                      :on-change="mvrFileChange"
                      :file-list="MVRFileList"
                      :auto-upload="false"
                      accept=".pdf,.png,.jpg,.jpeg"
                      :limit="1"
                      drag
                  >
                      <i class="el-icon-upload"></i>
                      <div>Click or drag to upload</div>
                      <template #tip>
                      <span v-if="MVRFileList.length">
                          <el-icon><Document /></el-icon>
                          {{ MVRFileList[0].name }} ({{ (MVRFileList[0].size/1024/1024).toFixed(2) }} MB)
                      </span>
                      </template>
                  </el-upload>
              </el-form-item>
          </el-col>
        </el-row>
        <el-divider content-position="left">Professional References</el-divider>
        <el-form-item label="References (Min 2)">
            <div v-for="(ref, index) in profileForm.references" :key="index" class="reference-entry">
                <el-row :gutter="10">
                <el-col :xs="24" :sm="8">
                    <el-input v-model="ref.name" placeholder="Name" />
                </el-col>
                <el-col :xs="24" :sm="8">
                    <el-input v-model="ref.phone" placeholder="Phone" />
                </el-col>
                <el-col :xs="24" :sm="6">
                    <el-input-number v-model="ref.years_known" :min="1" placeholder="Years Known" style="width: 100%" />
                </el-col>
                <el-col :xs="24" :sm="2" class="reference-actions">
                    <el-tooltip content="Remove Reference" placement="top">
                    <el-icon 
                        v-if="profileForm.references.length > 2" 
                        @click="removeReference(index)"
                        size="small"
                        class="remove-reference-button"
                    ><Delete /></el-icon>
                    </el-tooltip>
                </el-col>
                </el-row>
                <div v-if="refError(index)" class="field-error">{{ refError(index) }}</div>
            </div>
            <el-button type="primary" @click="addReference" style="margin-top: 10px;">
                + Add Reference
            </el-button>
        </el-form-item>

        <el-divider content-position="left">Information Confirmation</el-divider>
        <el-form-item prop="declaration_agreed" :required="true">
            <el-checkbox v-model="profileForm.declaration_agreed">
            I confirm that all the provided information is correct.
            </el-checkbox>
            <span class="terms-link">
            &nbsp;(<a :href="t_c_page_url" target="_blank" class="terms-conditions">View Terms & Conditions</a>)
            </span>
        </el-form-item>

        <el-form-item>
            <div class="button-container">
            <el-button type="primary" class="submit-button" @click="handleSubmit" :loading="loading">
                Submit
            </el-button>
            </div>
        </el-form-item>
    </el-form>
</template>
<script>

import { ref } from 'vue';
import { Delete, Document, Picture, CircleCheck, CirclePlus } from '@element-plus/icons-vue';

export default {
  name: 'AdjusterProfileSection',
  props: [
    'error',
    't_c_page_url',
    'loading'
  ],
  components: { Delete, Document, Picture, CircleCheck, CirclePlus },
  setup(props, { emit }) {
    const error = props.error;
    const t_c_page_url = props.t_c_page_url;
    const loading = props.loading;

    const profileForm = ref({
      phone: '',
      resume: '',
      medical_card: '',
      mvr: '',
      references: [
        { name: '', phone: '', years_known: null },
        { name: '', phone: '', years_known: null },
      ],
      license_classes: [],
      endorsements: [],
      equipment_experience: [
        { type: '', years: null }
      ],
      availability: [],
      declaration_agreed: false,
    });

    const rules = {
        phone: [
            { required: true, message: 'Please enter your phone number', trigger: 'blur' },
            { pattern: /^[0-9+\-\s()]{7,20}$/, message: 'Please enter a valid phone number', trigger: 'blur' },
        ],
        resume: [{ required: true, message: 'Please upload your resume (PDF)', trigger: 'change' }],
        medical_card: [{ required: true, message: 'Please upload your medical card (PDF/Image)', trigger: 'change' }],
        references: [
            {
                validator: (rule, value, callback) => {
                    if (!Array.isArray(value) || value.length < 2) {
                        return callback(new Error('Please provide at least 2 references'));
                    }
                    for (let i = 0; i < value.length; i++) {
                        const ref = value[i];
                        if (!ref.name || !ref.phone || !ref.years_known) {
                            return callback(new Error(`Reference #${i + 1} is incomplete`));
                        }
                    }
                    return callback();
                },
                trigger: 'blur',
            },
        ],
        license_classes: [{ required: true, message: 'Please select at least one license class', trigger: 'change' }],
        endorsements: [{ required: false, message: 'Please select at least one endorsement', trigger: 'change' }],
        equipment_experience: [
            {
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
            },
        ],
        availability: [{ required: false, message: 'Please select at least one availability option', trigger: 'change' }],
        declaration_agreed: [{ required: true, message: 'You must confirm the provided information is correct', trigger: 'change' }],
    };
    const profileFormRef = ref(null);
    const ResumeFileList = ref([]);
    const MedicalCardFileList = ref([]);
    const MVRFileList = ref([]);

    // Add/remove logic
    const addEquipment = () => {
      profileForm.value.equipment_experience.push({ type: '', years: null });
    };
    const removeEquipment = (idx) => {
      if (profileForm.value.equipment_experience.length > 1) {
        profileForm.value.equipment_experience.splice(idx, 1);
      }
    };
    const addReference = () => {
      profileForm.value.references.push({ name: '', phone: '', years_known: null });
    };
    const removeReference = (index) => {
      if (profileForm.value.references.length > 2) {
        profileForm.value.references.splice(index, 1);
      }
    };

    // Adjuster file handlers
    const resumeFileChange = (file, uploadedFiles) => {
      if (!file.raw.type === 'application/pdf' && !file.raw.name.toLowerCase().endsWith('.pdf')) {
        ResumeFileList.value = [];
        error('Invalid file type. Please upload a PDF file only.');
        return false;
      }
      const maxSize = 2 * 1024 * 1024;
      if (file.raw.size > maxSize) {
        ResumeFileList.value = [];
        error('File size must be less than 2MB');
        return false;
      }
      ResumeFileList.value = uploadedFiles.slice(-1);
      profileForm.value.resume = file.raw;
    };

    const medicalCardFileChange = (file, uploadedFiles) => {
      const allowedTypes = ['application/pdf', 'image/png', 'image/jpg', 'image/jpeg'];
      const allowedExtensions = ['.pdf', '.png', '.jpg', '.jpeg'];
      if (!allowedTypes.includes(file.raw.type) && !allowedExtensions.some(ext => file.raw.name.toLowerCase().endsWith(ext))) {
        MedicalCardFileList.value = [];
        error('Invalid file type. Please upload a PDF or image file (PNG, JPG, JPEG).');
        return false;
      }
      const maxSize = 2 * 1024 * 1024;
      if (file.raw.size > maxSize) {
        MedicalCardFileList.value = [];
        error('File size must be less than 2MB');
        return false;
      }
      MedicalCardFileList.value = uploadedFiles.slice(-1);
      profileForm.value.medical_card = file.raw;
    };

    const mvrFileChange = (file, uploadedFiles) => {
      const allowedTypes = ['application/pdf', 'image/png', 'image/jpg', 'image/jpeg'];
      const allowedExtensions = ['.pdf', '.png', '.jpg', '.jpeg'];
      if (!allowedTypes.includes(file.raw.type) && !allowedExtensions.some(ext => file.raw.name.toLowerCase().endsWith(ext))) {
        MVRFileList.value = [];
        error('Invalid file type. Please upload a PDF or image file (PNG, JPG, JPEG).');
        return false;
      }
      const maxSize = 2 * 1024 * 1024;
      if (file.raw.size > maxSize) {
        MVRFileList.value = [];
        error('File size must be less than 2MB');
        return false;
      }
      MVRFileList.value = uploadedFiles.slice(-1);
      profileForm.value.mvr = file.raw;
    };

    // Reference field error helper
    const refError = (index) => {
      const ref = profileForm.value.references[index];
      if (!ref.name || !ref.phone || !ref.years_known) return 'All fields required';
      if (!/^[0-9+\-\s()]{7,20}$/.test(ref.phone)) return 'Invalid phone';
      if (!ref.years_known || ref.years_known < 1) return 'Min 1 year';
      return '';
    };
    // Submit handler
    const handleSubmit = () => {
      profileFormRef.value.validate(async (valid) => {
        if (!valid) return;
        if (!validateReferences()) return;
        
        const formData = new FormData();
        formData.append('phone', profileForm.value.phone);
        formData.append('availability', JSON.stringify(profileForm.value.availability));
        formData.append('license_classes', JSON.stringify(profileForm.value.license_classes));
        formData.append('endorsements', JSON.stringify(profileForm.value.endorsements));
        formData.append('equipment_experience', JSON.stringify(profileForm.value.equipment_experience));
        formData.append('resume', profileForm.value.resume);
        formData.append('medical_card', profileForm.value.medical_card);
        formData.append('mvr', profileForm.value.mvr);
        formData.append('references', JSON.stringify(profileForm.value.references));
        formData.append('declaration_agreed', profileForm.value.declaration_agreed ? '1' : '0');
        emit('submit-profile', formData);
      });
    };

    // Custom references validation (adjuster only)
    const validateReferences = () => {
      const references = profileForm.value.references;
      if (!Array.isArray(references) || references.length < 2) {
        error('Please provide at least 2 references');
        return false;
      }
      for (let i = 0; i < references.length; i++) {
        const ref = references[i];
        if (!ref.name || !ref.phone || !ref.years_known) {
          error(`Reference #${i + 1} is incomplete. Please fill all fields.`);
          return false;
        }
        if (!/^[0-9+\-\s()]{7,20}$/.test(ref.phone)) {
          error(`Reference #${i + 1} has an invalid phone number.`);
          return false;
        }
        if (!ref.years_known || ref.years_known < 1) {
          error(`Reference #${i + 1} must have valid years known (minimum 1 year).`);
          return false;
        }
      }
      return true;
    };

    return {
      profileForm,
      profileFormRef,
      rules,
      ResumeFileList,
      MedicalCardFileList,
      MVRFileList,
      addEquipment,
      removeEquipment,
      addReference,
      removeReference,
      resumeFileChange,
      medicalCardFileChange,
      mvrFileChange,
      refError,
      Delete,
      Document,
      Picture,
      CircleCheck,
      CirclePlus,
      error,
      t_c_page_url,
      loading,
      handleSubmit
    };
  }
};
</script>
<style scoped>
.profile-complete-form {
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
/* Responsive Styles */
@media (max-width: 768px) {
  .profile-complete-form {
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
  .profile-complete-form {
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
