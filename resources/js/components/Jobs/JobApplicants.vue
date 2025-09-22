<template>
    <div class="common-layout">
        <el-container class="full-height">
            <el-main class="main-center">
                <div class="dashboard-container">
                    <Menu />
                    
                    <!-- Header -->
                    <div class="page-header">
                        <div class="header-left">
                            <el-button @click="goBack" type="primary" size="small">
                                <el-icon><ArrowLeft /></el-icon>
                                Back to Jobs
                            </el-button>
                            <div class="job-info" v-if="jobDetails">
                                <h2>Applicants for: {{ jobDetails.title }}</h2>
                                <p class="job-meta">Job ID: {{ jobId }} | Posted by: {{ jobDetails.author }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Applicants List -->
                    <el-card v-if="applicants.length > 0">
                        <template #header>
                            <div class="card-header">
                                <span>Applicants ({{ applicants.length }})</span>
                                <el-input
                                    v-model="searchQuery"
                                    placeholder="Search applicants..."
                                    style="width: 300px;"
                                    clearable
                                >
                                    <template #prefix>
                                        <el-icon><Search /></el-icon>
                                    </template>
                                </el-input>
                            </div>
                        </template>

                        <div class="applicants-grid">
                            <el-card 
                                v-for="applicant in filteredApplicants" 
                                :key="applicant.id" 
                                class="applicant-card"
                                shadow="hover"
                            >
                                <div class="applicant-header">
                                    <div class="applicant-info">
                                        <h3>{{ applicant.name }}</h3>
                                        <p class="applicant-email">{{ applicant.email }}</p>
                                        <el-tag 
                                            :type="getStatusType(applicant.status)" 
                                            size="small"
                                        >
                                            {{ applicant.status }}
                                        </el-tag>
                                    </div>
                                    <div class="applicant-date">
                                        <small>Applied: {{ formatDate(applicant.application_date) }}</small>
                                    </div>
                                </div>

                                <div class="cover-letter" v-if="applicant.cover_letter">
                                    <h4>Cover Letter:</h4>
                                    <p>{{ applicant.cover_letter }}</p>
                                </div>

                                <div class="employer-notes" v-if="applicant.employer_notes">
                                    <h4>Employer Notes:</h4>
                                    <p>{{ applicant.employer_notes }}</p>
                                </div>

                                <div class="applicant-actions">
                                    <el-button 
                                        type="primary" 
                                        size="small" 
                                        @click="openNoteDialog(applicant)"
                                    >
                                        Add Note
                                    </el-button>
                                    <el-button 
                                        type="success" 
                                        size="small" 
                                        @click="sendMessage(applicant)"
                                    >
                                        Send Message
                                    </el-button>
                                    <el-dropdown @command="updateStatus">
                                        <el-button size="small">
                                            Update Status<el-icon class="el-icon--right"><arrow-down /></el-icon>
                                        </el-button>
                                        <template #dropdown>
                                            <el-dropdown-menu>
                                                <el-dropdown-item :command="{applicant, status: 'pending'}">Pending</el-dropdown-item>
                                                <el-dropdown-item :command="{applicant, status: 'reviewing'}">Reviewing</el-dropdown-item>
                                                <el-dropdown-item :command="{applicant, status: 'shortlisted'}">Shortlisted</el-dropdown-item>
                                                <el-dropdown-item :command="{applicant, status: 'rejected'}">Rejected</el-dropdown-item>
                                                <el-dropdown-item :command="{applicant, status: 'hired'}">Hired</el-dropdown-item>
                                            </el-dropdown-menu>
                                        </template>
                                    </el-dropdown>
                                </div>
                            </el-card>
                        </div>
                    </el-card>

                    <!-- Empty State -->
                    <el-empty v-else description="No applicants found for this job" />

                    <!-- Add Note Dialog -->
                    <el-dialog 
                        v-model="showNoteDialog" 
                        title="Add Employer Note" 
                        width="600px"
                    >
                        <div v-if="selectedApplication" class="dialog-applicant-info">
                            <h3>{{ selectedApplication.name }}</h3>
                            <p>{{ selectedApplication.email }}</p>
                        </div>
                        
                        <el-form 
                            :model="noteForm" 
                            :rules="noteRules"
                            ref="noteFormRef"
                            label-width="120px"
                        >
                            <el-form-item label="Note" prop="note">
                                <el-input
                                    type="textarea"
                                    v-model="noteForm.note"
                                    :rows="4"
                                    placeholder="Add your note about this applicant..."
                                />
                            </el-form-item>
                        </el-form>

                        <template #footer>
                            <el-button @click="showNoteDialog = false">Cancel</el-button>
                            <el-button 
                                type="primary" 
                                @click="submitNote"
                                :loading="noteLoading"
                            >
                                Save Note
                            </el-button>
                        </template>
                    </el-dialog>

                    <!-- Send Message Dialog -->
                    <el-dialog 
                        v-model="showMessageDialog" 
                        title="Send Message to Applicant" 
                        width="600px"
                    >
                        <div v-if="selectedApplication" class="dialog-applicant-info">
                            <h3>{{ selectedApplication.name }}</h3>
                            <p>{{ selectedApplication.email }}</p>
                        </div>
                        
                        <el-form 
                            :model="messageForm" 
                            :rules="messageRules"
                            ref="messageFormRef"
                            label-width="120px"
                        >
                            <el-form-item label="Message" prop="message">
                                <el-input
                                    type="textarea"
                                    v-model="messageForm.message"
                                    :rows="6"
                                    placeholder="Type your message here..."
                                />
                            </el-form-item>
                        </el-form>

                        <template #footer>
                            <el-button @click="showMessageDialog = false">Cancel</el-button>
                            <el-button 
                                type="primary" 
                                @click="submitMessage"
                                :loading="messageLoading"
                            >
                                Send Message
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
import { useRouter, useRoute } from 'vue-router';
import { useAppHelper } from '../../Composable/appHelper';
import AlertMessage from "../../Composable/AlertMessage";
import { loader } from '../../Composable/Loader';
import Menu from '../Profile/Menu';
import { ArrowLeft, Search, ArrowDown } from '@element-plus/icons-vue';

export default {
    name: 'JobApplicants',
    components: {
        Menu,
        ArrowLeft,
        Search,
        ArrowDown,
    },
    setup() {
        const router = useRouter();
        const route = useRoute();
        const { get, post } = useAppHelper();
        const { error, success } = AlertMessage();
        const { startLoading, stopLoading } = loader();

        const jobId = ref(route.params.jobId);
        const jobDetails = ref(null);
        const applicants = ref([]);
        const searchQuery = ref('');
        const showNoteDialog = ref(false);
        const showMessageDialog = ref(false);
        const selectedApplication = ref(null);
        const noteLoading = ref(false);
        const messageLoading = ref(false);
        const noteFormRef = ref(null);
        const messageFormRef = ref(null);

        const noteForm = ref({
            note: ''
        });

        const messageForm = ref({
            message: ''
        });

        const noteRules = {
            note: [
                { required: true, message: 'Note is required', trigger: 'blur' },
                { min: 5, message: 'Note should be at least 5 characters', trigger: 'blur' }
            ]
        };

        const messageRules = {
            message: [
                { required: true, message: 'Message is required', trigger: 'blur' },
                { min: 10, message: 'Message should be at least 10 characters', trigger: 'blur' }
            ]
        };

        const filteredApplicants = computed(() => {
            if (!searchQuery.value) return applicants.value;
            return applicants.value.filter(applicant => 
                applicant.name.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
                applicant.email.toLowerCase().includes(searchQuery.value.toLowerCase())
            );
        });

        const fetchApplicants = async () => {
            startLoading();
            try {
                const response = await get(`job-applicants/${jobId.value}`);
                if (response && response.status === 'success') {
                    jobDetails.value = response.data.jobDetails;
                    applicants.value = response.data.applicants || [];
                } else {
                    error(response?.message || "Failed to fetch applicants");
                }
            } catch (err) {
                error(err.message);
            } finally {
                stopLoading();
            }
        };

        const goBack = () => {
            router.push({ name: 'jobs' });
        };

        const formatDate = (dateString) => {
            const date = new Date(dateString);
            return date.toLocaleDateString('en-US', {
                year: 'numeric',
                month: 'short',
                day: 'numeric',
                hour: '2-digit',
                minute: '2-digit'
            });
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

        const openNoteDialog = (applicant) => {
            selectedApplication.value = applicant;
            noteForm.value.note = applicant.employer_notes || '';
            showNoteDialog.value = true;
        };

        const submitNote = async () => {
            if (!noteFormRef.value) return;

            noteFormRef.value.validate(async (valid) => {
                if (!valid) return;

                noteLoading.value = true;
                try {
                    const response = await post('add-applicant-note', {
                        applicant_id: selectedApplication.value.id,
                        note: noteForm.value.note
                    });

                    if (response && response.status === 'success') {
                        success('Note saved successfully!');
                        
                        // Update local data
                        const index = applicants.value.findIndex(app => app.id === selectedApplication.value.id);
                        if (index !== -1) {
                            applicants.value[index].employer_notes = noteForm.value.note;
                        }
                        
                        showNoteDialog.value = false;
                    } else {
                        error(response?.message || 'Failed to save note');
                    }
                } catch (err) {
                    error(err.message);
                } finally {
                    noteLoading.value = false;
                }
            });
        };

        const sendMessage = (applicant) => {
            selectedApplication.value = applicant;
            messageForm.value.message = '';
            showMessageDialog.value = true;
        };

        const submitMessage = async () => {
            if (!messageFormRef.value) return;

            messageFormRef.value.validate(async (valid) => {
                if (!valid) return;

                messageLoading.value = true;
                try {
                    const response = await post('send-message', {
                        recipient_id: selectedApplication.value.applicant_id,
                        job_id: selectedApplication.value.job_id,
                        application_id: selectedApplication.value.id,
                        message: messageForm.value.message
                    });

                    if (response && response.status === 'success') {
                        success('Message sent successfully!');
                        showMessageDialog.value = false;
                    } else {
                        error(response?.message || 'Failed to send message');
                    }
                } catch (err) {
                    error(err.message);
                } finally {
                    messageLoading.value = false;
                }
            });
        };

        const updateStatus = async ({ applicant, status }) => {
            try {
                const response = await post('update-applicant-status', {
                    applicant_id: applicant.id,
                    status: status
                });

                if (response && response.status === 'success') {
                    success(`Status updated to ${status}`);
                    
                    // Update local data
                    const index = applicants.value.findIndex(app => app.id === applicant.id);
                    if (index !== -1) {
                        applicants.value[index].status = status;
                    }
                } else {
                    error(response?.message || 'Failed to update status');
                }
            } catch (err) {
                error(err.message);
            }
        };

        onMounted(() => {
            fetchApplicants();
        });

        return {
            jobId,
            jobDetails,
            applicants,
            filteredApplicants,
            searchQuery,
            showNoteDialog,
            showMessageDialog,
            selectedApplication,
            noteForm,
            messageForm,
            noteRules,
            messageRules,
            noteFormRef,
            messageFormRef,
            noteLoading,
            messageLoading,
            goBack,
            formatDate,
            getStatusType,
            openNoteDialog,
            submitNote,
            sendMessage,
            submitMessage,
            updateStatus,
        };
    },
};
</script>

<style scoped>
.dashboard-container {
    padding: 30px 0;
}

.page-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 30px;
}

.header-left {
    flex: 1;
}

.job-info {
    margin-top: 15px;
}

.job-info h2 {
    margin: 0 0 5px 0;
    color: #303133;
}

.job-meta {
    margin: 0;
    color: #606266;
    font-size: 14px;
}

.card-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.applicants-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(500px, 1fr));
    gap: 20px;
}

.applicant-card {
    border: 1px solid #ebeef5;
}

.applicant-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 15px;
}

.applicant-info h3 {
    margin: 0 0 5px 0;
    color: #303133;
}

.applicant-email {
    margin: 0 0 10px 0;
    color: #606266;
    font-size: 14px;
}

.applicant-date {
    text-align: right;
    color: #909399;
    font-size: 12px;
}

.cover-letter, .employer-notes {
    margin-bottom: 15px;
    padding: 10px;
    background: #f5f7fa;
    border-radius: 6px;
}

.employer-notes {
    background: #fff3cd;
    border-left: 4px solid #ffc107;
    border-radius: 6px;
    position: relative;
}

.employer-notes::before {
    content: '';
    position: absolute;
    top: -2px;
    right: 10px;
    width: 20px;
    height: 20px;
    background: #ffc107;
    transform: rotate(45deg);
    border-radius: 2px;
}

.cover-letter h4, .employer-notes h4 {
    margin: 0 0 8px 0;
    color: #303133;
    font-size: 14px;
}

.employer-notes h4 {
    color: #856404;
    font-weight: 600;
}

.cover-letter p, .employer-notes p {
    margin: 0;
    color: #606266;
    font-size: 14px;
    line-height: 1.5;
}

.employer-notes p {
    color: #856404;
}

.applicant-actions {
    display: flex;
    gap: 10px;
    flex-wrap: wrap;
}

.dialog-applicant-info {
    margin-bottom: 20px;
    padding: 15px;
    background: #f5f7fa;
    border-radius: 8px;
}

.dialog-applicant-info h3 {
    margin: 0 0 5px 0;
    color: #303133;
}

.dialog-applicant-info p {
    margin: 0;
    color: #606266;
}

@media (max-width: 768px) {
    .applicants-grid {
        grid-template-columns: 1fr;
    }
    
    .page-header {
        flex-direction: column;
        gap: 15px;
    }
    
    .applicant-header {
        flex-direction: column;
        gap: 10px;
    }
    
    .applicant-actions {
        justify-content: center;
    }
}
</style>