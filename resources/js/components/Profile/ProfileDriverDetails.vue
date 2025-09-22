<template>
  <div>
    <h3 style="margin-top:30px;">Driver Details</h3>
    <el-descriptions border :column="2">
      <el-descriptions-item label="Phone">{{ driverData.phone }}</el-descriptions-item>
      <el-descriptions-item label="Resume">
        <template v-if="driverData.resume">
          <div class="document-item">
            <el-button type="primary" size="small" @click="downloadFile(driverData.resume)">
              📄 Download
            </el-button>
          </div>
        </template>
        <template v-else>
          <span class="no-data">Not uploaded</span>
        </template>
      </el-descriptions-item>
      <el-descriptions-item label="Medical Card">
        <template v-if="driverData.medical_card">
          <div class="document-item">
            <el-button type="success" size="small" @click="downloadFile(driverData.medical_card)">
              🏥 Download
            </el-button>
          </div>
        </template>
        <template v-else>
          <span class="no-data">Not uploaded</span>
        </template>
      </el-descriptions-item>
      <el-descriptions-item label="Motor Vehicle Record (MVR)">
        <template v-if="driverData.mvr">
          <div class="document-item">
            <el-button type="warning" size="small" @click="downloadFile(driverData.mvr)">
              🚗 Download
            </el-button>
          </div>
        </template>
        <template v-else>
          <span class="no-data">Not uploaded</span>
        </template>
      </el-descriptions-item>
      <el-descriptions-item label="Professional References">
        <template v-if="driverData.references && driverData.references.length">
          <ul>
            <li v-for="(ref, idx) in driverData.references" :key="idx">
              <strong>{{ ref.name }}</strong> - {{ ref.phone }} ({{ ref.years_known }} years)
            </li>
          </ul>
        </template>
        <template v-else>Not provided</template>
      </el-descriptions-item>
      <el-descriptions-item label="License Classes">
        <div class="tag-container">
          <el-tag v-for="(license, index) in driverData.licenses" :key="index" type="primary" class="detail-tag">
            {{ license }}
          </el-tag>
          <span v-if="!driverData.licenses?.length" class="no-data">Not specified</span>
        </div>
      </el-descriptions-item>
      <el-descriptions-item label="Endorsements">
        <div class="tag-container">
          <el-tag v-for="(endorsement, index) in driverData.endorsements" :key="index" type="warning" class="detail-tag">
            {{ endorsement }}
          </el-tag>
          <span v-if="!driverData.endorsements?.length" class="no-data">None</span>
        </div>
      </el-descriptions-item>
      <el-descriptions-item label="Experience">
        <div class="tag-container">
          <el-tag v-for="(experience, index) in driverData.experience" :key="index" type="info" class="detail-tag">
            {{ experience.type }} ({{ experience.years }} years)
          </el-tag>
          <span v-if="!driverData.experience?.length" class="no-data">No experience listed</span>
        </div>
      </el-descriptions-item>
      <el-descriptions-item label="Availability">
        <div class="tag-container">
          <el-tag v-for="(slot, index) in driverData.availability" :key="index" type="success" class="detail-tag">
            {{ slot }}
          </el-tag>
          <span v-if="!driverData.availability?.length" class="no-data">Not specified</span>
        </div>
      </el-descriptions-item>
    </el-descriptions>
  </div>
</template>

<script>
export default {
  name: 'ProfileDriverDetails',
  props: {
    driverData: {
      type: Object,
      required: true
    }
  },
  setup() {
    const downloadFile = (filename) => {
      if (filename) {
        const downloadUrl = `${window.location.origin}/download/${filename}`;
        window.open(downloadUrl, '_blank');
      }
    };

    return {
      downloadFile
    };
  },
};
</script>

<style scoped>
h3 {
  font-weight: 600;
  color: #303133;
  margin-bottom: 18px;
}

.el-descriptions {
  background: #fff;
  border-radius: 8px;
  box-shadow: 0 2px 8px rgba(0,0,0,0.04);
  margin-bottom: 24px;
}

.el-descriptions__label {
  font-weight: 500;
  color: #606266;
}

.el-descriptions__content {
  color: #303133;
}

ul {
  padding-left: 18px;
  margin: 0;
}

li {
  margin-bottom: 6px;
  color: #606266;
}

.tag-container {
  display: flex;
  flex-wrap: wrap;
  gap: 8px;
  align-items: center;
}

.detail-tag {
  margin: 2px 4px 2px 0;
  font-size: 13px;
  padding: 4px 8px;
  border-radius: 6px;
}

.no-data {
  color: #909399;
  font-style: italic;
  font-size: 14px;
}

.document-item {
  display: flex;
  align-items: center;
  gap: 8px;
}

.document-item .el-button {
  border-radius: 6px;
  font-weight: 500;
}

@media (max-width: 768px) {
  .tag-container {
    flex-direction: column;
    align-items: flex-start;
  }
  
  .detail-tag {
    margin: 2px 0;
  }
  
  .document-item {
    flex-direction: column;
    align-items: flex-start;
  }
}
</style>
