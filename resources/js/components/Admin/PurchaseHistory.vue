<template>
    <el-container class="full-height">
        <el-main class="main-center">
            <Header />
            <div class="dashboard-container">
                <h1>All Purchased History</h1>

                <el-card>
                    <el-table :data="purchasedData" style="width: 100%">
                        <el-table-column prop="user_email" label="User Email" width="250px" align="center" />
                        <el-table-column prop="payment_id" label="Payment ID" width="160px" align="center" />
                        <el-table-column prop="payment_gateway" label="Payment Gateway" width="160px" align="center" />
                        <el-table-column prop="amount" label="Amount" width="150px" align="center">
                            <template #default="scope">
                                <span>{{ getAmount(scope.row.amount) }}</span>
                            </template>
                        </el-table-column>
                        <el-table-column prop="membership_plan" label="Card Type" width="150px" align="center" />
                        <el-table-column prop="duration" label="Duration" width="200px" align="center" />
                        <el-table-column prop="created_at" label="Purchase Date" width="200px">
                            <template #default="scope">
                                <span>{{ formatDate(scope.row.created_at) }}</span>
                            </template>
                        </el-table-column>

                        <el-table-column prop="payment_status" label="Status" width="200px">
                            <template #default="scope">
                                <span v-if="scope.row.refund_status">Refunded - {{ formatDate(scope.row.refund_date)
                                    }}</span>
                                <span v-else>Active</span>
                            </template>
                        </el-table-column>

                        <el-table-column align="right" width="150px">
                            <template #header>
                                <el-input v-model="search" size="small" placeholder="Type to search" />
                            </template>
                            <template #default="scope">
                                <el-popconfirm title="Are you sure you want to refund this subscription?"
                                    @confirm="refundSubscription(scope.row.id)" confirmButtonText="Yes"
                                    cancelButtonText="No" icon="el-icon-warning" iconColor="red">
                                    <template #reference>
                                        <el-button v-if="!scope.row.refund_status" size="small"
                                            type="danger">Refund</el-button>
                                        <span v-else>&nbsp;</span>
                                    </template>
                                </el-popconfirm>
                            </template>
                        </el-table-column>
                    </el-table>

                    <el-pagination @current-change="handlePageChange" @size-change="handleSizeChange"
                        :current-page="currentPage" :page-size="pageSize" :background="true" :total="totalItems"
                        layout="total, sizes, prev, pager, next" />
                </el-card>
            </div>
        </el-main>
    </el-container>
</template>

<script>
import { onMounted, ref, watch } from "vue";
import AlertMessage from "../../Composable/AlertMessage";
import { useAppHelper } from "../../Composable/appHelper";
import { loader } from '../../Composable/Loader';
import Header from "../Header";

export default {
    name: "PurchaseHistory",
    components: {
        Header,
    },
    setup() {
        const { get, post } = useAppHelper();
        const { error, success } = AlertMessage();
        const { startLoading, stopLoading } = loader();
        const app_vars = window.driver_forge_app_vars;
        const currency = `${app_vars.currency}`;
        const currentPage = ref(1);
        const pageSize = ref(10);
        const totalItems = ref(10);
        const purchasedData = ref([]);
        const search = ref('');

        // Fetch data from API based on search query
        const fetchDataQueryData = async (query) => {
            try {
                const response = await get(`search-purchased?page=${currentPage.value}&per_page=${pageSize.value}&search=${query}`);
                if (response.status === 'success') {
                    const data          = response.data;
                    currentPage.value   = data.current_page;
                    pageSize.value      = data.per_page;
                    totalItems.value    = data.total_items;
                    purchasedData.value = data.purchased_list;
                } 
            } catch (err) {
                //error('Failed to fetch data');
            }
        };

        const fetchPurchasedData = async () => {
            startLoading();
            try {
                const response = await get(`purchased-list?page=${currentPage.value}&per_page=${pageSize.value}`);
                if (response.status === 'success') {
                    const data = response.data;
                    currentPage.value = data.current_page;
                    pageSize.value = data.per_page;
                    totalItems.value = data.total_items;
                    purchasedData.value = data.purchased_list;
                } else {
                    error(response.message)
                }
            } catch (err) {
                error(err.message);
            } finally {
                stopLoading();
            }
        };

        const handlePageChange = (newPage) => {
            currentPage.value = newPage;
            fetchPurchasedData();
        };

        const handleSizeChange = (newPageSize) => {
            pageSize.value = newPageSize;
            if (pageSize.value > totalItems.value) {
                currentPage.value = 1;
            }
            fetchPurchasedData();
        };

        const getAmount = (value) => {
            return currency + ' ' + value;
        }

        // Function to format date
        const formatDate = (dateString) => {
            const options = { year: 'numeric', month: 'long', day: 'numeric' };
            return new Date(dateString).toLocaleDateString(undefined, options);
        };


        // refund subscription
        const refundSubscription = async (id) => {
            try {
                startLoading('Refund Processing...');
                const response = await post('create-payment-refund', {
                    id: id
                });

                if (response.status === 'success') {
                    success('Refund successfully');
                    fetchPurchasedData();
                } else {
                    error(response.message);
                }
            } catch (err) {
                error(err.message);
            } finally {
                stopLoading();
            }
        };

        // Debounce function
        const debounce = (func, delay) => {
            let timeout;
            return (...args) => {
                clearTimeout(timeout);
                timeout = setTimeout(() => {
                    func(...args);
                }, delay);
            };
        };

        watch(search, debounce((newQuery) => {
            fetchDataQueryData(newQuery);
        }, 1000));

        onMounted(() => {
            fetchPurchasedData();
        });

        return {
            search,
            getAmount,
            purchasedData,
            currentPage,
            pageSize,
            totalItems,
            handlePageChange,
            handleSizeChange,
            formatDate,
            refundSubscription,
        };
    },
};
</script>
<style scoped>
.dashboard-container {
    padding: 20px;
    max-width: 100%;
}

.full-height {
    min-height: 100vh;
}

.el-col {
    padding: 10px;
}

.el-pagination {
    margin-top: 15px;
}
</style>