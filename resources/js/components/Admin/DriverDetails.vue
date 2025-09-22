<template>
    <el-container class="full-height">
        <el-main class="main-center">
            <Header />
            <div class="dashboard-container">
                <h1 class="driver-title">Driver Details</h1>
                <el-button type="info" @click="goBack" class="back-btn">
                    ← Go Back to Driver List
                </el-button>
                <el-card v-if="driver" class="driver-details-card">
                    <div class="driver-profile">
                        <el-image
                            :src="imageUrl( image_url, driver.profile_picture ) || 'https://ui-avatars.com/api/?name=' + driver.first_name + '+' + driver.last_name"
                            fit="cover"
                            class="driver-image"
                            :alt="driver.first_name + ' ' + driver.last_name"
                        />
                        <div class="driver-name-status">
                            <h2>{{ driver.first_name }} {{ driver.last_name }}</h2>
                            <el-tag :type="driver.status === 'active' ? 'success' : driver.status === 'pending' ? 'warning' : 'info'">
                                {{ getStatusLabel(driver.status) }}
                            </el-tag>
                        </div>
                    </div>
                    <el-divider />
                    <el-row :gutter="20">
                        <el-col :span="12">
                            <el-descriptions :column="1" border>
                                <el-descriptions-item label="Username">{{ driver.username }}</el-descriptions-item>
                                <el-descriptions-item label="Email">{{ driver.user_email }}</el-descriptions-item>
                                <el-descriptions-item label="Phone">{{ driver.phone }}</el-descriptions-item>
                                <el-descriptions-item label="City">{{ driver.city }}</el-descriptions-item>
                                <el-descriptions-item label="State">{{ driver.state }}</el-descriptions-item>
                            </el-descriptions>
                        </el-col>
                        <el-col :span="12">
                            <el-descriptions :column="1" border>
                                <el-descriptions-item label="Resume">
                                    <template v-if="driver.resume">
                                        <el-link :href="download_url( driver.resume )" target="_blank" type="primary" download>
                                            Download
                                        </el-link>
                                    </template>
                                    <template v-else>
                                        <el-tag type="info" size="small">No Resume</el-tag>
                                    </template>
                                </el-descriptions-item>
                                <el-descriptions-item label="Medical Card">
                                    <template v-if="driver.medical_card">
                                        <el-link :href="download_url( driver.medical_card )" target="_blank" type="primary" download>
                                            Download
                                        </el-link>
                                    </template>
                                    <template v-else>
                                        <el-tag type="info" size="small">No Medical Card</el-tag>
                                    </template>
                                </el-descriptions-item>
                                <el-descriptions-item label="MVR">
                                    <template v-if="driver.mvr">
                                        <el-link :href="download_url( driver.mvr )" target="_blank" type="primary" download>
                                            Download
                                        </el-link>
                                    </template>
                                    <template v-else>
                                        <el-tag type="info" size="small">No MVR</el-tag>
                                    </template>
                                </el-descriptions-item>
                                <el-descriptions-item label="Profile Completed">
                                    <el-tag :type="driver.profile_completed ? 'success' : 'info'">
                                        {{ driver.profile_completed ? 'Yes' : 'No' }}
                                    </el-tag>
                                </el-descriptions-item>
                            </el-descriptions>
                        </el-col>
                    </el-row>
                    <el-divider />
                    <div v-if="driver.references && driver.references.length" class="driver-references">
                        <h3>References</h3>
                        <el-table :data="driver.references" style="width:100%;margin-bottom:16px;">
                            <el-table-column prop="name" label="Name" />
                            <el-table-column prop="phone" label="Phone" />
                            <el-table-column prop="years_known" label="Years Known" />
                        </el-table>
                    </div>
                    <!-- Action Buttons -->
                    <div class="action-btn-group">
                        <template v-if="driver.profile_completed">
                            <template v-if="driver.status != 'approved' && driver.status != 'rejected' && driver.status != 'active'">
                                <el-button type="primary" size="large" @click="toggleDriver(driver.user_id, 'approved')">
                                    Approve
                                </el-button>
                                <el-button type="danger" size="large" @click="toggleDriver(driver.user_id, 'rejected')">
                                    Reject
                                </el-button>
                            </template>
                            <template v-else>
                                <el-button
                                    :type="driver.status === 'active' ? 'danger' : 'success'"
                                    size="large"
                                    @click="toggleAccess(driver.user_id, driver.status)"
                                >
                                    {{ driver.status === 'active' ? 'Revoke Access' : 'Permit Access' }}
                                </el-button>
                            </template>
                        </template>
                    </div>
                </el-card>
                <el-empty v-else description="No driver data found." />
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

export default {
    name: "DriverDetails",
    components: {
        Header,
    },
    setup() {
        const route = useRoute();
        const router = useRouter();
        const { get, post, imageUrl, getStatusLabel } = useAppHelper();
        const { error, success } = AlertMessage();
        const { startLoading, stopLoading } = loader();
        const driver = ref({});
        const app_vars = window.driver_forge_app_vars;
        const image_url = `${app_vars.image_url ? app_vars.image_url : ''}`;
        const home_url = `${app_vars.home_url ? app_vars.home_url : ''}`;

        const fetchDriverData = async () => {
            startLoading();
            try {
                const response = await get(`driver/${route.params.user_id}`);
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

                    driver.value = data;
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
            router.push({ name: 'DriversList' });
        };

        // Approve or Reject Driver
        const toggleDriver = async (user_id, action = 'approved') => {
            let message = action === 'approved' ? 'Approving...' : 'Rejecting...';
            startLoading(message);
            try {
                const response = await post('toggle-driver', { user_id:user_id, action: action });
                if (response.status === 'success') {
                    success(response.message);
                    fetchDriverData();
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
            startLoading(status === 'active' ? 'Revoking...' : 'Permitting...');
            const url = status === 'active' ? 'revoke-access' : 'permit-access';
            try {
                const response = await post(url, { id: user_id });
                if (response.status === 'success') {
                    success(response.message);
                    // Update status locally for instant UI feedback
                    driver.value.status = status === 'active' ? 'revoked' : 'active';
                    // Optionally, sync with backend
                    fetchDriverData();
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

        onMounted(() => {
            console.log('Mounted');
            fetchDriverData();
        });

        return {
            driver,
            image_url,
            imageUrl,
            download_url,
            goBack,
            toggleDriver,
            toggleAccess,
            getStatusLabel
        };
    },
};
</script>

<style scoped>
.driver-title {
    font-size: 2rem;
    font-weight: 700;
    color: #1a1a1a;
    margin-bottom: 12px;
}
.back-btn {
    margin-bottom: 18px;
    border-radius: 8px;
}
.driver-details-card {
    width: 90%;
    margin: 0 auto;
    padding: 32px 24px;
    border-radius: 18px;
    box-shadow: 0 4px 24px rgba(102,126,234,0.08);
    background: #fff;
}
.driver-profile {
    display: flex;
    align-items: center;
    gap: 28px;
    margin-bottom: 16px;
}
.driver-image {
    width: 100px;
    height: 100px;
    border-radius: 50%;
    object-fit: cover;
    border: 2px solid #e4e7ed;
    box-shadow: 0 2px 8px rgba(102,126,234,0.12);
}
.driver-name-status h2 {
    margin: 0 0 8px 0;
    font-size: 1.6em;
    font-weight: 600;
}
.driver-name-status .el-tag {
    margin-top: 4px;
}
.action-btn-group {
    display: flex;
    gap: 16px;
    justify-content: flex-end;
    margin-top: 32px;
}
.el-divider {
    margin: 24px 0;
}
@media (max-width: 700px) {
    .driver-details-card {
        padding: 16px 8px;
    }
    .driver-profile {
        flex-direction: column;
        align-items: flex-start;
        gap: 12px;
    }
    .action-btn-group {
        flex-direction: column;
        gap: 12px;
        align-items: stretch;
    }
}
.driver-references {
  margin-bottom: 24px;
}
.driver-references h3 {
  font-size: 1.2em;
  font-weight: 600;
  margin-bottom: 10px;
}
</style>