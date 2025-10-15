<template>
  <div class="common-layout">
    <el-container class="full-height membership-container">
      <el-main class="main-center">
        <Menu />
        <div class="dashboard-container">
          <div class="payment-details modern-payment">
            <div class="payment-header">
              <svg width="48" height="48" viewBox="0 0 48 48" fill="none">
                <circle cx="24" cy="24" r="24" fill="#409eff"/>
                <path d="M16 24L22 30L32 18" stroke="white" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"/>
              </svg>
              <h3 class="payment-title">Verification Fee Payment</h3>
              <p class="payment-desc">
                Securely pay your verification fee to submit your account for review.
              </p>
            </div>
            <div class="membership-summary">
              <div class="summary-row">
                <span class="summary-label">Plan:</span>
                <span class="summary-value">{{ membershipForm.plan_name }}</span>
              </div>
              <div class="summary-row">
                <span class="summary-label">Amount:</span>
                <span class="summary-value">${{ membershipForm.amount }} {{ currency }}</span>
              </div>
            </div>
            <el-divider></el-divider>
            <div class="stripe-form">
              <div class="stripe-form-row-group">
                <div class="stripe-form-row">
                  <label for="card-number">Card Number</label>
                  <div id="card-number" class="stripe-element"></div>
                </div>
                <div class="stripe-form-row">
                  <label for="card-expiry">Expiration Date</label>
                  <div id="card-expiry" class="stripe-element"></div>
                </div>
                <div class="stripe-form-row">
                  <label for="card-cvc">CVC</label>
                  <div id="card-cvc" class="stripe-element"></div>
                </div>
              </div>
              <el-button
                type="primary"
                class="checkout-button modern-btn"
                @click="handlePaymentSubmission"
                :disabled="loading"
              >
                <template v-if="loading">
                  <i class="el-icon-loading"></i> Processing...
                </template>
                <template v-else>
                  <i class="el-icon-credit-card"></i> Make a Payment
                </template>
              </el-button>
            </div>
          </div>
        </div>
      </el-main>
    </el-container>
  </div>
</template>

<script>
import { onMounted, ref, computed, watch } from 'vue';
import { useRouter  } from 'vue-router';
import { useAppHelper } from '../../Composable/appHelper';
import AlertMessage from "../../Composable/AlertMessage";
import { loader } from '../../Composable/Loader';
import Menu from './Menu';
import { loadStripe } from '@stripe/stripe-js'

export default {
    name: 'VerificationFee',
    components: {
        Menu,
    },
    setup() {
        const router = useRouter();
        const loading = ref(false)
        const { get, post } = useAppHelper()
        const { error, success } = AlertMessage()
        const { startLoading, stopLoading } = loader();
        const app_vars = window.adjuster_forge_app_vars;
        const { profile_page: profilePageURL } = app_vars;
        const currency = app_vars.currency_Name || 'USD';
        const user_data = app_vars.user_data || {};
        const stripe_public_key = ref(app_vars.stripe_public_key)
        const membership_plans = ref([])
        const selectedPlan = ref({})
        const stripePromise = loadStripe(stripe_public_key.value)
        const stripe = ref(null)
        const cardNumber = ref(null)
        const cardExpiry = ref(null)
        const cardCvc = ref(null)
        const isCardValid = ref(false)
        const isExpiryValid = ref(false)
        const isCvcValid = ref(false)
        const membershipForm = ref({
            plan_name: 'Verification Fee',
            amount: 49.99,
            payment_gateway: 'Stripe',
        })
        const membershipFormRef = ref(null)

        // Initialize Stripe form
    const initStripeForm = async () => {
        stripe.value = await stripePromise
        const elements = stripe.value.elements()

        // Create separate elements for each field
        cardNumber.value = elements.create('cardNumber')
        cardExpiry.value = elements.create('cardExpiry')
        cardCvc.value = elements.create('cardCvc')

        // Mount elements
        cardNumber.value.mount('#card-number')
        cardExpiry.value.mount('#card-expiry')
        cardCvc.value.mount('#card-cvc')

        // Add listeners for error handling
        const fields = [
            { element: cardNumber.value, name: 'Card number' },
            { element: cardExpiry.value, name: 'Expiration date' },
            { element: cardCvc.value, name: 'CVC' },
        ]

        // Add listeners for validation
        cardNumber.value.on('change', (event) => {
            isCardValid.value = event.complete
            if (event.error) {
                error.value = `Card number: ${event.error.message}`
            } else {
                error.value = ''
            }
        })

        cardExpiry.value.on('change', (event) => {
            isExpiryValid.value = event.complete
            if (event.error) {
                error.value = `Expiration date: ${event.error.message}`
            } else if (!error.value.includes('Card number:')) {
                error.value = ''
            }
        })

        cardCvc.value.on('change', (event) => {
            isCvcValid.value = event.complete
            if (event.error) {
                error.value = `CVC: ${event.error.message}`
            } else if (
                !error.value.includes('Card number:') &&
                !error.value.includes('Expiration date:')
            ) {
                error.value = ''
            }
        })
        fields.forEach(({ element, name: fieldName }) => {
            element.addEventListener('change', (event) => {
                if (event.error) {
                    error.value = `${fieldName}: ${event.error.message}`
                } else {
                    error.value = ''
                }
            })
        })
    }

    // Handle Stripe payment submission
    const handlePaymentSubmission = async () => {
        const stripe = await stripePromise
        const formData = new FormData()
        formData.append('amount', parseInt(membershipForm.value.amount) * 100)
        formData.append('currency', currency)
        formData.append('name', user_data.user_name )
        formData.append('email', user_data.user_email )
        loading.value = true
        try {
            const response = await post('create-payment-intent', formData)

            if ( response.status !== 'success') {
                error(response.message || 'Something went wrong')
                return
            }

            const clientSecret = response.client_secret
            const customer_id = response.customer_id

            const result = await stripe.confirmCardPayment(clientSecret, {
                payment_method: {
                    card: cardNumber.value,
                },
            })

            if (result.error) {
                error(result.error.message)
            } else if (result.paymentIntent.status === 'succeeded') {
                const paymentIntent = result.paymentIntent
                let paymentData = {
                    plan_name: membershipForm.value.plan_name,
                    amount: membershipForm.value.amount,
                    currency: paymentIntent.currency,
                    payment_status: paymentIntent.status,
                    customer_id: customer_id,
                    transaction_id: result.paymentIntent.id,
                }

                const response = await post('verification-fee', paymentData)

                if (response.status === 'success') {
                    success(response.message)
                    setTimeout(() => {
                      window.location.href = profilePageURL;
                    }, 1200);
                } else {
                    error(response.message)
                }
            }
        }
        catch (err) {
            error(err.message)
        }
        finally {
            loading.value = false
        }
    }

        // Fetch data on mount
        onMounted(() => {
            initStripeForm()
        });

        return {
            loading,
            membershipForm,
            membershipFormRef,
            stripe,
            cardNumber,
            cardExpiry,
            cardCvc,
            isCardValid,
            isExpiryValid,
            isCvcValid,
            membership_plans,
            selectedPlan,
            initStripeForm,
            startLoading,
            stopLoading,
            error,
            success,
            get, 
            post,
            handlePaymentSubmission,
            currency
        };
    },
};
</script>

<style scoped>

.modern-payment {
  display: flex;
  flex-direction: column;
  align-items: center;
}

.payment-header {
  text-align: center;
  margin-bottom: 24px;
}

.payment-header svg {
  margin-bottom: 8px;
}

.payment-title {
  font-size: 24px;
  font-weight: 700;
  color: #409eff;
  margin-bottom: 4px;
}

.payment-desc {
  color: #666;
  font-size: 15px;
  margin-bottom: 0;
}

.membership-summary {
  width: 100%;
  margin-bottom: 16px;
  background: #f0f9ff;
  border-radius: 8px;
  padding: 12px 18px;
  box-sizing: border-box;
}

.summary-row {
  display: flex;
  justify-content: space-between;
  font-size: 16px;
  margin-bottom: 4px;
}

.summary-label {
  color: #888;
  font-weight: 500;
}

.summary-value {
  color: #333;
  font-weight: 600;
}

.stripe-form-row-group {
  display: flex;
  flex-direction: column;
  gap: 14px;
  margin-bottom: 18px;
}

.stripe-form-row label {
  font-size: 14px;
  color: #409eff;
  font-weight: 500;
  margin-bottom: 4px;
  display: block;
}

.stripe-element {
  border: 1px solid #dcdfe6;
  border-radius: 6px;
  padding: 12px;
  background: #f9f9f9;
  font-size: 16px;
  transition: border-color 0.3s;
}

.stripe-element:focus {
  border-color: #409eff;
  outline: none;
}

.modern-btn {
  width: 100%;
  font-size: 16px;
  padding: 12px 0;
  border-radius: 8px;
  font-weight: 600;
  margin-top: 10px;
  box-shadow: 0 2px 8px rgba(64, 158, 255, 0.08);
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 8px;
}

.el-icon-credit-card {
  font-size: 18px;
}

.el-icon-loading {
  font-size: 18px;
}

@media (max-width: 600px) {
  .main-center {
    padding: 10px 0;
  }
  .dashboard-container {
    padding: 16px 8px;
  }
  .membership-summary {
    padding: 8px 10px;
  }
  .stripe-form-row-group {
    flex-direction: column;
    gap: 10px;
  }
  .stripe-form-row {
    width: 100%;
    display: block;
    margin-bottom: 0;
  }
}
</style>