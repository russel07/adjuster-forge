<template>
    <div class="common-layout">
        <el-container class="full-height">
            <el-main class="main-center">
                <div class="dashboard-container">
                    <Menu />
                    <template v-if="user_status !== 'active' || ['free_trial', 'standard'].includes(plan_type)">
                        <Message :page="'create-job'" :user_status="user_status" :user_type="user_type" :plan_type="plan_type" />
                    </template>
                    <template v-else>
                        <template v-if="job_this_month >= 1">
                            <el-alert
                                :title="`You have reached the maximum limit of job postings for this month. Please try again after ${expire_at}`"
                                type="warning"
                                show-icon
                                style="margin-top: 20px;"
                            />
                         </template>
                        <template v-else>
                            <el-row :gutter="30">
                                <el-col :span="12">
                                    <h2>Post a Job</h2>
                                    <el-form
                                        ref="jobForm" 
                                        :model="form"
                                        :rules="rules"
                                        label-position="top"
                                        validate-on-blur
                                        validate-on-change
                                        label-width="120px">
                                        <el-form-item label="Title" prop="title" :rules="[{ required: true, message: 'Title is required', trigger: 'blur' }]">
                                            <el-input v-model="form.title" placeholder="Job Title" />
                                        </el-form-item>
                                        <el-form-item label="Description" prop="content" :rules="[{ required: true, message: 'Description is required', trigger: 'blur' }]">
                                            <el-input
                                                type="textarea"
                                                v-model="form.content"
                                                :rows="5"
                                                placeholder="Job Description"
                                            />
                                        </el-form-item>
                                        <el-form-item label="Add Attachment" prop="job_attachment">
                                            <el-upload
                                                class="upload-box"
                                                :on-change="handleFileChange"
                                                :file-list="fileList"
                                                :auto-upload="false"
                                        accept=".png,.jpg,.jpeg"
                                        :limit="1"
                                        drag
                                        >
                                        <i class="el-icon-upload"></i>
                                        <div>Click or drag to upload</div>
                                        </el-upload>
                                    </el-form-item>
                                        <el-form-item>
                                            <el-button type="primary" @click="submitJob" :loading="loading">Submit</el-button>
                                        </el-form-item>
                                    </el-form>
                                </el-col>
                            </el-row>
                        </template>
                    </template>
                </div>
            </el-main>
        </el-container>
    </div>
</template>

<script>
import { ref } from 'vue';
import { useRouter } from 'vue-router'
import { useAppHelper } from '../../Composable/appHelper';
import AlertMessage from "../../Composable/AlertMessage";
import { loader } from '../../Composable/Loader';
import Menu from '../Profile/Menu';
import Message from '../Messages/Message.vue';

export default {
    name: 'CreateJob',
    components: {
        Menu,
        Message
    },
    setup() {
        const router = useRouter();
        const { post } = useAppHelper();
        const { error, success } = AlertMessage();
        const { startLoading, stopLoading } = loader();
        const app_vars          = window.adjuster_forge_app_vars;
        const user_status       = `${app_vars.user_status ? app_vars.user_status : ''}`;
        const user_type         = `${app_vars.user_data? app_vars.user_data.user_type : ''}`;
        const plan_type         = `${app_vars.plan_type ? app_vars.plan_type : ''}`;
        const job_this_month    = `${app_vars.job_this_month ? app_vars.job_this_month : '0'}`;
        const expire_at         = `${app_vars.expire_date ? app_vars.expire_date : ''}`;
        const jobForm = ref(null);
        const form = ref({
            title: '',
            content: '',
            job_attachment: '',
        });

        const rules = {
            title: [
                { required: true, message: 'Title is required', trigger: 'blur' },
            ],
            content: [
                { required: true, message: 'Description is required', trigger: 'blur' },
            ],
        };
        const fileList = ref([]);
        const loading = ref(false);

        const handleFileChange = (file, uploadedFiles) => {
            const allowedTypes = ['application/pdf', 'image/png', 'image/jpg', 'image/jpeg'];
            const allowedExtensions = ['.pdf','.png', '.jpg', '.jpeg'];
            if (!allowedTypes.includes(file.raw.type) && !allowedExtensions.some(ext => file.raw.name.toLowerCase().endsWith(ext))) {
                fileList.value = [];
                error('Invalid file type. Please upload a PDF or image file (PNG, JPG, JPEG).');
                return false;
            }
            const maxSize = 5 * 1024 * 1024;
            if (file.raw.size > maxSize) {
                fileList.value = [];
                error('File size must be less than 5MB');
                return false;
            }
            fileList.value = uploadedFiles.slice(-1);
            form.value.job_attachment = file.raw;
        };

        // Submit job post
        const submitJob = async () => {
            jobForm.value.validate(async (valid) => {
                if (valid) {
                    startLoading();
                    try {
                        const formData = new FormData();
                        formData.append('title', form.value.title);
                        formData.append('content', form.value.content);
                        formData.append('job_attachment', form.value.job_attachment);
                        const response = await post('create-job', formData, {
                            headers: { 'Content-Type': 'multipart/form-data' },
                        });
                        if (response && response.status === 'success') {
                            success('Job created successfully');
                            setTimeout(() => {
                                router.push({ name: 'jobs' });
                                //window.location.reload();
                            }, 2000);
                        } else {
                            error(response.message || 'Something went wrong');
                        }
                    } catch (err) {
                        error(err.message);
                    } finally {
                        stopLoading();
                    }
                } else {
                    error('Please correct the errors in the form');
                }
            });
        };

        return {
            jobForm,
            rules,
            form,
            fileList,
            loading,
            submitJob,
            handleFileChange,
            user_status,
            user_type,
            plan_type,
            job_this_month,
            expire_at
        };
    },
};
</script>

<style scoped>
.dashboard-container {
    padding: 30px 0;
}
</style>
