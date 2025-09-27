<template>
  <div>
    <h3>Profile Picture</h3>
    <div v-if="existingImageUrl" class="profile-image-preview">
      <img :src="existingImageUrl" alt="Profile Image" style="max-width: 100%; max-height: 200px; border-radius: 8px; margin-bottom: 10px;" />
    </div>
    <el-upload
      class="upload-demo"
      drag
      :on-change="onFileChange"
      :file-list="fileList"
      list-type="picture-card"
      :show-file-list="false"
      :auto-upload="false">
      <div class="el-upload__text">
        Drag and drop file here, or <em>click to upload</em>
      </div>
    </el-upload>
  </div>
</template>

<script>
import { ref, computed } from 'vue';
import { useAppHelper } from '../../Composable/appHelper';
import AlertMessage from '../../Composable/AlertMessage';
import { loader } from '../../Composable/Loader';

export default {
  name: 'ProfilePicture',
  props: {
    existingImage: {
      type: String,
      default: ''
    },
    userType: {
      type: String,
      required: true
    }
  },
  emits: ['image-uploaded'],
  setup(props, { emit }) {
    const { post } = useAppHelper();
    const { error, success } = AlertMessage();
    const { startLoading, stopLoading } = loader();
    
    const app_vars = window.adjuster_forge_app_vars;
    const img_url = `${app_vars.image_url}/`;
    const fileList = ref([]);

    const existingImageUrl = computed(() => {
      if (props.existingImage) {
        return img_url + props.existingImage;
      }
      return '';
    });

    const onFileChange = (file, uploadedFiles) => {
      if (file.raw.type.startsWith('image/')) {
        uploadImage(file.raw);
        fileList.value = uploadedFiles.slice(-1);
      } else {
        fileList.value = [];
        error('Invalid file type. Please upload an image.');
        return false;
      }
    };

    const uploadImage = async (file) => {
      const formData = new FormData();
      formData.append('file', file);
      startLoading();
      try {
        const response = await post('profile-picture', formData, {
          headers: { 'Content-Type': 'multipart/form-data' },
        });
        if (response) {
          success(response);
          emit('image-uploaded', response);
        } else {
          throw new Error('Failed to upload image');
        }
      } catch (err) {
        error(err.message);
      } finally {
        stopLoading();
      }
    };

    return {
      fileList,
      existingImageUrl,
      onFileChange
    };
  },
};
</script>

<style scoped>
.profile-image-preview {
  text-align: center;
  margin-bottom: 10px;
}

.profile-image-preview img {
  box-shadow: 0 2px 8px rgba(0,0,0,0.08);
  border: 2px solid #e4e7ed;
}

.upload-demo {
  margin-top: 10px;
}

.el-upload__text {
  color: #909399;
  font-size: 15px;
}

h3 {
  font-weight: 600;
  color: #303133;
  margin-bottom: 18px;
}
</style>
