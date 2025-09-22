<template>
  <el-container class="full-height">
    <el-main class="main-center">
      <Menu />
      <div class="dashboard-container">
        <h1>Payment History</h1>
        <el-card>
          <div class="table-responsive">
            <el-table
              :data="history"
              v-loading="loading"
              style="width: 100%"
              empty-text="No payment history found."
              class="subscription-table"
            >
              <el-table-column prop="plan_name" label="Plan" min-width="120" />
              <el-table-column prop="amount" label="Amount" min-width="80">
                <template #default="scope">
                  {{ scope.row.amount }} {{ scope.row.currency }}
                </template>
              </el-table-column>
              <el-table-column prop="payment_status" label="Status" min-width="100">
                <template #default="scope">
                  <el-tag :type="statusType(scope.row.payment_status)">
                    {{ getStatusLabel(scope.row.payment_status) }}
                  </el-tag>
                </template>
              </el-table-column>
              <el-table-column prop="created_at" label="Date" min-width="140">
                <template #default="scope">
                  {{ formatDate(scope.row.created_at) }}
                </template>
              </el-table-column>
              <el-table-column prop="transaction_id" label="Transaction ID" min-width="160" />
              <el-table-column align="right" width="200px">
                <template #header>
                    <el-input v-model="search" style="max-width: 200px" placeholder="Type to Search"
                        class="input-with-select">
                    </el-input>
                </template>
            </el-table-column>
            </el-table>
          </div>
          <el-pagination @current-change="handlePageChange" @size-change="handleSizeChange"
            :current-page="currentPage" :page-size="pageSize" :background="true" :total="totalItems"
            layout="total, sizes, prev, pager, next" />
        </el-card>
      </div>
    </el-main>
  </el-container>
</template>

<script>
import { onMounted, ref, watch } from 'vue';
import Menu from './Menu.vue';
import { useAppHelper } from '../../Composable/appHelper';
import AlertMessage from '../../Composable/AlertMessage';
import { loader } from '../../Composable/Loader';

export default {
  name: 'PaymentHistory',
  components: { Menu },
  setup() {
    const { get, getStatusLabel } = useAppHelper();
    const { error } = AlertMessage();
    const { startLoading, stopLoading } = loader();
    const history = ref([]);
    const loading = ref(false);
    const currentPage = ref(1);
    const pageSize = ref(10);
    const totalItems = ref(10);
    const search = ref('');

    const handlePageChange = (newPage) => {
        currentPage.value = newPage;
        fetchHistory();
    };

    const handleSizeChange = (newPageSize) => {
        pageSize.value = newPageSize;
        if (pageSize.value > totalItems.value) {
            currentPage.value = 1;
        }
        fetchHistory();
    };

    const fetchHistory = async () => {
      startLoading();
      loading.value = true;
      try {
        const response = await get(`payment-history?page=${currentPage.value}&per_page=${pageSize.value}&search=${search.value}`);
        if (response && response.status === 'success') {
          const data = response.data || [];
          currentPage.value = data.current_page;
          pageSize.value = data.per_page;
          totalItems.value = data.total_items;
          history.value = data.history;
        } else {
          history.value = [];
        }
      } catch (err) {
        history.value = [];
        error(err.message);
      } finally {
        loading.value = false;
        stopLoading();
      }
    };

    const formatDate = (dateStr) => {
      if (!dateStr) return '';
      const date = new Date(dateStr.replace(' ', 'T'));
      return `${date.getDate()}, ${date.toLocaleString('en-US', { month: 'long' })} ${date.getFullYear()}`;
    };

    const statusType = (status) => {
      switch (status) {
        case 'success':
        case 'active':
        case 'Approved':
          return 'success';
        case 'pending':
        case 'Pending':
          return 'warning';
        case 'Refunded':
        case 'refunded':
        case 'Rejected':
          return 'danger';
        default:
          return 'info';
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
      fetchHistory();
    }, 1000));

    onMounted(() => {
      fetchHistory();
    });

    return {
      history,
      loading,
      search,
      currentPage,
      pageSize,
      totalItems,
      formatDate,
      statusType,
      getStatusLabel,
      handlePageChange,
      handleSizeChange,
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
.el-pagination {
  margin-top: 15px;
}
.table-responsive {
  width: 100%;
  overflow-x: auto;
}
.el-table {
  min-width: 900px;
}
.subscription-table .el-tag {
  border-radius: 6px;
  font-weight: 500;
  text-transform: capitalize;
}
@media (max-width: 600px) {
  .el-table {
    min-width: 600px;
    font-size: 13px;
  }
  .el-table th, .el-table td {
    padding: 6px 4px;
    white-space: normal;
  }
}
</style>