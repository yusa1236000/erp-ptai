<!-- src/layouts/AdminAppLayout.vue -->
<template>
    <div class="app-container">
      <aside
        class="sidebar"
        :class="{ 'sidebar-collapsed': sidebarCollapsed }"
      >
        <div class="sidebar-header">
          <div class="brand-logo">
            <span v-if="!sidebarCollapsed">ERP Admin</span>
            <span v-else>EA</span>
          </div>
          <button class="collapse-btn" @click="toggleSidebar">
            <i
              :class="
                sidebarCollapsed
                  ? 'fas fa-angle-right'
                  : 'fas fa-angle-left'
              "
            ></i>
          </button>
        </div>
        <nav class="sidebar-menu">
          <router-link
            to="/admin/dashboard"
            class="menu-item"
            active-class="active"
          >
            <i class="fas fa-tachometer-alt"></i>
            <span v-if="!sidebarCollapsed">Dashboard</span>
          </router-link>
  
          <!-- User Management Section -->
          <div class="menu-section">
            <div
              @click="toggleMenuSection('users')"
              class="section-header"
            >
              <div class="section-title-container">
                <i class="fas fa-users"></i>
                <span v-if="!sidebarCollapsed" class="section-title"
                  >Users</span
                >
              </div>
              <i
                v-if="!sidebarCollapsed"
                :class="
                  menuSections.users
                    ? 'fas fa-chevron-down'
                    : 'fas fa-chevron-right'
                "
                class="section-icon"
              ></i>
            </div>
          </div>
  
          <div
            v-show="!sidebarCollapsed && menuSections.users"
            class="submenu"
          >
            <router-link
              to="/admin/users"
              class="menu-item"
              active-class="active"
            >
              <i class="fas fa-user"></i>
              <span v-if="!sidebarCollapsed">User List</span>
            </router-link>
  
            <router-link
              to="/admin/users/roles"
              class="menu-item"
              active-class="active"
            >
              <i class="fas fa-user-tag"></i>
              <span v-if="!sidebarCollapsed">Roles & Permissions</span>
            </router-link>
          </div>
  
          <!-- System Settings Section -->
          <div class="menu-section">
            <div
              @click="toggleMenuSection('settings')"
              class="section-header"
            >
              <div class="section-title-container">
                <i class="fas fa-cogs"></i>
                <span v-if="!sidebarCollapsed" class="section-title"
                  >Settings</span
                >
              </div>
              <i
                v-if="!sidebarCollapsed"
                :class="
                  menuSections.settings
                    ? 'fas fa-chevron-down'
                    : 'fas fa-chevron-right'
                "
                class="section-icon"
              ></i>
            </div>
          </div>
  
          <div
            v-show="!sidebarCollapsed && menuSections.settings"
            class="submenu"
          >
            <router-link
              to="/admin/settings"
              class="menu-item"
              active-class="active"
            >
              <i class="fas fa-sliders-h"></i>
              <span v-if="!sidebarCollapsed">System Settings</span>
            </router-link>
  
            <router-link
              to="/admin/settings/integrations"
              class="menu-item"
              active-class="active"
            >
              <i class="fas fa-plug"></i>
              <span v-if="!sidebarCollapsed">Integrations</span>
            </router-link>
  
            <router-link
              to="/admin/settings/backup"
              class="menu-item"
              active-class="active"
            >
              <i class="fas fa-database"></i>
              <span v-if="!sidebarCollapsed">Backup & Recovery</span>
            </router-link>
          </div>
  
          <!-- Logs and Monitoring Section -->
          <div class="menu-section">
            <div
              @click="toggleMenuSection('logs')"
              class="section-header"
            >
              <div class="section-title-container">
                <i class="fas fa-clipboard-list"></i>
                <span v-if="!sidebarCollapsed" class="section-title"
                  >Logs & Monitoring</span
                >
              </div>
              <i
                v-if="!sidebarCollapsed"
                :class="
                  menuSections.logs
                    ? 'fas fa-chevron-down'
                    : 'fas fa-chevron-right'
                "
                class="section-icon"
              ></i>
            </div>
          </div>
  
          <div
            v-show="!sidebarCollapsed && menuSections.logs"
            class="submenu"
          >
            <router-link
              to="/admin/logs/system"
              class="menu-item"
              active-class="active"
            >
              <i class="fas fa-clipboard-check"></i>
              <span v-if="!sidebarCollapsed">System Logs</span>
            </router-link>
  
            <router-link
              to="/admin/logs/access"
              class="menu-item"
              active-class="active"
            >
              <i class="fas fa-clipboard-list"></i>
              <span v-if="!sidebarCollapsed">Access Logs</span>
            </router-link>
  
            <router-link
              to="/admin/logs/audit"
              class="menu-item"
              active-class="active"
            >
              <i class="fas fa-history"></i>
              <span v-if="!sidebarCollapsed">Audit Trail</span>
            </router-link>
          </div>
  
          <!-- Back to Main App -->
          <router-link
            to="/"
            class="menu-item back-to-app"
            active-class="active"
          >
            <i class="fas fa-arrow-left"></i>
            <span v-if="!sidebarCollapsed">Back to Main App</span>
          </router-link>
        </nav>
      </aside>
  
      <div class="main-content">
        <header class="main-header">
          <div class="header-left">
            <h1 class="page-title">{{ pageTitle }}</h1>
          </div>
          <div class="header-right">
            <div class="user-menu" @click="toggleUserMenu">
              <img
                class="avatar"
                src="/images/user-avatar.png"
                alt="User avatar"
              />
              <span class="username">{{ user.name }}</span>
              <i class="fas fa-chevron-down"></i>
  
              <div v-if="userMenuOpen" class="dropdown-menu">
                <div class="dropdown-item">
                  <i class="fas fa-user"></i>
                  Profile
                </div>
                <div class="dropdown-item">
                  <i class="fas fa-cog"></i>
                  Settings
                </div>
                <div class="dropdown-divider"></div>
                <div class="dropdown-item" @click="logout">
                  <i class="fas fa-sign-out-alt"></i>
                  Logout
                </div>
              </div>
            </div>
          </div>
        </header>
  
        <main class="content">
          <router-view />
        </main>
      </div>
    </div>
  </template>
  
  <script>
  import { ref, computed, onMounted, reactive } from "vue";
  import { useRouter, useRoute } from "vue-router";
  import axios from "axios";
  
  export default {
    name: "AdminAppLayout",
    setup() {
      const router = useRouter();
      const route = useRoute();
      const sidebarCollapsed = ref(
        localStorage.getItem("adminSidebarCollapsed") === "true"
      );
      const userMenuOpen = ref(false);
      const user = ref(JSON.parse(localStorage.getItem("user") || "{}"));
  
      // Menu sections state
      const menuSections = reactive({
        users: false,
        settings: true, // Open settings by default
        logs: false,
      });
  
      // Automatically open submenu for active section
      const initializeActiveSection = () => {
        const path = route.path;
        if (path.includes("/admin/users")) {
          menuSections.users = true;
        } else if (path.includes("/admin/settings")) {
          menuSections.settings = true;
        } else if (path.includes("/admin/logs")) {
          menuSections.logs = true;
        }
      };
  
      const toggleMenuSection = (section) => {
        // If sidebar is collapsed, expand it first
        if (sidebarCollapsed.value) {
          sidebarCollapsed.value = false;
          localStorage.setItem("adminSidebarCollapsed", "false");
  
          // Set a short timeout to let the sidebar expand first
          setTimeout(() => {
            menuSections[section] = !menuSections[section];
          }, 50);
        } else {
          menuSections[section] = !menuSections[section];
        }
      };
  
      const pageTitle = computed(() => {
        switch (route.name) {
          case "AdminDashboard":
            return "Admin Dashboard";
          case "UserList":
            return "User Management";
          case "RolesPermissions":
            return "Roles & Permissions";
          case "SystemSettings":
            return "System Settings";
          case "Integrations":
            return "External Integrations";
          case "BackupRecovery":
            return "Backup & Recovery";
          case "SystemLogs":
            return "System Logs";
          case "AccessLogs":
            return "Access Logs";
          case "AuditTrail":
            return "Audit Trail";
          default:
            return "Admin Panel";
        }
      });
  
      const toggleSidebar = () => {
        sidebarCollapsed.value = !sidebarCollapsed.value;
        localStorage.setItem("adminSidebarCollapsed", sidebarCollapsed.value);
      };
  
      const toggleUserMenu = () => {
        userMenuOpen.value = !userMenuOpen.value;
      };
  
      const logout = async () => {
        try {
          await axios.post("/api/auth/logout");
        } catch (error) {
          console.error("Logout error:", error);
        } finally {
          localStorage.removeItem("token");
          localStorage.removeItem("user");
          axios.defaults.headers.common["Authorization"] = "";
          router.push("/login");
        }
      };
  
      // Close dropdown when clicking outside
      onMounted(() => {
        document.addEventListener("click", (event) => {
          const userMenu = document.querySelector(".user-menu");
          if (userMenu && !userMenu.contains(event.target)) {
            userMenuOpen.value = false;
          }
        });
  
        initializeActiveSection();
      });
  
      return {
        sidebarCollapsed,
        userMenuOpen,
        user,
        pageTitle,
        menuSections,
        toggleMenuSection,
        toggleSidebar,
        toggleUserMenu,
        logout,
      };
    },
  };
  </script>
  
  <style scoped>
  .app-container {
    display: flex;
    height: 100vh;
    overflow: hidden;
  }
  
  .sidebar {
    width: 250px;
    background-color: #1e293b;
    color: #f8fafc;
    display: flex;
    flex-direction: column;
    transition: width 0.3s ease;
  }
  
  .sidebar-collapsed {
    width: 70px;
  }
  
  .sidebar-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 1rem;
    border-bottom: 1px solid #334155;
  }
  
  .brand-logo {
    font-size: 1.25rem;
    font-weight: 700;
    white-space: nowrap;
    overflow: hidden;
  }
  
  .collapse-btn {
    background: transparent;
    border: none;
    color: #f8fafc;
    cursor: pointer;
    font-size: 1rem;
    padding: 0.25rem;
  }
  
  .sidebar-menu {
    flex: 1;
    overflow-y: auto;
    padding: 1rem 0;
  }
  
  .menu-section {
    margin-top: 0.5rem;
  }
  
  .section-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 0.75rem 1rem;
    color: #94a3b8;
    cursor: pointer;
    transition: background-color 0.2s;
  }
  
  .section-header:hover {
    background-color: #334155;
  }
  
  .section-title-container {
    display: flex;
    align-items: center;
    gap: 1rem;
  }
  
  .section-title {
    font-size: 0.75rem;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    font-weight: 600;
  }
  
  .section-icon {
    font-size: 0.75rem;
  }
  
  .submenu {
    padding-left: 0.5rem;
  }
  
  .menu-item {
    display: flex;
    align-items: center;
    padding: 0.75rem 1rem;
    color: #e2e8f0;
    text-decoration: none;
    transition: background-color 0.2s;
  }
  
  .menu-item:hover {
    background-color: #334155;
  }
  
  .menu-item.active {
    background-color: #2563eb;
    font-weight: 500;
  }
  
  .menu-item i {
    font-size: 1rem;
    margin-right: 1rem;
    width: 20px;
    text-align: center;
  }
  
  .back-to-app {
    margin-top: 2rem;
    border-top: 1px solid #334155;
    padding-top: 1rem;
  }
  
  .main-content {
    flex: 1;
    display: flex;
    flex-direction: column;
    overflow: hidden;
    background-color: #f1f5f9;
  }
  
  .main-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 1rem 2rem;
    background-color: white;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
    z-index: 10;
  }
  
  .page-title {
    font-size: 1.5rem;
    font-weight: 600;
    color: #1e293b;
    margin: 0;
  }
  
  .user-menu {
    display: flex;
    align-items: center;
    cursor: pointer;
    position: relative;
  }
  
  .avatar {
    width: 2.5rem;
    height: 2.5rem;
    border-radius: 50%;
    margin-right: 0.75rem;
  }
  
  .username {
    font-weight: 500;
    margin-right: 0.5rem;
  }
  
  .dropdown-menu {
    position: absolute;
    top: 100%;
    right: 0;
    margin-top: 0.5rem;
    background-color: white;
    border-radius: 0.375rem;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    width: 12rem;
    z-index: 20;
  }
  
  .dropdown-item {
    padding: 0.75rem 1rem;
    display: flex;
    align-items: center;
    color: #1e293b;
    transition: background-color 0.2s;
  }
  
  .dropdown-item i {
    width: 1.25rem;
    margin-right: 0.75rem;
  }
  
  .dropdown-item:hover {
    background-color: #f1f5f9;
  }
  
  .dropdown-divider {
    height: 1px;
    background-color: #e2e8f0;
    margin: 0.25rem 0;
  }
  
  .content {
    flex: 1;
    padding: 2rem;
    overflow-y: auto;
  }
  
  @media (max-width: 768px) {
    .sidebar {
      position: fixed;
      height: 100vh;
      z-index: 30;
      transform: translateX(0);
      transition: transform 0.3s ease;
    }
  
    .sidebar-collapsed {
      transform: translateX(-100%);
    }
  
    .main-header {
      padding: 1rem;
    }
  
    .content {
      padding: 1rem;
    }
  }
  </style>