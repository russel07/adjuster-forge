<template>
  <div class="common-layout">
    <el-container class="full-height membership-container">
      <el-main class="main-center">
        <Menu />
        <div class="dashboard-container">
          
          <!-- Loading State -->
          <div v-if="loading" class="loading-container">
            <el-skeleton :rows="4" animated />
          </div>

          <!-- Subscription Plans -->
          <div class="subscription-plans" v-if="!showPaymentForm && !loading">
            <template v-if="user_data.user_type === 'driver'">
              <h3 class="section-title">Activate Your Driver profile</h3>
              <div class="plan-switch-row">
                <span class="switch-label">Billing:</span>
                <el-switch
                  v-model="isYearly"
                  active-text="Yearly"
                  inactive-text="Monthly"
                  active-color="#409eff"
                  inactive-color="#e4e7ed"
                  @change="handleDriverIntervalChange"
                />
              </div>
              <div class="plans-grid">
                <div class="plan-card selected">
                  <div class="plan-header">
                    <h4>{{ selectedPlan.name }}</h4>
                    <div class="plan-price">
                      <span class="currency">$</span>
                      <span class="amount">{{ selectedPlan.price }}</span>
                      <span class="period">/{{ selectedPlan.interval }}</span>
                    </div>
                  </div>
                  <div class="plan-description">
                    <p>{{ selectedPlan.description }}</p>
                  </div>
                  <div class="plan-features">
                    <div v-for="feature in selectedPlan.features" :key="feature.title" class="plan-feature-row">
                      <component :is="getFeatureIcon(feature.icon)" class="feature-icon" />
                      <span>{{ feature.title }}</span>
                    </div>
                  </div>
                </div>
              </div>
            </template>
            <template v-else>
              <h3 class="section-title">Choose Your Subscription Plan</h3>
              <div class="plan-switch-row">
                <span class="switch-label">Billing:</span>
                <el-switch
                  v-model="isYearly"
                  active-text="Yearly"
                  inactive-text="Monthly"
                  active-color="#409eff"
                  inactive-color="#e4e7ed"
                />
              </div>
              <div class="plans-grid">
                <div 
                  v-for="plan in filteredPlans" 
                  :key="plan.id"
                  class="plan-card"
                  :class="{ 'selected': selectedPlan.id === plan.id }"
                  @click="selectPlan(plan)"
                >
                  <div class="plan-header">
                    <h4>{{ plan.name }}</h4>
                    <div class="plan-price">
                      <span class="currency">$</span>
                      <span class="amount">{{ plan.price }}</span>
                      <span class="period">/{{ plan.interval }}</span>
                    </div>
                  </div>
                  <div class="plan-description">
                    <p>{{ plan.description }}</p>
                  </div>
                  <div class="plan-features">
                    <div v-for="feature in plan.features" :key="feature.title" class="plan-feature-row">
                      <component :is="getFeatureIcon(feature.icon)" class="feature-icon" />
                      <span>{{ feature.title }}</span>
                    </div>
                  </div>
                  <el-button 
                    type="primary" 
                    class="select-plan-btn"
                    :class="{ 'selected': selectedPlan.id === plan.id }"
                  >
                    {{ selectedPlan.id === plan.id ? 'Selected' : 'Select Plan' }}
                  </el-button>
                </div>
              </div>
            </template>
            <div class="plan-actions" v-if="selectedPlan.id">
              <el-button 
                v-if="selectedPlan.name === 'Free Trial'"
                type="success"
                class="proceed-btn"
                @click="activateFreeTrial"
                :loading="processing"
              >
                Activate Free Trial
              </el-button>
              <el-button 
                v-else
                type="primary" 
                class="proceed-btn"
                @click="proceedToPayment"
                :loading="processing"
              >
                Proceed to Payment - ${{ selectedPlan.price }}/{{ selectedPlan.interval }}
              </el-button>
            </div>
          </div>

          <!-- Payment Form -->
          <div class="payment-form" v-if="showPaymentForm && !loading">
            <h3 class="section-title">Payment Details</h3>
            <div class="selected-plan-summary">
              <h4>{{ selectedPlan.name }}</h4>
              <p>${{ selectedPlan.price }}/{{ selectedPlan.interval }}</p>
            </div>
            
            <el-form label-position="top">
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
                </div>
            </el-form>
            
            <div class="payment-actions">
              <el-button @click="goBackToPlans">Back to Plans</el-button>
              <el-button 
                type="primary" 
                @click="handleSubscription"
                :loading="processing"
                :disabled="!isFormValid"
              >
                Subscribe Now - ${{ selectedPlan.price }}/{{ selectedPlan.interval }}
              </el-button>
            </div>
          </div>

        </div>
      </el-main>
    </el-container>
  </div>
</template>

<script>
import { ref, reactive, onMounted, computed } from 'vue'
import { useRouter } from 'vue-router';
import { loadStripe } from '@stripe/stripe-js'
import { useAppHelper } from '../../Composable/appHelper';
import AlertMessage from '../../Composable/AlertMessage';
import { loader } from '../../Composable/Loader';
import Menu from './Menu.vue';

import IconCheckmark from './IconCheckmark.vue';
import CrossIcon from './CrossIcon.vue';
export default {
    name: 'Subscription',
    components: {
        Menu,
        IconCheckmark,
        CrossIcon
    },
    setup() {
      const router = useRouter();
      const { get, post } = useAppHelper();
      const { error, success } = AlertMessage();
      const { startLoading, stopLoading } = loader();
      const app_vars = window.driver_forge_app_vars;
      const { profile_page: profilePageURL } = app_vars;
      const stripe_public_key = ref(app_vars.stripe_public_key)
      const stripePromise = loadStripe(stripe_public_key.value)
      const loading = ref(false)
      const processing = ref(false)
      const showPaymentForm = ref(false)
      const stripe = ref(null)
      const cardNumber = ref(null)
      const cardExpiry = ref(null)
      const cardCvc = ref(null)
      const isCardValid = ref(false)
      const isExpiryValid = ref(false)
      const isCvcValid = ref(false)
      const isYearly = ref(true);
      const user_data = reactive({
          user_id: '',
          user_type: '',
          user_email: '',
          customer_id: '',
          user_name: ''
      });

      const getFeatureIcon = (icon) => {
        switch (icon) {
          case 'check':
            return 'IconCheckmark';
          case 'cross':
            return 'CrossIcon';
          default:
            return 'IconCheckmark';
        }
      };

      const filteredPlans = computed(() => {
        if (user_data.user_type !== 'company') {
          return subscription_plans.value;
        }
        // For company, map each plan to the correct interval data
        return subscription_plans.value.map(plan => {
          if (plan.monthly_id && plan.yearly_id) {
            // Standard or Premium plan with both intervals
            if (isYearly.value) {
              return {
                id: plan.yearly_id,
                name: plan.yearly_name,
                price: plan.yearly_price,
                interval: plan.yearly_interval,
                description: plan.description,
                features: plan.features || [],
              };
            } else {
              return {
                id: plan.monthly_id,
                name: plan.monthly_name,
                price: plan.monthly_price,
                interval: plan.monthly_interval,
                description: plan.description,
                features: plan.features || [],
              };
            }
          }
          // Free Trial or other plans
          return {
            id: plan.id,
            name: plan.name,
            price: plan.price,
            interval: plan.interval,
            description: plan.description,
            features: plan.features || [],
          };
        }).filter(plan => {
          // Only show Free Trial, Standard, Premium
          return (
            plan.name === 'Free Trial' ||
            plan.name.includes('Standard') ||
            plan.name.includes('Premium')
          );
        });
      });
      
      const subscription_plans = ref([])
      
      const selectedPlan = reactive({
          id: '',
          name: '',
          price: 0,
          interval: 'month',
          description: '',
          features: []
      })
      
      // Computed properties
      const isFormValid = computed(() => {
          return isCardValid.value && 
                  isExpiryValid.value && 
                  isCvcValid.value
      })
      // Methods
      const loadSubscriptionPlans = async () => {
          loading.value = true
          try {
              const response = await get('subscription-plans')
              if (response.status === 'success') {
                  subscription_plans.value = response.data.plans || [];
                  user_data.user_id = response.data.user_data.user_id || '';
                  user_data.user_type = response.data.user_data.user_type || '';
                  user_data.user_email = response.data.user_data.user_email || '';
                  user_data.user_name = response.data.user_data.user_name || '';
                  user_data.customer_id = response.data.user_data.customer_id || '';

                  if (user_data.user_type === 'driver' && subscription_plans.value.length > 0) {
                    // Automatically select the first plan for drivers
                    selectDriverPlan();
                  }
              } else {
                  error(response.message || 'Failed to load subscription plans')
              }
          } catch (err) {
              error('Error loading subscription plans')
          } finally {
              loading.value = false
          }
      }

        // Initialize Stripe form
      const initStripeForm = async () => {
          stripe.value = await stripePromise
          const elements = stripe.value.elements()

          const style = {
                  base: {
                      fontSize: '16px',
                      color: '#424770',
                      '::placeholder': {
                          color: '#aab7c4',
                      },
                  },
                  invalid: {
                      color: '#9e2146',
                  },
              }
              
          // Create card elements
          cardNumber.value = elements.create('cardNumber', { style })
          cardExpiry.value = elements.create('cardExpiry', { style })
          cardCvc.value = elements.create('cardCvc', { style })

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
      
      const selectPlan = (plan) => {
          selectedPlan.id = plan.id
          selectedPlan.name = plan.name
          selectedPlan.price = plan.price
          selectedPlan.interval = plan.interval
          selectedPlan.description = plan.description
          selectedPlan.features = plan.features
      }

      const handleDriverIntervalChange = () => {
        if (subscription_plans.value.length > 0) {
          selectDriverPlan();
        }
      };

      const selectDriverPlan = () => {
        if (subscription_plans.value.length === 0) return;
        
        const plan = subscription_plans.value[0]; // First plan for drivers
        
        // Check if plan has both monthly and yearly options
        if (plan.monthly_id && plan.yearly_id) {
          if (isYearly.value) {
            selectPlan({
              id: plan.yearly_id,
              name: plan.yearly_name,
              price: plan.yearly_price,
              interval: plan.yearly_interval,
              description: plan.description,
              features: plan.features || [],
            });
          } else {
            selectPlan({
              id: plan.monthly_id,
              name: plan.monthly_name,
              price: plan.monthly_price,
              interval: plan.monthly_interval,
              description: plan.description,
              features: plan.features || [],
            });
          }
        } else {
          // Single interval plan
          selectPlan({
            id: plan.id,
            name: plan.name,
            price: plan.price,
            interval: plan.interval,
            description: plan.description,
            features: plan.features || [],
          });
        }
      };
      
      const proceedToPayment = async () => {
          if (!selectedPlan.id) {
              error('Please select a plan first')
              return
          }
          
          showPaymentForm.value = true
          
          // Initialize Stripe form after DOM updates
          setTimeout(() => {
              initStripeForm()
          }, 100)
      }
      
      const goBackToPlans = () => {
          showPaymentForm.value = false
          // Clear any existing Stripe elements
          if (cardNumber.value) {
              cardNumber.value.unmount()
              cardNumber.value = null
          }
          if (cardExpiry.value) {
              cardExpiry.value.unmount()
              cardExpiry.value = null
          }
          if (cardCvc.value) {
              cardCvc.value.unmount()
              cardCvc.value = null
          }
      }
      
      const handleSubscription = async () => {
          if (!isFormValid.value) {
              error('Please fill in all required fields')
              return
          }
          
          processing.value = true
          
          try {
              // Create payment method
              const { paymentMethod, error: stripeError } = await stripe.value.createPaymentMethod({
                  type: 'card',
                  card: cardNumber.value,
                  billing_details: {
                      name: user_data.user_name,
                      email: user_data.user_email,
                  },
              })
              
              if (stripeError) {
                  error(stripeError.message)
                  return
              }
              
              // Send to backend
              const response = await post('create-subscription', {
                  plan_id: selectedPlan.id,
                  payment_method_id: paymentMethod.id,
                  user_id: user_data.user_id,
                  email: user_data.user_email,
                  name: user_data.user_name,
                  customer_id: user_data.customer_id,
                  interval: isYearly.value ? 'year' : 'month'
              })
              
              if (response.status === 'success') {
                  success('Subscription created successfully!')
                  setTimeout(() => {
                      window.location.href = profilePageURL;
                    }, 1200);
              } else {
                  error(response.message || 'Failed to create subscription')
              }
              
          } catch (err) {
              error('Error processing payment')
              console.error('Subscription error:', err)
          } finally {
              processing.value = false
          }
      }

      const activateFreeTrial = async () => {
          try {
            startLoading();
              const response = await post('activate-free-trial', {
                  user_id: user_data.user_id,
                  email: user_data.user_email,
                  name: user_data.user_name,
                  customer_id: user_data.customer_id
              })
              
              if (response.status === 'success') {
                success('Free trial activated successfully!')
                router.push('/my-profile');
              } else {
                  error(response.message || 'Failed to activate free trial')
              }
          } catch (err) {
            error(err.message);
          } finally {
            stopLoading();
          }
      }
        
      // Initialize component
      onMounted(() => {
          loadSubscriptionPlans()
      })
      
      return {
          // Reactive data
          loading,
          processing,
          showPaymentForm,
          user_data,
          subscription_plans,
          selectedPlan, // Only this one
          isFormValid,
          isYearly,
          filteredPlans,
          // Methods
          selectPlan,
          proceedToPayment,
          goBackToPlans,
          handleSubscription,
          loadSubscriptionPlans,
          initStripeForm,
          getFeatureIcon,
          activateFreeTrial,
          handleDriverIntervalChange // Add this
      }
    }
}
</script>

<style scoped>
.membership-container {
  min-height: 100vh;
  background-color: #f5f7fa;
}

.main-center {
  max-width: 1200px;
  margin: 0 auto;
  padding: 20px;
}

.dashboard-container {
  background: white;
  border-radius: 8px;
  box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
  padding: 30px;
  margin-bottom: 20px;
}

.loading-container {
  padding: 40px;
}

.plan-switch-row {
  display: flex;
  align-items: center;
  gap: 12px;
  margin-bottom: 18px;
  justify-content: flex-end;
}
.switch-label {
  font-weight: 500;
  color: #333;
}

.plan-feature-row {
  display: flex;
  align-items: center;
  gap: 8px;
  margin-bottom: 8px;
}
.feature-icon {
  font-size: 18px;
  color: #67c23a;
}

.section-title {
  font-size: 24px;
  font-weight: 600;
  color: #333;
  margin-bottom: 20px;
  text-align: center;
}

.plans-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
  gap: 20px;
  margin-bottom: 30px;
}

.plan-card {
  border: 2px solid #e4e7ed;
  border-radius: 8px;
  padding: 20px;
  cursor: pointer;
  transition: all 0.3s ease;
  background: white;
}

.plan-card:hover {
  border-color: #409eff;
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
}

.plan-card.selected {
  border-color: #409eff;
  background: #f0f9ff;
}

.plan-header {
  text-align: center;
  margin-bottom: 15px;
}

.plan-header h4 {
  margin: 0 0 10px 0;
  font-size: 20px;
  color: #333;
}

.plan-price {
  display: flex;
  align-items: baseline;
  justify-content: center;
  margin-bottom: 10px;
}

.plan-price .currency {
  font-size: 18px;
  color: #666;
}

.plan-price .amount {
  font-size: 32px;
  font-weight: bold;
  color: #409eff;
}

.plan-price .period {
  font-size: 14px;
  color: #666;
  margin-left: 4px;
}

.plan-description {
  margin-bottom: 15px;
  text-align: center;
}

.plan-description p {
  color: #666;
  margin: 0;
}

.plan-features {
  margin-bottom: 20px;
}

.plan-features ul {
  list-style: none;
  padding: 0;
  margin: 0;
}

.plan-features li {
  padding: 8px 0;
  display: flex;
  align-items: center;
  color: #333;
}

.plan-features li i {
  color: #67c23a;
  margin-right: 10px;
}

.select-plan-btn {
  width: 100%;
  margin-top: 10px;
}

.select-plan-btn.selected {
  background: #67c23a;
  border-color: #67c23a;
}

.plan-actions {
  text-align: center;
  margin-top: 30px;
}

.proceed-btn {
  font-size: 16px;
  padding: 12px 30px;
  min-width: 200px;
}

.payment-form {
  max-width: 600px;
  margin: 0 auto;
}

.selected-plan-summary {
  background: #f0f9ff;
  border: 1px solid #409eff;
  border-radius: 6px;
  padding: 15px;
  margin-bottom: 20px;
  text-align: center;
}

.selected-plan-summary h4 {
  margin: 0 0 5px 0;
  color: #333;
}

.selected-plan-summary p {
  margin: 0;
  font-size: 18px;
  font-weight: 600;
  color: #409eff;
}

.stripe-element {
  border: 1px solid #dcdfe6;
  border-radius: 4px;
  padding: 12px;
  background: white;
  font-size: 16px;
  transition: border-color 0.3s;
}

.stripe-element:focus {
  border-color: #409eff;
  outline: none;
}

.payment-actions {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-top: 30px;
  gap: 15px;
}

.payment-actions .el-button {
  flex: 1;
  max-width: 200px;
}

@media (max-width: 768px) {
  .main-center {
    padding: 10px;
  }
  
  .dashboard-container {
    padding: 20px;
  }
  
  .plans-grid {
    grid-template-columns: 1fr;
  }
  
  .payment-actions {
    flex-direction: column;
  }
  
  .payment-actions .el-button {
    max-width: 100%;
  }
}
</style>