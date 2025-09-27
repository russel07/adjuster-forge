<template>
  <div>
    <component :is="componentToShow" />
  </div>
</template>

<script>
import { toRefs, computed } from 'vue';
import NotActive from './NotActive.vue'
import RejectedAccount from './RejectedAccount.vue'
import UnderReview from './UnderReview.vue'
import UpgradeToPro from './UpgradeToPro.vue'

export default {
  name: 'Message',
  props: {
    page: { type: String, default: '' },
    user_status: { type: String, required: true },
    user_type: { type: String, required: true },
    plan_type: { type: String, required: true }
  },
  components: {
    NotActive,
    RejectedAccount,
    UnderReview,
    UpgradeToPro
  },
  setup(props) {
    const { page, user_status, user_type, plan_type } = toRefs(props);

    const componentToShow = computed(() => {
      if( user_type.value === 'adjuster' ) {
        if (['inactive', 'pending', ''].includes(user_status.value)){
          return NotActive;
        } else if (user_status.value === 'rejected') {
          return RejectedAccount;
        } else if (user_status.value === 'under_review') {
          return UnderReview;
        }
      } else if (user_type.value === 'company') {
        if (['inactive', 'pending', ''].includes(user_status.value)){
          return NotActive;
        } else if (user_status.value === 'cancelled'){
          return UpgradeToPro;
        } else {
          if (page.value === 'inbox' && plan_type.value === 'free_trial') {
            return UpgradeToPro;
          } else if (page.value === 'create-job' && ( ['free_trial', 'standard'].includes(plan_type.value) ) ) {
            return UpgradeToPro;
          } else if (page.value === 'jobs' && ( ['free_trial', 'standard'].includes(plan_type.value) ) ) {
            return UpgradeToPro;
          }
        }
      }
      return NotActive;
    });

    return {
      componentToShow
    };
  }
}
</script>