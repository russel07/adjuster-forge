<template>
  <div>
    <!-- Subscription status alerts -->
    <template v-if="statusValue != 'active' && statusValue != 'under_review'">
      <el-alert
        type="warning"
        show-icon
        style="margin-top: 20px;"
      >
        <template #title>
          Your subscription is due. Please renew to continue using the platform.
          <a @click.prevent="$router.push('/subscription')"
            href="#"
            style="margin-left: 8px; color: #409EFF; text-decoration: underline; font-weight: 500;"
          >Go to Subscription Page
          </a>
        </template>
      </el-alert>
    </template>
    <template v-else-if="statusValue === 'expire'">
      <el-alert
        type="error"
        show-icon
        style="margin-top: 20px;"
      >
      <template #title>
          Your subscription has expired. Please renew to continue using the platform.
          <a @click.prevent="$router.push('/subscription')"
            href="#"
            style="margin-left: 8px; color: #409EFF; text-decoration: underline; font-weight: 500;"
          >Go to Subscription Page
          </a>
        </template>
      </el-alert>
    </template>
    <template v-else-if="statusValue === 'cancelled'">
      <el-alert
        type="error"
        show-icon
        style="margin-top: 20px;"
      >
      <template #title>
          Your have cancelled your subscription. Your access will be removed after {{ formattedEndDate }}. You can renew it anytime.
          <a @click.prevent="$router.push('/subscription')"
            href="#"
            style="margin-left: 8px; color: #409EFF; text-decoration: underline; font-weight: 500;"
          >Go to Subscription Page
          </a>
        </template>
      </el-alert>
    </template>
  </div>
</template>

<script>
import { computed } from 'vue';

export default {
  name: 'SubscriptionStatus',
  props: {
    statusValue: {
      type: String,
      required: true
    },
    subscriptionEndDate: {
      type: String,
      default: ''
    }
  },
  setup(props) {
    const formattedEndDate = computed(() => {
      if (!props.subscriptionEndDate) return '';
      const date = new Date(props.subscriptionEndDate);
      return date.toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'long',
        day: '2-digit'
      });
    });

    return {
      formattedEndDate
    };
  },
};
</script>
