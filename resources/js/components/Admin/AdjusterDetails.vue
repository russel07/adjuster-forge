<template>
    <el-container class="full-height">
        <el-main class="main-center">
            <Header />
            <div class="dashboard-container">
                <h1 class="adjuster-title">Adjuster Details</h1>
                <el-button type="info" @click="goBack" class="back-btn">
                    ← Go Back to Adjuster List
                </el-button>
                <el-card v-if="adjuster" class="adjuster-details-card">
                    <div class="adjuster-profile">
                        <el-image
                            :src="imageUrl( image_url, adjuster.profile_picture ) || 'https://ui-avatars.com/api/?name=' + adjuster.first_name + '+' + adjuster.last_name"
                            fit="cover"
                            class="adjuster-image"
                            :alt="adjuster.first_name + ' ' + adjuster.last_name"
                        />
                        <div class="adjuster-name-status">
                            <h2>{{ adjuster.first_name }} {{ adjuster.last_name }}</h2>
                            <el-tag :type="adjuster.status === 'active' ? 'success' : adjuster.status === 'pending' ? 'warning' : 'info'">
                                {{ getStatusLabel(adjuster.status) }}
                            </el-tag>
                        </div>
                    </div>
                    <el-divider />
                    <el-row :gutter="20">
                        <el-col :span="12">
                            <el-descriptions :column="1" border>
                                <el-descriptions-item label="Username">{{ adjuster.username }}</el-descriptions-item>
                                <el-descriptions-item label="Email">{{ adjuster.user_email }}</el-descriptions-item>
                                <el-descriptions-item label="Phone">{{ adjuster.phone }}</el-descriptions-item>
                                <el-descriptions-item label="City">{{ adjuster.city }}</el-descriptions-item>
                                <el-descriptions-item label="State">{{ adjuster.state }}</el-descriptions-item>
                            </el-descriptions>
                        </el-col>
                        <el-col :span="12">
                            <el-descriptions :column="1" border>
                                <el-descriptions-item label="Resume">
                                    <template v-if="adjuster.resume">
                                        <el-link :href="download_url( adjuster.resume )" target="_blank" type="primary" download>
                                            Download
                                        </el-link>
                                    </template>
                                    <template v-else>
                                        <el-tag type="info" size="small">No Resume</el-tag>
                                    </template>
                                </el-descriptions-item>
                                <el-descriptions-item label="Medical Card">
                                    <template v-if="adjuster.medical_card">
                                        <el-link :href="download_url( adjuster.medical_card )" target="_blank" type="primary" download>
                                            Download
                                        </el-link>
                                    </template>
                                    <template v-else>
                                        <el-tag type="info" size="small">No Medical Card</el-tag>
                                    </template>
                                </el-descriptions-item>
                                <el-descriptions-item label="MVR">
                                    <template v-if="adjuster.mvr">
                                        <el-link :href="download_url( adjuster.mvr )" target="_blank" type="primary" download>
                                            Download
                                        </el-link>
                                    </template>
                                    <template v-else>
                                        <el-tag type="info" size="small">No MVR</el-tag>
                                    </template>
                                </el-descriptions-item>
                                <el-descriptions-item label="Profile Completed">
                                    <el-tag :type="adjuster.profile_completed ? 'success' : 'info'">
                                        {{ adjuster.profile_completed ? 'Yes' : 'No' }}
                                    </el-tag>
                                </el-descriptions-item>
                            </el-descriptions>
                        </el-col>
                    </el-row>
                    <el-divider />
                    <div v-if="adjuster.references && adjuster.references.length" class="adjuster-references">
                        <h3>References</h3>
                        <el-table :data="adjuster.references" style="width:100%;margin-bottom:16px;">
                            <el-table-column prop="name" label="Name" />
                            <el-table-column prop="phone" label="Phone" />
                            <el-table-column prop="years_known" label="Years Known" />
                        </el-table>
                    </div>
                    <!-- Action Buttons -->
                    <div class="action-btn-group">
                        <template v-if="adjuster.profile_completed">
                            <template v-if="adjuster.status != 'approved' && adjuster.status != 'rejected' && adjuster.status != 'active'">
                                <el-button type="primary" size="large" @click="toggleAdjuster(adjuster.user_id, 'approved')">
                                    Approve
                                </el-button>
                                <el-button type="danger" size="large" @click="toggleAdjuster(adjuster.user_id, 'rejected')">
                                    Reject
                                </el-button>
                            </template>
                            <template v-else>
                                <el-button
                                    :type="adjuster.status === 'active' ? 'danger' : 'success'"
                                    size="large"
                                    @click="toggleAccess(adjuster.user_id, adjuster.status)"
                                >
                                    {{ adjuster.status === 'active' ? 'Revoke Access' : 'Permit Access' }}
                                </el-button>
                            </template>
                        </template>
                    </div>
                </el-card>
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

export default {
    name: "AdjusterDetails",
    components: {
        Header,
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
            startLoading(status === 'active' ? 'Revoking...' : 'Permitting...');
            const url = status === 'active' ? 'revoke-access' : 'permit-access';
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

        onMounted(() => {
            console.log('Mounted');
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
            getStatusLabel
        };
    },
};
</script>

<style scoped>
.adjuster-title {
    font-size: 2rem;
    font-weight: 700;
    color: #1a1a1a;
    margin-bottom: 12px;
}
.back-btn {
    margin-bottom: 18px;
    border-radius: 8px;
}
.adjuster-details-card {
    width: 90%;
    margin: 0 auto;
    padding: 32px 24px;
    border-radius: 18px;
    box-shadow: 0 4px 24px rgba(102,126,234,0.08);
    background: #fff;
}
.adjuster-profile {
    display: flex;
    align-items: center;
    gap: 28px;
    margin-bottom: 16px;
}
.adjuster-image {
    width: 100px;
    height: 100px;
    border-radius: 50%;
    object-fit: cover;
    border: 2px solid #e4e7ed;
    box-shadow: 0 2px 8px rgba(102,126,234,0.12);
}
.adjuster-name-status h2 {
    margin: 0 0 8px 0;
    font-size: 1.6em;
    font-weight: 600;
}
.adjuster-name-status .el-tag {
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
    .adjuster-details-card {
        padding: 16px 8px;
    }
    .adjuster-profile {
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
.adjuster-references {
  margin-bottom: 24px;
}
.adjuster-references h3 {
  font-size: 1.2em;
  font-weight: 600;
  margin-bottom: 10px;
}
</style>