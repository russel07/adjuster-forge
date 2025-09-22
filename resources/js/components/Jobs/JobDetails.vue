<template>
    <div class="common-layout">
        <el-container class="full-height">
            <el-main class="main-center">
                <div class="dashboard-container">
                    <Menu />
                    
                    <!-- Modern Back Button -->
                    <div class="back-button-wrapper">
                        <el-button @click="goBack" class="modern-back-btn" size="large">
                            <el-icon><ArrowLeft /></el-icon>
                            Back to Jobs
                        </el-button>
                    </div>

                    <!-- Modern Job Details Card -->
                    <div v-if="jobDetails" class="modern-job-container">
                        <!-- Hero Section -->
                        <div class="job-hero-section">
                            <div class="hero-content">
                                <div class="job-title-section">
                                    <h1 class="modern-job-title">{{ jobDetails.title }}</h1>
                                    <div class="job-meta-badges">
                                        <el-tag type="primary" size="large" class="job-id-tag">
                                            <el-icon><Flag /></el-icon>
                                            ID: {{ jobDetails.ID }}
                                        </el-tag>
                                        <el-tag type="info" size="large" class="author-tag">
                                            <el-icon><User /></el-icon>
                                            {{ jobDetails.author }}
                                        </el-tag>
                                        <el-tag type="warning" size="large" class="date-tag">
                                            <el-icon><Calendar /></el-icon>
                                            {{ formatDate(jobDetails.date) }}
                                        </el-tag>
                                    </div>
                                </div>
                                <div class="hero-actions">
                                    <el-button 
                                        v-if="!jobDetails.is_applied && !isAuthor"
                                        type="primary" 
                                        size="large" 
                                        class="apply-btn"
                                        @click="showApplyDialog = true"
                                        :disabled="loading"
                                    >
                                        <el-icon><Check /></el-icon>
                                        Apply Now
                                    </el-button>
                                    <el-tag v-else-if="jobDetails.is_applied && !isAuthor" type="success" size="large" class="applied-tag">
                                        <el-icon><CircleCheck /></el-icon>
                                        Applied Successfully
                                    </el-tag>
                                </div>
                            </div>
                        </div>

                        <!-- Content Section -->
                        <div class="job-content-section">
                            <!-- Company Details Section -->
                            <el-card v-if="jobDetails.company_data" class="modern-content-card company-card" shadow="never">
                                <div class="company-details-flex">
                                    <div class="company-logo-wrapper">
                                        <img
                                            :src="imageUrl( image_url, jobDetails.company_data.logo ) || 'https://ui-avatars.com/api/?name=' + jobDetails.company_data.company_name"
                                            alt="Company Logo"
                                            class="company-logo-img"
                                            @error="onLogoError"
                                        />
                                    </div>
                                    <div class="company-info-grid">
                                        <div class="company-info-row">
                                            <span class="company-info-label">Name:</span>
                                            <span class="company-info-value">{{ jobDetails.company_data.company_name }}</span>
                                        </div>
                                        <div class="company-info-row">
                                            <span class="company-info-label">Address:</span>
                                            <span class="company-info-value">{{ jobDetails.company_data.address }}</span>
                                        </div>
                                        <div class="company-info-row">
                                            <span class="company-info-label">Phone:</span>
                                            <span class="company-info-value">{{ jobDetails.company_data.phone }}</span>
                                        </div>
                                        <div class="company-info-row">
                                            <span class="company-info-label">Website:</span>
                                            <span class="company-info-value"><a :href="jobDetails.company_data.website" target="_blank">{{ jobDetails.company_data.website }}</a></span>
                                        </div>
                                        <div class="company-info-row">
                                            <span class="company-info-label">DOT/MC:</span>
                                            <span class="company-info-value">{{ jobDetails.company_data.dot_mc }}</span>
                                        </div>
                                    </div>
                                </div>
                            </el-card>

                            <el-card class="modern-content-card" shadow="never">
                                <div class="content-header">
                                    <h2><el-icon><Document /></el-icon>Job Description</h2>
                                </div>
                                <div v-html="jobDetails.content" class="modern-job-description"></div>
                            </el-card>

                            <!-- Attachment Section -->
                            <el-card v-if="jobDetails.attachment" class="modern-attachment-card" shadow="never">
                                <div class="attachment-header">
                                    <h2><el-icon><Paperclip /></el-icon>Attachment</h2>
                                </div>
                                <div class="attachment-content">
                                    <el-link :href="jobDetails.attachment" target="_blank" class="modern-attachment-link">
                                        <div class="attachment-icon">
                                            <el-icon size="24"><Document /></el-icon>
                                        </div>
                                        <div class="attachment-info">
                                            <span>View Attachment</span>
                                            <small>Click to open in new tab</small>
                                        </div>
                                        <el-icon class="external-icon"><Top /></el-icon>
                                    </el-link>
                                </div>
                            </el-card>

                            <!-- Application Details Section -->
                            <el-card v-if="jobDetails.application" class="modern-application-card" shadow="never">
                                <div class="application-header">
                                    <h2><el-icon><Memo /></el-icon>Your Application</h2>
                                    <el-tag 
                                        :type="getStatusType(jobDetails.application.status)" 
                                        size="large"
                                        class="status-tag"
                                    >
                                        {{ getStatusText(jobDetails.application.status) }}
                                    </el-tag>
                                </div>
                                <div class="application-content">
                                    <div class="application-meta">
                                        <div class="meta-row">
                                            <span class="meta-label">Applied on:</span>
                                            <span class="meta-value">{{ formatDate(jobDetails.application.application_date) }}</span>
                                        </div>
                                        <div class="meta-row">
                                            <span class="meta-label">Application ID:</span>
                                            <span class="meta-value">#{{ jobDetails.application.id }}</span>
                                        </div>
                                        <div class="meta-row">
                                            <span class="meta-label">Status:</span>
                                            <span class="meta-value">{{ getStatusText(jobDetails.application.status) }}</span>
                                        </div>
                                    </div>
                                    
                                    <div class="cover-letter-section">
                                        <h3>Cover Letter</h3>
                                        <div class="cover-letter-content">
                                            {{ jobDetails.application.cover_letter }}
                                        </div>
                                    </div>

                                    <!-- Employer Notes (if any) -->
                                    <div v-if="jobDetails.application.employer_notes" class="employer-notes-section">
                                        <h3>Employer Notes</h3>
                                        <div class="employer-notes-content">
                                            {{ jobDetails.application.employer_notes }}
                                        </div>
                                    </div>
                                </div>
                            </el-card>
                        </div>
                    </div>

                    <!-- Modern Loading State -->
                    <div v-else class="modern-loading-container">
                        <div class="loading-content">
                            <el-icon class="loading-icon" size="40"><Loading /></el-icon>
                            <h3>Loading job details...</h3>
                            <p>Please wait while we fetch the information</p>
                        </div>
                    </div>

                    <!-- Modern Apply Dialog -->
                    <el-dialog 
                        v-model="showApplyDialog" 
                        title="Submit Your Application" 
                        width="650px"
                        :before-close="handleCloseDialog"
                        class="modern-dialog"
                    >
                        <div class="dialog-job-info">
                            <h3>{{ jobDetails?.title }}</h3>
                            <p>You are applying for this position</p>
                        </div>
                        
                        <el-form 
                            :model="applicationForm" 
                            :rules="applicationRules"
                            ref="applicationFormRef"
                            label-width="0"
                            class="modern-form"
                        >
                            <el-form-item prop="cover_letter">
                                <label class="modern-label">Cover Letter *</label>
                                <el-input
                                    type="textarea"
                                    v-model="applicationForm.cover_letter"
                                    :rows="8"
                                    placeholder="Tell us why you're the perfect fit for this role. Highlight your relevant experience, skills, and enthusiasm for the position..."
                                    class="modern-textarea"
                                />
                                <div class="character-count">
                                    {{ applicationForm.cover_letter.length }} characters
                                </div>
                            </el-form-item>
                        </el-form>

                        <template #footer>
                            <div class="dialog-footer">
                                <el-button @click="handleCloseDialog" size="large">Cancel</el-button>
                                <el-button 
                                    type="primary" 
                                    @click="submitApplication"
                                    :loading="submitLoading"
                                    size="large"
                                    class="submit-btn"
                                >
                                    <el-icon v-if="!submitLoading"><View /></el-icon>
                                    Submit Application
                                </el-button>
                            </div>
                        </template>
                    </el-dialog>
                </div>
            </el-main>
        </el-container>
    </div>
</template>

<script>
import { ref, onMounted, computed } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { useAppHelper } from '../../Composable/appHelper';
import AlertMessage from "../../Composable/AlertMessage";
import { loader } from '../../Composable/Loader';
import Menu from '../Profile/Menu';
import { 
    ArrowLeft, 
    Paperclip, 
    Loading, 
    Document, 
    User, 
    Calendar, 
    Flag, 
    Check, 
    CircleCheck, 
    Top, 
    View,
    Memo
} from '@element-plus/icons-vue';

export default {
    name: 'JobDetails',
    components: {
        Menu,
        ArrowLeft,
        Paperclip,
        Loading,
        Document,
        User,
        Calendar,
        Flag,
        Check,
        CircleCheck,
        Top,
        View,
        Memo,
    },
    setup() {
        const route = useRoute();
        const router = useRouter();
        const { get, post, imageUrl } = useAppHelper();
        const { error, success } = AlertMessage();
        const { startLoading, stopLoading } = loader();
        // Get current user ID from global app_vars
        const app_vars = window.driver_forge_app_vars || {};
        const image_url = `${app_vars.image_url ? app_vars.image_url : ''}`;
        const currentUserId = app_vars.user_data ? app_vars.user_data.user_id : null;
        const isAuthor = computed(() => {
            return jobDetails.value && currentUserId && String(jobDetails.value.author_id) === String(currentUserId);
        });
        const jobDetails = ref(null);
        const loading = ref(false);
        const submitLoading = ref(false);
        const showApplyDialog = ref(false);
        const applicationFormRef = ref(null);
        const applicationForm = ref({
            cover_letter: ''
        });

        const applicationRules = {
            cover_letter: [
                { required: true, message: 'Cover letter is required', trigger: 'blur' },
                { min: 50, message: 'Cover letter should be at least 50 characters', trigger: 'blur' }
            ]
        };

        const getStatusType = (status) => {
            switch (status) {
                case 'pending': return 'warning';
                case 'approved': return 'success';
                case 'rejected': return 'danger';
                case 'under_review': return 'info';
                default: return 'info';
            }
        };

        const getStatusText = (status) => {
            switch (status) {
                case 'pending': return 'Pending Review';
                case 'approved': return 'Approved';
                case 'rejected': return 'Rejected';
                case 'under_review': return 'Under Review';
                default: return status.charAt(0).toUpperCase() + status.slice(1);
            }
        };

        const fetchJobDetails = async () => {
            const jobId = route.params.id;
            if (!jobId) {
                error('Job ID is required');
                router.push({ name: 'jobs' });
                return;
            }

            startLoading();
            loading.value = true;
            try {
                const response = await get(`job-details/${jobId}`);
                if (response && response.status === 'success') {
                    jobDetails.value = response.data;
                } else {
                    error(response?.message || "Failed to fetch job details");
                    router.push({ name: 'jobs' });
                }
            } catch (err) {
                error(err.message);
                router.push({ name: 'jobs' });
            } finally {
                stopLoading();
                loading.value = false;
            }
        };

        const formatDate = (dateString) => {
            const date = new Date(dateString);
            return date.toLocaleDateString('en-US', {
                year: 'numeric',
                month: 'long',
                day: 'numeric'
            });
        };

        const goBack = () => {
            router.push({ name: 'all-jobs' });
        };

        const handleCloseDialog = () => {
            applicationForm.value.cover_letter = '';
            showApplyDialog.value = false;
        };

        const submitApplication = async () => {
            if (!applicationFormRef.value) return;

            applicationFormRef.value.validate(async (valid) => {
                if (!valid) return;

                submitLoading.value = true;
                try {
                    const response = await post('apply-job', {
                        job_id: jobDetails.value.ID,
                        cover_letter: applicationForm.value.cover_letter
                    });

                    if (response && response.status === 'success') {
                        success(response.message || 'Application submitted successfully!');
                        handleCloseDialog();
                        // Update the job details to reflect applied status
                        if (jobDetails.value) {
                            jobDetails.value.is_applied = true;
                        }
                    } else {
                        error(response?.message || 'Failed to submit application');
                    }
                } catch (err) {
                    error(err.message);
                } finally {
                    submitLoading.value = false;
                }
            });
        };
        
        // Fallback for logo error
        const onLogoError = (e) => {
            e.target.src = 'https://ui-avatars.com/api/?name=' + jobDetails.value.company_data.company_name;
        };

        onMounted(() => {
            fetchJobDetails();
        });

        return {
            jobDetails,
            loading,
            submitLoading,
            showApplyDialog,
            applicationForm,
            applicationRules,
            applicationFormRef,
            formatDate,
            goBack,
            handleCloseDialog,
            submitApplication,
            getStatusType,
            getStatusText,
            onLogoError,
            isAuthor,
            image_url,
            imageUrl,
        };
    },
};
</script>

<style scoped>
/* Improved Company Details Section */
.company-card {
    margin-bottom: 30px;
    padding: 0;
}
.company-details-flex {
    display: flex;
    align-items: flex-start;
    gap: 32px;
    padding: 32px 24px;
}
.company-logo-wrapper {
    flex-shrink: 0;
    display: flex;
    align-items: center;
    justify-content: center;
    width: 110px;
    height: 110px;
    background: #f3f4f6;
    border-radius: 16px;
    border: 1px solid #e5e7eb;
}
.company-logo-img {
    width: 90px;
    height: 90px;
    object-fit: contain;
    border-radius: 12px;
    background: #fff;
}
.company-info-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 12px 32px;
    width: 100%;
}
.company-info-row {
    display: flex;
    gap: 8px;
    font-size: 15px;
    align-items: center;
}
.company-info-label {
    font-weight: 600;
    color: #374151;
    min-width: 90px;
}
.company-info-value {
    color: #4b5563;
    word-break: break-all;
}
@media (max-width: 900px) {
    .company-details-flex {
        flex-direction: column;
        gap: 18px;
        padding: 18px 10px;
    }
    .company-info-grid {
        grid-template-columns: 1fr;
        gap: 10px;
    }
    .company-logo-wrapper {
        width: 90px;
        height: 90px;
    }
    .company-logo-img {
        width: 70px;
        height: 70px;
    }
}
.dashboard-container {
    padding: 20px 0;
    max-width: 1000px;
    margin: 0 auto;
}

.back-button-wrapper {
    margin: 15px 0;
}

.modern-back-btn {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border: none;
    border-radius: 12px;
    color: white;
    font-weight: 600;
    padding: 12px 24px;
    transition: all 0.3s ease;
}

.modern-back-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(102, 126, 234, 0.3);
}

.modern-job-container {
    background: white;
    border-radius: 20px;
    overflow: hidden;
    box-shadow: 0 10px 40px rgba(0, 0, 0, 0.1);
}

.job-hero-section {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    padding: 40px;
}

.hero-content {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    gap: 30px;
}

.modern-job-title {
    font-size: 2.5rem;
    font-weight: 700;
    margin: 0 0 20px 0;
    line-height: 1.2;
}

.job-meta-badges {
    display: flex;
    gap: 12px;
    flex-wrap: wrap;
}

.job-meta-badges .el-tag {
    border-radius: 8px;
    font-weight: 500;
    padding: 8px 16px;
    border: none;
}

.apply-btn {
    background: white;
    color: #667eea;
    border: none;
    border-radius: 12px;
    font-weight: 600;
    padding: 16px 32px;
    font-size: 16px;
    transition: all 0.3s ease;
}

.apply-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(255, 255, 255, 0.3);
}

.applied-tag {
    background: rgba(255, 255, 255, 0.2);
    color: white;
    border: 1px solid rgba(255, 255, 255, 0.3);
    border-radius: 12px;
    padding: 16px 32px;
    font-size: 16px;
    font-weight: 600;
}

.job-content-section {
    padding: 40px;
    display: flex;
    flex-direction: column;
    gap: 30px;
}

.modern-content-card, .modern-attachment-card {
    border-radius: 16px;
    border: 1px solid #e5e7eb;
    background: #f9fafb;
}

.content-header, .attachment-header {
    margin-bottom: 20px;
}

.content-header h2, .attachment-header h2 {
    font-size: 1.5rem;
    font-weight: 600;
    color: #374151;
    display: flex;
    align-items: center;
    gap: 8px;
    margin: 0;
}

.modern-job-description {
    line-height: 1.8;
    color: #4b5563;
    font-size: 16px;
}

.modern-job-description h1, .modern-job-description h2, .modern-job-description h3 {
    color: #374151;
    margin: 20px 0 10px 0;
}

.modern-job-description ul, .modern-job-description ol {
    padding-left: 20px;
    margin: 15px 0;
}

.modern-job-description li {
    margin: 8px 0;
}

.attachment-content {
    background: white;
    border-radius: 12px;
    padding: 20px;
}

.modern-attachment-link {
    display: flex;
    align-items: center;
    gap: 16px;
    padding: 20px;
    background: #f3f4f6;
    border-radius: 12px;
    transition: all 0.3s ease;
    text-decoration: none;
}

.modern-attachment-link:hover {
    background: #e5e7eb;
    transform: translateY(-1px);
}

.attachment-icon {
    background: #667eea;
    color: white;
    border-radius: 8px;
    padding: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.attachment-info {
    flex: 1;
    display: flex;
    flex-direction: column;
    gap: 4px;
}

.attachment-info span {
    font-weight: 600;
    color: #374151;
}

.attachment-info small {
    color: #6b7280;
}

.external-icon {
    color: #9ca3af;
}

.modern-loading-container {
    display: flex;
    align-items: center;
    justify-content: center;
    min-height: 400px;
    background: white;
    border-radius: 20px;
    box-shadow: 0 10px 40px rgba(0, 0, 0, 0.1);
}

.loading-content {
    text-align: center;
}

.loading-icon {
    color: #667eea;
    margin-bottom: 16px;
}

.loading-content h3 {
    color: #374151;
    margin: 0 0 8px 0;
}

.loading-content p {
    color: #6b7280;
    margin: 0;
}

.dialog-job-info {
    background: #f9fafb;
    border-radius: 12px;
    padding: 20px;
    margin-bottom: 24px;
    border-left: 4px solid #667eea;
}

.dialog-job-info h3 {
    margin: 0 0 8px 0;
    color: #374151;
    font-size: 1.25rem;
}

.dialog-job-info p {
    margin: 0;
    color: #6b7280;
}

.modern-label {
    display: block;
    font-weight: 600;
    color: #374151;
    margin-bottom: 8px;
    font-size: 14px;
}

.modern-textarea {
    border-radius: 12px;
}

.character-count {
    text-align: right;
    font-size: 12px;
    color: #9ca3af;
    margin-top: 4px;
}

.dialog-footer {
    display: flex;
    gap: 12px;
    justify-content: flex-end;
}

.submit-btn {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border: none;
    border-radius: 8px;
}

.modern-application-card {
    border-radius: 16px;
    border: 1px solid #e5e7eb;
    background: #f9fafb;
}

.application-header {
    margin-bottom: 20px;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.application-header h2 {
    font-size: 1.5rem;
    font-weight: 600;
    color: #374151;
    display: flex;
    align-items: center;
    gap: 8px;
    margin: 0;
}

.status-tag {
    border-radius: 8px;
    font-weight: 600;
    padding: 8px 16px;
}

.application-content {
    background: white;
    border-radius: 12px;
    padding: 24px;
}

.application-meta {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 16px;
    margin-bottom: 24px;
    padding-bottom: 24px;
    border-bottom: 1px solid #e5e7eb;
}

.meta-row {
    display: flex;
    flex-direction: column;
    gap: 4px;
}

.meta-label {
    font-size: 0.875rem;
    font-weight: 600;
    color: #6b7280;
    text-transform: uppercase;
    letter-spacing: 0.025em;
}

.meta-value {
    font-size: 1rem;
    font-weight: 500;
    color: #374151;
}

.cover-letter-section, .employer-notes-section {
    margin-bottom: 24px;
}

.cover-letter-section h3, .employer-notes-section h3 {
    font-size: 1.125rem;
    font-weight: 600;
    color: #374151;
    margin: 0 0 12px 0;
}

.cover-letter-content, .employer-notes-content {
    background: #f9fafb;
    border-radius: 8px;
    padding: 16px;
    line-height: 1.6;
    color: #4b5563;
    white-space: pre-wrap;
    border-left: 4px solid #667eea;
}

.employer-notes-content {
    border-left-color: #10b981;
}

@media (max-width: 768px) {
    .dashboard-container {
        padding: 10px;
    }

    .hero-content {
        flex-direction: column;
        gap: 20px;
    }

    .modern-job-title {
        font-size: 2rem;
    }

    .job-content-section {
        padding: 20px;
    }

    .job-hero-section {
        padding: 30px 20px;
    }

    .job-meta-badges {
        flex-direction: column;
        align-items: flex-start;
    }

    .modern-attachment-link {
        flex-direction: column;
        text-align: center;
        gap: 12px;
    }

    .application-meta {
        grid-template-columns: 1fr;
    }
}
</style>