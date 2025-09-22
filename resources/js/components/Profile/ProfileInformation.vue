<template>
  <div>
    <h3>Profile Information</h3>
    <el-descriptions border :column="2">
      <el-descriptions-item label="First Name">{{ profileData.first_name }}</el-descriptions-item>
      <el-descriptions-item label="Last Name">{{ profileData.last_name }}</el-descriptions-item>
      <el-descriptions-item label="Email">{{ profileData.email }}</el-descriptions-item>
      <el-descriptions-item label="City">{{ profileData.city }}</el-descriptions-item>
      <el-descriptions-item label="State">{{ profileData.state }}</el-descriptions-item>
      <el-descriptions-item label="Status">
        <span :class="['status-label', statusInfo.value]">
          <component
            :is="statusInfo.icon"
            style="vertical-align: middle; margin-right: 6px; font-size: 16px; height: 20px; width: 20px;"
          />
          {{ statusInfo.label }}
        </span>
      </el-descriptions-item>
      <el-descriptions-item label="Subscription Type">
        <span>{{ planTypeLabel }}</span>
        <template v-if="[ 'no_plan', 'free_trial' ].includes(planType)">
          <a @click.prevent="$router.push('/subscription')"
            href="#"
            style="margin-left: 8px; color: #409EFF; text-decoration: underline; font-weight: 500;"
          >Update Plan
          </a>
        </template>
      </el-descriptions-item>
      <el-descriptions-item v-if="showCancelSubscription" label="Subscription">
        <el-popconfirm title="Are you sure you want to cancel this subscription?"
          @confirm="$emit('cancel-subscription')" confirmButtonText="Yes"
          cancelButtonText="No" icon="el-icon-warning" iconColor="red">
          <template #reference>
              <el-button size="small" type="danger" :loading="cancelLoading">Cancel Subscription</el-button>
          </template>
        </el-popconfirm>
      </el-descriptions-item>
    </el-descriptions>
  </div>
</template>

<script>
import { computed } from 'vue';
import { CircleCloseFilled, CircleCheckFilled, Clock, WarningFilled, InfoFilled, RemoveFilled } from '@element-plus/icons-vue';

export default {
  name: 'ProfileInformation',
  components: { CircleCloseFilled, CircleCheckFilled, Clock, WarningFilled, InfoFilled, RemoveFilled },
  props: {
    profileData: {
      type: Object,
      required: true
    },
    statusInfo: {
      type: Object,
      required: true
    },
    planType: {
      type: String,
      default: ''
    },
    showCancelSubscription: {
      type: Boolean,
      default: false
    },
    cancelLoading: {
      type: Boolean,
      default: false
    }
  },
  emits: ['cancel-subscription'],
  setup(props) {
    const planTypeLabel = computed(() => {
      if (props.planType === 'no_plan') {
        return 'No Plan';
      } else if (props.planType === 'free_trial') {
        return 'Free Trial';
      } else if (props.planType === 'standard') {
        return 'Standard Plan';
      } else if (props.planType === 'premium') {
        return 'Premium Plan';
      }
      return 'Driver Plan';
    });

    return {
      planTypeLabel
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

.status-label {
  font-weight: 500;
  font-size: 15px;
  vertical-align: middle;
  display: inline-flex;
  align-items: center;
  color: #606266;
}

.status-label.rejected {
  color: #f56c6c;
}
.status-label.active {
  color: #67c23a;
}
.status-label.completed {
  color: #409EFF;
}
.status-label.pending {
  color: #e6a23c;
}
.status-label.submitted {
  color: #909399;
}
.status-label.expire {
  color: #909399;
}
</style>
