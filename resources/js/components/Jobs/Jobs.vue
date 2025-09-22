<template>
    <div class="common-layout">
        <el-container class="full-height">
            <el-main class="main-center">
                <div class="dashboard-container">
                    <Menu />
                    <template v-if="user_status !== 'active' || ['free_trial', 'standard'].includes(plan_type)">
                        <Message :page="'jobs'" :user_status="user_status" :user_type="user_type" :plan_type="plan_type" />
                    </template>
                    <template v-else>
                        <div class="jobs-header">
                            <h1>Job Listings</h1>
                            <div class="jobs-actions">
                                <el-input
                                    v-model="search"
                                    placeholder="Search by title or applicant..."
                                    clearable
                                    size="large"
                                    class="search-input"
                                    style="max-width: 350px;"
                                    @input="filterJobs"
                                >
                                    <template #prefix>
                                        <el-icon><Search /></el-icon>
                                    </template>
                                </el-input>
                            </div>
                        </div>
                        <el-card class="jobs-card">
                            <el-table
                                :data="filteredJobs"
                                style="width: 100%;"
                                border
                                stripe
                                highlight-current-row
                                class="modern-table"
                            >
                                <el-table-column prop="ID" label="Job ID" width="100px" align="center" />
                                <el-table-column prop="title" label="Job Title" min-width="180px" />
                                <el-table-column label="Attachment" align="center" width="140px">
                                    <template #default="scope">
                                        <el-link
                                            v-if="scope.row.thumbnail"
                                            :href="scope.row.thumbnail"
                                            target="_blank"
                                            type="primary"
                                            icon="el-icon-paperclip"
                                        >
                                            View
                                        </el-link>
                                        <el-tag v-else type="info" size="small">No Attachment</el-tag>
                                    </template>
                                </el-table-column>
                                <el-table-column prop="applicant" label="Applicants" width="160px" align="center">
                                    <template #default="scope">
                                        <el-button
                                            v-if="scope.row.applicant > 0"
                                            type="primary"
                                            size="small"
                                            @click="viewApplicants(scope.row.ID)"
                                        >
                                            {{ scope.row.applicant }} Applicant{{ scope.row.applicant > 1 ? 's' : '' }}
                                        </el-button>
                                        <el-tag v-else type="info" size="small">
                                            No Applicants
                                        </el-tag>
                                    </template>
                                </el-table-column>
                                <el-table-column label="Actions" align="center" width="140px">
                                    <template #default="scope">
                                        <el-tooltip content="View Job" placement="top">
                                            <el-button
                                                size="small"
                                                @click="viewJob(scope.row)"
                                                type="info"
                                                circle
                                            >
                                                <el-icon class="click-icon"><View /></el-icon>
                                            </el-button>
                                        </el-tooltip>
                                        <el-popconfirm
                                            title="Are you sure you want to delete this job?"
                                            @confirm="deleteJob(scope.row)"
                                            confirmButtonText="Yes"
                                            cancelButtonText="No"
                                            icon="el-icon-warning"
                                            iconColor="red"
                                        >
                                            <template #reference>
                                                <el-button
                                                    v-if="!scope.row.refund_status"
                                                    icon="el-icon-delete"
                                                    size="small"
                                                    type="danger"
                                                    circle
                                                    style="margin-left: 8px;"
                                                > <el-icon class="click-icon"><Delete /></el-icon>
                                                </el-button>
                                            </template>
                                        </el-popconfirm>
                                    </template>
                                </el-table-column>
                            </el-table>

                            <div class="pagination-container">
                                <el-pagination
                                    @current-change="handlePageChange"
                                    @size-change="handleSizeChange"
                                    :current-page="currentPage"
                                    :page-size="pageSize"
                                    :page-sizes="[5, 10, 20, 50]"
                                    :background="true"
                                    :total="totalItems"
                                    layout="total, sizes, prev, pager, next"
                                    class="modern-pagination"
                                />
                            </div>
                        </el-card>
                    </template>
                </div>
            </el-main>
        </el-container>
    </div>
</template>

<script>
import { ref, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import { useAppHelper } from '../../Composable/appHelper';
import AlertMessage from "../../Composable/AlertMessage";
import { loader } from '../../Composable/Loader';
import Menu from '../Profile/Menu';
import { Search, View, Delete } from '@element-plus/icons-vue';
import Message from '../Messages/Message.vue';

export default {
    name: 'Jobs',
    components: {
        Menu,
        Search,
        View,
        Delete,
        Message
    },
    setup() {
        const router = useRouter();
        const { get, post } = useAppHelper();
        const { error, success } = AlertMessage();
        const { startLoading, stopLoading } = loader();
        const currentPage = ref(1);
        const pageSize = ref(10);
        const totalItems = ref(0);
        const JobData = ref([]);
        const search = ref('');
        const filteredJobs = ref([]);
        const app_vars = window.driver_forge_app_vars;
        const user_status = `${app_vars.user_status ? app_vars.user_status : ''}`;
        const user_type = `${app_vars.user_data? app_vars.user_data.user_type : ''}`;
        const plan_type = `${app_vars.plan_type ? app_vars.plan_type : ''}`;

        const fetchJobs = async () => {
            startLoading();
            try {
                const response = await get(`get-jobs?page=${currentPage.value}&per_page=${pageSize.value}`);
                if (response && response.status === 'success') {
                    const data = response.data;
                    currentPage.value = data.current_page;
                    pageSize.value = data.per_page;
                    totalItems.value = data.total_items;
                    JobData.value = response.data.jobs || [];
                    filterJobs();
                } else {
                    error(response?.message || "Failed to fetch jobs");
                }
            } catch (err) {
                error(err.message);
            } finally {
                stopLoading();
            }
        };

        const filterJobs = () => {
            if (!search.value) {
                filteredJobs.value = JobData.value;
            } else {
                const term = search.value.toLowerCase();
                filteredJobs.value = JobData.value.filter(job =>
                    job.title.toLowerCase().includes(term) ||
                    (job.applicant && job.applicant.toString().includes(term))
                );
            }
        };

        const handlePageChange = (newPage) => {
            currentPage.value = newPage;
            fetchJobs();
        };

        const handleSizeChange = (newPageSize) => {
            pageSize.value = newPageSize;
            currentPage.value = 1;
            fetchJobs();
        };

        const viewJob = (row) => {
            router.push({ name: 'job-details', params: { id: row.ID } });
        };

        const viewApplicants = (jobId) => {
            router.push({ name: 'job-applicants', params: { jobId } });
        };

        const deleteJob = async (row) => {
            startLoading();
            try {
                const res = await post(`delete-job/${row.ID}`);
                if (res && res.status === 'success') {
                    success('Job deleted');
                    fetchJobs();
                } else {
                    error(res?.message || 'Failed to delete job');
                }
            } catch (e) {
                error('Failed to delete job');
            } finally {
                stopLoading();
            }
        };

        onMounted(() => {
            fetchJobs();
        });

        return {
            fetchJobs,
            JobData,
            filteredJobs,
            viewJob,
            viewApplicants,
            deleteJob,
            search,
            currentPage,
            pageSize,
            totalItems,
            handlePageChange,
            handleSizeChange,
            filterJobs,
            user_status,
            user_type,
            plan_type
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
    margin-bottom: 24px;
}
.jobs-header h1 {
    font-size: 2rem;
    font-weight: 700;
    color: #1a1a1a;
}
.jobs-actions {
    display: flex;
    align-items: center;
    gap: 16px;
}
.jobs-card {
    border-radius: 16px;
    box-shadow: 0 4px 24px rgba(102,126,234,0.08);
    padding: 24px;
    background: #fff;
}
.modern-table {
    border-radius: 12px;
    overflow: hidden;
}
.search-input {
    border-radius: 10px;
}
.pagination-container {
    display: flex;
    justify-content: center;
    margin-top: 32px;
}
.modern-pagination {
    --el-pagination-bg-color: white;
    --el-pagination-border-radius: 12px;
}
@media (max-width: 768px) {
    .jobs-header {
        flex-direction: column;
        align-items: flex-start;
        gap: 12px;
    }
    .jobs-card {
        padding: 12px;
    }
}
</style>