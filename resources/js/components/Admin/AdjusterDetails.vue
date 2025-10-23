<template>
    <el-container class="full-height">
        <el-main class="main-center">
            <Header />
            <div class="dashboard-container">
                <div class="header-section">
                    <h1 class="adjuster-title">Adjuster Details</h1>
                    <el-button type="info" @click="goBack" class="back-btn">
                        <el-icon><ArrowLeft /></el-icon>
                        Go Back to Adjuster List
                    </el-button>
                </div>
                
                <div v-if="adjuster" class="adjuster-layout">
                    <!-- Adjuster Profile Header -->
                    <el-card class="profile-header">
                        <div class="profile-header-flex">
                            <div class="profile-content">
                                <div class="profile-avatar">
                                    <el-image
                                        :src="imageUrl( image_url, adjuster.profile_picture ) || 'https://ui-avatars.com/api/?name=' + encodeURIComponent(adjuster.first_name + ' ' + adjuster.last_name)"
                                        fit="cover"
                                        class="avatar-image"
                                        :alt="adjuster.first_name + ' ' + adjuster.last_name"
                                    />
                                </div>
                                <div class="profile-info">
                                    <h1 class="profile-name">{{ adjuster.first_name }} {{ adjuster.last_name }}</h1>
                                    <div class="profile-badges">
                                        <el-tag :type="adjuster.status === 'active' ? 'success' : adjuster.status === 'pending' ? 'warning' : adjuster.status === 'approved' ? 'success' : adjuster.status === 'rejected' ? 'danger' : 'info'" size="large">
                                            {{ getStatusLabel(adjuster.status) }}
                                        </el-tag>
                                        <el-tag v-if="adjuster.plan_type" :type="adjuster.plan_type === 'premium' ? 'warning' : 'info'" size="large">
                                            {{ adjuster.plan_type.toUpperCase() }}
                                        </el-tag>
                                    </div>
                                </div>
                                <div class="profile-contact">
                                    <div class="contact-item">
                                        <el-icon><Message /></el-icon>
                                        <span>{{ adjuster.user_email }}</span>
                                    </div>
                                    <div class="contact-item" v-if="adjuster.phone">
                                        <el-icon><Phone /></el-icon>
                                        <span>{{ adjuster.phone }}</span>
                                    </div>
                                    <div class="contact-item" v-if="adjuster.city && adjuster.state">
                                        <el-icon><Location /></el-icon>
                                        <span>{{ adjuster.city }}, {{ adjuster.state }}</span>
                                    </div>
                                </div>
                            </div>
                            <div class="about-content" v-if="adjuster.about">
                                <h2 class="about-title">About Adjuster</h2>
                                <p>{{ adjuster.about }}</p>
                            </div>
                        </div>
                    </el-card>
                    <!-- Main Content Grid -->
                    <el-row :gutter="24" class="content-grid">
                        <!-- Left Column -->
                        <el-col :lg="12" :md="24" :sm="24">
                            <!-- Basic Information -->
                            <el-card class="info-card" header="Basic Information">
                                <el-descriptions :column="1" border>
                                    <el-descriptions-item label="Username">
                                        <el-tag type="info">{{ adjuster.username }}</el-tag>
                                    </el-descriptions-item>
                                    <el-descriptions-item label="User Type">
                                        <el-tag type="primary">{{ adjuster.user_type || 'Adjuster' }}</el-tag>
                                    </el-descriptions-item>
                                    <el-descriptions-item label="Years Experience">
                                        <span>{{ adjuster.years_experience || 'N/A' }}</span>
                                    </el-descriptions-item>
                                    <el-descriptions-item label="Location">
                                        <div class="address-info">
                                            <div>{{ adjuster.address || 'N/A' }}</div>
                                            <div>{{ adjuster.city }}, {{ adjuster.state }}</div>
                                        </div>
                                    </el-descriptions-item>
                                </el-descriptions>
                            </el-card>

                            <!-- Profile Status -->
                            <el-card class="info-card" header="Profile Status">
                                <el-descriptions :column="1" border>
                                    <el-descriptions-item label="Profile Completed">
                                        <el-tag :type="adjuster.profile_completed ? 'success' : 'warning'" size="large">
                                            {{ adjuster.profile_completed ? 'Completed' : 'Incomplete' }}
                                        </el-tag>
                                    </el-descriptions-item>
                                    <el-descriptions-item label="Completed At">
                                        <span>{{ formatDate(adjuster.profile_completed_at) }}</span>
                                    </el-descriptions-item>
                                    <el-descriptions-item label="Account Active">
                                        <el-tag :type="adjuster.is_active ? 'success' : 'danger'" size="large">
                                            {{ adjuster.is_active ? 'Active' : 'Inactive' }}
                                        </el-tag>
                                    </el-descriptions-item>
                                    <el-descriptions-item label="Verification Status">
                                        <el-tag :type="adjuster.status === 'approved' ? 'success' : adjuster.status === 'rejected' ? 'danger' : 'warning'" size="large">
                                            {{ adjuster.status === 'approved' ? 'Verified' : adjuster.status === 'rejected' ? 'Rejected' : 'Pending Verification' }}
                                        </el-tag>
                                    </el-descriptions-item>
                                </el-descriptions>
                            </el-card>
                        </el-col>

                        <!-- Right Column -->
                        <el-col :lg="12" :md="24" :sm="24">
                            <!-- Documents -->
                            <el-card class="info-card" header="Documents">
                                <el-descriptions :column="1" border>
                                    <el-descriptions-item label="Resume">
                                        <div class="document-status">
                                            <template v-if="adjuster.resume">
                                                <el-link :href="download_url( adjuster.resume )" target="_blank" type="primary" download>
                                                    <el-icon><Download /></el-icon>
                                                    Download Resume
                                                </el-link>
                                            </template>
                                            <template v-else>
                                                <el-tag type="info" size="small">No Resume</el-tag>
                                            </template>
                                        </div>
                                    </el-descriptions-item>
                                    <el-descriptions-item label="W-9 Form">
                                        <div class="document-status">
                                            <template v-if="adjuster.w9">
                                                <el-link :href="download_url( adjuster.w9 )" target="_blank" type="primary" download>
                                                    <el-icon><Download /></el-icon>
                                                    Download W-9
                                                </el-link>
                                            </template>
                                            <template v-else>
                                                <el-tag type="warning" size="small">No W-9 Form</el-tag>
                                            </template>
                                        </div>
                                    </el-descriptions-item>
                                    <el-descriptions-item label="Insurance Proof">
                                        <div class="document-status">
                                            <template v-if="adjuster.insurance_proof">
                                                <el-link :href="download_url( adjuster.insurance_proof )" target="_blank" type="primary" download>
                                                    <el-icon><Download /></el-icon>
                                                    Download Insurance Proof
                                                </el-link>
                                            </template>
                                            <template v-else>
                                                <el-tag type="warning" size="small">No Insurance Proof</el-tag>
                                            </template>
                                        </div>
                                    </el-descriptions-item>
                                </el-descriptions>
                            </el-card>

                            <!-- Last Payment Information -->
                            <el-card class="info-card" header="Last Payment Information">
                                <el-descriptions :column="1" border>
                                    <el-descriptions-item label="Subscription Fee">
                                        <div class="payment-status">
                                            <el-tag :type="adjuster.paid_subscription_fee ? 'success' : 'danger'">
                                                {{ adjuster.paid_subscription_fee ? 'Paid' : 'Unpaid' }}
                                            </el-tag>
                                            <span v-if="adjuster.paid_subscription_fee_at" class="payment-date">
                                                {{ formatDate(adjuster.paid_subscription_fee_at) }}
                                            </span>
                                        </div>
                                    </el-descriptions-item>
                                    <el-descriptions-item label="Customer ID">
                                        <el-tag type="info" size="small">{{ adjuster.customer_id || 'N/A' }}</el-tag>
                                    </el-descriptions-item>
                                    <el-descriptions-item label="Transaction ID">
                                        <el-tag type="info" size="small">{{ adjuster.transaction_id || 'N/A' }}</el-tag>
                                    </el-descriptions-item>
                                </el-descriptions>
                                <div class="payment-actions">
                                    <el-button type="primary" @click="editPaymentInfo(adjuster.user_id)" class="edit-payment-btn">
                                        <el-icon><Edit /></el-icon>
                                        Edit Payment Information
                                    </el-button>
                                </div>
                            </el-card>
                        </el-col>
                    </el-row>

                    <!-- Subscription Details Section -->
                    <el-row :gutter="24" class="content-grid">
                        <el-col :lg="24" :md="24" :sm="24">
                            <el-card class="info-card" header="Subscription Details">
                                <el-descriptions :column="2" border>
                                    <el-descriptions-item label="Plan Type">
                                        <el-tag :type="adjuster.plan_type === 'premium' ? 'warning' : 'info'" size="large">
                                            {{ adjuster.plan_type || 'Basic' }}
                                        </el-tag>
                                    </el-descriptions-item>
                                    <el-descriptions-item label="Subscription Type">
                                        <span>{{ adjuster.subscription_type || 'N/A' }}</span>
                                    </el-descriptions-item>
                                    <el-descriptions-item label="Status">
                                        <div class="subscription-status">
                                            <el-tag :type="!adjuster.is_expired ? 'success' : 'danger'" size="large">
                                                {{ !adjuster.is_expired ? 'Active' : 'Expired' }}
                                            </el-tag>
                                            <el-tag v-if="adjuster.subscription_canceled" type="warning" size="small">
                                                Canceled
                                            </el-tag>
                                        </div>
                                    </el-descriptions-item>
                                    <el-descriptions-item label="Days Remaining">
                                        <el-tag :type="adjuster.days_remaining > 30 ? 'success' : adjuster.days_remaining > 7 ? 'warning' : 'danger'">
                                            {{ adjuster.days_remaining || 0 }} days
                                        </el-tag>
                                    </el-descriptions-item>
                                    <el-descriptions-item label="Subscription Expires At">
                                        <span>{{ formatDate(adjuster.subscription_expire_at) }}</span>
                                    </el-descriptions-item>
                                    <el-descriptions-item label="Account Active">
                                        <el-tag :type="adjuster.is_active ? 'success' : 'danger'" size="large">
                                            {{ adjuster.is_active ? 'Active' : 'Inactive' }}
                                        </el-tag>
                                    </el-descriptions-item>
                                </el-descriptions>
                            </el-card>
                        </el-col>
                    </el-row>

                    <!-- Licenses Section -->
                    <el-card v-if="adjuster.licenses && adjuster.licenses.length" class="licenses-card" header="Licenses">
                        <el-table :data="adjuster.licenses" style="width: 100%;" stripe>
                            <el-table-column prop="state" label="State" min-width="100" />
                            <el-table-column prop="number" label="License Number" min-width="150" />
                            <el-table-column prop="expiration" label="Expiration" min-width="120" />
                            <el-table-column label="File" min-width="120">
                                <template #default="scope">
                                    <template v-if="scope.row.file_url">
                                        <el-link :href="download_url(scope.row.file_url)" target="_blank" type="primary" download>
                                            <el-icon><Download /></el-icon>
                                            Download
                                        </el-link>
                                    </template>
                                    <template v-else>
                                        <el-tag type="info" size="small">No File</el-tag>
                                    </template>
                                </template>
                            </el-table-column>
                        </el-table>
                    </el-card>

                    <!-- Badges Section -->
                    <el-card v-if="adjuster.badges && adjuster.badges.length" class="badges-card" header="Certifications & Badges">
                        <el-table :data="adjuster.badges" style="width: 100%;" stripe>
                            <el-table-column prop="badge" label="Badge/Certification" min-width="200" />
                            <el-table-column label="Proof File" min-width="150">
                                <template #default="scope">
                                    <template v-if="scope.row.proof_file_url">
                                        <el-link :href="download_url(scope.row.proof_file_url)" target="_blank" type="primary" download>
                                            <el-icon><Download /></el-icon>
                                            Download Proof
                                        </el-link>
                                    </template>
                                    <template v-else>
                                        <el-tag type="info" size="small">No Proof</el-tag>
                                    </template>
                                </template>
                            </el-table-column>
                        </el-table>
                    </el-card>

                    <!-- References Section -->
                    <el-card v-if="adjuster.references && adjuster.references.length" class="references-card" header="References">
                        <el-table :data="adjuster.references" style="width: 100%;" stripe>
                            <el-table-column prop="name" label="Name" min-width="150" />
                            <el-table-column prop="phone" label="Phone" min-width="130" />
                            <el-table-column prop="relationship" label="Relationship" min-width="180" />
                        </el-table>
                    </el-card>

                    <!-- Action Buttons -->
                    <el-card class="action-card">
                        <div class="action-btn-group">
                            <template v-if="adjuster.profile_completed">
                                <template v-if="adjuster.status != 'approved' && adjuster.status != 'rejected' && adjuster.status != 'active'">
                                    <el-button type="success" size="large" @click="toggleAdjuster(adjuster.user_id, 'approved')">
                                        <el-icon><Check /></el-icon>
                                        Approve Adjuster
                                    </el-button>
                                    <el-button type="danger" size="large" @click="toggleAdjuster(adjuster.user_id, 'rejected')">
                                        <el-icon><Close /></el-icon>
                                        Reject Adjuster
                                    </el-button>
                                </template>
                                <template v-else>
                                    <el-button
                                        :type="adjuster.status === 'active' || adjuster.status === 'approved' ? 'danger' : 'success'"
                                        size="large"
                                        @click="toggleAccess(adjuster.user_id, adjuster.status)"
                                    >
                                        <el-icon v-if="adjuster.status === 'active' || adjuster.status === 'approved'"><Lock /></el-icon>
                                        <el-icon v-else><Unlock /></el-icon>
                                        {{ adjuster.status === 'active' || adjuster.status === 'approved' ? 'Revoke Access' : 'Permit Access' }}
                                    </el-button>
                                </template>
                            </template>
                            <el-button type="info" size="large" @click="goBack">
                                <el-icon><Back /></el-icon>
                                Back to List
                            </el-button>
                        </div>
                    </el-card>
                </div>
                
                <el-empty v-else description="No adjuster data found." />
            </div>
        </el-main>
    </el-container>
</template>

<script>
import { ref, onMounted } from "vue";
import { useRoute, useRouter } from 'vue-router';
import AlertMessage from "../../Composable/AlertMessage";
import { useAppHelper } from "../../Composable/appHelper";
import { loader } from '../../Composable/Loader';
import Header from "../Header";
import { 
    ArrowLeft, 
    Message, 
    Phone, 
    Location,
    Download,
    Check,
    Close,
    Lock, 
    Unlock, 
    Back,
    Edit
} from '@element-plus/icons-vue';

export default {
    name: "AdjusterDetails",
    components: {
        Header,
        ArrowLeft,
        Message,
        Phone,
        Location,
        Download,
        Check,
        Close,
        Lock,
        Unlock,
        Back,
        Edit
    },
    setup() {
        const route = useRoute();
        const router = useRouter();
        const { get, post, imageUrl, getStatusLabel } = useAppHelper();
        const { error, success } = AlertMessage();
        const { startLoading, stopLoading } = loader();
        const adjuster = ref({});
        const app_vars = window.adjuster_forge_app_vars;
        const image_url = `${app_vars.image_url ? app_vars.image_url : ''}`;
        const home_url = `${app_vars.home_url ? app_vars.home_url : ''}`;

        const fetchAdjusterData = async () => {
            startLoading();
            try {
                const response = await get(`adjuster/${route.params.user_id}`);
                if (response.status === 'success') {
                    let data = response.data;
                    let references = data.references || [];
                    if (typeof references === 'string') {
                        try {
                            references = references.replace(/\\"/g, '"');
                            data.references = JSON.parse(references);
                        } catch (err) {
                            console.warn('Failed to parse references JSON:', err);
                            data.references = [];
                        }
                    } else if (Array.isArray(references)) {
                        data.references = references;
                    } else {
                        data.references = [];
                    }

                    adjuster.value = data;
                } else {
                    error(response.message);
                }
            } catch (err) {
                error(err.message);
            } finally {
                stopLoading();
            }
        };

        const goBack = () => {
            router.push({ name: 'AdjustersList' });
        };

        // Approve or Reject Adjuster
        const toggleAdjuster = async (user_id, action = 'approved') => {
            let message = action === 'approved' ? 'Approving...' : 'Rejecting...';
            startLoading(message);
            try {
                const response = await post('toggle-adjuster', { user_id:user_id, action: action });
                if (response.status === 'success') {
                    success(response.message);
                    fetchAdjusterData();
                } else {
                    error(response.message);
                }
            } catch (err) {
                error(err.message);
            } finally {
                stopLoading();
            }
        };

        // Permit or Revoke Access based on status
        const toggleAccess = async (user_id, status) => {
            startLoading(status === 'active' || status === 'approved' ? 'Revoking...' : 'Permitting...');
            const url = status === 'active' || status === 'approved' ? 'revoke-access' : 'permit-access';
            try {
                const response = await post(url, { id: user_id });
                if (response.status === 'success') {
                    success(response.message);
                    // Update status locally for instant UI feedback
                    adjuster.value.status = status === 'active' ? 'revoked' : 'active';
                    // Optionally, sync with backend
                    fetchAdjusterData();
                } else {
                    error(response.message);
                }
            } catch (err) {
                error(err.message);
            } finally {
                stopLoading();
            }
        };

        const download_url = (file_name) => {
            return home_url + '/download/' + file_name;
        };

        // Format date for display
        const formatDate = (dateString) => {
            if (!dateString) return 'N/A';
            try {
                const date = new Date(dateString);
                return date.toLocaleDateString('en-US', {
                    year: 'numeric',
                    month: 'long',
                    day: 'numeric',
                    hour: '2-digit',
                    minute: '2-digit'
                });
            } catch (err) {
                return dateString;
            }
        };

        // Navigate to manual payment page
        const editPaymentInfo = (userId) => {
            router.push({ path: `/manual-payment/${userId}` });
        };

        onMounted(() => {
            fetchAdjusterData();
        });

        return {
            adjuster,
            image_url,
            imageUrl,
            download_url,
            goBack,
            toggleAdjuster,
            toggleAccess,
            getStatusLabel,
            formatDate,
            editPaymentInfo
        };
    },
};
</script>

<style scoped>
.dashboard-container {
    max-width: 1400px;
    margin: 0 auto;
    padding: 20px;
}

.header-section {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 24px;
}

.adjuster-title {
    font-size: 2.2rem;
    font-weight: 700;
    color: #1a1a1a;
    margin: 0;
}

.back-btn {
    border-radius: 8px;
    font-weight: 500;
}

.adjuster-layout {
    display: flex;
    flex-direction: column;
    gap: 24px;
}

/* Profile Header Styles */
.profile-header {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border: none;
    border-radius: 16px;
    box-shadow: 0 8px 32px rgba(102, 126, 234, 0.2);
    overflow: hidden;
}

.profile-header :deep(.el-card__body) {
    padding: 32px;
}

.profile-header-flex {
  display: flex;
  flex-direction: column;
  align-items: stretch;
  gap: 48px;
  color: white;
}

.profile-content {
  display: flex;
  flex-direction: row;
  justify-content: center;
  gap: 32px;
  color: white;
  flex: 1;
}

.profile-avatar {
    flex-shrink: 0;
}

.avatar-image {
    width: 120px;
    height: 120px;
    border-radius: 50%;
    border: 4px solid rgba(255, 255, 255, 0.2);
    box-shadow: 0 8px 24px rgba(0, 0, 0, 0.2);
}

.profile-info {
    flex: 1;
}

.profile-name {
    font-size: 2.25rem;
    font-weight: 700;
    color: white;
    margin: 0 0 16px 0;
    text-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

.profile-badges {
    display: flex;
    gap: 12px;
    flex-wrap: wrap;
}

.profile-contact {
    display: flex;
    flex-direction: column;
    gap: 12px;
    min-width: 280px;
}

.contact-item {
    display: flex;
    align-items: center;
    gap: 12px;
    color: rgba(255, 255, 255, 0.9);
    font-size: 1rem;
    font-weight: 500;
}

.contact-item .el-icon {
    font-size: 18px;
    color: rgba(255, 255, 255, 0.8);
}

/* Content Grid */
.content-grid {
    margin-bottom: 24px;
}

.info-card {
    margin-bottom: 24px;
    border-radius: 12px;
    box-shadow: 0 2px 12px rgba(0, 0, 0, 0.05);
    border: 1px solid #e4e7ed;
}

.info-card .el-card__header {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    font-weight: 600;
    font-size: 16px;
    border-radius: 12px 12px 0 0;
}

.licenses-card, .badges-card {
    margin-bottom: 24px;
    border-radius: 12px;
    box-shadow: 0 2px 12px rgba(0, 0, 0, 0.05);
    border: 1px solid #e4e7ed;
}

.licenses-card .el-card__header,
.badges-card .el-card__header {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    font-weight: 600;
    font-size: 16px;
    border-radius: 12px 12px 0 0;
}

/* Address Info */
.address-info div {
    margin-bottom: 4px;
}

/* Document Status */
.document-status {
    display: flex;
    align-items: center;
    gap: 8px;
}

.document-status .el-link {
    display: flex;
    align-items: center;
    gap: 6px;
}

/* Subscription Status */
.subscription-status {
    display: flex;
    align-items: center;
    gap: 8px;
}

/* Payment Status */
.payment-status {
    display: flex;
    flex-direction: column;
    gap: 4px;
}

.payment-date {
    font-size: 12px;
    color: #8492a6;
}

/* Payment Actions */
.payment-actions {
    margin-top: 16px;
    padding-top: 16px;
    border-top: 1px solid #e4e7ed;
    display: flex;
    justify-content: center;
}

.edit-payment-btn {
    display: flex;
    align-items: center;
    gap: 8px;
    font-weight: 500;
}

/* About Content */
.about-content {
  flex: 1;
  background: rgba(255,255,255,0.08);
  border-radius: 12px;
  padding: 32px 24px;
  color: #fff;
  display: flex;
  flex-direction: column;
  justify-content: center;
  align-items: flex-start;
  min-width: 320px;
  box-shadow: 0 2px 12px rgba(0,0,0,0.04);
}

.about-title {
  font-size: 1.3rem;
  font-weight: 600;
  margin-bottom: 12px;
  color: #fff;
  letter-spacing: 0.5px;
}

.about-content p {
  font-size: 1rem;
  line-height: 1.7;
  color: #f8f9ff;
  margin: 0;
  word-break: break-word;
}

/* References Card */
.references-card {
    border-radius: 12px;
    box-shadow: 0 2px 12px rgba(0, 0, 0, 0.05);
    border: 1px solid #e4e7ed;
}

.references-card .el-card__header {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    font-weight: 600;
    font-size: 16px;
    border-radius: 12px 12px 0 0;
}

/* Action Card */
.action-card {
    border-radius: 12px;
    box-shadow: 0 2px 12px rgba(0, 0, 0, 0.05);
    border: 1px solid #e4e7ed;
}

.action-btn-group {
    display: flex;
    gap: 16px;
    justify-content: center;
    flex-wrap: wrap;
}

.action-btn-group .el-button {
    font-weight: 500;
    border-radius: 8px;
    padding: 12px 24px;
    display: flex;
    align-items: center;
    gap: 8px;
}

/* Responsive Design */
@media (max-width: 1200px) {
    .dashboard-container {
        padding: 16px;
    }
    
    .adjuster-title {
        font-size: 1.8rem;
    }
}

@media (max-width: 768px) {
    .header-section {
        flex-direction: column;
        align-items: flex-start;
        gap: 16px;
    }
    
    .profile-content {
        flex-direction: column;
        align-items: center;
        text-align: center;
    }
    
    .profile-contact {
        align-items: center;
    }
    
    .action-btn-group {
        flex-direction: column;
    }
    
    .adjuster-title {
        font-size: 1.6rem;
    }
    
    .profile-name {
        font-size: 1.8rem;
    }
}

@media (max-width: 480px) {
    .dashboard-container {
        padding: 12px;
    }
    
    .avatar-image {
        width: 100px;
        height: 100px;
    }
    
    .adjuster-title {
        font-size: 1.4rem;
    }
    
    .profile-name {
        font-size: 1.5rem;
    }
}

/* Element UI Customizations */
.el-descriptions {
    --el-descriptions-item-bordered-label-background: #f8f9fa;
}

.el-tag--large {
    font-size: 14px;
    padding: 8px 16px;
    font-weight: 500;
}

.el-table {
    border-radius: 8px;
    overflow: hidden;
}

.el-card {
    --el-card-border-radius: 12px;
}
</style>