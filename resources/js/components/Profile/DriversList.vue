<template>
    <el-container class="full-height">
        <el-main class="main-center">
            <Menu />
            <template v-if="user_status !== 'active'">
                <Message :user_status="user_status" :user_type="user_type" :plan_type="plan_type" />
            </template>
            <template v-else>
                <div class="dashboard-container">
                    <el-container class="layout-container-demo">
                        <el-aside class="sidebar">
                            <el-scrollbar>
                                <div class="sidebar-menu">
                                    <div class="sidebar-filters">
                                        <h4 class="sidebar-headline">Licenses</h4>
                                        <el-checkbox-group v-model="filter_form.license_classes">
                                            <div><el-checkbox label="CDL Class A" :value="'CDL-Class-A'">CDL Class A</el-checkbox></div>
                                            <div><el-checkbox label="CDL Class B" :value="'CDL-Class-B'">CDL Class B</el-checkbox></div>
                                            <div><el-checkbox label="CDL Class C" :value="'CDL-Class-C'">CDL Class C</el-checkbox></div>
                                            <div><el-checkbox label="Non-CDL" :value="'Non-CDL'">Non CDL</el-checkbox></div>
                                        </el-checkbox-group>
                                        <h4 class="sidebar-headline" style="margin-top: 18px;">Endorsements</h4>
                                        <el-checkbox-group v-model="filter_form.endorsements">
                                            <div><el-checkbox label="Hazmat (H)" :value="'Hazmat'">Hazmat (H)</el-checkbox></div>
                                            <div><el-checkbox label="Tank Vehicles (N)" :value="'Tank-Vehicles'">Tank Vehicles (N)</el-checkbox></div>
                                            <div><el-checkbox label="Passenger (P)" :value="'Passenger'">Passenger (P)</el-checkbox></div>
                                            <div><el-checkbox label="Double/Triple Trailers (T)" :value="'Double-Triple-Trailers'">Double/Triple Trailers (T)</el-checkbox></div>
                                            <div><el-checkbox label="Combination Hazmat/Tank (X)" :value="'Combination-Hazmat-Tank'">Combination Hazmat/Tank (X)</el-checkbox></div>
                                            <div><el-checkbox label="School Bus (S)" :value="'School-Bus'">School Bus (S)</el-checkbox></div>
                                        </el-checkbox-group>
                                        <h4 class="sidebar-headline" style="margin-top: 18px;">Vehicle/Equipment</h4>
                                        <el-checkbox-group v-model="filter_form.vehicles">
                                            <div><el-checkbox label="18-Wheeler / Tractor-Trailer" :value="'18-Wheeler-Tractor-Trailer'">18-Wheeler / Tractor-Trailer</el-checkbox></div>
                                            <div><el-checkbox label="Doubles / Triples" :value="'Doubles-Triples'">Doubles / Triples</el-checkbox></div>
                                            <div><el-checkbox label="Straight Truck / Box Truck" :value="'Straight-Truck-Box-Truck'">Straight Truck / Box Truck</el-checkbox></div>
                                            <div><el-checkbox label="Flatbed" :value="'Flatbed'">Flatbed</el-checkbox></div>
                                            <div><el-checkbox label="Tanker" :value="'Tanker'">Tanker</el-checkbox></div>
                                            <div><el-checkbox label="Refrigerated (Reefer)" :value="'Refrigerated-Reefer'">Refrigerated (Reefer)</el-checkbox></div>
                                            <div><el-checkbox label="Dry Van" :value="'Dry-Van'">Dry Van</el-checkbox></div>
                                            <div><el-checkbox label="Dump Truck" :value="'Dump-Truck'">Dump Truck</el-checkbox></div>
                                            <div><el-checkbox label="Heavy Haul / Oversize Loads" :value="'Heavy-Haul-Oversize-Loads'">Heavy Haul / Oversize Loads</el-checkbox></div>
                                        </el-checkbox-group>
                                        <h4 class="sidebar-headline" style="margin-top: 18px;">Experience</h4>
                                        <el-slider v-model="filter_form.experience" :min="0" :max="10" :step="1" />
                                        <h4 class="sidebar-headline" style="margin-top: 18px;">Availability</h4>
                                        <el-checkbox-group v-model="filter_form.availability">
                                            <div><el-checkbox label="Full Time" :value="'Full Time'">Full Time</el-checkbox></div>
                                            <div><el-checkbox label="Part Time" :value="'Part Time'">Part Time</el-checkbox></div>
                                            <div><el-checkbox label="Weekends" :value="'Weekends'">Weekends</el-checkbox></div>
                                            <div><el-checkbox label="Nights" :value="'Nights'">Nights</el-checkbox></div>
                                            <div><el-checkbox label="Seasonal" :value="'Seasonal'">Seasonal</el-checkbox></div>
                                        </el-checkbox-group>
                                    </div>
                                </div>
                            </el-scrollbar>
                        </el-aside>
                        <el-container>
                            <el-header>
                                <!-- Search and Filter Row -->
                                <el-row :gutter="20" class="filter-row">
                                    <el-col :span="8">
                                        <el-form-item label="Search" label-position="top">
                                            <el-input
                                                v-model="filter_form.search"
                                                placeholder="Search by name, email..."
                                                clearable
                                                class="modern-input"
                                            />
                                        </el-form-item>
                                    </el-col>
                                    <el-col :span="6">
                                        <el-form-item label="City" label-position="top">
                                            <el-input
                                                v-model="filter_form.city"
                                                placeholder="Search by City"
                                                clearable
                                                class="modern-input"
                                            />
                                        </el-form-item>
                                    </el-col>
                                    <el-col :span="6">
                                        <el-form-item label="State" label-position="top">
                                            <el-input
                                                v-model="filter_form.state"
                                                placeholder="Search by State"
                                                clearable
                                                class="modern-input"
                                            />
                                        </el-form-item>
                                    </el-col>
                                </el-row>
                            </el-header>

                            <el-main style="margin-top: 20px;">
                                <el-row :gutter="24" class="card-row">
                                    <el-col
                                        v-for="driver in driversData"
                                        :key="driver.user_id"
                                        :span="8"
                                    >
                                        <el-card class="modern-driver-card clickable-card" shadow="always">
                                            <div class="driver-avatar-section">
                                                <el-avatar
                                                    :src="driver.profile_picture || 'https://ui-avatars.com/api/?name=' + driver.first_name + '+' + driver.last_name"
                                                    :size="70"
                                                    class="driver-avatar"
                                                />
                                                <div class="driver-status">
                                                    <el-tag
                                                        :type="driver.status === 'active' ? 'success' : driver.status === 'pending' ? 'warning' : 'info'"
                                                        effect="dark"
                                                    >
                                                        {{ driver.status }}
                                                    </el-tag>
                                                </div>
                                            </div>
                                            <div class="driver-info-section">
                                                <h3 class="driver-name">{{ driver.first_name }} {{ driver.last_name }}</h3>
                                                <p class="driver-username">@{{ driver.username }}</p>
                                                <p class="driver-email">
                                                    <el-icon><i class="el-icon-message"></i></el-icon>
                                                    {{ driver.user_email }}
                                                </p>
                                                <div class="driver-location">
                                                    <span>
                                                        <el-icon><i class="el-icon-location"></i></el-icon>
                                                        {{ driver.city }}, {{ driver.state }}
                                                    </span>
                                                </div>
                                            </div>

                                            <!-- Hover Overlay -->
                                            <div class="hover-overlay">
                                                <el-button type="primary" size="large" round @click.stop="viewDetails(driver)">
                                                    View Details
                                                </el-button>
                                            </div>
                                        </el-card>
                                    </el-col>
                                </el-row>

                                <el-empty v-if="driversData.length === 0" description="No drivers found." />

                                <el-pagination
                                    v-if="totalItems > pageSize"
                                    @current-change="handlePageChange"
                                    @size-change="handleSizeChange"
                                    :current-page="currentPage"
                                    :page-size="pageSize"
                                    :background="true"
                                    :total="totalItems"
                                    layout="total, sizes, prev, pager, next"
                                    class="modern-pagination"
                                />
                            </el-main>
                        </el-container>
                    </el-container>
                </div>
            </template>
        </el-main>
    </el-container>
</template>

<script>
import { onMounted, ref, watch, computed } from "vue";
import { useRouter } from 'vue-router';
import AlertMessage from "../../Composable/AlertMessage";
import { useAppHelper } from "../../Composable/appHelper";
import { loader } from '../../Composable/Loader';
import Menu from "./Menu.vue";
import Message from '../Messages/Message.vue';
export default {
    name: "DriversList",
    components: {
        Menu,
        Message
    },
    setup() {
        const router = useRouter();
        const { get } = useAppHelper();
        const { error } = AlertMessage();
        const { startLoading, stopLoading } = loader();
        const app_vars = window.driver_forge_app_vars;
        const user_status = `${app_vars.user_status ? app_vars.user_status : ''}`;
        const user_type = `${app_vars.user_data? app_vars.user_data.user_type : ''}`;
        const plan_type = `${app_vars.plan_type ? app_vars.plan_type : ''}`;
        const currentPage = ref(1);
        const pageSize = ref(10);
        const totalItems = ref(10);
        const driversData = ref([]);
        const filter_form = ref({
            search: '',
            city: '',
            state: '',
            license_classes: [],
            endorsements: [],
            vehicles: [],
            experience: 0,
            availability: [],
        });
        const viewDetails = (row) => {
            router.push({
                name: 'driver-details',
                params: { user_id: row.user_id },
            });
        };

        // Helper to build query string from filter_form
        const buildQueryParams = () => {
            const params = {
                page: currentPage.value,
                per_page: pageSize.value,
            };
            Object.entries(filter_form.value).forEach(([key, val]) => {
                if (Array.isArray(val)) {
                    params[key] = val.join(',');
                } else if (val !== '' && val !== null && val !== undefined) {
                    params[key] = val;
                }
            });
            return new URLSearchParams(params).toString();
        };

        const fetchDriversData = async () => {
            startLoading();
            try {
                const query = buildQueryParams();
                const response = await get(`active-drivers?${query}`);
                if (response.status === 'success') {
                    const data = response.data;
                    currentPage.value = data.current_page;
                    pageSize.value = data.per_page;
                    totalItems.value = data.total_items;
                    driversData.value = data.drivers_list;
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
            fetchDriversData();
        };

        const handleSizeChange = (newPageSize) => {
            pageSize.value = newPageSize;
            if (pageSize.value > totalItems.value) {
                currentPage.value = 1;
            }
            fetchDriversData();
        };

        // Function to format date
        const formatDate = (dateString) => {
            const options = { year: 'numeric', month: 'long', day: 'numeric' };
            return new Date(dateString).toLocaleDateString(undefined, options);
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

        // Debounced watchers for search, city, state, experience
        watch(() => filter_form.value.search, debounce(() => {
            fetchDriversData();
        }, 600));
        watch(() => filter_form.value.city, debounce(() => {
            fetchDriversData();
        }, 600));
        watch(() => filter_form.value.state, debounce(() => {
            fetchDriversData();
        }, 600));
        watch(() => filter_form.value.experience, debounce(() => {
            fetchDriversData();
        }, 600));

        // Immediate watcher for other fields (array/value changes)
        watch(
            () => [
                filter_form.value.license_classes.slice(),
                filter_form.value.endorsements.slice(),
                filter_form.value.vehicles.slice(),
                filter_form.value.availability.slice()
            ],
            fetchDriversData,
            { deep: true }
        );

        onMounted(() => {
            fetchDriversData();
        });

        return {
            driversData,
            currentPage,
            pageSize,
            totalItems,
            filter_form,
            handlePageChange,
            handleSizeChange,
            formatDate,
            viewDetails,
            user_status,
            user_type,
            plan_type
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

.driver-card {
    transition: transform 0.2s;
}

.driver-card:hover {
    transform: scale(1.02);
}

.driver-card-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
}

.driver-card-name {
    flex-grow: 1;
    margin-left: 10px;
}

.driver-card-info {
    margin: 10px 0;
}

.driver-card-actions {
    text-align: right;
}

.drivers-title {
    font-size: 2.2rem;
    font-weight: 700;
    color: #2c3e50;
    margin-bottom: 24px;
    letter-spacing: 1px;
}

.filter-row {
    margin-bottom: 30px;
}
.el-input__wrapper,
.el-select__wrapper {
    padding: 1px 8px !important;
}
.modern-input,
.modern-select {
    border-radius: 8px;
    font-size: 1.05em;
    height: 42px !important;
    min-height: 36px !important;
    line-height: 36px !important;
    box-sizing: border-box;
}

.card-row {
    margin-bottom: 30px;
}

.modern-driver-card {
    border-radius: 18px;
    box-shadow: 0 4px 24px rgba(60, 120, 200, 0.08);
    transition: box-shadow 0.2s, transform 0.2s;
    padding: 28px 18px 18px 18px;
    background: linear-gradient(135deg, #f8fafc 80%, #e3f0ff 100%);
    display: flex;
    flex-direction: column;
    align-items: center;
    min-height: 340px;
}

.modern-driver-card:hover {
    box-shadow: 0 8px 32px rgba(60, 120, 200, 0.18);
    transform: translateY(-4px) scale(1.03);
}

.driver-avatar-section {
    display: flex;
    flex-direction: column;
    align-items: center;
    margin-bottom: 12px;
}

.driver-avatar {
    margin-bottom: 8px;
    box-shadow: 0 2px 8px rgba(60, 120, 200, 0.12);
}

.driver-status {
    margin-bottom: 2px;
}

.driver-info-section {
    text-align: center;
    margin-bottom: 16px;
}

.driver-name {
    font-size: 1.25rem;
    font-weight: 600;
    margin-bottom: 4px;
    color: #34495e;
}

.driver-username {
    font-size: 0.98em;
    color: #888;
    margin-bottom: 6px;
}

.driver-email {
    font-size: 0.97em;
    color: #409eff;
    margin-bottom: 6px;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 4px;
}

.driver-location {
    font-size: 0.97em;
    color: #666;
    margin-bottom: 2px;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 4px;
}

.driver-actions {
    margin-top: 12px;
    text-align: center;
}

.modern-pagination {
    margin-top: 30px;
    display: flex;
    justify-content: center;
}

.clickable-card {
    position: relative;
    cursor: pointer;
    overflow: hidden;
}

.hover-overlay {
    position: absolute;
    inset: 0;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    display: flex;
    align-items: center;
    justify-content: center;
    opacity: 0;
    transition: opacity 0.35s cubic-bezier(0.4,0,0.2,1);
    z-index: 2;
}

.clickable-card:hover .hover-overlay {
    opacity: 1;
}

.hover-overlay .el-button {
    box-shadow: 0 4px 16px rgba(0,0,0,0.12);
    font-size: 1.1em;
    padding: 16px 32px;
}

.sidebar{
    width: 250px;
    border-right: 1px dashed #bcbcbc;
}

.sidebar-menu {
    padding: 10px 0;
}
.sidebar-filters {
    margin-top: 24px;
    padding: 0 10px;
}
.sidebar-headline {
    font-size: 1.08em;
    font-weight: 600;
    color: #34495e;
    margin-bottom: 8px;
    border-bottom: 1px solid #d3d3d3;
    padding-bottom: 4px;
}

@media (max-width: 900px) {
    .card-row .el-col {
        flex: 0 0 50%;
        max-width: 50%;
    }
}
@media (max-width: 600px) {
    .card-row .el-col {
        flex: 0 0 100%;
        max-width: 100%;
    }
    .modern-driver-card {
        min-height: 280px;
        padding: 18px 6px;
    }
}
</style>