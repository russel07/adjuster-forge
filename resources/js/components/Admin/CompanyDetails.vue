<template>
    <el-container class="full-height">
        <el-main class="main-center">
            <Header />
            <div class="dashboard-container">
                <div class="header-section">
                    <h1 class="company-title">Company Details</h1>
                    <el-button type="info" @click="goBack" class="back-btn">
                        <el-icon><ArrowLeft /></el-icon>
                        Go Back to Company List
                    </el-button>
                </div>
                
                <div v-if="company" class="company-layout">
                    <!-- Company Profile Header -->
                    <el-card class="profile-header-card">
                        <div class="company-profile-header">
                            <div class="profile-image-section">
                                <el-image
                                    :src="imageUrl( image_url, company.profile_picture ) || 'https://ui-avatars.com/api/?name=' + encodeURIComponent(company.company_name || company.first_name + ' ' + company.last_name)"
                                    fit="cover"
                                    class="company-image"
                                    :alt="company.company_name || company.first_name + ' ' + company.last_name"
                                />
                                <div class="profile-badges">
                                    <el-tag :type="company.status === 'active' ? 'success' : company.status === 'pending' ? 'warning' : 'danger'" size="large">
                                        {{ getStatusLabel(company.status) }}
                                    </el-tag>
                                    <el-tag v-if="company.plan_type" :type="company.plan_type === 'premium' ? 'warning' : 'info'" size="large">
                                        {{ company.plan_type.toUpperCase() }}
                                    </el-tag>
                                </div>
                            </div>
                            <div class="company-info-header">
                                <h2 class="company-name">{{ company.company_name || 'N/A' }}</h2>
                                <h3 class="contact-person">{{ company.first_name }} {{ company.last_name }}</h3>
                                <div class="contact-details">
                                    <div class="contact-item">
                                        <el-icon><Message /></el-icon>
                                        <span>{{ company.user_email }}</span>
                                    </div>
                                    <div class="contact-item">
                                        <el-icon><Phone /></el-icon>
                                        <span>{{ company.phone }}</span>
                                    </div>
                                    <div v-if="company.website" class="contact-item">
                                        <el-icon><Link /></el-icon>
                                        <el-link :href="company.website" target="_blank" type="primary">{{ company.website }}</el-link>
                                    </div>
                                </div>
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
                                        <el-tag type="info">{{ company.username }}</el-tag>
                                    </el-descriptions-item>
                                    <el-descriptions-item label="User Type">
                                        <el-tag type="primary">{{ company.user_type || 'Company' }}</el-tag>
                                    </el-descriptions-item>
                                    <el-descriptions-item label="DOT/MC Number">
                                        <span>{{ company.dot_mc || 'N/A' }}</span>
                                    </el-descriptions-item>
                                    <el-descriptions-item label="Address">
                                        <div class="address-info">
                                            <div>{{ company.address || 'N/A' }}</div>
                                            <div>{{ company.city }}, {{ company.state }}</div>
                                        </div>
                                    </el-descriptions-item>
                                </el-descriptions>
                            </el-card>

                            <!-- Subscription Information -->
                            <el-card class="info-card" header="Subscription Details">
                                <el-descriptions :column="1" border>
                                    <el-descriptions-item label="Plan Type">
                                        <el-tag :type="company.plan_type === 'premium' ? 'warning' : 'info'" size="large">
                                            {{ company.plan_type || 'Basic' }}
                                        </el-tag>
                                    </el-descriptions-item>
                                    <el-descriptions-item label="Subscription Type">
                                        <span>{{ company.subscription_type || 'N/A' }}</span>
                                    </el-descriptions-item>
                                    <el-descriptions-item label="Status">
                                        <div class="subscription-status">
                                            <el-tag :type="!company.is_expired ? 'success' : 'danger'" size="large">
                                                {{ !company.is_expired ? 'Active' : 'Expired' }}
                                            </el-tag>
                                            <el-tag v-if="company.subscription_canceled" type="warning" size="small">
                                                Canceled
                                            </el-tag>
                                        </div>
                                    </el-descriptions-item>
                                    <el-descriptions-item label="Days Remaining">
                                        <el-tag :type="company.days_remaining > 30 ? 'success' : company.days_remaining > 7 ? 'warning' : 'danger'">
                                            {{ company.days_remaining || 0 }} days
                                        </el-tag>
                                    </el-descriptions-item>
                                    <el-descriptions-item label="Expires At">
                                        <span>{{ formatDate(company.expires_at) }}</span>
                                    </el-descriptions-item>
                                </el-descriptions>
                            </el-card>
                        </el-col>

                        <!-- Right Column -->
                        <el-col :lg="12" :md="24" :sm="24">
                            <!-- Payment Information -->
                            <el-card class="info-card" header="Last Payment Information">
                                <el-descriptions :column="1" border>
                                    <el-descriptions-item label="Subscription Fee">
                                        <div class="payment-status">
                                            <el-tag :type="company.paid_subscription_fee ? 'success' : 'danger'">
                                                {{ company.paid_subscription_fee ? 'Paid' : 'Unpaid' }}
                                            </el-tag>
                                            <span v-if="company.paid_subscription_fee_at" class="payment-date">
                                                {{ formatDate(company.paid_subscription_fee_at) }}
                                            </span>
                                        </div>
                                    </el-descriptions-item>
                                    <el-descriptions-item label="Customer ID">
                                        <el-tag type="info" size="small">{{ company.customer_id || 'N/A' }}</el-tag>
                                    </el-descriptions-item>
                                    <el-descriptions-item label="Transaction ID">
                                        <el-tag type="info" size="small">{{ company.transaction_id || 'N/A' }}</el-tag>
                                    </el-descriptions-item>
                                </el-descriptions>
                                <div class="payment-actions">
                                    <el-button type="primary" @click="editPaymentInfo(company.user_id)" class="edit-payment-btn">
                                        <el-icon><Edit /></el-icon>
                                        Edit Payment Information
                                    </el-button>
                                </div>
                            </el-card>

                            <!-- Profile Status -->
                            <el-card class="info-card" header="Profile Status">
                                <el-descriptions :column="1" border>
                                    <el-descriptions-item label="Profile Completed">
                                        <el-tag :type="company.profile_completed ? 'success' : 'warning'" size="large">
                                            {{ company.profile_completed ? 'Completed' : 'Incomplete' }}
                                        </el-tag>
                                    </el-descriptions-item>
                                    <el-descriptions-item label="Completed At">
                                        <span>{{ formatDate(company.profile_completed_at) }}</span>
                                    </el-descriptions-item>
                                    <el-descriptions-item label="Account Active">
                                        <el-tag :type="company.is_active ? 'success' : 'danger'" size="large">
                                            {{ company.is_active ? 'Active' : 'Inactive' }}
                                        </el-tag>
                                    </el-descriptions-item>
                                </el-descriptions>
                            </el-card>
                        </el-col>
                    </el-row>

                    <!-- Company Description -->
                    <el-card v-if="company.about" class="about-card" header="About Company">
                        <div class="about-content">
                            <p>{{ company.about }}</p>
                        </div>
                    </el-card>

                    <!-- Action Buttons -->
                    <el-card class="action-card">
                        <div class="action-btn-group">
                            <template v-if="company.profile_completed">
                                <div v-if="company.status == 'active'">
                                    <el-popconfirm 
                                        title="Are you sure you want to Revoke Access?"
                                        @confirm="PermitOrRevokeAccess(company.user_id, 'Revoke')"
                                        confirmButtonText="Yes" 
                                        cancelButtonText="No" 
                                        icon="el-icon-warning"
                                        iconColor="red">
                                        <template #reference>
                                            <el-button type="danger" size="large">
                                                <el-icon><Lock /></el-icon>
                                                Revoke Access
                                            </el-button>
                                        </template>
                                    </el-popconfirm>
                                </div>
                                <div v-else>
                                    <el-popconfirm 
                                        title="Are you sure you want to Permit Access?"
                                        @confirm="PermitOrRevokeAccess(company.user_id, 'Permit')"
                                        confirmButtonText="Yes" 
                                        cancelButtonText="No" 
                                        icon="el-icon-warning"
                                        iconColor="green">
                                        <template #reference>
                                            <el-button type="success" size="large">
                                                <el-icon><Unlock /></el-icon>
                                                Permit Access
                                            </el-button>
                                        </template>
                                    </el-popconfirm>
                                </div>
                            </template>
                            <el-button type="info" size="large" @click="goBack">
                                <el-icon><Back /></el-icon>
                                Back to List
                            </el-button>
                        </div>
                    </el-card>
                </div>
                
                <el-empty v-else description="No company data found." />
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
    Link, 
    Lock, 
    Unlock, 
    Back,
    Edit
} from '@element-plus/icons-vue';

export default {
    name: "CompanyDetails",
    components: {
        Header,
        ArrowLeft,
        Message,
        Phone,
        Link,
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
        const company = ref({});
        const app_vars = window.adjuster_forge_app_vars;
        const image_url = `${app_vars.image_url ? app_vars.image_url : ''}`;
        const home_url = `${app_vars.home_url ? app_vars.home_url : ''}`;

        const fetchCompanyData = async () => {
            startLoading();
            try {
                const response = await get(`company/${route.params.user_id}`);
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

                    company.value = data;
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
            router.push({ name: 'CompaniesList' });
        };

        // Permit or Revoke Access based on status
        const PermitOrRevokeAccess = async (id, action) => {
            try {
                const message = action === 'Permit' ? 'Permit Processing...' : 'Revoke Processing...';
                const url = action === 'Permit' ? 'permit-access' : 'revoke-access';
                startLoading(message);
                const response = await post(url, {
                    id: id
                });

                if (response.status === 'success') {
                    success(response.message);
                    fetchCompanyData(); // Fixed function name
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
            fetchCompanyData();
        });

        return {
            company,
            image_url,
            imageUrl,
            download_url,
            goBack,
            getStatusLabel,
            PermitOrRevokeAccess,
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

.company-title {
    font-size: 2.2rem;
    font-weight: 700;
    color: #1a1a1a;
    margin: 0;
}

.back-btn {
    border-radius: 8px;
    font-weight: 500;
}

.company-layout {
    display: flex;
    flex-direction: column;
    gap: 24px;
}

/* Profile Header Card */
.profile-header-card {
    border-radius: 16px;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
    border: 1px solid #e4e7ed;
}

.company-profile-header {
    display: flex;
    gap: 32px;
    align-items: flex-start;
}

.profile-image-section {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 16px;
}

.company-image {
    width: 120px;
    height: 120px;
    border-radius: 50%;
    object-fit: cover;
    border: 4px solid #fff;
    box-shadow: 0 4px 16px rgba(0, 0, 0, 0.1);
}

.profile-badges {
    display: flex;
    flex-direction: column;
    gap: 8px;
    align-items: center;
}

.company-info-header {
    flex: 1;
}

.company-name {
    font-size: 2rem;
    font-weight: 700;
    color: #2c3e50;
    margin: 0 0 8px 0;
}

.contact-person {
    font-size: 1.4rem;
    font-weight: 500;
    color: #34495e;
    margin: 0 0 16px 0;
}

.contact-details {
    display: flex;
    flex-direction: column;
    gap: 12px;
}

.contact-item {
    display: flex;
    align-items: center;
    gap: 12px;
    font-size: 14px;
    color: #5a6c7d;
}

.contact-item .el-icon {
    color: #409eff;
    font-size: 16px;
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

/* Address Info */
.address-info div {
    margin-bottom: 4px;
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

/* About Card */
.about-card {
    border-radius: 12px;
    box-shadow: 0 2px 12px rgba(0, 0, 0, 0.05);
    border: 1px solid #e4e7ed;
}

.about-card .el-card__header {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    font-weight: 600;
    font-size: 16px;
    border-radius: 12px 12px 0 0;
}

.about-content p {
    line-height: 1.6;
    color: #5a6c7d;
    margin: 0;
    text-align: justify;
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
}

/* Responsive Design */
@media (max-width: 1200px) {
    .dashboard-container {
        padding: 16px;
    }
    
    .company-title {
        font-size: 1.8rem;
    }
}

@media (max-width: 768px) {
    .header-section {
        flex-direction: column;
        align-items: flex-start;
        gap: 16px;
    }
    
    .company-profile-header {
        flex-direction: column;
        align-items: center;
        text-align: center;
        gap: 20px;
    }
    
    .contact-details {
        align-items: center;
    }
    
    .action-btn-group {
        flex-direction: column;
    }
    
    .company-title {
        font-size: 1.6rem;
    }
    
    .company-name {
        font-size: 1.6rem;
    }
    
    .contact-person {
        font-size: 1.2rem;
    }
}

@media (max-width: 480px) {
    .dashboard-container {
        padding: 12px;
    }
    
    .company-image {
        width: 100px;
        height: 100px;
    }
    
    .company-title {
        font-size: 1.4rem;
    }
    
    .company-name {
        font-size: 1.4rem;
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