<template>
    <el-container class="full-height">
        <el-main class="main-center">
            <Header />
            <div class="dashboard-container">
                <h1>List of Adjuster Users</h1>

                <el-card>
                    <div class="table-responsive">
                        <el-table :data="adjustersData" style="width: 100%">
                            <el-table-column label="Profile" width="100px" align="center">
                                <template #default="scope">
                                    <el-image
                                        :src="imageUrl( image_url, scope.row.profile_picture ) || 'https://ui-avatars.com/api/?name=' + scope.row.first_name + '+' + scope.row.last_name"
                                        fit="cover"
                                        style="width:48px; height:48px; border-radius:50%; object-fit:cover;"
                                        :alt="scope.row.first_name + ' ' + scope.row.last_name"
                                    />
                                </template>
                            </el-table-column>
                            <el-table-column prop="username" label="Username" width="100px" align="center" />
                            <el-table-column prop="user_email" label="User Email" width="250px" align="center" />
                            <el-table-column label="Name" width="150px" align="center">
                                <template #default="scope">
                                    {{ scope.row.first_name }} {{ scope.row.last_name }}
                                </template>
                            </el-table-column>
                            <el-table-column prop="phone" label="Phone" width="80px" align="center" />
                            <el-table-column prop="city" label="City" width="80px" align="center" />
                            <el-table-column prop="state" label="State" width="80px" align="center" />
                            <el-table-column label="User Status" width="150px" align="center">
                                <template #default="scope">
                                    <el-tag :type="scope.row.status === 'active' ? 'success' : 'danger'">
                                        {{ getStatusLabel(scope.row.status, scope.row.user_type) }}
                                    </el-tag>
                                </template>
                            </el-table-column>
                            <el-table-column align="right" width="200px">
                                <template #header>
                                    <el-input v-model="search" style="max-width: 200px" placeholder="Type to Search"
                                        class="input-with-select">
                                    </el-input>
                                </template>
                                <template #default="scope">
                                    <el-button
                                        type="primary"
                                        size="small"
                                        @click="viewDetails(scope.row)"
                                    >
                                        View Details
                                    </el-button>
                                    <template v-if="scope.row.status === 'rejected' || scope.row.status === 'approved'">
                                        <div v-if="scope.row.status === 'active'">
                                            <el-popconfirm title="Are you sure you want to Revoke Access?"
                                                @confirm="PermitOrRevokeAccess(scope.row.user_id, 'Revoke')"
                                                confirmButtonText="Yes" cancelButtonText="No" icon="el-icon-warning"
                                                iconColor="red">
                                                <template #reference>
                                                    <el-button size="small" type="danger">Revoke Access</el-button>
                                                </template>
                                            </el-popconfirm>
                                        </div>
                                        <div v-else>
                                            <el-popconfirm title="Are you sure you want to Permit Access?"
                                                @confirm="PermitOrRevokeAccess(scope.row.user_id, 'Permit')"
                                                confirmButtonText="Yes" cancelButtonText="No" icon="el-icon-warning"
                                                iconColor="red">
                                                <template #reference>
                                                    <el-button size="small" type="success">Permit Access</el-button>
                                                </template>
                                            </el-popconfirm>
                                        </div>
                                    </template>
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
import { onMounted, ref, watch } from "vue";
import { useRouter } from 'vue-router';
import AlertMessage from "../../Composable/AlertMessage";
import { useAppHelper } from "../../Composable/appHelper";
import { loader } from '../../Composable/Loader';
import Header from "../Header";

export default {
    name: "AdjustersList",
    components: {
        Header,
    },
    setup() {
        const router = useRouter();
        const { get, post, imageUrl, getStatusLabel } = useAppHelper();
        const { error, success } = AlertMessage();
        const { startLoading, stopLoading } = loader();
        const app_vars = window.adjuster_forge_app_vars;
        const image_url = `${app_vars.image_url ? app_vars.image_url : ''}`;
        const currentPage = ref(1);
        const pageSize = ref(10);
        const totalItems = ref(10);
        const adjustersData = ref([]);
        const search = ref('');

        const viewDetails = (row) => {
            router.push({
                name: 'adjuster-details',
                params: { user_id: row.user_id },
            });
        };

        const fetchAdjustersData = async () => {
            startLoading();
            try {
                const response = await get(`adjusters?page=${currentPage.value}&per_page=${pageSize.value}&search=${search.value}`);
                if (response.status === 'success') {
                    const data = response.data;
                    currentPage.value = data.current_page;
                    pageSize.value = data.per_page;
                    totalItems.value = data.total_items;
                    adjustersData.value = data.adjusters_list;
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
            fetchAdjustersData();
        };

        const handleSizeChange = (newPageSize) => {
            pageSize.value = newPageSize;
            if (pageSize.value > totalItems.value) {
                currentPage.value = 1;
            }
            fetchAdjustersData();
        };

        // Function to format date
        const formatDate = (dateString) => {
            const options = { year: 'numeric', month: 'long', day: 'numeric' };
            return new Date(dateString).toLocaleDateString(undefined, options);
        };

        // refund subscription
        const PermitOrRevokeAccess = async (id, action) => {
            try {
                const message = action === 'Permit' ? 'Permit Processing...' : 'Revoke Processing...';
                const url = action === 'Permit' ? 'permit-access' : 'revoke-access';
                startLoading(message);
                const response = await post(url, {
                    id: id
                });

                if (response.status === 'success') {
                    success(response.message);
                    fetchAdjustersData();
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
            fetchAdjustersData();
        }, 1000));

        onMounted(() => {
            fetchAdjustersData();
        });

        return {
            search,
            adjustersData,
            currentPage,
            pageSize,
            totalItems,
            image_url,
            imageUrl,
            getStatusLabel,
            handlePageChange,
            handleSizeChange,
            formatDate,
            PermitOrRevokeAccess,
            viewDetails,
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
.table-responsive {
  width: 100%;
  overflow-x: auto;
}

.el-table {
  min-width: 900px;
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