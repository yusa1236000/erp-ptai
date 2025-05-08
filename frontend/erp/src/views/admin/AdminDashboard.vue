<!-- src/views/admin/AdminDashboard.vue -->
<template>
    <div class="admin-dashboard">
      <div class="page-header">
        <h1>Dashboard Admin</h1>
        <p>Selamat datang di panel admin sistem ERP</p>
      </div>
  
      <div class="dashboard-stats">
        <div class="stat-card">
          <div class="stat-icon">
            <i class="fas fa-users"></i>
          </div>
          <div class="stat-content">
            <h3>Pengguna</h3>
            <div class="stat-value">{{ stats.users }}</div>
            <div class="stat-label">Total Pengguna Terdaftar</div>
          </div>
        </div>
  
        <div class="stat-card">
          <div class="stat-icon">
            <i class="fas fa-cogs"></i>
          </div>
          <div class="stat-content">
            <h3>Pengaturan</h3>
            <div class="stat-value">{{ stats.settings }}</div>
            <div class="stat-label">Total Pengaturan Sistem</div>
          </div>
        </div>
  
        <div class="stat-card">
          <div class="stat-icon">
            <i class="fas fa-box"></i>
          </div>
          <div class="stat-content">
            <h3>Produk</h3>
            <div class="stat-value">{{ stats.products }}</div>
            <div class="stat-label">Total Produk Aktif</div>
          </div>
        </div>
  
        <div class="stat-card">
          <div class="stat-icon">
            <i class="fas fa-server"></i>
          </div>
          <div class="stat-content">
            <h3>Status Sistem</h3>
            <div class="stat-value">
              <span class="status-indicator" :class="systemStatus.class"></span>
              {{ systemStatus.text }}
            </div>
            <div class="stat-label">{{ serverUptime }}</div>
          </div>
        </div>
      </div>
  
      <div class="dashboard-sections">
        <div class="dashboard-section">
          <div class="section-header">
            <h2>Aktivitas Terbaru</h2>
            <button class="btn-link">Lihat Semua</button>
          </div>
          <div class="activity-list">
            <div v-if="isLoading" class="loading-indicator">
              <i class="fas fa-spinner fa-spin"></i> Memuat aktivitas...
            </div>
            <div v-else-if="activities.length === 0" class="empty-state">
              Tidak ada aktivitas terbaru
            </div>
            <div v-else class="activity-item" v-for="(activity, index) in activities" :key="index">
              <div class="activity-icon" :class="activity.type">
                <i :class="getActivityIcon(activity.type)"></i>
              </div>
              <div class="activity-details">
                <div class="activity-message">{{ activity.message }}</div>
                <div class="activity-meta">
                  <span class="activity-user">{{ activity.user }}</span>
                  <span class="activity-time">{{ activity.time }}</span>
                </div>
              </div>
            </div>
          </div>
        </div>
  
        <div class="dashboard-section">
          <div class="section-header">
            <h2>Shortcut</h2>
          </div>
          <div class="shortcuts-grid">
            <router-link to="/admin/settings" class="shortcut-card">
              <i class="fas fa-cogs"></i>
              <span>Pengaturan Sistem</span>
            </router-link>
            <router-link to="/admin/users" class="shortcut-card">
              <i class="fas fa-users"></i>
              <span>Manajemen Pengguna</span>
            </router-link>
            <router-link to="/admin/logs/system" class="shortcut-card">
              <i class="fas fa-clipboard-list"></i>
              <span>Log Sistem</span>
            </router-link>
            <router-link to="/admin/settings/backup" class="shortcut-card">
              <i class="fas fa-database"></i>
              <span>Backup &amp; Restore</span>
            </router-link>
          </div>
        </div>
      </div>
  
      <div class="dashboard-sections">
        <div class="dashboard-section">
          <div class="section-header">
            <h2>Status Sistem</h2>
            <button class="btn-link">Detail Lengkap</button>
          </div>
          <div class="system-status-grid">
            <div v-for="(service, index) in systemServices" :key="index" class="system-status-card">
              <div class="service-info">
                <div class="service-name">{{ service.name }}</div>
                <div class="service-description">{{ service.description }}</div>
              </div>
              <div class="service-status">
                <span class="status-indicator" :class="service.status"></span>
                {{ service.statusText }}
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </template>
  
  <script>
  import { ref, onMounted, computed } from 'vue';
  import axios from 'axios';
  
  export default {
    name: 'AdminDashboard',
    setup() {
      const isLoading = ref(true);
      const stats = ref({
        users: 0,
        settings: 0,
        products: 0,
        systemStatus: 'normal'
      });
      const activities = ref([]);
      const systemServices = ref([]);
      const serverUptime = ref('Uptime: 0 hari');
  
      const systemStatus = computed(() => {
        const statusMap = {
          'critical': { text: 'Kritis', class: 'status-critical' },
          'warning': { text: 'Peringatan', class: 'status-warning' },
          'normal': { text: 'Normal', class: 'status-normal' }
        };
        
        return statusMap[stats.value.systemStatus] || statusMap.normal;
      });
  
      const getActivityIcon = (type) => {
        const iconMap = {
          'login': 'fas fa-sign-in-alt',
          'update': 'fas fa-edit',
          'create': 'fas fa-plus-circle',
          'delete': 'fas fa-trash-alt',
          'error': 'fas fa-exclamation-triangle',
          'warning': 'fas fa-exclamation-circle',
          'info': 'fas fa-info-circle'
        };
        
        return iconMap[type] || 'fas fa-info-circle';
      };
  
      const fetchDashboardData = async () => {
        try {
          isLoading.value = true;
          
          // Fetch dashboard statistics
          // Di implementasi nyata, ini akan memanggil API
          const response = await axios.get('/api/admin/dashboard/stats');
          stats.value = response.data;
          
          // Untuk demo, gunakan data contoh
          setTimeout(() => {
            stats.value = {
              users: 158,
              settings: 42,
              products: 287,
              systemStatus: 'normal'
            };
            
            serverUptime.value = 'Uptime: 14 hari, 8 jam';
            
            activities.value = [
              {
                type: 'login',
                message: 'Login ke sistem',
                user: 'admin@example.com',
                time: '10 menit yang lalu'
              },
              {
                type: 'update',
                message: 'Memperbarui pengaturan inventaris',
                user: 'operator1@example.com',
                time: '35 menit yang lalu'
              },
              {
                type: 'create',
                message: 'Menambahkan pengguna baru',
                user: 'admin@example.com',
                time: '2 jam yang lalu'
              },
              {
                type: 'warning',
                message: 'Percobaan login gagal berulang',
                user: 'unknown@example.com',
                time: '3 jam yang lalu'
              },
              {
                type: 'delete',
                message: 'Menghapus produk tidak aktif',
                user: 'manager@example.com',
                time: '1 hari yang lalu'
              }
            ];
            
            systemServices.value = [
              {
                name: 'Database',
                description: 'MySQL Database Server',
                status: 'normal',
                statusText: 'Berjalan normal'
              },
              {
                name: 'Cache',
                description: 'Redis Cache Server',
                status: 'normal',
                statusText: 'Berjalan normal'
              },
              {
                name: 'Storage',
                description: 'Disk Storage',
                status: 'warning',
                statusText: 'Kapasitas 85%'
              },
              {
                name: 'Email',
                description: 'Email Service',
                status: 'normal',
                statusText: 'Berjalan normal'
              },
              {
                name: 'Queue',
                description: 'Job Queue',
                status: 'normal',
                statusText: 'Berjalan normal'
              },
              {
                name: 'Backup',
                description: 'Automated Backup',
                status: 'normal',
                statusText: 'Terakhir: 6 jam lalu'
              }
            ];
            
            isLoading.value = false;
          }, 1000);
          
        } catch (error) {
          console.error('Error fetching dashboard data:', error);
          isLoading.value = false;
        }
      };
  
      onMounted(() => {
        fetchDashboardData();
      });
  
      return {
        isLoading,
        stats,
        activities,
        systemServices,
        serverUptime,
        systemStatus,
        getActivityIcon
      };
    }
  };
  </script>
  
  <style scoped>
  .admin-dashboard {
    max-width: 100%;
    position: relative;
  }
  
  .page-header {
    margin-bottom: 2rem;
  }
  
  .page-header h1 {
    margin-bottom: 0.5rem;
    color: var(--gray-800);
  }
  
  .page-header p {
    color: var(--gray-600);
  }
  
  .dashboard-stats {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
    gap: 1.5rem;
    margin-bottom: 2rem;
  }
  
  .stat-card {
    background-color: white;
    border-radius: 0.5rem;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
    padding: 1.5rem;
    display: flex;
    gap: 1rem;
    transition: transform 0.2s, box-shadow 0.2s;
  }
  
  .stat-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
  }
  
  .stat-icon {
    background-color: rgba(37, 99, 235, 0.1);
    color: var(--primary-color);
    width: 3rem;
    height: 3rem;
    border-radius: 0.5rem;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.5rem;
  }
  
  .stat-content {
    flex: 1;
  }
  
  .stat-content h3 {
    margin: 0 0 0.5rem 0;
    font-size: 0.875rem;
    color: var(--gray-600);
    font-weight: 500;
  }
  
  .stat-value {
    font-size: 1.5rem;
    font-weight: 700;
    margin-bottom: 0.25rem;
    color: var(--gray-800);
    display: flex;
    align-items: center;
  }
  
  .stat-label {
    font-size: 0.75rem;
    color: var(--gray-500);
  }
  
  .dashboard-sections {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 1.5rem;
    margin-bottom: 2rem;
  }
  
  .dashboard-section {
    background-color: white;
    border-radius: 0.5rem;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
    padding: 1.5rem;
  }
  
  .section-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 1.5rem;
  }
  
  .section-header h2 {
    margin: 0;
    font-size: 1.125rem;
    font-weight: 600;
  }
  
  .btn-link {
    color: var(--primary-color);
    background: none;
    border: none;
    padding: 0;
    cursor: pointer;
    font-size: 0.875rem;
    font-weight: 500;
  }
  
  .btn-link:hover {
    text-decoration: underline;
  }
  
  .activity-list {
    display: flex;
    flex-direction: column;
    gap: 1rem;
  }
  
  .activity-item {
    display: flex;
    gap: 1rem;
    padding-bottom: 1rem;
    border-bottom: 1px solid var(--gray-100);
  }
  
  .activity-item:last-child {
    border-bottom: none;
    padding-bottom: 0;
  }
  
  .activity-icon {
    width: 2.5rem;
    height: 2.5rem;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 1rem;
  }
  
  .activity-icon.login {
    background-color: #10b981;
  }
  
  .activity-icon.update {
    background-color: #3b82f6;
  }
  
  .activity-icon.create {
    background-color: #8b5cf6;
  }
  
  .activity-icon.delete {
    background-color: #ef4444;
  }
  
  .activity-icon.warning {
    background-color: #f59e0b;
  }
  
  .activity-icon.error {
    background-color: #dc2626;
  }
  
  .activity-icon.info {
    background-color: #3b82f6;
  }
  
  .activity-details {
    flex: 1;
  }
  
  .activity-message {
    font-weight: 500;
    color: var(--gray-800);
    margin-bottom: 0.25rem;
  }
  
  .activity-meta {
    display: flex;
    font-size: 0.75rem;
    color: var(--gray-500);
  }
  
  .activity-user {
    margin-right: 0.5rem;
  }
  
  .activity-time:before {
    content: '•';
    margin-right: 0.5rem;
  }
  
  .loading-indicator {
    display: flex;
    justify-content: center;
    padding: 2rem 0;
    color: var(--gray-400);
  }
  
  .empty-state {
    display: flex;
    justify-content: center;
    padding: 2rem 0;
    color: var(--gray-500);
    font-style: italic;
  }
  
  .shortcuts-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(120px, 1fr));
    gap: 1rem;
  }
  
  .shortcut-card {
    background-color: #f1f5f9;
    border-radius: 0.5rem;
    padding: 1rem;
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 0.5rem;
    text-decoration: none;
    color: var(--gray-700);
    text-align: center;
    transition: background-color 0.2s, transform 0.2s;
  }
  
  .shortcut-card:hover {
    background-color: #e0e7ff;
    transform: translateY(-2px);
  }
  
  .shortcut-card i {
    font-size: 1.5rem;
    color: var(--primary-color);
  }
  
  .shortcut-card span {
    font-size: 0.75rem;
    font-weight: 500;
  }
  
  .system-status-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
    gap: 1rem;
  }
  
  .system-status-card {
    background-color: #f1f5f9;
    border-radius: 0.5rem;
    padding: 1rem;
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
  }
  
  .service-name {
    font-weight: 600;
    color: var(--gray-800);
  }
  
  .service-description {
    font-size: 0.75rem;
    color: var(--gray-600);
    margin-bottom: 0.5rem;
  }
  
  .service-status {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    font-size: 0.875rem;
    color: var(--gray-700);
  }
  
  .status-indicator {
    display: inline-block;
    width: 0.75rem;
    height: 0.75rem;
    border-radius: 50%;
    margin-right: 0.5rem;
  }
  
  .status-indicator.normal,
  .status-indicator.status-normal {
    background-color: #10b981;
  }
  
  .status-indicator.warning,
  .status-indicator.status-warning {
    background-color: #f59e0b;
  }
  
  .status-indicator.critical,
  .status-indicator.status-critical {
    background-color: #ef4444;
  }
  
  @media (max-width: 768px) {
    .dashboard-stats {
      grid-template-columns: 1fr;
    }
    
    .dashboard-sections {
      grid-template-columns: 1fr;
    }
    
    .shortcuts-grid {
      grid-template-columns: repeat(2, 1fr);
    }
    
    .system-status-grid {
      grid-template-columns: 1fr;
    }
  }
  </style>