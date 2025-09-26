<template>
    <div class="adjuster-application-container">
        <header class="application-header">
            <div class="brand-icon">
                <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M12 2L4 5v6c0 5 4 9 8 11 4-2 8-6 8-11V5l-8-3z" fill="url(#g)"></path>
                    <defs><linearGradient id="g" x1="0" x2="1"><stop offset="0" stop-color="#d4af37"/><stop offset="1" stop-color="#b5892b"/></linearGradient></defs>
                </svg>
            </div>
            <div>
                <h1>Adjuster Profile Application — Adjusters On Demand</h1>
                <p>Premium verified adjusters only — verification required before listing.</p>
            </div>
            <div class="premium-badge">
                <div class="verified-tag">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none">
                        <path d="M9 11l2 2 4-4" stroke="#061018" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                    Premium Verified
                </div>
                <p class="badge-subtitle">Exclusive. Vetted. Deployable.</p>
            </div>
        </header>

        <el-form
            :model="profileForm"
            ref="profileFormRef"
            :rules="rules"
            label-position="top"
            class="profile-complete-form"
            validate-on-blur
            validate-on-change
            >

            <!-- Personal & Contact Information -->
            <div class="form-section">
                <div class="section-legend">
                    <h2>Personal & Contact</h2>
                    <p>Tell us who you are</p>
                </div>
                <el-row :gutter="20">
                    <el-col :span="12">
                        <el-form-item label="Phone Number" prop="phone">
                            <el-input v-model="profileForm.phone" placeholder="+1 (555) 555-5555"></el-input>
                        </el-form-item>
                    </el-col>
                    <el-col :span="12">
                        <el-form-item label="Current Availability" prop="availability">
                            <el-select v-model="profileForm.availability" multiple placeholder="Select availability">
                                <el-option label="Immediate" value="immediate" />
                                <el-option label="7–14 days" value="7-14" />
                                <el-option label="1 month" value="1-month" />
                                <el-option label="Not currently available" value="not-available" />
                            </el-select>
                        </el-form-item>
                    </el-col>
                </el-row>
                <el-row :gutter="20">
                    <el-col :span="12">
                        <el-form-item label="Location (City, State)" prop="location">
                            <el-row :gutter="10">
                                <el-col :span="14">
                                    <el-input v-model="profileForm.city" placeholder="Houston"></el-input>
                                </el-col>
                                <el-col :span="10">
                                    <el-select v-model="profileForm.state" placeholder="Select State">
                                        <el-option label="TX" value="TX" />
                                        <el-option label="FL" value="FL" />
                                        <el-option label="LA" value="LA" />
                                        <el-option label="CA" value="CA" />
                                        <el-option label="NY" value="NY" />
                                        <el-option label="Other" value="Other" />
                                    </el-select>
                                </el-col>
                            </el-row>
                        </el-form-item>
                    </el-col>
                </el-row>
            </div>

                <!-- Experience & Eligibility -->
                <div class="form-section">
                    <div class="section-legend">
                        <h2>Experience & Eligibility</h2>
                        <p>Minimum requirements and quick summary</p>
                    </div>
                    <el-row :gutter="20">
                        <el-col :span="12">
                            <el-form-item label="Years of Licensed Experience" prop="years_experience">
                                <el-input-number v-model="profileForm.years_experience" :min="0" :max="60" placeholder="e.g., 5" style="width: 100%"></el-input-number>
                            </el-form-item>
                        </el-col>
                        <el-col :span="12">
                            <el-form-item label="CAT Deployments" prop="cat_deployments">
                                <el-input-number v-model="profileForm.cat_deployments" :min="0" placeholder="Number of CAT deployments" style="width: 100%"></el-input-number>
                            </el-form-item>
                        </el-col>
                    </el-row>
                    <el-form-item label="Brief Bio / Summary" prop="bio">
                        <el-input 
                            v-model="profileForm.bio" 
                            type="textarea" 
                            :rows="4"
                            maxlength="500"
                            show-word-limit
                            placeholder="Short summary of your experience, areas of expertise, and typical deployments (200–500 chars)"
                        ></el-input>
                        <div class="helper-text">Minimum 60 characters. Please include verifiable deployment details when possible.</div>
                    </el-form-item>

                    <!-- Requirements Checklist -->
                    <div class="requirements-checklist">
                        <div class="check-item">
                            <el-icon class="check-icon"><CircleCheck /></el-icon>
                            <span><strong>3+ years licensed</strong> OR <strong>2+ CAT deployments</strong> with verifiable references</span>
                        </div>
                        <div class="check-item">
                            <el-icon class="check-icon"><CircleCheck /></el-icon>
                            <span><strong>At least one active state license</strong> (TX, FL, LA, CA, NY recommended)</span>
                        </div>
                        <div class="check-item">
                            <el-icon class="check-icon"><CircleCheck /></el-icon>
                            <span><strong>Required documents</strong> (Resume, W-9, Background check, References, E&O/COI)</span>
                        </div>
                    </div>
                </div>

                <el-divider content-position="left">Licensing</el-divider>
                <!-- Licensing -->
                <div class="form-section">
                    <div class="section-legend">
                        <h2>Licensing</h2>
                        <p>At least one active license in a high-demand state is required</p>
                    </div>
                    <div v-for="(license, idx) in profileForm.licenses" :key="idx" class="license-entry">
                        <div class="license-header">
                            <span class="license-label">License {{ idx + 1 }}</span>
                            <el-tooltip v-if="idx > 0" content="Remove License" placement="top">
                                <el-icon @click="removeLicense(idx)" class="remove-button"><Delete /></el-icon>
                            </el-tooltip>
                        </div>
                        <el-row :gutter="10">
                            <el-col :span="8">
                                <el-select v-model="license.state" placeholder="State" :required="idx === 0">
                                    <el-option label="TX" value="TX" />
                                    <el-option label="FL" value="FL" />
                                    <el-option label="LA" value="LA" />
                                    <el-option label="CA" value="CA" />
                                    <el-option label="NY" value="NY" />
                                    <el-option label="Other" value="Other" />
                                </el-select>
                            </el-col>
                            <el-col :span="8">
                                <el-input v-model="license.number" placeholder="License number" :required="idx === 0" />
                            </el-col>
                            <el-col :span="8">
                                <el-date-picker 
                                    v-model="license.expiration" 
                                    type="date" 
                                    placeholder="Expiration date"
                                    :required="idx === 0"
                                    style="width: 100%"
                                />
                            </el-col>
                        </el-row>
                        <el-form-item :label="idx === 0 ? 'Upload License Copy *' : 'Upload License Copy'" style="margin-top: 10px">
                            <el-upload
                                :on-change="(file, files) => licenseFileChange(file, files, idx)"
                                :file-list="license.fileList || []"
                                :auto-upload="false"
                                accept=".pdf,.jpg,.png"
                                :limit="1"
                                drag
                                class="license-upload"
                            >
                                <el-icon><Document /></el-icon>
                                <div>Click or drag license copy to upload</div>
                            </el-upload>
                        </el-form-item>
                    </div>
                    <el-button type="primary" @click="addLicense" style="margin-top: 10px;">
                        + Add License
                    </el-button>
                </div>

                <el-divider content-position="left">Certifications & Badges</el-divider>
                <!-- Certifications & Badges -->
                <div class="form-section">
                    <div class="section-legend">
                        <h2>Certifications & Badges</h2>
                        <p>Select at least one proficiency (stackable)</p>
                    </div>
                    <div class="badges-grid">
                        <div 
                            v-for="badge in availableBadges" 
                            :key="badge.id"
                            :class="['badge-selector', { active: profileForm.badges.includes(badge.id) }]"
                            @click="toggleBadge(badge.id)"
                        >
                            {{ badge.name }}
                        </div>
                    </div>
                    <div class="badge-preview" v-if="profileForm.badges.length">
                        <div class="preview-label">Selected Badges:</div>
                        <div class="badge-pills">
                            <div 
                                v-for="badgeId in profileForm.badges" 
                                :key="badgeId" 
                                class="badge-pill"
                            >
                                {{ getBadgeName(badgeId) }}
                            </div>
                        </div>
                    </div>
                    <div class="helper-text">Badges represent skills and compliance — they do not indicate membership tiers.</div>
                </div>

                <el-divider content-position="left">Carrier & IA Experience</el-divider>
                
                <!-- Carrier & IA Experience -->
                <div class="form-section">
                    <div class="section-legend">
                        <h2>Carrier & IA Experience</h2>
                        <p>Show prior work with major carriers</p>
                    </div>
                    <el-row :gutter="20">
                        <el-col :span="12">
                            <el-form-item label="Carrier Experience" prop="carrier_experience">
                                <el-select v-model="profileForm.carrier_experience" multiple placeholder="Select carriers">
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
                            <el-form-item label="Employers / IA Firms" prop="employers">
                                <el-input 
                                    v-model="profileForm.employers" 
                                    type="textarea" 
                                    :rows="3"
                                    placeholder="List prior firms, roles, and years"
                                ></el-input>
                            </el-form-item>
                        </el-col>
                    </el-row>
                    <el-form-item label="Upload Work Samples (optional)" prop="work_samples">
                        <el-upload
                            class="upload-box"
                            :on-change="workSamplesFileChange"
                            :file-list="WorkSamplesFileList"
                            :auto-upload="false"
                            accept=".pdf,.jpg,.png,.zip"
                            :limit="5"
                            multiple
                            drag
                        >
                            <el-icon><Document /></el-icon>
                            <div>Click or drag work samples to upload</div>
                            <template #tip>
                                <div class="helper-text">Examples: photos, sample reports, Xactimate exports. (Max 5 files)</div>
                            </template>
                        </el-upload>
                    </el-form-item>
                </div>

                <el-divider content-position="left">Verification Documents</el-divider>
                <!-- Verification Documents -->
                <div class="form-section">
                    <div class="section-legend">
                        <h2>Verification Documents (required)</h2>
                        <p>Upload all required documents for manual review</p>
                    </div>
                    <el-row :gutter="20">
                        <el-col :span="12">
                            <el-form-item label="Resume / CV" prop="resume">
                                <el-upload
                                    class="upload-box"
                                    :on-change="resumeFileChange"
                                    :file-list="ResumeFileList"
                                    :auto-upload="false"
                                    accept=".pdf,.doc,.docx"
                                    :limit="1"
                                    drag
                                >
                                    <el-icon><Document /></el-icon>
                                    <div>Click or drag resume to upload</div>
                                    <template #tip>
                                        <span v-if="ResumeFileList.length">
                                            <el-icon><Document /></el-icon>
                                            {{ ResumeFileList[0].name }} ({{ (ResumeFileList[0].size/1024/1024).toFixed(2) }} MB)
                                        </span>
                                    </template>
                                </el-upload>
                            </el-form-item>
                        </el-col>
                        <el-col :span="12">
                            <el-form-item label="W-9" prop="w9">
                                <el-upload
                                    class="upload-box"
                                    :on-change="w9FileChange"
                                    :file-list="W9FileList"
                                    :auto-upload="false"
                                    accept=".pdf,.jpg,.png"
                                    :limit="1"
                                    drag
                                >
                                    <el-icon><Document /></el-icon>
                                    <div>Click or drag W-9 to upload</div>
                                    <template #tip>
                                        <span v-if="W9FileList.length">
                                            <el-icon><Document /></el-icon>
                                            {{ W9FileList[0].name }} ({{ (W9FileList[0].size/1024/1024).toFixed(2) }} MB)
                                        </span>
                                    </template>
                                </el-upload>
                            </el-form-item>
                        </el-col>
                    </el-row>
                    <el-row :gutter="20">
                        <el-col :span="12">
                            <el-form-item label="Background Check / Drug Screen Date" prop="bg_check_date">
                                <el-date-picker 
                                    v-model="profileForm.bg_check_date" 
                                    type="date" 
                                    placeholder="Select date"
                                    style="width: 100%"
                                />
                            </el-form-item>
                            <el-form-item label="Upload Background Check (within 12 months)" prop="background_check">
                                <el-upload
                                    class="upload-box"
                                    :on-change="backgroundCheckFileChange"
                                    :file-list="BackgroundCheckFileList"
                                    :auto-upload="false"
                                    accept=".pdf,.jpg,.png"
                                    :limit="1"
                                    drag
                                >
                                    <el-icon><Document /></el-icon>
                                    <div>Click or drag background check to upload</div>
                                    <template #tip>
                                        <span v-if="BackgroundCheckFileList.length">
                                            <el-icon><Document /></el-icon>
                                            {{ BackgroundCheckFileList[0].name }} ({{ (BackgroundCheckFileList[0].size/1024/1024).toFixed(2) }} MB)
                                        </span>
                                    </template>
                                </el-upload>
                            </el-form-item>
                        </el-col>
                        <el-col :span="12">
                            <el-form-item label="Proof of Insurance (E&O) or COI" prop="insurance_proof">
                                <el-upload
                                    class="upload-box"
                                    :on-change="insuranceProofFileChange"
                                    :file-list="InsuranceProofFileList"
                                    :auto-upload="false"
                                    accept=".pdf,.jpg,.png"
                                    :limit="1"
                                    drag
                                >
                                    <el-icon><Document /></el-icon>
                                    <div>Click or drag E&O/COI to upload</div>
                                    <template #tip>
                                        <span v-if="InsuranceProofFileList.length">
                                            <el-icon><Document /></el-icon>
                                            {{ InsuranceProofFileList[0].name }} ({{ (InsuranceProofFileList[0].size/1024/1024).toFixed(2) }} MB)
                                        </span>
                                    </template>
                                </el-upload>
                            </el-form-item>
                        </el-col>
                    </el-row>
                </div>

                <el-divider content-position="left">Professional References</el-divider>
                <!-- Professional References -->
                <el-form-item label="References (Min 2)">
                    <div v-for="(ref, index) in profileForm.references" :key="index" class="reference-entry">
                        <div class="reference-header">
                            <span class="reference-label">Reference {{ index + 1 }}</span>
                            <el-tooltip v-if="profileForm.references.length > 2" content="Remove Reference" placement="top">
                                <el-icon @click="removeReference(index)" class="remove-button"><Delete /></el-icon>
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
                        <div v-if="refError(index)" class="field-error">{{ refError(index) }}</div>
                    </div>
                    <el-button type="primary" @click="addReference" style="margin-top: 10px;">
                        + Add Reference
                    </el-button>
                </el-form-item>

                <el-divider content-position="left">Terms & Submit</el-divider>
                
                <!-- Terms & Submit -->
                <div class="form-section">
                    <div class="section-legend">
                        <h2>Terms & Submit</h2>
                        <p>Final steps</p>
                    </div>
                    <el-form-item prop="declaration_agreed" :required="true">
                        <el-checkbox v-model="profileForm.declaration_agreed">
                            I confirm all information is accurate and I consent to a background check.
                        </el-checkbox>
                        <span class="terms-link">
                            &nbsp;(<a :href="t_c_page_url" target="_blank" class="terms-conditions">View Terms & Conditions</a>)
                        </span>
                    </el-form-item>
                    <div class="helper-text">
                        Submission is an application. Manual review required. Approved adjusters will be listed as Premium Verified members.
                    </div>

                    <el-form-item>
                        <div class="button-container">
                            <el-button type="primary" class="submit-button" @click="handleSubmit" :loading="loading">
                                Submit Application
                            </el-button>
                            <el-button type="text" class="save-draft">Save as Draft</el-button>
                        </div>
                    </el-form-item>
                </div>
            </el-form>
    </div>
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
      // Personal & Contact
      phone: '',
      city: '',
      state: '',
      availability: [],
      
      // Experience & Eligibility
      years_experience: null,
      cat_deployments: null,
      bio: '',
      
      // Licensing
      licenses: [
        { state: '', number: '', expiration: '', file: null, fileList: [] }
      ],
      
      // Certifications & Badges
      badges: ['xactimate'], // Default to Xactimate Level 2+
      
      // Carrier & IA Experience
      carrier_experience: [],
      employers: '',
      work_samples: [],
      
      // Verification Documents
      resume: '',
      w9: '',
      bg_check_date: '',
      background_check: '',
      insurance_proof: '',
      
      // References
      references: [
        { name: '', phone: '', relationship: '' },
        { name: '', phone: '', relationship: '' },
      ],
      
      declaration_agreed: false,
    });

    const availableBadges = [
      { id: 'xactimate', name: 'Xactimate Level 2+' },
      { id: 'haag', name: 'HAAG' },
      { id: 'nfip', name: 'NFIP' },
      { id: 'rope', name: 'Rope & Harness' },
      { id: 'drone', name: 'Drone Part 107' },
      { id: 'multistate', name: 'Multi-State Licensed' },
      { id: 'flood', name: 'Flood Certified' },
    ];

    const rules = {
      phone: [
        { required: true, message: 'Please enter your phone number', trigger: 'blur' },
        { pattern: /^[0-9+\-\s()]{7,20}$/, message: 'Please enter a valid phone number', trigger: 'blur' },
      ],
      state: [
        { required: true, message: 'Please select your state', trigger: 'change' }
      ],
      years_experience: [
        { required: true, message: 'Please enter years of experience', trigger: 'blur' },
        { type: 'number', min: 0, message: 'Years of experience must be a positive number', trigger: 'blur' }
      ],
      bio: [
        { required: true, message: 'Please enter a brief bio', trigger: 'blur' },
        { min: 60, message: 'Bio must be at least 60 characters', trigger: 'blur' }
      ],
      resume: [{ required: true, message: 'Please upload your resume', trigger: 'change' }],
      w9: [{ required: true, message: 'Please upload your W-9', trigger: 'change' }],
      bg_check_date: [{ required: true, message: 'Please select background check date', trigger: 'change' }],
      background_check: [{ required: true, message: 'Please upload background check', trigger: 'change' }],
      insurance_proof: [{ required: true, message: 'Please upload proof of insurance', trigger: 'change' }],
      references: [
        {
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
        },
      ],
      declaration_agreed: [{ required: true, message: 'You must confirm the provided information is correct', trigger: 'change' }],
    };

    const profileFormRef = ref(null);
    const ResumeFileList = ref([]);
    const W9FileList = ref([]);
    const BackgroundCheckFileList = ref([]);
    const InsuranceProofFileList = ref([]);
    const WorkSamplesFileList = ref([]);

    // Badge methods
    const toggleBadge = (badgeId) => {
      const index = profileForm.value.badges.indexOf(badgeId);
      if (index > -1) {
        profileForm.value.badges.splice(index, 1);
      } else {
        profileForm.value.badges.push(badgeId);
      }
    };

    const getBadgeName = (badgeId) => {
      const badge = availableBadges.find(b => b.id === badgeId);
      return badge ? badge.name : badgeId;
    };

    // License methods
    const addLicense = () => {
      profileForm.value.licenses.push({ state: '', number: '', expiration: '', file: null, fileList: [] });
    };

    const removeLicense = (idx) => {
      if (profileForm.value.licenses.length > 1) {
        profileForm.value.licenses.splice(idx, 1);
      }
    };

    // Reference methods
    const addReference = () => {
      profileForm.value.references.push({ name: '', phone: '', relationship: '' });
    };
    
    const removeReference = (index) => {
      if (profileForm.value.references.length > 2) {
        profileForm.value.references.splice(index, 1);
      }
    };

    // File handlers
    // File handlers
    const resumeFileChange = (file, uploadedFiles) => {
      const allowedTypes = ['application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'];
      const allowedExtensions = ['.pdf', '.doc', '.docx'];
      if (!allowedTypes.includes(file.raw.type) && !allowedExtensions.some(ext => file.raw.name.toLowerCase().endsWith(ext))) {
        ResumeFileList.value = [];
        error('Invalid file type. Please upload a PDF, DOC, or DOCX file.');
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

    const w9FileChange = (file, uploadedFiles) => {
      const allowedTypes = ['application/pdf', 'image/png', 'image/jpg', 'image/jpeg'];
      const allowedExtensions = ['.pdf', '.png', '.jpg', '.jpeg'];
      if (!allowedTypes.includes(file.raw.type) && !allowedExtensions.some(ext => file.raw.name.toLowerCase().endsWith(ext))) {
        W9FileList.value = [];
        error('Invalid file type. Please upload a PDF or image file (PNG, JPG, JPEG).');
        return false;
      }
      const maxSize = 2 * 1024 * 1024;
      if (file.raw.size > maxSize) {
        W9FileList.value = [];
        error('File size must be less than 2MB');
        return false;
      }
      W9FileList.value = uploadedFiles.slice(-1);
      profileForm.value.w9 = file.raw;
    };

    const backgroundCheckFileChange = (file, uploadedFiles) => {
      const allowedTypes = ['application/pdf', 'image/png', 'image/jpg', 'image/jpeg'];
      const allowedExtensions = ['.pdf', '.png', '.jpg', '.jpeg'];
      if (!allowedTypes.includes(file.raw.type) && !allowedExtensions.some(ext => file.raw.name.toLowerCase().endsWith(ext))) {
        BackgroundCheckFileList.value = [];
        error('Invalid file type. Please upload a PDF or image file (PNG, JPG, JPEG).');
        return false;
      }
      const maxSize = 2 * 1024 * 1024;
      if (file.raw.size > maxSize) {
        BackgroundCheckFileList.value = [];
        error('File size must be less than 2MB');
        return false;
      }
      BackgroundCheckFileList.value = uploadedFiles.slice(-1);
      profileForm.value.background_check = file.raw;
    };

    const insuranceProofFileChange = (file, uploadedFiles) => {
      const allowedTypes = ['application/pdf', 'image/png', 'image/jpg', 'image/jpeg'];
      const allowedExtensions = ['.pdf', '.png', '.jpg', '.jpeg'];
      if (!allowedTypes.includes(file.raw.type) && !allowedExtensions.some(ext => file.raw.name.toLowerCase().endsWith(ext))) {
        InsuranceProofFileList.value = [];
        error('Invalid file type. Please upload a PDF or image file (PNG, JPG, JPEG).');
        return false;
      }
      const maxSize = 2 * 1024 * 1024;
      if (file.raw.size > maxSize) {
        InsuranceProofFileList.value = [];
        error('File size must be less than 2MB');
        return false;
      }
      InsuranceProofFileList.value = uploadedFiles.slice(-1);
      profileForm.value.insurance_proof = file.raw;
    };

    const workSamplesFileChange = (file, uploadedFiles) => {
      const allowedTypes = ['application/pdf', 'image/png', 'image/jpg', 'image/jpeg', 'application/zip'];
      const allowedExtensions = ['.pdf', '.png', '.jpg', '.jpeg', '.zip'];
      if (!allowedTypes.includes(file.raw.type) && !allowedExtensions.some(ext => file.raw.name.toLowerCase().endsWith(ext))) {
        WorkSamplesFileList.value = [];
        error('Invalid file type. Please upload PDF, image, or ZIP files.');
        return false;
      }
      const maxSize = 5 * 1024 * 1024; // 5MB for work samples
      if (file.raw.size > maxSize) {
        WorkSamplesFileList.value = [];
        error('File size must be less than 5MB');
        return false;
      }
      WorkSamplesFileList.value = uploadedFiles;
      profileForm.value.work_samples = uploadedFiles.map(f => f.raw);
    };

    const licenseFileChange = (file, uploadedFiles, licenseIndex) => {
      const allowedTypes = ['application/pdf', 'image/png', 'image/jpg', 'image/jpeg'];
      const allowedExtensions = ['.pdf', '.png', '.jpg', '.jpeg'];
      if (!allowedTypes.includes(file.raw.type) && !allowedExtensions.some(ext => file.raw.name.toLowerCase().endsWith(ext))) {
        profileForm.value.licenses[licenseIndex].fileList = [];
        error('Invalid file type. Please upload a PDF or image file (PNG, JPG, JPEG).');
        return false;
      }
      const maxSize = 2 * 1024 * 1024;
      if (file.raw.size > maxSize) {
        profileForm.value.licenses[licenseIndex].fileList = [];
        error('File size must be less than 2MB');
        return false;
      }
      profileForm.value.licenses[licenseIndex].fileList = uploadedFiles.slice(-1);
      profileForm.value.licenses[licenseIndex].file = file.raw;
    };

    // Submit handler
    const handleSubmit = () => {
      profileFormRef.value.validate(async (valid) => {
        if (!valid) return;
        if (!validateReferences()) return;
        
        const formData = new FormData();
        
        // Personal & Contact
        formData.append('phone', profileForm.value.phone);
        formData.append('city', profileForm.value.city);
        formData.append('state', profileForm.value.state);
        formData.append('availability', JSON.stringify(profileForm.value.availability));
        
        // Experience
        formData.append('years_experience', profileForm.value.years_experience);
        formData.append('cat_deployments', profileForm.value.cat_deployments);
        formData.append('bio', profileForm.value.bio);
        
        // Licensing
        formData.append('licenses', JSON.stringify(profileForm.value.licenses.map(l => ({
          state: l.state,
          number: l.number,
          expiration: l.expiration
        }))));
        profileForm.value.licenses.forEach((license, idx) => {
          if (license.file) {
            formData.append(`license_file_${idx}`, license.file);
          }
        });
        
        // Badges & Experience
        formData.append('badges', JSON.stringify(profileForm.value.badges));
        formData.append('carrier_experience', JSON.stringify(profileForm.value.carrier_experience));
        formData.append('employers', profileForm.value.employers);
        
        // Documents
        formData.append('resume', profileForm.value.resume);
        formData.append('w9', profileForm.value.w9);
        formData.append('bg_check_date', profileForm.value.bg_check_date);
        formData.append('background_check', profileForm.value.background_check);
        formData.append('insurance_proof', profileForm.value.insurance_proof);
        
        // Work samples
        profileForm.value.work_samples.forEach((file, idx) => {
          formData.append(`work_sample_${idx}`, file);
        });
        
        // References
        formData.append('references', JSON.stringify(profileForm.value.references));
        formData.append('declaration_agreed', profileForm.value.declaration_agreed ? '1' : '0');
        
        emit('submit-profile', formData);
      });
    };

    // Reference field error helper
    const refError = (index) => {
      const ref = profileForm.value.references[index];
      if (!ref.name || !ref.phone) return 'Name and contact required';
      if (!/^[0-9+\-\s()@.]{7,30}$/.test(ref.phone)) return 'Invalid contact format';
      return '';
    };

    // Custom references validation
    const validateReferences = () => {
      const references = profileForm.value.references;
      if (!Array.isArray(references) || references.length < 2) {
        error('Please provide at least 2 references');
        return false;
      }
      for (let i = 0; i < references.length; i++) {
        const ref = references[i];
        if (!ref.name || !ref.phone) {
          error(`Reference #${i + 1} is incomplete. Please fill name and contact.`);
          return false;
        }
        if (!/^[0-9+\-\s()@.]{7,30}$/.test(ref.phone)) {
          error(`Reference #${i + 1} has an invalid contact format.`);
          return false;
        }
      }
      return true;
    };

    return {
      profileForm,
      profileFormRef,
      rules,
      availableBadges,
      ResumeFileList,
      W9FileList,
      BackgroundCheckFileList,
      InsuranceProofFileList,
      WorkSamplesFileList,
      toggleBadge,
      getBadgeName,
      addLicense,
      removeLicense,
      addReference,
      removeReference,
      resumeFileChange,
      w9FileChange,
      backgroundCheckFileChange,
      insuranceProofFileChange,
      workSamplesFileChange,
      licenseFileChange,
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
/* Adjuster Application CSS - Dark theme with gold accents */
:root {
  --bg: #031026;
  --card: #071329;
  --muted: #9fb0c8;
  --accent: #d4af37;
  --accent-2: #b5892b;
  --white: #f7fbff;
  --radius: 16px;
  --shadow: 0 10px 30px rgba(0,0,0,0.6);
  --focus: 0 0 0 4px rgba(212,175,55,0.14);
  --glass: rgba(255,255,255,0.02);
}

.adjuster-application-container {
  background: linear-gradient(180deg, var(--bg) 0%, #05132a 100%);
  color: var(--white);
  min-height: 100vh;
  padding: 40px 20px;
}

/* Header Styling */
.application-header {
  background: linear-gradient(180deg, var(--card), rgba(7,19,41,0.9));
  border-radius: var(--radius);
  padding: 22px;
  box-shadow: var(--shadow);
  display: flex;
  gap: 20px;
  align-items: center;
  margin-bottom: 24px;
}

.brand-icon {
  min-width: 84px;
  height: 84px;
  background: linear-gradient(135deg, rgba(212,175,55,0.12), rgba(212,175,55,0.06));
  border-radius: 12px;
  display: flex;
  align-items: center;
  justify-content: center;
  border: 1px solid rgba(212,175,55,0.08);
  flex-shrink: 0;
}

.brand-icon svg {
  width: 48px;
  height: 48px;
  opacity: 0.98;
}

.application-header h1 {
  margin: 0;
  font-size: 20px;
  letter-spacing: 0.2px;
  color: var(--white);
}

.application-header p {
  margin: 6px 0 0;
  color: var(--muted);
  font-size: 13px;
}

.premium-badge {
  margin-left: auto;
  text-align: right;
}

.verified-tag {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  background: linear-gradient(90deg, var(--accent), var(--accent-2));
  padding: 8px 12px;
  border-radius: 999px;
  color: #081018;
  font-weight: 800;
  font-size: 14px;
}

.badge-subtitle {
  margin-top: 6px;
  font-size: 13px;
  color: var(--muted);
}

/* Form Content Grid */
.profile-complete-form {
  background: var(--card);
  border-radius: var(--radius);
  padding: 28px;
  box-shadow: var(--shadow);
  border: 1px solid rgba(255,255,255,0.03);
  max-width: 1100px;
  margin: 0 auto;
}

/* Form Sections */
.form-section {
  background: linear-gradient(180deg, rgba(255,255,255,0.01), rgba(255,255,255,0.01));
  border-radius: 12px;
  padding: 16px;
  border: 1px solid rgba(255,255,255,0.03);
  margin-bottom: 16px;
}

.form-section:hover {
  transform: translateY(-2px);
  transition: transform 160ms;
}

.section-legend {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 16px;
}

.section-legend h2 {
  margin: 0;
  font-size: 16px;
  color: var(--white);
}

.section-legend p {
  margin: 0;
  color: var(--muted);
  font-size: 13px;
}

/* Element Plus Overrides */
.el-form-item__label {
  color: var(--muted) !important;
  font-size: 13px !important;
}

.el-input__wrapper {
  background: transparent !important;
  border: 1px solid rgba(255,255,255,0.06) !important;
  border-radius: 10px !important;
}

.el-input__wrapper:hover {
  border-color: rgba(255,255,255,0.12) !important;
}

.el-input__wrapper.is-focus {
  border-color: var(--accent) !important;
  box-shadow: var(--focus) !important;
}

.el-input__inner {
  color: var(--white) !important;
  background: transparent !important;
}

.el-textarea__inner {
  color: var(--white) !important;
  background: transparent !important;
  border: 1px solid rgba(255,255,255,0.06) !important;
  border-radius: 10px !important;
}

.el-textarea__inner:focus {
  border-color: var(--accent) !important;
  box-shadow: var(--focus) !important;
}

.el-select__wrapper {
  background: transparent !important;
  border: 1px solid rgba(255,255,255,0.06) !important;
  border-radius: 10px !important;
}

.el-select__wrapper.is-focused {
  border-color: var(--accent) !important;
  box-shadow: var(--focus) !important;
}

/* Requirements Checklist */
.requirements-checklist {
  margin-top: 12px;
}

.check-item {
  display: flex;
  gap: 10px;
  align-items: flex-start;
  color: var(--muted);
  font-size: 14px;
  margin-bottom: 8px;
}

.check-icon {
  color: var(--accent);
  margin-top: 2px;
  flex-shrink: 0;
}

/* License Entry */
.license-entry {
  background: rgba(255,255,255,0.01);
  border-radius: 8px;
  padding: 12px;
  margin-bottom: 12px;
  border: 1px solid rgba(255,255,255,0.03);
}

.license-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 10px;
}

.license-label {
  font-weight: 600;
  color: var(--white);
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

.badge-selector {
  display: inline-block;
  padding: 8px 12px;
  border-radius: 999px;
  border: 1px solid rgba(255,255,255,0.04);
  background: linear-gradient(180deg, rgba(255,255,255,0.01), rgba(255,255,255,0.00));
  color: var(--muted);
  font-weight: 600;
  font-size: 13px;
  cursor: pointer;
  transition: all 160ms;
  user-select: none;
}

.badge-selector:hover {
  transform: translateY(-2px);
  border-color: rgba(212,175,55,0.1);
}

.badge-selector.active {
  background: linear-gradient(180deg, rgba(212,175,55,0.12), rgba(212,175,55,0.04));
  color: var(--white);
  border-color: rgba(212,175,55,0.2);
  transform: translateY(-3px);
  box-shadow: 0 8px 20px rgba(11,22,33,0.6);
}

.badge-preview {
  margin-top: 12px;
}

.preview-label {
  font-size: 13px;
  color: var(--muted);
  margin-bottom: 8px;
}

.badge-pills {
  display: flex;
  flex-wrap: wrap;
  gap: 8px;
}

.badge-pill {
  background: linear-gradient(90deg, var(--accent), var(--accent-2));
  padding: 6px 10px;
  border-radius: 999px;
  color: #061018;
  font-weight: 700;
  font-size: 13px;
}

/* Reference Entry */
.reference-entry {
  background: rgba(255,255,255,0.01);
  border-radius: 8px;
  padding: 12px;
  margin-bottom: 12px;
  border: 1px solid rgba(255,255,255,0.03);
}

.reference-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 10px;
}

.reference-label {
  font-weight: 600;
  color: var(--white);
  font-size: 14px;
}

/* Upload Styling */
.upload-box {
  border: 1px dashed rgba(255,255,255,0.1) !important;
  border-radius: 8px !important;
  background: rgba(255,255,255,0.02) !important;
}

.license-upload {
  border: 1px dashed rgba(255,255,255,0.1) !important;
  border-radius: 8px !important;
  background: rgba(255,255,255,0.02) !important;
  width: 100%;
}

/* Helper Text */
.helper-text {
  font-size: 13px;
  color: var(--muted);
  margin-top: 6px;
}

/* Field Error */
.field-error {
  color: #f56c6c;
  font-size: 0.85em;
  margin-top: 4px;
}

/* Button Styling */
.submit-button {
  background: var(--accent) !important;
  border-color: var(--accent) !important;
  color: #061018 !important;
  font-weight: 700 !important;
  min-width: 200px;
  padding: 12px 24px !important;
}

.save-draft {
  background: transparent !important;
  color: var(--muted) !important;
  text-decoration: underline;
}

.button-container {
  display: flex;
  justify-content: center;
  gap: 16px;
  align-items: center;
  width: 100%;
}

/* Terms Link */
.terms-link {
  font-size: 0.95em;
}

.terms-conditions {
  color: var(--accent);
  text-decoration: underline;
}

/* Responsive Design */
@media (max-width: 1200px) {
  .profile-complete-form {
    margin: 0 20px;
  }
}

@media (max-width: 768px) {
  .adjuster-application-container {
    padding: 20px 10px;
  }
  
  .application-header {
    flex-direction: column;
    text-align: center;
    gap: 16px;
  }
  
  .premium-badge {
    margin-left: 0;
  }
  
  .profile-complete-form {
    padding: 20px;
  }
  
  .license-entry,
  .reference-entry {
    padding: 10px;
  }
  
  .button-container {
    flex-direction: column;
  }
}

@media (max-width: 480px) {
  .badges-grid {
    gap: 6px;
  }
  
  .badge-selector {
    padding: 6px 10px;
    font-size: 12px;
  }
  
  .application-header h1 {
    font-size: 18px;
  }
  
  .form-content-grid {
    gap: 16px;
  }
}

/* Divider Styling */
.el-divider {
  border-color: rgba(255,255,255,0.06) !important;
}

.el-divider__text {
  color: var(--white) !important;
  background-color: var(--card) !important;
}

/* Date Picker Styling */
.el-date-editor.el-input {
  --el-input-bg-color: transparent !important;
  --el-input-border-color: rgba(255,255,255,0.06) !important;
  --el-input-focus-border-color: var(--accent) !important;
}

/* Input Number Styling */
.el-input-number {
  --el-input-bg-color: transparent !important;
  --el-input-border-color: rgba(255,255,255,0.06) !important;
  --el-input-focus-border-color: var(--accent) !important;
}

/* Checkbox Styling */
.el-checkbox {
  --el-checkbox-checked-bg-color: var(--accent) !important;
  --el-checkbox-checked-border-color: var(--accent) !important;
}

.el-checkbox__label {
  color: var(--white) !important;
}
</style>
