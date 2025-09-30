<template>
  <div>
    <h3 style="margin-top:30px;">Adjuster Details</h3>
    <el-descriptions border :column="1">
      <el-descriptions-item label="Bio / Summary">
        <div style="max-width: 400px; word-wrap: break-word;">
          {{ adjusterData.bio || 'Not provided' }}
        </div>
      </el-descriptions-item>
    </el-descriptions>
    <el-descriptions border :column="2">
      <el-descriptions-item label="Phone">{{ adjusterData.phone }}</el-descriptions-item>
      <el-descriptions-item label="Years of Experience">{{ adjusterData.years_experience || 0 }}</el-descriptions-item>
      <el-descriptions-item label="CAT Deployments">{{ adjusterData.cat_deployments || 0 }}</el-descriptions-item>
      <el-descriptions-item label="Resume">
        <template v-if="adjusterData.resume">
          <div class="document-item">
            <el-button type="primary" size="small" @click="downloadFile(adjusterData.resume)">
              📄 Download
            </el-button>
          </div>
        </template>
        <template v-else>
          <span class="no-data">Not uploaded</span>
        </template>
      </el-descriptions-item>
      
      <el-descriptions-item label="W-9">
        <template v-if="adjusterData.w9">
          <div class="document-item">
            <el-button type="success" size="small" @click="downloadFile(adjusterData.w9)">
              📄 Download
            </el-button>
          </div>
        </template>
        <template v-else>
          <span class="no-data">Not uploaded</span>
        </template>
      </el-descriptions-item>
      
      <el-descriptions-item label="Insurance Proof">
        <template v-if="adjusterData.insurance_proof">
          <div class="document-item">
            <el-button type="warning" size="small" @click="downloadFile(adjusterData.insurance_proof)">
              🏥 Download
            </el-button>
          </div>
        </template>
        <template v-else>
          <span class="no-data">Not uploaded</span>
        </template>
      </el-descriptions-item>
      
      <el-descriptions-item label="Availability">
        <div class="tag-container">
          <el-tag v-for="(slot, index) in adjusterData.availability" :key="index" type="success" class="detail-tag">
            {{ slot }}
          </el-tag>
          <span v-if="!adjusterData.availability?.length" class="no-data">Not specified</span>
        </div>
      </el-descriptions-item>
      
      <el-descriptions-item label="Licenses">
        <div class="license-container">
          <div v-for="(license, index) in adjusterData.licenses" :key="index" class="license-item">
            <el-tag type="primary" class="detail-tag">
              {{ license.state }} - {{ license.number }}
            </el-tag>
            <span class="license-expiry">(Exp: {{ formatDate(license.expiration) }})</span>
            <el-button v-if="license.file_url" type="text" size="small" @click="downloadFile(license.file_url)">
              📄 View
            </el-button>
          </div>
          <span v-if="!adjusterData.licenses?.length" class="no-data">Not specified</span>
        </div>
      </el-descriptions-item>
      
      <el-descriptions-item label="Badges & Certifications">
        <div class="badge-container">
          <div v-for="(badge, index) in adjusterData.badges" :key="index" class="badge-item">
            <el-tag type="warning" class="detail-tag">
              {{ badge.badge }}
            </el-tag>
            <el-button v-if="badge.proof_file_url" type="text" size="small" @click="downloadFile(badge.proof_file_url)">
              📄 Proof
            </el-button>
          </div>
          <span v-if="!adjusterData.badges?.length" class="no-data">None</span>
        </div>
      </el-descriptions-item>
      
      <el-descriptions-item label="Carrier Experience">
        <div class="tag-container">
          <el-tag v-for="(carrier, index) in adjusterData.carrier_experience" :key="index" type="info" class="detail-tag">
            {{ carrier }}
          </el-tag>
          <span v-if="!adjusterData.carrier_experience?.length" class="no-data">Not specified</span>
        </div>
      </el-descriptions-item>
      
      <el-descriptions-item label="Employers / IA Firms">
        <div style="max-width: 400px; word-wrap: break-word;">
          {{ adjusterData.employers_ia_firms || 'Not provided' }}
        </div>
      </el-descriptions-item>
      
      <el-descriptions-item label="Professional References">
        <template v-if="adjusterData.references && adjusterData.references.length">
          <ul>
            <li v-for="(ref, idx) in adjusterData.references" :key="idx">
              <strong>{{ ref.name }}</strong> - {{ ref.phone }} 
              <span v-if="ref.relationship">({{ ref.relationship }})</span>
            </li>
          </ul>
        </template>
        <template v-else>Not provided</template>
      </el-descriptions-item>
    </el-descriptions>
  </div>
</template>

<script>
export default {
  name: 'ProfileAdjusterDetails',
  props: {
    adjusterData: {
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

    const formatDate = (dateString) => {
      if (!dateString) return 'N/A';
      const date = new Date(dateString);
      return date.toLocaleDateString();
    };

    return {
      downloadFile,
      formatDate
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

.license-container, .badge-container {
  display: flex;
  flex-direction: column;
  gap: 8px;
}

.license-item, .badge-item {
  display: flex;
  align-items: center;
  gap: 8px;
  flex-wrap: wrap;
}

.license-expiry {
  font-size: 12px;
  color: #909399;
  margin-left: 4px;
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
  
  .license-item, .badge-item {
    flex-direction: column;
    align-items: flex-start;
  }
}
</style>
