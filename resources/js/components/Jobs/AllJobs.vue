<template>
    <div class="common-layout">
        <el-container class="full-height">
            <el-main class="main-center">
                <div class="dashboard-container">
                    <Menu />
                    <template v-if="user_status !== 'active'">
                        <Message :page="'all-jobs'" :user_status="user_status" :user_type="user_type" :plan_type="plan_type" />
                    </template>
                    <template v-else>

                        <div class="jobs-header">
                            <h2>All Available Jobs</h2>
                            <el-input
                                v-model="search"
                                placeholder="Search jobs..."
                                style="width: 300px;"
                                clearable
                            >
                                <template #prefix>
                                    <el-icon><Search /></el-icon>
                                </template>
                            </el-input>
                        </div>

                        <!-- Jobs Cards Grid -->
                        <div class="jobs-grid" v-if="JobData.length > 0">
                            <el-card 
                                v-for="job in filteredJobs" 
                                :key="job.ID" 
                                class="job-card modern-card clickable-card"
                                shadow="hover"
                                @click="viewJob(job)"
                            >
                                <template #header>
                                    <div class="job-card-header">
                                        <div class="job-title-section">
                                            <h3>{{ job.title }}</h3>
                                            <el-tag type="primary" size="small">ID: {{ job.ID }}</el-tag>
                                        </div>
                                        <div class="header-actions">
                                            <el-tooltip content="View Details" placement="top">
                                                <el-icon class="view-icon"><View /></el-icon>
                                            </el-tooltip>
                                        </div>
                                    </div>
                                </template>

                                <div class="job-content">
                                    <p v-html="job.content.substring(0, 150) + (job.content.length > 150 ? '...' : '')"></p>
                                    
                                    <div class="job-meta">
                                        <div class="meta-item">
                                            <el-icon><User /></el-icon>
                                            <span>Author: {{ job.author }}</span>
                                        </div>
                                        <div class="meta-item">
                                            <el-icon><Calendar /></el-icon>
                                            <span>{{ formatDate(job.date) }}</span>
                                        </div>
                                    </div>
                                </div>

                                <!-- Hover Effect Overlay -->
                                <div class="hover-overlay">
                                    <el-icon class="click-icon"><View /></el-icon>
                                    <span>Click to view details</span>
                                </div>
                            </el-card>
                        </div>

                        <!-- Empty State -->
                        <el-empty v-else description="No jobs found" />

                        <!-- Pagination -->
                        <div class="pagination-container" v-if="totalItems > 0">
                            <el-pagination
                                @current-change="handlePageChange"
                                :current-page="currentPage"
                                :background="true"
                                :total="totalItems"
                                :page-size="pageSize"
                                layout="total, prev, pager, next"
                            />
                        </div>
                    </template>
                    <!-- Apply Dialog -->
                    <el-dialog 
                        v-model="showApplyDialog" 
                        title="Apply for Job" 
                        width="600px"
                        :before-close="handleCloseDialog"
                    >
                        <div v-if="selectedJob" class="dialog-job-info">
                            <h3>{{ selectedJob.title }}</h3>
                            <p class="job-author">By: {{ selectedJob.author }}</p>
                        </div>
                        
                        <el-form 
                            :model="applicationForm" 
                            :rules="applicationRules"
                            ref="applicationFormRef"
                            label-width="120px"
                        >
                            <el-form-item label="Cover Letter" prop="cover_letter">
                                <el-input
                                    type="textarea"
                                    v-model="applicationForm.cover_letter"
                                    :rows="6"
                                    placeholder="Write a cover letter explaining why you're interested in this job..."
                                />
                            </el-form-item>
                        </el-form>

                        <template #footer>
                            <el-button @click="showApplyDialog = false">Cancel</el-button>
                            <el-button 
                                type="primary" 
                                @click="submitApplication"
                                :loading="submitLoading"
                            >
                                Submit Application
                            </el-button>
                        </template>
                    </el-dialog>
                </div>
            </el-main>
        </el-container>
    </div>
</template>

<script>
import { ref, onMounted, computed } from 'vue';
import { useRouter } from 'vue-router';
import { useAppHelper } from '../../Composable/appHelper';
import AlertMessage from "../../Composable/AlertMessage";
import { loader } from '../../Composable/Loader';
import Menu from '../Profile/Menu';
import { Search, User, Calendar, Paperclip, View } from '@element-plus/icons-vue';
import Message from '../Messages/Message.vue';

export default {
    name: 'AllJobs',
    components: {
        Menu,
        Search,
        User,
        Calendar,
        Paperclip,
        View,
        Message
    },
    setup() {
        const router = useRouter();
        const { get, post } = useAppHelper();
        const { error, success } = AlertMessage();
        const { startLoading, stopLoading } = loader();
        const currentPage = ref(1);
        const pageSize = ref(5);
        const totalItems = ref(10);
        const JobData = ref([]);
        const search = ref('');
        const loading = ref(false);
        const submitLoading = ref(false);
        const showApplyDialog = ref(false);
        const selectedJob = ref(null);
        const applicationFormRef = ref(null);
        const app_vars = window.driver_forge_app_vars;
        const user_status = `${app_vars.user_status ? app_vars.user_status : ''}`;
        const user_type = `${app_vars.user_data? app_vars.user_data.user_type : ''}`;
        const plan_type = `${app_vars.plan_type ? app_vars.plan_type : ''}`;

        const applicationForm = ref({
            cover_letter: ''
        });

        const applicationRules = {
            cover_letter: [
                { required: true, message: 'Cover letter is required', trigger: 'blur' },
                { min: 50, message: 'Cover letter should be at least 50 characters', trigger: 'blur' }
            ]
        };

        // Computed property for filtered jobs
        const filteredJobs = computed(() => {
            if (!search.value) return JobData.value;
            return JobData.value.filter(job => 
                job.title.toLowerCase().includes(search.value.toLowerCase()) ||
                job.content.toLowerCase().includes(search.value.toLowerCase())
            );
        });

        const fetchJobs = async () => {
            startLoading();
            try {
                const response = await get(`jobs?page=${currentPage.value}&per_page=${pageSize.value}`);
                if (response && response.status === 'success') {
                    const data = response.data;
                    currentPage.value = data.current_page;
                    pageSize.value = data.per_page;
                    totalItems.value = data.total_items;
                    JobData.value = response.data.jobs || [];
                } else {
                    error(response?.message || "Failed to fetch jobs");
                }
            } catch (err) {
                error(err.message);
            } finally {
                stopLoading();
            }
        };

        const handlePageChange = (newPage) => {
            currentPage.value = newPage;
            fetchJobs();
        };

        const formatDate = (dateString) => {
            const date = new Date(dateString);
            return date.toLocaleDateString('en-US', {
                year: 'numeric',
                month: 'short',
                day: 'numeric'
            });
        };

        const viewJob = (job) => {
            router.push({ name: 'job-details', params: { id: job.ID } });
        };

        const applyJob = (job) => {
            selectedJob.value = job;
            showApplyDialog.value = true;
        };

        const handleCloseDialog = () => {
            applicationForm.value.cover_letter = '';
            selectedJob.value = null;
            showApplyDialog.value = false;
        };

        const submitApplication = async () => {
            if (!applicationFormRef.value) return;

            applicationFormRef.value.validate(async (valid) => {
                if (!valid) return;

                submitLoading.value = true;
                try {
                    const response = await post('apply-job', {
                        job_id: selectedJob.value.ID,
                        cover_letter: applicationForm.value.cover_letter
                    });

                    if (response && response.status === 'success') {
                        success(response.message || 'Application submitted successfully!');
                        
                        // Update the job's applied status in the local data
                        const jobIndex = JobData.value.findIndex(job => job.ID === selectedJob.value.ID);
                        if (jobIndex !== -1) {
                            JobData.value[jobIndex].is_applied = true;
                        }
                        
                        handleCloseDialog();
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

        onMounted(() => {
            fetchJobs();
        });

        return {
            fetchJobs,
            JobData,
            filteredJobs,
            search,
            currentPage,
            pageSize,
            totalItems,
            loading,
            submitLoading,
            showApplyDialog,
            selectedJob,
            applicationForm,
            applicationRules,
            applicationFormRef,
            user_status,
            user_type,
            plan_type,
            handlePageChange,
            formatDate,
            viewJob,
            applyJob,
            handleCloseDialog,
            submitApplication,
        };
    },
};
</script>

<style scoped>
.dashboard-container {
    padding: 30px 0;
}

.jobs-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 30px;
}

.jobs-header h2 {
    margin: 0;
    color: #303133;
}

.jobs-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(450px, 1fr));
    gap: 30px;
    margin-bottom: 40px;
}

.job-card {
    height: fit-content;
}

.modern-card {
    border-radius: 20px;
    border: none;
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
    transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
    position: relative;
    overflow: hidden;
    background: linear-gradient(135deg, #ffffff 0%, #f8fafc 100%);
}

.job-card-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    padding: 0;
}

.job-title-section h3 {
    margin: 0 0 8px 0;
    font-size: 1.5rem;
    font-weight: 700;
    color: #1f2937;
    line-height: 1.3;
}

.header-actions .view-icon {
    font-size: 1.25rem;
    color: #9ca3af;
    transition: color 0.3s ease;
}

.clickable-card:hover .header-actions .view-icon {
    color: #667eea;
}

.job-content p {
    color: #606266;
    line-height: 1.6;
    margin-bottom: 15px;
}

.job-meta {
    display: flex;
    flex-direction: column;
    gap: 8px;
    margin-bottom: 15px;
}

.meta-item {
    display: flex;
    align-items: center;
    gap: 8px;
    color: #909399;
    font-size: 14px;
}

.job-attachment {
    margin-bottom: 15px;
}

.job-actions {
    display: flex;
    gap: 10px;
    justify-content: flex-end;
    margin-top: 15px;
}

.pagination-container {
    display: flex;
    justify-content: center;
    margin-top: 30px;
}

.dialog-job-info {
    margin-bottom: 20px;
    padding: 15px;
    background: #f5f7fa;
    border-radius: 8px;
}

.dialog-job-info h3 {
    margin: 0 0 5px 0;
    color: #303133;
}

.job-author {
    margin: 0;
    color: #606266;
    font-size: 14px;
}

.hover-overlay {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(102, 126, 234, 0.95);
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    gap: 8px;
    color: white;
    opacity: 0;
    transition: opacity 0.3s ease;
    font-weight: 600;
}

.clickable-card {
    cursor: pointer;
}

.clickable-card:hover {
    transform: translateY(-8px) scale(1.02);
    box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
}

.clickable-card:hover .hover-overlay {
    opacity: 1;
}

.click-icon {
    font-size: 2rem;
}

@media (max-width: 768px) {
    .jobs-grid {
        grid-template-columns: 1fr;
    }
    
    .jobs-header {
        flex-direction: column;
        gap: 15px;
        align-items: stretch;
    }
    
    .jobs-header .el-input {
        width: 100% !important;
    }
    
    .modern-card {
        margin: 0 -10px;
    }
}
</style>
