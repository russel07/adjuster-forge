<template>
    <div class="common-layout">
        <el-container class="full-height">
            <el-main class="main-center">
                <div class="dashboard-container">
                    <Menu />
                    <template v-if="user_status !== 'active'">
                        <Message :page="'my-jobs'" :user_status="user_status" :user_type="user_type" :plan_type="plan_type" />
                    </template>
                    <template v-else>
                        <!-- Enhanced Header -->
                        <div class="jobs-header">
                            <div class="header-content">
                                <div class="header-text">
                                    <h1>My Applications</h1>
                                    <p class="subtitle">Track your job application status and progress</p>
                                </div>
                                <div class="header-stats">
                                    <div class="stat-card">
                                        <div class="stat-number">{{ JobData.length }}</div>
                                        <div class="stat-label">Total Applications</div>
                                    </div>
                                    <div class="stat-card">
                                        <div class="stat-number">{{ getStatusCount('pending') }}</div>
                                        <div class="stat-label">Pending</div>
                                    </div>
                                    <div class="stat-card success">
                                        <div class="stat-number">{{ getStatusCount('hired') }}</div>
                                        <div class="stat-label">Hired</div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Enhanced Search -->
                            <div class="search-section">
                                <el-input
                                    v-model="search"
                                    placeholder="Search by job title, company, or status..."
                                    style="width: 400px;"
                                    clearable
                                    size="large"
                                    class="search-input"
                                >
                                    <template #prefix>
                                        <el-icon class="search-icon"><Search /></el-icon>
                                    </template>
                                </el-input>
                                
                                <!-- Filter Options -->
                                <el-select 
                                    v-model="statusFilter" 
                                    placeholder="Filter by Status" 
                                    style="width: 200px;" 
                                    clearable
                                    size="large"
                                >
                                    <el-option label="All Status" value="" />
                                    <el-option label="Pending" value="pending" />
                                    <el-option label="Reviewing" value="reviewing" />
                                    <el-option label="Shortlisted" value="shortlisted" />
                                    <el-option label="Rejected" value="rejected" />
                                    <el-option label="Hired" value="hired" />
                                </el-select>
                            </div>
                        </div>

                        <!-- Enhanced Jobs Grid -->
                        <div class="jobs-grid" v-if="filteredJobs.length > 0">
                            <el-card 
                                v-for="job in filteredJobs" 
                                :key="job.ID" 
                                class="job-card modern-card clickable-card"
                                shadow="hover"
                                @click="viewJobApplication(job)"
                            >
                                <!-- Status Badge -->
                                <div class="status-badge" :class="getStatusType(job.application?.status)">
                                    {{ job.application?.status || 'Unknown' }}
                                </div>

                                <template #header>
                                    <div class="job-card-header">
                                        <div class="job-title-section">
                                            <h3>{{ job.title }}</h3>
                                            <el-tag class="job-id-tag" size="small">ID: {{ job.ID }}</el-tag>
                                        </div>
                                        <div class="header-actions">
                                            <el-tooltip content="View Details" placement="top">
                                                <el-icon class="view-icon"><View /></el-icon>
                                            </el-tooltip>
                                        </div>
                                    </div>
                                </template>

                                <div class="job-content">
                                    <div class="job-description">
                                        <p v-html="job.content.substring(0, 120) + (job.content.length > 120 ? '...' : '')"></p>
                                    </div>
                                    
                                    <!-- Company Info -->
                                    <div class="company-info">
                                        <div class="company-avatar">
                                            <el-icon><User /></el-icon>
                                        </div>
                                        <div class="company-details">
                                            <span class="company-name">{{ job.author }}</span>
                                            <span class="posted-date">Posted {{ getRelativeTime(job.date) }}</span>
                                        </div>
                                    </div>

                                    <!-- Application Details -->
                                    <div class="application-details" v-if="job.is_applied && job.application">
                                        <div class="application-header">
                                            <el-icon><Document /></el-icon>
                                            <span>Application Details</span>
                                        </div>
                                        
                                        <div class="application-info">
                                            <div class="info-item">
                                                <span class="label">Applied:</span>
                                                <span class="value">{{ formatDate(job.application.application_date) }}</span>
                                            </div>
                                            
                                            <div class="info-item">
                                                <span class="label">Status:</span>
                                                <el-tag 
                                                    :type="getStatusType(job.application.status)" 
                                                    size="small"
                                                    class="status-tag"
                                                >
                                                    {{ job.application.status }}
                                                </el-tag>
                                            </div>
                                        </div>

                                        <div class="cover-letter-preview" v-if="job.application.cover_letter">
                                            <div class="cover-letter-header">
                                                <el-icon><ChatDotRound /></el-icon>
                                                <span>Cover Letter Preview</span>
                                            </div>
                                            <p class="cover-letter-text">
                                                {{ job.application.cover_letter.substring(0, 100) }}
                                                <span v-if="job.application.cover_letter.length > 100">...</span>
                                            </p>
                                        </div>
                                    </div>

                                    <!-- Job Attachment -->
                                    <div class="job-attachment" v-if="job.thumbnail">
                                        <el-link :href="job.thumbnail" target="_blank" type="primary" @click.stop class="attachment-link">
                                            <el-icon><Paperclip /></el-icon>
                                            <span>View Attachment</span>
                                        </el-link>
                                    </div>
                                </div>

                                <!-- Hover Effect Overlay -->
                                <div class="hover-overlay">
                                    <el-icon class="click-icon"><View /></el-icon>
                                    <span>Click to view details</span>
                                </div>
                            </el-card>
                        </div>

                        <!-- Enhanced Empty State -->
                        <div v-else class="empty-state">
                            <div class="empty-icon">
                                <el-icon><Document /></el-icon>
                            </div>
                            <h3>No Applications Found</h3>
                            <p>{{ search || statusFilter ? 'Try adjusting your search or filter criteria' : 'You haven\'t applied to any jobs yet' }}</p>
                            <el-button type="primary" @click="goToJobs" v-if="!search && !statusFilter">
                                Browse Available Jobs
                            </el-button>
                        </div>

                        <!-- Enhanced Pagination -->
                        <div class="pagination-container" v-if="totalItems > 0">
                            <el-pagination
                                @current-change="handlePageChange"
                                :current-page="currentPage"
                                :background="true"
                                :total="totalItems"
                                :page-size="pageSize"
                                layout="total, prev, pager, next"
                                class="modern-pagination"
                            />
                        </div>
                    </template>
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
import { Search, User, Calendar, Paperclip, Document, View, ChatDotRound } from '@element-plus/icons-vue';
import Message from '../Messages/Message.vue';

export default {
    name: 'AppliedJobs',
    components: {
        Menu,
        Search,
        User,
        Calendar,
        Paperclip,
        Document,
        View,
        ChatDotRound,
        Message
    },
    setup() {
        const router = useRouter();
        const { get } = useAppHelper();
        const { error, success } = AlertMessage();
        const { startLoading, stopLoading } = loader();
        const currentPage = ref(1);
        const pageSize = ref(12);
        const totalItems = ref(10);
        const JobData = ref([]);
        const search = ref('');
        const statusFilter = ref('');
        const app_vars = window.adjuster_forge_app_vars;
        const user_status = `${app_vars.user_status ? app_vars.user_status : ''}`;
        const user_type = `${app_vars.user_data? app_vars.user_data.user_type : ''}`;
        const plan_type = `${app_vars.plan_type ? app_vars.plan_type : ''}`;

        const viewJob = (job) => {
            router.push({ name: 'job-details', params: { id: job.ID } });
        };

        // Enhanced computed property for filtered jobs
        const filteredJobs = computed(() => {
            let filtered = JobData.value;
            
            // Search filter
            if (search.value) {
                filtered = filtered.filter(job => 
                    job.title.toLowerCase().includes(search.value.toLowerCase()) ||
                    job.content.toLowerCase().includes(search.value.toLowerCase()) ||
                    job.author.toLowerCase().includes(search.value.toLowerCase())
                );
            }
            
            // Status filter
            if (statusFilter.value) {
                filtered = filtered.filter(job => 
                    job.application?.status === statusFilter.value
                );
            }
            
            return filtered;
        });

        const fetchJobs = async () => {
            startLoading();
            try {
                const response = await get(`applied-jobs?page=${currentPage.value}&per_page=${pageSize.value}`);
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

        const getRelativeTime = (dateString) => {
            const date = new Date(dateString);
            const now = new Date();
            const diffTime = Math.abs(now - date);
            const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
            
            if (diffDays === 1) return '1 day ago';
            if (diffDays < 7) return `${diffDays} days ago`;
            if (diffDays < 30) return `${Math.floor(diffDays / 7)} weeks ago`;
            return `${Math.floor(diffDays / 30)} months ago`;
        };

        const getStatusType = (status) => {
            const statusTypes = {
                'pending': 'warning',
                'reviewing': 'primary',
                'shortlisted': 'success',
                'rejected': 'danger',
                'hired': 'success'
            };
            return statusTypes[status] || 'info';
        };

        const getStatusCount = (status) => {
            return JobData.value.filter(job => job.application?.status === status).length;
        };

        const viewJobApplication = (job) => {
            router.push({ name: 'job-details', params: { id: job.ID } });
        };

        const goToJobs = () => {
            router.push({ name: 'all-jobs' });
        };

        onMounted(() => {
            fetchJobs();
        });

        return {
            fetchJobs,
            JobData,
            filteredJobs,
            search,
            statusFilter,
            currentPage,
            pageSize,
            totalItems,
            handlePageChange,
            formatDate,
            getRelativeTime,
            getStatusType,
            getStatusCount,
            viewJobApplication,
            goToJobs,
            user_status,
            user_type,
            plan_type
        };
    },
};
</script>

<style scoped>
.dashboard-container {
    padding: 20px 0;
}

.jobs-header {
    margin-bottom: 40px;
}

.header-content {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 30px;
}

.header-text h1 {
    margin: 0 0 8px 0;
    color: #1a1a1a;
    font-size: 2.5rem;
    font-weight: 700;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
}

.subtitle {
    margin: 0;
    color: #6b7280;
    font-size: 1.1rem;
}

.header-stats {
    display: flex;
    gap: 20px;
}

.stat-card {
    background: white;
    padding: 20px;
    border-radius: 16px;
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
    text-align: center;
    min-width: 120px;
    border: 2px solid #f3f4f6;
    transition: all 0.3s ease;
}

.stat-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 25px -5px rgba(0, 0, 0, 0.1);
}

.stat-card.success {
    border-color: #10b981;
    background: linear-gradient(135deg, #ecfdf5 0%, #d1fae5 100%);
}

.stat-number {
    font-size: 2rem;
    font-weight: 700;
    color: #1f2937;
    margin-bottom: 4px;
}

.stat-label {
    font-size: 0.875rem;
    color: #6b7280;
    font-weight: 500;
}

.search-section {
    display: flex;
    gap: 20px;
    align-items: center;
    justify-content: center;
}

.search-input {
    --el-input-border-radius: 12px;
}

.search-icon {
    color: #9ca3af;
}

.jobs-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(450px, 1fr));
    gap: 30px;
    margin-bottom: 40px;
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

.clickable-card {
    cursor: pointer;
}

.clickable-card:hover {
    transform: translateY(-8px) scale(1.02);
    box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
}

.status-badge {
    position: absolute;
    top: 20px;
    right: 20px;
    padding: 6px 12px;
    border-radius: 20px;
    font-size: 0.75rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    z-index: 10;
}

.status-badge.warning {
    background: #fef3c7;
    color: #92400e;
}

.status-badge.success {
    background: #d1fae5;
    color: #065f46;
}

.status-badge.primary {
    background: #dbeafe;
    color: #1e40af;
}

.status-badge.danger {
    background: #fee2e2;
    color: #991b1b;
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

.job-id-tag {
    background: #f3f4f6;
    color: #6b7280;
    border: none;
    font-weight: 500;
}

.header-actions .view-icon {
    font-size: 1.25rem;
    color: #9ca3af;
    transition: color 0.3s ease;
}

.clickable-card:hover .header-actions .view-icon {
    color: #667eea;
}

.job-description p {
    color: #4b5563;
    line-height: 1.6;
    margin-bottom: 20px;
    font-size: 0.95rem;
}

.company-info {
    display: flex;
    align-items: center;
    gap: 12px;
    margin-bottom: 20px;
    padding: 16px;
    background: #f8fafc;
    border-radius: 12px;
    border-left: 4px solid #667eea;
}

.company-avatar {
    width: 40px;
    height: 40px;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
}

.company-details {
    display: flex;
    flex-direction: column;
    gap: 2px;
}

.company-name {
    font-weight: 600;
    color: #1f2937;
    font-size: 0.95rem;
}

.posted-date {
    color: #6b7280;
    font-size: 0.875rem;
}

.application-details {
    background: linear-gradient(135deg, #f0f9ff 0%, #e0f2fe 100%);
    padding: 20px;
    border-radius: 16px;
    margin-bottom: 20px;
    border: 1px solid #e0f2fe;
}

.application-header {
    display: flex;
    align-items: center;
    gap: 8px;
    margin-bottom: 16px;
    color: #0369a1;
    font-weight: 600;
    font-size: 0.95rem;
}

.application-info {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 16px;
    margin-bottom: 16px;
}

.info-item {
    display: flex;
    flex-direction: column;
    gap: 4px;
}

.info-item .label {
    font-size: 0.8rem;
    color: #6b7280;
    font-weight: 500;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.info-item .value {
    color: #1f2937;
    font-weight: 600;
}

.cover-letter-preview {
    margin-top: 16px;
    padding-top: 16px;
    border-top: 1px solid #bae6fd;
}

.cover-letter-header {
    display: flex;
    align-items: center;
    gap: 8px;
    margin-bottom: 8px;
    color: #0369a1;
    font-weight: 600;
    font-size: 0.875rem;
}

.cover-letter-text {
    color: #374151;
    font-size: 0.875rem;
    line-height: 1.5;
    margin: 0;
    font-style: italic;
}

.attachment-link {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 10px 16px;
    background: #f3f4f6;
    border-radius: 10px;
    text-decoration: none;
    color: #4338ca;
    font-weight: 500;
    transition: all 0.3s ease;
}

.attachment-link:hover {
    background: #e5e7eb;
    transform: translateX(4px);
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

.clickable-card:hover .hover-overlay {
    opacity: 1;
}

.click-icon {
    font-size: 2rem;
}

.empty-state {
    text-align: center;
    padding: 80px 20px;
    color: #6b7280;
}

.empty-icon {
    font-size: 4rem;
    color: #d1d5db;
    margin-bottom: 20px;
}

.empty-state h3 {
    color: #374151;
    margin: 0 0 12px 0;
    font-size: 1.5rem;
}

.empty-state p {
    margin: 0 0 24px 0;
    font-size: 1.1rem;
}

.modern-pagination {
    --el-pagination-bg-color: white;
    --el-pagination-border-radius: 12px;
}

.pagination-container {
    display: flex;
    justify-content: center;
    margin-top: 40px;
}

@media (max-width: 768px) {
    .jobs-grid {
        grid-template-columns: 1fr;
    }
    
    .header-content {
        flex-direction: column;
        gap: 20px;
    }
    
    .header-stats {
        width: 100%;
        justify-content: space-between;
    }
    
    .stat-card {
        flex: 1;
        min-width: 0;
    }
    
    .search-section {
        flex-direction: column;
        align-items: stretch;
    }
    
    .search-section .el-input,
    .search-section .el-select {
        width: 100% !important;
    }
    
    .application-info {
        grid-template-columns: 1fr;
    }
    
    .header-text h1 {
        font-size: 2rem;
    }
}

@media (max-width: 480px) {
    .header-stats {
        flex-direction: column;
        gap: 12px;
    }
    
    .modern-card {
        margin: 0 -10px;
    }
}
</style>
