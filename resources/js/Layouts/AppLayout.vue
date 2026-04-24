<template>
  <div class="app-layout">
    <!-- Mobile Overlay -->
    <div
      v-if="sidebarOpen"
      class="sidebar-overlay"
      @click="sidebarOpen = false"
    />

    <!-- Sidebar -->
    <aside :class="['sidebar', { open: sidebarOpen }]">
      <div class="sidebar-header">
        <div class="brand">
          <div class="brand-icon">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" width="24" height="24">
              <path d="M11.47 3.84a.75.75 0 011.06 0l8.69 8.69a.75.75 0 101.06-1.06l-8.689-8.69a2.25 2.25 0 00-3.182 0l-8.69 8.69a.75.75 0 001.061 1.06l8.69-8.69z" />
              <path d="M12 5.432l8.159 8.159c.03.03.06.058.091.086v6.198c0 1.035-.84 1.875-1.875 1.875H15a.75.75 0 01-.75-.75v-4.5a.75.75 0 00-.75-.75h-3a.75.75 0 00-.75.75V21a.75.75 0 01-.75.75H5.625a1.875 1.875 0 01-1.875-1.875v-6.198a2.29 2.29 0 00.091-.086L12 5.432z" />
            </svg>
          </div>
          <div class="brand-text">
            <h1>Dlatif POS</h1>
            <span>Kosmetik & Aksesoris</span>
          </div>
        </div>
      </div>

      <nav class="sidebar-nav">
        <div class="nav-section">
          <span class="nav-section-title">Menu Utama</span>
          <Link
            :href="route('dashboard')"
            :class="['nav-item', { active: isActive('dashboard') }]"
          >
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor"><path d="M11.47 3.84a.75.75 0 011.06 0l8.69 8.69a.75.75 0 101.06-1.06l-8.689-8.69a2.25 2.25 0 00-3.182 0l-8.69 8.69a.75.75 0 001.061 1.06l8.69-8.69z" /><path d="M12 5.432l8.159 8.159c.03.03.06.058.091.086v6.198c0 1.035-.84 1.875-1.875 1.875H15a.75.75 0 01-.75-.75v-4.5a.75.75 0 00-.75-.75h-3a.75.75 0 00-.75.75V21a.75.75 0 01-.75.75H5.625a1.875 1.875 0 01-1.875-1.875v-6.198a2.29 2.29 0 00.091-.086L12 5.432z" /></svg>
            <span>Dashboard</span>
          </Link>
          <Link
            href="/pos"
            :class="['nav-item', { active: isActive('pos') }]"
          >
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor"><path d="M2.25 2.25a.75.75 0 000 1.5h1.386c.17 0 .318.114.362.278l2.558 9.592a3.752 3.752 0 00-2.806 3.63c0 .414.336.75.75.75h15.75a.75.75 0 000-1.5H5.378A2.25 2.25 0 017.5 15h11.218a.75.75 0 00.674-.421 60.358 60.358 0 002.96-7.228.75.75 0 00-.525-.965A60.864 60.864 0 005.68 4.509l-.232-.867A1.875 1.875 0 003.636 2.25H2.25zM3.75 20.25a1.5 1.5 0 113 0 1.5 1.5 0 01-3 0zM16.5 20.25a1.5 1.5 0 113 0 1.5 1.5 0 01-3 0z" /></svg>
            <span>Kasir (POS)</span>
          </Link>
        </div>

        <div class="nav-section">
          <span class="nav-section-title">Manajemen</span>
          <Link
            :href="route('products.index')"
            :class="['nav-item', { active: isActive('products') }]"
          >
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor"><path fill-rule="evenodd" d="M7.5 6v.75H5.513c-.96 0-1.764.724-1.865 1.679l-1.263 12A1.875 1.875 0 004.25 22.5h15.5a1.875 1.875 0 001.865-2.071l-1.263-12a1.875 1.875 0 00-1.865-1.679H16.5V6a4.5 4.5 0 10-9 0zM12 3a3 3 0 00-3 3v.75h6V6a3 3 0 00-3-3zm-3 8.25a3 3 0 106 0v.75a.75.75 0 01-1.5 0v-.75a1.5 1.5 0 00-3 0v.75a.75.75 0 01-1.5 0v-.75z" clip-rule="evenodd" /></svg>
            <span>Produk</span>
          </Link>
          <Link
            href="#"
            :class="['nav-item', { active: isActive('transactions') }]"
          >
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor"><path fill-rule="evenodd" d="M5.625 1.5c-1.036 0-1.875.84-1.875 1.875v17.25c0 1.035.84 1.875 1.875 1.875h12.75c1.035 0 1.875-.84 1.875-1.875V12.75A3.75 3.75 0 0016.5 9h-1.875a1.875 1.875 0 01-1.875-1.875V5.25A3.75 3.75 0 009 1.5H5.625zM7.5 15a.75.75 0 01.75-.75h7.5a.75.75 0 010 1.5h-7.5A.75.75 0 017.5 15zm.75 2.25a.75.75 0 000 1.5H12a.75.75 0 000-1.5H8.25z" clip-rule="evenodd" /><path d="M12.971 1.816A5.23 5.23 0 0114.25 5.25v1.875c0 .207.168.375.375.375H16.5a5.23 5.23 0 013.434 1.279 9.768 9.768 0 00-6.963-6.963z" /></svg>
            <span>Transaksi</span>
            <span class="badge soon">Soon</span>
          </Link>
          <Link
            href="#"
            :class="['nav-item', { active: isActive('reports') }]"
          >
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor"><path fill-rule="evenodd" d="M2.25 13.5a8.25 8.25 0 018.25-8.25.75.75 0 01.75.75v6.75H18a.75.75 0 01.75.75 8.25 8.25 0 01-16.5 0z" clip-rule="evenodd" /><path fill-rule="evenodd" d="M12.75 3a.75.75 0 01.75-.75 8.25 8.25 0 018.25 8.25.75.75 0 01-.75.75h-7.5a.75.75 0 01-.75-.75V3z" clip-rule="evenodd" /></svg>
            <span>Laporan</span>
            <span class="badge soon">Soon</span>
          </Link>
        </div>
      </nav>

      <div class="sidebar-footer">
        <div class="version-info">
          <span>v1.0.0</span>
        </div>
      </div>
    </aside>

    <!-- Main Content -->
    <div class="main-wrapper">
      <header class="topbar">
        <button class="menu-toggle" @click="sidebarOpen = !sidebarOpen">
          <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" width="24" height="24"><path fill-rule="evenodd" d="M3 6.75A.75.75 0 013.75 6h16.5a.75.75 0 010 1.5H3.75A.75.75 0 013 6.75zM3 12a.75.75 0 01.75-.75h16.5a.75.75 0 010 1.5H3.75A.75.75 0 013 12zm0 5.25a.75.75 0 01.75-.75h16.5a.75.75 0 010 1.5H3.75a.75.75 0 01-.75-.75z" clip-rule="evenodd" /></svg>
        </button>
        <div class="topbar-title">
          <h2>{{ title }}</h2>
        </div>
        <div class="topbar-actions">
          <slot name="actions" />
        </div>
      </header>

      <!-- Flash Messages -->
      <Transition name="flash">
        <div v-if="$page.props.flash?.success" class="flash-message success">
          <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" width="20" height="20"><path fill-rule="evenodd" d="M2.25 12c0-5.385 4.365-9.75 9.75-9.75s9.75 4.365 9.75 9.75-4.365 9.75-9.75 9.75S2.25 17.385 2.25 12zm13.36-1.814a.75.75 0 10-1.22-.872l-3.236 4.53L9.53 12.22a.75.75 0 00-1.06 1.06l2.25 2.25a.75.75 0 001.14-.094l3.75-5.25z" clip-rule="evenodd" /></svg>
          <span>{{ $page.props.flash.success }}</span>
        </div>
      </Transition>

      <main class="main-content">
        <slot />
      </main>
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';

const props = defineProps({
  title: {
    type: String,
    default: 'Dashboard',
  },
});

const sidebarOpen = ref(false);
const page = usePage();

function isActive(routeName) {
  const url = page.url;
  if (routeName === 'dashboard') return url === '/';
  return url.startsWith(`/${routeName}`);
}

function route(name, params) {
  const routes = {
    dashboard: '/',
    'products.index': '/products',
    'products.create': '/products/create',
  };
  return routes[name] || '#';
}
</script>

<style scoped>
.app-layout {
  display: flex;
  min-height: 100vh;
  background: #0f0f12;
  color: #e4e4e7;
}

/* Sidebar */
.sidebar {
  width: 260px;
  background: linear-gradient(180deg, #18181b 0%, #1a1a2e 100%);
  border-right: 1px solid rgba(255, 255, 255, 0.06);
  display: flex;
  flex-direction: column;
  position: fixed;
  top: 0;
  left: 0;
  bottom: 0;
  z-index: 40;
  transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

.sidebar-overlay {
  position: fixed;
  inset: 0;
  background: rgba(0, 0, 0, 0.5);
  backdrop-filter: blur(4px);
  z-index: 35;
  display: none;
}

.sidebar-header {
  padding: 1.25rem 1.25rem 1rem;
  border-bottom: 1px solid rgba(255, 255, 255, 0.06);
}

.brand {
  display: flex;
  align-items: center;
  gap: 0.75rem;
}

.brand-icon {
  width: 40px;
  height: 40px;
  background: linear-gradient(135deg, #a855f7, #ec4899);
  border-radius: 12px;
  display: flex;
  align-items: center;
  justify-content: center;
  color: white;
  flex-shrink: 0;
}

.brand-text h1 {
  font-size: 1.1rem;
  font-weight: 700;
  color: white;
  margin: 0;
  line-height: 1.2;
}

.brand-text span {
  font-size: 0.7rem;
  color: #a1a1aa;
  text-transform: uppercase;
  letter-spacing: 0.05em;
}

.sidebar-nav {
  flex: 1;
  overflow-y: auto;
  padding: 0.75rem;
}

.nav-section {
  margin-bottom: 1.25rem;
}

.nav-section-title {
  display: block;
  font-size: 0.65rem;
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: 0.08em;
  color: #71717a;
  padding: 0.5rem 0.75rem 0.4rem;
}

.nav-item {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  padding: 0.65rem 0.75rem;
  border-radius: 10px;
  color: #a1a1aa;
  text-decoration: none;
  font-size: 0.875rem;
  font-weight: 500;
  transition: all 0.2s ease;
  margin-bottom: 2px;
}

.nav-item svg {
  width: 20px;
  height: 20px;
  flex-shrink: 0;
}

.nav-item:hover {
  background: rgba(255, 255, 255, 0.05);
  color: #e4e4e7;
}

.nav-item.active {
  background: linear-gradient(135deg, rgba(168, 85, 247, 0.15), rgba(236, 72, 153, 0.1));
  color: #c084fc;
  border: 1px solid rgba(168, 85, 247, 0.2);
}

.badge {
  margin-left: auto;
  font-size: 0.6rem;
  padding: 0.15rem 0.45rem;
  border-radius: 6px;
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: 0.03em;
}

.badge.soon {
  background: rgba(250, 204, 21, 0.12);
  color: #facc15;
  border: 1px solid rgba(250, 204, 21, 0.2);
}

.sidebar-footer {
  padding: 1rem 1.25rem;
  border-top: 1px solid rgba(255, 255, 255, 0.06);
}

.version-info {
  font-size: 0.7rem;
  color: #52525b;
  text-align: center;
}

/* Main Wrapper */
.main-wrapper {
  flex: 1;
  margin-left: 260px;
  display: flex;
  flex-direction: column;
  min-height: 100vh;
}

/* Topbar */
.topbar {
  height: 64px;
  background: rgba(24, 24, 27, 0.8);
  backdrop-filter: blur(12px);
  border-bottom: 1px solid rgba(255, 255, 255, 0.06);
  display: flex;
  align-items: center;
  padding: 0 1.5rem;
  gap: 1rem;
  position: sticky;
  top: 0;
  z-index: 20;
}

.menu-toggle {
  display: none;
  background: none;
  border: none;
  color: #a1a1aa;
  cursor: pointer;
  padding: 0.5rem;
  border-radius: 8px;
  transition: all 0.2s;
}

.menu-toggle:hover {
  background: rgba(255, 255, 255, 0.05);
  color: white;
}

.topbar-title h2 {
  font-size: 1.1rem;
  font-weight: 600;
  color: white;
  margin: 0;
}

.topbar-actions {
  margin-left: auto;
  display: flex;
  align-items: center;
  gap: 0.75rem;
}

/* Flash Messages */
.flash-message {
  margin: 1rem 1.5rem 0;
  padding: 0.75rem 1rem;
  border-radius: 10px;
  display: flex;
  align-items: center;
  gap: 0.5rem;
  font-size: 0.875rem;
  animation: slideDown 0.3s ease;
}

.flash-message.success {
  background: rgba(34, 197, 94, 0.1);
  border: 1px solid rgba(34, 197, 94, 0.2);
  color: #4ade80;
}

@keyframes slideDown {
  from { opacity: 0; transform: translateY(-10px); }
  to { opacity: 1; transform: translateY(0); }
}

.flash-enter-active { animation: slideDown 0.3s ease; }
.flash-leave-active { animation: slideDown 0.3s ease reverse; }

/* Main Content */
.main-content {
  flex: 1;
  padding: 1.5rem;
}

/* Mobile Responsive */
@media (max-width: 768px) {
  .sidebar {
    transform: translateX(-100%);
  }

  .sidebar.open {
    transform: translateX(0);
  }

  .sidebar-overlay {
    display: block;
  }

  .main-wrapper {
    margin-left: 0;
  }

  .menu-toggle {
    display: block;
  }
}
</style>
