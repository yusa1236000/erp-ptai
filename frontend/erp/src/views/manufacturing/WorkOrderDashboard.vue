<!-- src/views/manufacturing/WorkOrderDashboard.vue -->
<template>
    <div class="dashboard-page">
      <div class="card">
        <div class="card-header">
          <h2 class="card-title">Manufacturing Dashboard</h2>
        </div>
        <div class="card-body">
          <div v-if="isLoading" class="loading-indicator">
            <i class="fas fa-spinner fa-spin"></i> Loading dashboard data...
          </div>
          
          <div v-else>
            <!-- Summary Stats -->
            <div class="stats-summary">
              <div class="stat-card">
                <div class="stat-icon bg-primary">
                  <i class="fas fa-clipboard-list"></i>
                </div>
                <div class="stat-content">
                  <div class="stat-value">{{ stats.totalWorkOrders }}</div>
                  <div class="stat-label">Total Work Orders</div>
                </div>
              </div>
              
              <div class="stat-card">
                <div class="stat-icon bg-warning">
                  <i class="fas fa-spinner"></i>
                </div>
                <div class="stat-content">
                  <div class="stat-value">{{ stats.inProgressOrders }}</div>
                  <div class="stat-label">In Progress</div>
                </div>
              </div>
              
              <div class="stat-card">
                <div class="stat-icon bg-info">
                  <i class="fas fa-clipboard-check"></i>
                </div>
                <div class="stat-content">
                  <div class="stat-value">{{ stats.plannedOrders }}</div>
                  <div class="stat-label">Planned</div>
                </div>
              </div>
              
              <div class="stat-card">
                <div class="stat-icon bg-success">
                  <i class="fas fa-check-circle"></i>
                </div>
                <div class="stat-content">
                  <div class="stat-value">{{ stats.completedOrders }}</div>
                  <div class="stat-label">Completed</div>
                </div>
              </div>
            </div>
            
            <!-- Production Status Chart -->
            <div class="chart-container">
              <div class="card">
                <div class="card-header">
                  <h3>Work Order Status</h3>
                </div>
                <div class="card-body">
                  <div class="chart-placeholder">
                    <div class="donut-chart">
                      <div 
                        v-for="(segment, index) in statusChartSegments" 
                        :key="index"
                        class="donut-segment"
                        :style="{
                          '--start': segment.start + 'deg',
                          '--end': segment.end + 'deg',
                          '--color': segment.color
                        }"
                      ></div>
                      <div class="donut-center">
                        <div class="donut-label">{{ stats.totalWorkOrders }}</div>
                        <div class="donut-sublabel">Total</div>
                      </div>
                    </div>
                    
                    <div class="chart-legend">
                      <div 
                        v-for="(segment, index) in statusChartSegments" 
                        :key="index"
                        class="legend-item"
                      >
                        <div class="legend-color" :style="{ 'background-color': segment.color }"></div>
                        <div class="legend-label">{{ segment.label }}</div>
                        <div class="legend-value">{{ segment.value }}</div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
            
            <!-- Recent Work Orders and Production Activity -->
            <div class="dashboard-grid">
              <!-- Recent Work Orders -->
              <div class="grid-item">
                <div class="card">
                  <div class="card-header">
                    <h3>Recent Work Orders</h3>
                    <router-link to="/manufacturing/work-orders" class="btn btn-sm btn-primary">
                      View All
                    </router-link>
                  </div>
                  <div class="card-body p-0">
                    <div class="table-responsive">
                      <table class="dashboard-table">
                        <thead>
                          <tr>
                            <th>WO #</th>
                            <th>Product</th>
                            <th>Status</th>
                            <th>Qty</th>
                            <th>Due Date</th>
                          </tr>
                        </thead>
                        <tbody>
                          <tr v-for="order in recentWorkOrders" :key="order.wo_id">
                            <td>
                              <router-link :to="`/manufacturing/work-orders/${order.wo_id}`">
                                {{ order.wo_number }}
                              </router-link>
                            </td>
                            <td>{{ order.product_name }}</td>
                            <td>
                              <span class="badge" :class="getStatusClass(order.status)">
                                {{ order.status }}
                              </span>
                            </td>
                            <td>{{ order.planned_quantity }}</td>
                            <td>{{ formatDate(order.planned_end_date) }}</td>
                          </tr>
                        </tbody>
                      </table>
                    </div>
                  </div>
                </div>
              </div>
              
              <!-- Production Activity -->
              <div class="grid-item">
                <div class="card">
                  <div class="card-header">
                    <h3>Recent Activity</h3>
                  </div>
                  <div class="card-body">
                    <div class="activity-list">
                      <div v-for="(activity, index) in recentActivities" :key="index" class="activity-item">
                        <div class="activity-icon" :class="getActivityIconClass(activity.type)">
                          <i :class="getActivityIcon(activity.type)"></i>
                        </div>
                        <div class="activity-content">
                          <div class="activity-message" v-html="activity.message"></div>
                          <div class="activity-time">{{ formatTimeAgo(activity.timestamp) }}</div>
                        </div>
                      </div>
                      
                      <div v-if="recentActivities.length === 0" class="empty-activity">
                        <p>No recent activities.</p>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
            
            <!-- Capacity Utilization -->
            <div class="card mt-4">
              <div class="card-header">
                <h3>Work Center Capacity Utilization</h3>
              </div>
              <div class="card-body">
                <div class="table-responsive">
                  <table class="dashboard-table">
                    <thead>
                      <tr>
                        <th>Work Center</th>
                        <th>Capacity</th>
                        <th>Allocated</th>
                        <th>Available</th>
                        <th>Utilization</th>
                      </tr>
                    </thead>
                    <tbody>
                      <tr v-for="center in workCenters" :key="center.id">
                        <td>{{ center.name }}</td>
                        <td>{{ center.capacity }} hrs/day</td>
                        <td>{{ center.allocated }} hrs</td>
                        <td>{{ center.available }} hrs</td>
                        <td>
                          <div class="progress-container">
                            <div 
                              class="progress-bar"
                              :class="getUtilizationClass(center.utilization)"
                              :style="{ width: `${center.utilization}%` }"
                            ></div>
                            <span class="progress-text">{{ center.utilization }}%</span>
                          </div>
                        </td>
                      </tr>
                    </tbody>
                  </table>
                </div>
              </div>
            </div>
            
            <!-- Critical Materials -->
            <div class="card mt-4">
              <div class="card-header">
                <h3>Critical Materials</h3>
              </div>
              <div class="card-body">
                <div v-if="criticalMaterials.length === 0" class="empty-state">
                  <p>No critical materials at this time.</p>
                </div>
                <div v-else class="table-responsive">
                  <table class="dashboard-table">
                    <thead>
                      <tr>
                        <th>Item Code</th>
                        <th>Item Name</th>
                        <th>Current Stock</th>
                        <th>Required</th>
                        <th>Shortage</th>
                        <th>Status</th>
                      </tr>
                    </thead>
                    <tbody>
                      <tr v-for="material in criticalMaterials" :key="material.item_id">
                        <td>{{ material.item_code }}</td>
                        <td>{{ material.name }}</td>
                        <td>{{ material.current_stock }}</td>
                        <td>{{ material.required_qty }}</td>
                        <td class="text-danger">{{ material.shortage }}</td>
                        <td>
                          <span class="badge badge-danger">Critical</span>
                        </td>
                      </tr>
                    </tbody>
                  </table>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </template>
  
  <script>
  import { ref, computed, onMounted } from 'vue';
  //import axios from 'axios';
  
  export default {
    name: 'WorkOrderDashboard',
    setup() {
      // Data
      const isLoading = ref(true);
      const stats = ref({
        totalWorkOrders: 0,
        inProgressOrders: 0,
        plannedOrders: 0,
        completedOrders: 0
      });
      const recentWorkOrders = ref([]);
      const recentActivities = ref([]);
      const workCenters = ref([]);
      const criticalMaterials = ref([]);
      
      // Computed
      const statusChartSegments = computed(() => {
        const total = stats.value.totalWorkOrders;
        if (total === 0) return [];
        
        let startAngle = 0;
        
        const calculateSegment = (value, label, color) => {
          if (value === 0) return null;
          
          const percentage = (value / total) * 100;
          const degrees = (percentage / 100) * 360;
          
          const segment = {
            value,
            label,
            color,
            start: startAngle,
            end: startAngle + degrees
          };
          
          startAngle += degrees;
          return segment;
        };
        
        return [
          calculateSegment(stats.value.inProgressOrders, 'In Progress', '#f59e0b'),
          calculateSegment(stats.value.plannedOrders, 'Planned', '#3b82f6'),
          calculateSegment(stats.value.completedOrders, 'Completed', '#10b981'),
          calculateSegment(
            total - stats.value.inProgressOrders - stats.value.plannedOrders - stats.value.completedOrders,
            'Other',
            '#6b7280'
          )
        ].filter(segment => segment !== null);
      });
      
      // Methods
      const loadDashboardData = async () => {
        isLoading.value = true;
        
        try {
          // In a real implementation, you would make API calls to fetch the data
          // For this demo, we'll simulate the API responses
          
          // Fetch statistics
          await simulateApiCall();
          stats.value = {
            totalWorkOrders: 152,
            inProgressOrders: 35,
            plannedOrders: 48,
            completedOrders: 64
          };
          
          // Fetch recent work orders
          recentWorkOrders.value = [
            {
              wo_id: '1',
              wo_number: 'WO-2023-0125',
              product_name: 'Aluminum Frame XL',
              status: 'In Progress',
              planned_quantity: 250,
              planned_end_date: '2025-05-02'
            },
            {
              wo_id: '2',
              wo_number: 'WO-2023-0124',
              product_name: 'Steel Casing Type B',
              status: 'Released',
              planned_quantity: 100,
              planned_end_date: '2025-05-10'
            },
            {
              wo_id: '3',
              wo_number: 'WO-2023-0123',
              product_name: 'Plastic Housing',
              status: 'Completed',
              planned_quantity: 500,
              planned_end_date: '2025-04-28'
            },
            {
              wo_id: '4',
              wo_number: 'WO-2023-0122',
              product_name: 'Mounting Bracket',
              status: 'In Progress',
              planned_quantity: 300,
              planned_end_date: '2025-05-05'
            },
            {
              wo_id: '5',
              wo_number: 'WO-2023-0121',
              product_name: 'Control Panel',
              status: 'Planned',
              planned_quantity: 150,
              planned_end_date: '2025-05-15'
            }
          ];
          
          // Fetch recent activities
          recentActivities.value = [
            {
              type: 'start_operation',
              message: 'Operation <strong>Assembly</strong> started for <strong>WO-2023-0125</strong>',
              timestamp: new Date(Date.now() - 30 * 60000) // 30 minutes ago
            },
            {
              type: 'complete_work_order',
              message: 'Work Order <strong>WO-2023-0123</strong> completed',
              timestamp: new Date(Date.now() - 2 * 60 * 60000) // 2 hours ago
            },
            {
              type: 'material_issue',
              message: 'Materials issued for <strong>WO-2023-0124</strong>',
              timestamp: new Date(Date.now() - 4 * 60 * 60000) // 4 hours ago
            },
            {
              type: 'create_work_order',
              message: 'New Work Order <strong>WO-2023-0126</strong> created',
              timestamp: new Date(Date.now() - 8 * 60 * 60000) // 8 hours ago
            },
            {
              type: 'quality_inspection',
              message: 'Quality inspection passed for <strong>WO-2023-0122</strong>',
              timestamp: new Date(Date.now() - 1 * 24 * 60 * 60000) // 1 day ago
            }
          ];
          
          // Fetch work center data
          workCenters.value = [
            {
              id: 1,
              name: 'Assembly Line 1',
              capacity: 8,
              allocated: 6.4,
              available: 1.6,
              utilization: 80
            },
            {
              id: 2,
              name: 'CNC Machine',
              capacity: 12,
              allocated: 10.8,
              available: 1.2,
              utilization: 90
            },
            {
              id: 3,
              name: 'Paint Shop',
              capacity: 8,
              allocated: 4,
              available: 4,
              utilization: 50
            },
            {
              id: 4,
              name: 'Testing Station',
              capacity: 10,
              allocated: 7,
              available: 3,
              utilization: 70
            },
            {
              id: 5,
              name: 'Packaging',
              capacity: 6,
              allocated: 3.6,
              available: 2.4,
              utilization: 60
            }
          ];
          
          // Fetch critical materials
          criticalMaterials.value = [
            {
              item_id: 1,
              item_code: 'RM-1001',
              name: 'Aluminum Sheet 2mm',
              current_stock: 120,
              required_qty: 180,
              shortage: 60
            },
            {
              item_id: 2,
              item_code: 'RM-1042',
              name: 'Steel Rod 10mm',
              current_stock: 50,
              required_qty: 95,
              shortage: 45
            },
            {
              item_id: 3,
              item_code: 'RM-2015',
              name: 'Plastic Resin Type A',
              current_stock: 200,
              required_qty: 250,
              shortage: 50
            }
          ];
        } catch (error) {
          console.error('Error loading dashboard data:', error);
        } finally {
          isLoading.value = false;
        }
      };
      
      // Utility for simulating API calls
      const simulateApiCall = () => {
        return new Promise(resolve => {
          setTimeout(resolve, 800);
        });
      };
      
      const formatDate = (dateString) => {
        if (!dateString) return '-';
        const date = new Date(dateString);
        return date.toLocaleDateString('id-ID', {
          year: 'numeric',
          month: '2-digit',
          day: '2-digit'
        });
      };
      
      const formatTimeAgo = (date) => {
        const now = new Date();
        const diffMs = now - date;
        const diffMin = Math.round(diffMs / 60000);
        const diffHours = Math.round(diffMs / (60000 * 60));
        const diffDays = Math.round(diffMs / (60000 * 60 * 24));
        
        if (diffMin < 60) {
          return `${diffMin} minute${diffMin === 1 ? '' : 's'} ago`;
        } else if (diffHours < 24) {
          return `${diffHours} hour${diffHours === 1 ? '' : 's'} ago`;
        } else {
          return `${diffDays} day${diffDays === 1 ? '' : 's'} ago`;
        }
      };
      
      const getStatusClass = (status) => {
        switch (status) {
          case 'Draft': return 'badge-secondary';
          case 'Planned': return 'badge-info';
          case 'Released': return 'badge-primary';
          case 'In Progress': return 'badge-warning';
          case 'Completed': return 'badge-success';
          case 'Closed': return 'badge-dark';
          case 'Cancelled': return 'badge-danger';
          default: return 'badge-secondary';
        }
      };
      
      const getActivityIcon = (type) => {
        switch (type) {
          case 'start_operation': return 'fas fa-play-circle';
          case 'complete_operation': return 'fas fa-check-circle';
          case 'complete_work_order': return 'fas fa-clipboard-check';
          case 'create_work_order': return 'fas fa-file-alt';
          case 'material_issue': return 'fas fa-dolly';
          case 'quality_inspection': return 'fas fa-check-square';
          default: return 'fas fa-info-circle';
        }
      };
      
      const getActivityIconClass = (type) => {
        switch (type) {
          case 'start_operation': return 'bg-warning';
          case 'complete_operation': return 'bg-success';
          case 'complete_work_order': return 'bg-success';
          case 'create_work_order': return 'bg-primary';
          case 'material_issue': return 'bg-info';
          case 'quality_inspection': return 'bg-secondary';
          default: return 'bg-primary';
        }
      };
      
      const getUtilizationClass = (percentage) => {
        if (percentage >= 90) return 'progress-danger';
        if (percentage >= 70) return 'progress-warning';
        return 'progress-success';
      };
      
      // Lifecycle hooks
      onMounted(async () => {
        await loadDashboardData();
      });
      
      return {
        isLoading,
        stats,
        recentWorkOrders,
        recentActivities,
        workCenters,
        criticalMaterials,
        statusChartSegments,
        formatDate,
        formatTimeAgo,
        getStatusClass,
        getActivityIcon,
        getActivityIconClass,
        getUtilizationClass
      };
    }
  };
  </script>
  
  <style scoped>
  .dashboard-page {
    padding: 1rem;
  }
  
  .card {
    background-color: white;
    border-radius: 0.5rem;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
    margin-bottom: 1.5rem;
    overflow: hidden;
  }
  
  .card:last-child {
    margin-bottom: 0;
  }
  
  .card-header {
    padding: 1rem 1.5rem;
    border-bottom: 1px solid var(--gray-200);
    display: flex;
    justify-content: space-between;
    align-items: center;
  }
  
  .card-header h2, .card-header h3 {
    margin: 0;
  }
  
  .card-header h2 {
    font-size: 1.5rem;
    color: var(--gray-800);
  }
  
  .card-header h3 {
    font-size: 1.25rem;
    color: var(--gray-800);
  }
  
  .card-body {
    padding: 1.5rem;
  }
  
  .card-body.p-0 {
    padding: 0;
  }
  
  .loading-indicator {
    display: flex;
    justify-content: center;
    align-items: center;
    min-height: 200px;
    color: var(--gray-500);
  }
  
  .stats-summary {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 1.5rem;
    margin-bottom: 1.5rem;
  }
  
  @media (max-width: 992px) {
    .stats-summary {
      grid-template-columns: repeat(2, 1fr);
    }
  }
  
  @media (max-width: 576px) {
    .stats-summary {
      grid-template-columns: 1fr;
    }
  }
  
  .stat-card {
    display: flex;
    align-items: center;
    background-color: white;
    border-radius: 0.5rem;
    padding: 1.5rem;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
  }
  
  .stat-icon {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 3.5rem;
    height: 3.5rem;
    border-radius: 50%;
    margin-right: 1rem;
    color: white;
    font-size: 1.5rem;
  }
  
  .bg-primary {
    background-color: var(--primary-color);
  }
  
  .bg-warning {
    background-color: var(--warning-color);
  }
  
  .bg-info {
    background-color: #0ea5e9;
  }
  
  .bg-success {
    background-color: var(--success-color);
  }
  
  .bg-secondary {
    background-color: var(--gray-500);
  }
  
  .stat-content {
    flex: 1;
  }
  
  .stat-value {
    font-size: 1.5rem;
    font-weight: 700;
    line-height: 1;
    color: var(--gray-900);
  }
  
  .stat-label {
    margin-top: 0.25rem;
    font-size: 0.875rem;
    color: var(--gray-500);
  }
  
  .dashboard-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 1.5rem;
    margin-bottom: 1.5rem;
  }
  
  @media (max-width: 768px) {
    .dashboard-grid {
      grid-template-columns: 1fr;
    }
  }
  
  .grid-item {
    min-height: 400px;
    display: flex;
    flex-direction: column;
  }
  
  .grid-item .card {
    flex: 1;
    display: flex;
    flex-direction: column;
    margin-bottom: 0;
  }
  
  .grid-item .card-body {
    flex: 1;
    overflow: auto;
  }
  
  .chart-container {
    max-width: 800px;
    margin: 0 auto 1.5rem;
  }
  
  .chart-placeholder {
    display: flex;
    flex-wrap: wrap;
    justify-content: center;
    gap: 2rem;
    padding: 1rem 0;
  }
  
  .donut-chart {
    position: relative;
    width: 200px;
    height: 200px;
    border-radius: 50%;
    background-color: var(--gray-200);
  }
  
  .donut-segment {
    position: absolute;
    width: 100%;
    height: 100%;
    border-radius: 50%;
    clip-path: polygon(50% 50%, 50% 0%, 
      calc(50% + 50% * cos(var(--start))) calc(50% - 50% * sin(var(--start))),
      calc(50% + 50% * cos(var(--end))) calc(50% - 50% * sin(var(--end))), 
      50% 50%);
    background-color: var(--color);
  }
  
  .donut-center {
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    width: 120px;
    height: 120px;
    border-radius: 50%;
    background-color: white;
    display: flex;
    flex-direction: column;
    justify-content: center;
    align-items: center;
  }
  
  .donut-label {
    font-size: 2rem;
    font-weight: 700;
    line-height: 1;
    color: var(--gray-900);
  }
  
  .donut-sublabel {
    margin-top: 0.25rem;
    font-size: 0.875rem;
    color: var(--gray-500);
  }
  
  .chart-legend {
    display: flex;
    flex-direction: column;
    gap: 0.75rem;
    justify-content: center;
  }
  
  .legend-item {
    display: flex;
    align-items: center;
    gap: 0.5rem;
  }
  
  .legend-color {
    width: 1.25rem;
    height: 1.25rem;
    border-radius: 0.25rem;
  }
  
  .legend-label {
    font-size: 0.875rem;
    color: var(--gray-700);
    min-width: 6rem;
  }
  
  .legend-value {
    font-weight: 600;
    color: var(--gray-800);
  }
  
  .dashboard-table {
    width: 100%;
    border-collapse: collapse;
  }
  
  .dashboard-table th,
  .dashboard-table td {
    padding: 0.75rem 1rem;
    text-align: left;
    border-bottom: 1px solid var(--gray-200);
  }
  
  .dashboard-table th {
    font-weight: 600;
    color: var(--gray-600);
    background-color: var(--gray-50);
  }
  
  .dashboard-table tr:last-child td {
    border-bottom: none;
  }
  
  .dashboard-table tbody tr:hover {
    background-color: var(--gray-50);
  }
  
  .activity-list {
    display: flex;
    flex-direction: column;
    gap: 1rem;
  }
  
  .activity-item {
    display: flex;
    gap: 1rem;
  }
  
  .activity-icon {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 2.5rem;
    height: 2.5rem;
    border-radius: 50%;
    color: white;
    flex-shrink: 0;
  }
  
  .activity-content {
    flex: 1;
  }
  
  .activity-message {
    color: var(--gray-800);
  }
  
  .activity-time {
    font-size: 0.75rem;
    color: var(--gray-500);
    margin-top: 0.25rem;
  }
  
  .empty-state, .empty-activity {
    text-align: center;
    color: var(--gray-500);
    padding: 2rem 0;
  }
  
  .progress-container {
    width: 100%;
    height: 0.5rem;
    background-color: var(--gray-200);
    border-radius: 0.25rem;
    overflow: hidden;
    position: relative;
  }
  
  .progress-bar {
    height: 100%;
    border-radius: 0.25rem;
    background-color: var(--primary-color);
  }
  
  .progress-text {
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    font-size: 0.75rem;
    font-weight: 600;
    color: var(--gray-800);
  }
  
  .progress-success {
    background-color: var(--success-color);
  }
  
  .progress-warning {
    background-color: var(--warning-color);
  }
  
  .progress-danger {
    background-color: var(--danger-color);
  }
  
  .text-danger {
    color: var(--danger-color);
  }
  
  .badge {
    display: inline-block;
    padding: 0.25rem 0.5rem;
    border-radius: 9999px;
    font-size: 0.75rem;
    font-weight: 500;
    text-align: center;
    white-space: nowrap;
  }
  
  .badge-primary {
    background-color: #e0f2fe;
    color: #0369a1;
  }
  
  .badge-secondary {
    background-color: #f1f5f9;
    color: #475569;
  }
  
  .badge-success {
    background-color: #dcfce7;
    color: #166534;
  }
  
  .badge-warning {
    background-color: #fef3c7;
    color: #92400e;
  }
  
  .badge-danger {
    background-color: #fee2e2;
    color: #b91c1c;
  }
  
  .badge-info {
    background-color: #dbeafe;
    color: #1e40af;
  }
  
  .badge-dark {
    background-color: #1e293b;
    color: white;
  }
  
  .btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    padding: 0.5rem 1rem;
    border-radius: 0.375rem;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.2s;
    border: none;
    text-decoration: none;
  }
  
  .btn-sm {
    padding: 0.25rem 0.5rem;
    font-size: 0.875rem;
  }
  
  .btn-primary {
    background-color: var(--primary-color);
    color: white;
  }
  
  .btn-primary:hover {
    background-color: var(--primary-dark);
  }
  
  .mt-4 {
    margin-top: 1rem;
  }
  
  .table-responsive {
    overflow-x: auto;
  }
  </style>