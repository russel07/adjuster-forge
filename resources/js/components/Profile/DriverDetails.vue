<template>
    <el-container class="full-height">
        <el-main class="main-center">
            <Menu />
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
                                {{ driver.status }}
                            </el-tag>
                        </div>
                    </div>
                    <el-divider />
                    <el-row :gutter="20">
                        <el-col :span="12">
                            <el-descriptions :column="1" border>
                                <el-descriptions-item label="Username">{{ driver.username }}</el-descriptions-item>
                                <el-descriptions-item label="Email">{{ driver.user_email }}</el-descriptions-item>
                                <el-descriptions-item label="Phone">{{ driver.phone || 'N/A' }}</el-descriptions-item>
                                <el-descriptions-item label="City">{{ driver.city }}</el-descriptions-item>
                                <el-descriptions-item label="State">{{ driver.state }}</el-descriptions-item>
                            </el-descriptions>
                        </el-col>
                        <el-col :span="12">
                            <el-descriptions :column="1" border>
                                <el-descriptions-item label="Resume">
                                    <template v-if="driver.resume">
                                        <el-link :href="download_url(driver.resume)" target="_blank" type="primary" download>
                                            Download
                                        </el-link>
                                    </template>
                                    <template v-else>
                                        <el-tag type="info" size="small">No Resume</el-tag>
                                    </template>
                                </el-descriptions-item>
                                <el-descriptions-item label="Medical Card">
                                    <template v-if="driver.medical_card">
                                        <el-link :href="download_url(driver.medical_card)" target="_blank" type="primary" download>
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
                                        <el-tag type="info" size="small">No MVR {{ driver.mvr }}</el-tag>
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
                    <!-- Action Buttons -->
                    <div class="action-btn-group" v-if="user_status === 'active' && plan_type !== 'free_trial'">
                        <el-button
                            type="primary"
                            size="large"
                            @click="openMessageDialog(driver)"
                        >
                            Send Message
                        </el-button>
                    </div>
                </el-card>
                <el-empty v-else description="No driver data found." />
            </div>
        </el-main>
    </el-container>

    <!-- Send Message Dialog -->
    <el-dialog
        v-model="showMessageDialog"
        title="Send Message to Driver"
        width="600px"
    >
        <div v-if="selectedDriver" class="dialog-driver-info">
            <h3>{{ selectedDriver.first_name }} {{ selectedDriver.last_name }}</h3>
            <p>{{ selectedDriver.user_email }}</p>
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
</template>

<script>
import { ref, onMounted } from "vue";
import { useRoute, useRouter } from 'vue-router';
import AlertMessage from "../../Composable/AlertMessage";
import { useAppHelper } from "../../Composable/appHelper";
import { loader } from '../../Composable/Loader';
import Menu from "./Menu.vue";

export default {
    name: "DriverDetails",
    components: {
        Menu,
    },
    setup() {
        const route = useRoute();
        const router = useRouter();
        const { get, post, imageUrl } = useAppHelper();
        const { error, success } = AlertMessage();
        const { startLoading, stopLoading } = loader();
        const driver = ref({});
        const showMessageDialog = ref(false);
        const selectedDriver = ref(null);
        const messageForm = ref({ message: '' });
        const messageFormRef = ref(null);
        const messageLoading = ref(false);
        const app_vars = window.driver_forge_app_vars;
        const home_url = `${app_vars.home_url ? app_vars.home_url : ''}`;
        const image_url = `${app_vars.image_url ? app_vars.image_url : ''}`;
        const user_status = `${app_vars.user_status ? app_vars.user_status : ''}`;
        const user_type = `${app_vars.user_data? app_vars.user_data.user_type : ''}`;
        const plan_type = `${app_vars.plan_type ? app_vars.plan_type : ''}`;

        const messageRules = {
            message: [
                { required: true, message: 'Message is required', trigger: 'blur' },
                { min: 10, message: 'Message should be at least 10 characters', trigger: 'blur' }
            ]
        };

        const fetchDriverData = async () => {
            startLoading();
            try {
                const response = await get(`driver/${route.params.user_id}`);
                if (response.status === 'success') {
                    driver.value = response.data;
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
            router.back();
        };

        const openMessageDialog = (driver) => {
            selectedDriver.value = driver;
            messageForm.value.message = ''; // Reset message form
            showMessageDialog.value = true;
        };

        const submitMessage = async () => {
            if (!messageFormRef.value) return;
            messageFormRef.value.validate(async (valid) => {
                if (!valid) return;
                messageLoading.value = true;
                try {
                    const response = await post('send-message', {
                        recipient_id: selectedDriver.value.user_id,
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

        const download_url = (file_name) => {
            return home_url + '/download/' + file_name;
        };

        onMounted(() => {
            fetchDriverData();
        });

        return {
            driver,
            goBack,
            openMessageDialog,
            showMessageDialog,
            selectedDriver,
            messageForm,
            messageFormRef,
            messageLoading,
            messageRules,
            submitMessage,
            user_status,
            user_type,
            plan_type,
            download_url,
            image_url,
            imageUrl
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
.dialog-driver-info {
    margin-bottom: 16px;
    text-align: center;
}
.dialog-driver-info h3 {
    margin: 0;
    font-size: 1.5em;
    font-weight: 500;
}
.dialog-driver-info p {
    margin: 4px 0 0 0;
    color: #666;
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
</style>