<template>
  <div :class="['app-layout', { collapsed: sidebarCollapsed, light: isLight }]">
    <!-- Mobile Overlay -->
    <div v-if="sidebarOpen" class="sidebar-overlay" @click="sidebarOpen = false" />

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
            <h1>Dlatif Store</h1>
            <span>Skincare, Aksesoris, & ATK</span>
          </div>
        </div>
      </div>

      <nav class="sidebar-nav">
        <div class="nav-section">
          <span class="nav-section-title">Menu Utama</span>
          <Link :href="route('dashboard')" :class="['nav-item', { active: isActive('dashboard') }]">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor"><path d="M11.47 3.84a.75.75 0 011.06 0l8.69 8.69a.75.75 0 101.06-1.06l-8.689-8.69a2.25 2.25 0 00-3.182 0l-8.69 8.69a.75.75 0 001.061 1.06l8.69-8.69z" /><path d="M12 5.432l8.159 8.159c.03.03.06.058.091.086v6.198c0 1.035-.84 1.875-1.875 1.875H15a.75.75 0 01-.75-.75v-4.5a.75.75 0 00-.75-.75h-3a.75.75 0 00-.75.75V21a.75.75 0 01-.75.75H5.625a1.875 1.875 0 01-1.875-1.875v-6.198a2.29 2.29 0 00.091-.086L12 5.432z" /></svg>
            <span>Dashboard</span>
          </Link>
          <Link href="/pos" :class="['nav-item', { active: isActive('pos') }]">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor"><path d="M2.25 2.25a.75.75 0 000 1.5h1.386c.17 0 .318.114.362.278l2.558 9.592a3.752 3.752 0 00-2.806 3.63c0 .414.336.75.75.75h15.75a.75.75 0 000-1.5H5.378A2.25 2.25 0 017.5 15h11.218a.75.75 0 00.674-.421 60.358 60.358 0 002.96-7.228.75.75 0 00-.525-.965A60.864 60.864 0 005.68 4.509l-.232-.867A1.875 1.875 0 003.636 2.25H2.25zM3.75 20.25a1.5 1.5 0 113 0 1.5 1.5 0 01-3 0zM16.5 20.25a1.5 1.5 0 113 0 1.5 1.5 0 01-3 0z" /></svg>
            <span>Kasir (POS)</span>
          </Link>
        </div>

        <div class="nav-section">
          <span class="nav-section-title">Inventori</span>
          <Link :href="route('products.index')" :class="['nav-item', { active: isActive('products') }]">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor"><path fill-rule="evenodd" d="M7.5 6v.75H5.513c-.96 0-1.764.724-1.865 1.679l-1.263 12A1.875 1.875 0 004.25 22.5h15.5a1.875 1.875 0 001.865-2.071l-1.263-12a1.875 1.875 0 00-1.865-1.679H16.5V6a4.5 4.5 0 10-9 0zM12 3a3 3 0 00-3 3v.75h6V6a3 3 0 00-3-3zm-3 8.25a3 3 0 106 0v.75a.75.75 0 01-1.5 0v-.75a1.5 1.5 0 00-3 0v.75a.75.75 0 01-1.5 0v-.75z" clip-rule="evenodd" /></svg>
            <span>Produk</span>
          </Link>
          <Link href="/categories" :class="['nav-item', { active: isActive('categories') }]">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor"><path fill-rule="evenodd" d="M5.25 2.25a3 3 0 00-3 3v4.318a3 3 0 00.879 2.121l9.58 9.581c.92.92 2.39.986 3.39.152l6.13-5.105c1.04-.867 1.12-2.418.18-3.39L12.83 3.349a3.001 3.001 0 00-2.121-.879H5.25zM6.375 7.5a1.125 1.125 0 100-2.25 1.125 1.125 0 000 2.25z" clip-rule="evenodd" /></svg>
            <span>Kategori</span>
          </Link>
          <Link href="/suppliers" :class="['nav-item', { active: isActive('suppliers') }]">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor"><path fill-rule="evenodd" d="M7.5 6a4.5 4.5 0 119 0 4.5 4.5 0 01-9 0zM3.751 20.105a8.25 8.25 0 0116.498 0 .75.75 0 01-.437.695A18.683 18.683 0 0112 22.5c-2.786 0-5.433-.608-7.812-1.7a.75.75 0 01-.437-.695z" clip-rule="evenodd" /></svg>
            <span>Supplier</span>
          </Link>
          <Link href="/purchases" :class="['nav-item', { active: isActive('purchases') }]">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor"><path d="M3.375 4.5C2.339 4.5 1.5 5.34 1.5 6.375V13.5h12V6.375c0-1.036-.84-1.875-1.875-1.875h-8.25zM13.5 15h-12v2.625c0 1.035.84 1.875 1.875 1.875h8.25c1.035 0 1.875-.84 1.875-1.875V15z" /><path d="M8.25 19.5a3.75 3.75 0 10-7.5 0 3.75 3.75 0 007.5 0zM15.75 6.75a.75.75 0 00-.75.75v11.25c0 .087.015.17.042.248a3 3 0 015.958.464c.853-.175 1.522-.935 1.5-1.849V8.249a1.5 1.5 0 00-1.218-1.473L17.25 6.003V6.75z" /><path d="M19.5 19.5a1.5 1.5 0 11-3 0 1.5 1.5 0 013 0zM4.5 19.5a1.5 1.5 0 11-3 0 1.5 1.5 0 013 0z" /></svg>
            <span>Pembelian</span>
          </Link>
        </div>

        <div class="nav-section">
          <span class="nav-section-title">Penjualan</span>
          <Link href="/transactions" :class="['nav-item', { active: isActive('transactions') }]">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor"><path fill-rule="evenodd" d="M5.625 1.5c-1.036 0-1.875.84-1.875 1.875v17.25c0 1.035.84 1.875 1.875 1.875h12.75c1.035 0 1.875-.84 1.875-1.875V12.75A3.75 3.75 0 0016.5 9h-1.875a1.875 1.875 0 01-1.875-1.875V5.25A3.75 3.75 0 009 1.5H5.625zM7.5 15a.75.75 0 01.75-.75h7.5a.75.75 0 010 1.5h-7.5A.75.75 0 017.5 15zm.75 2.25a.75.75 0 000 1.5H12a.75.75 0 000-1.5H8.25z" clip-rule="evenodd" /><path d="M12.971 1.816A5.23 5.23 0 0114.25 5.25v1.875c0 .207.168.375.375.375H16.5a5.23 5.23 0 013.434 1.279 9.768 9.768 0 00-6.963-6.963z" /></svg>
            <span>Transaksi</span>
          </Link>
          <Link href="/reports" :class="['nav-item', { active: isActive('reports') }]">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor"><path fill-rule="evenodd" d="M2.25 13.5a8.25 8.25 0 018.25-8.25.75.75 0 01.75.75v6.75H18a.75.75 0 01.75.75 8.25 8.25 0 01-16.5 0z" clip-rule="evenodd" /><path fill-rule="evenodd" d="M12.75 3a.75.75 0 01.75-.75 8.25 8.25 0 018.25 8.25.75.75 0 01-.75.75h-7.5a.75.75 0 01-.75-.75V3z" clip-rule="evenodd" /></svg>
            <span>Laporan</span>
          </Link>
        </div>
      </nav>

      <div class="sidebar-footer">
        <Link href="/logout" method="post" as="button" class="nav-item logout-btn">
          <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor"><path fill-rule="evenodd" d="M7.5 3.75A1.5 1.5 0 006 5.25v13.5a1.5 1.5 0 001.5 1.5h6a1.5 1.5 0 001.5-1.5V15a.75.75 0 011.5 0v3.75a3 3 0 01-3 3h-6a3 3 0 01-3-3V5.25a3 3 0 013-3h6a3 3 0 013 3V9a.75.75 0 01-1.5 0V5.25a1.5 1.5 0 00-1.5-1.5h-6zm10.72 4.72a.75.75 0 011.06 0l3 3a.75.75 0 010 1.06l-3 3a.75.75 0 11-1.06-1.06l1.72-1.72H9a.75.75 0 010-1.5h10.94l-1.72-1.72a.75.75 0 010-1.06z" clip-rule="evenodd" /></svg>
          <span>Keluar</span>
        </Link>
        <button class="collapse-btn desktop-only" @click="toggleCollapse" :title="sidebarCollapsed ? 'Expand sidebar' : 'Collapse sidebar'">
          <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" width="18" height="18"><path fill-rule="evenodd" :d="sidebarCollapsed ? 'M13.28 11.47a.75.75 0 010 1.06l-7.5 7.5a.75.75 0 01-1.06-1.06L11.69 12 4.72 5.03a.75.75 0 011.06-1.06l7.5 7.5z' : 'M10.72 11.47a.75.75 0 000 1.06l7.5 7.5a.75.75 0 101.06-1.06L12.31 12l6.97-6.97a.75.75 0 00-1.06-1.06l-7.5 7.5z'" clip-rule="evenodd" /></svg>
        </button>
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
          <button class="theme-toggle" @click="toggleTheme" :title="isLight ? 'Dark mode' : 'Light mode'">
            <!-- Moon (show in light mode) -->
            <svg v-if="isLight" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" width="20" height="20"><path fill-rule="evenodd" d="M9.528 1.718a.75.75 0 01.162.819A8.97 8.97 0 009 6a9 9 0 009 9 8.97 8.97 0 003.463-.69.75.75 0 01.981.98 10.503 10.503 0 01-9.694 6.46c-5.799 0-10.5-4.701-10.5-10.5 0-4.368 2.667-8.112 6.46-9.694a.75.75 0 01.818.162z" clip-rule="evenodd" /></svg>
            <!-- Sun (show in dark mode) -->
            <svg v-else xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" width="20" height="20"><path d="M12 2.25a.75.75 0 01.75.75v2.25a.75.75 0 01-1.5 0V3a.75.75 0 01.75-.75zM7.5 12a4.5 4.5 0 119 0 4.5 4.5 0 01-9 0zM18.894 6.166a.75.75 0 00-1.06-1.06l-1.591 1.59a.75.75 0 101.06 1.061l1.591-1.59zM21.75 12a.75.75 0 01-.75.75h-2.25a.75.75 0 010-1.5H21a.75.75 0 01.75.75zM17.834 18.894a.75.75 0 001.06-1.06l-1.59-1.591a.75.75 0 10-1.061 1.06l1.59 1.591zM12 18a.75.75 0 01.75.75V21a.75.75 0 01-1.5 0v-2.25A.75.75 0 0112 18zM7.758 17.303a.75.75 0 00-1.061-1.06l-1.591 1.59a.75.75 0 001.06 1.061l1.591-1.59zM6 12a.75.75 0 01-.75.75H3a.75.75 0 010-1.5h2.25A.75.75 0 016 12zM6.697 7.757a.75.75 0 001.06-1.06l-1.59-1.591a.75.75 0 00-1.061 1.06l1.59 1.591z" /></svg>
          </button>
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
import { ref, onMounted, watch } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';

const props = defineProps({
  title: { type: String, default: 'Dashboard' },
});

const sidebarOpen = ref(false);
const sidebarCollapsed = ref(false);
const isLight = ref(false);
const page = usePage();

onMounted(() => {
  sidebarCollapsed.value = localStorage.getItem('sidebar-collapsed') === 'true';
  isLight.value = localStorage.getItem('theme') === 'light';
  applyTheme();
});

function toggleCollapse() {
  sidebarCollapsed.value = !sidebarCollapsed.value;
  localStorage.setItem('sidebar-collapsed', sidebarCollapsed.value);
}

function toggleTheme() {
  isLight.value = !isLight.value;
  localStorage.setItem('theme', isLight.value ? 'light' : 'dark');
  applyTheme();
}

function applyTheme() {
  document.documentElement.setAttribute('data-theme', isLight.value ? 'light' : 'dark');
}

function isActive(routeName) {
  const url = page.url;
  if (routeName === 'dashboard') return url === '/';
  return url.startsWith(`/${routeName}`);
}

function route(name) {
  const routes = { dashboard: '/', 'products.index': '/products', 'products.create': '/products/create' };
  return routes[name] || '#';
}
</script>

<style scoped>
/* ===== LAYOUT ===== */
.app-layout { display: flex; min-height: 100vh; background: var(--bg); color: var(--text); transition: all 0.3s; }

/* ===== CSS VARIABLES (Dark = default) ===== */
.app-layout {
  --bg: #0f0f12; --bg-card: rgba(24,24,27,0.8); --bg-sidebar: linear-gradient(180deg, #18181b 0%, #1a1a2e 100%);
  --text: #e4e4e7; --text-muted: #a1a1aa; --text-dim: #71717a; --text-faint: #52525b;
  --border: rgba(255,255,255,0.06); --border-hover: rgba(255,255,255,0.1);
  --topbar-bg: rgba(24,24,27,0.8); --flash-bg: rgba(34,197,94,0.1); --flash-border: rgba(34,197,94,0.2); --flash-text: #4ade80;
  --nav-hover: rgba(255,255,255,0.05); --nav-active-bg: linear-gradient(135deg, rgba(168,85,247,0.15), rgba(236,72,153,0.1));
  --nav-active-border: rgba(168,85,247,0.2); --nav-active-text: #c084fc;
}

.app-layout.light {
  --bg: #f4f4f5; --bg-card: rgba(255,255,255,0.9); --bg-sidebar: linear-gradient(180deg, #ffffff 0%, #f8f7ff 100%);
  --text: #18181b; --text-muted: #52525b; --text-dim: #71717a; --text-faint: #a1a1aa;
  --border: rgba(0,0,0,0.08); --border-hover: rgba(0,0,0,0.12);
  --topbar-bg: rgba(255,255,255,0.85); --flash-bg: rgba(34,197,94,0.08); --flash-border: rgba(34,197,94,0.2); --flash-text: #16a34a;
  --nav-hover: rgba(0,0,0,0.04); --nav-active-bg: linear-gradient(135deg, rgba(168,85,247,0.08), rgba(236,72,153,0.05));
  --nav-active-border: rgba(168,85,247,0.15); --nav-active-text: #9333ea;
}

/* ===== SIDEBAR ===== */
.sidebar { width: 260px; background: var(--bg-sidebar); border-right: 1px solid var(--border); display: flex; flex-direction: column; position: fixed; top: 0; left: 0; bottom: 0; z-index: 40; transition: width 0.3s cubic-bezier(0.4,0,0.2,1); }
.collapsed .sidebar { width: 72px; }
.sidebar-overlay { position: fixed; inset: 0; background: rgba(0,0,0,0.5); backdrop-filter: blur(4px); z-index: 35; display: none; }

.sidebar-header { padding: 1.25rem; border-bottom: 1px solid var(--border); }
.brand { display: flex; align-items: center; gap: 0.75rem; }
.brand-icon { width: 40px; height: 40px; background: linear-gradient(135deg, #a855f7, #ec4899); border-radius: 12px; display: flex; align-items: center; justify-content: center; color: white; flex-shrink: 0; }
.brand-text h1 { font-size: 1.1rem; font-weight: 700; color: var(--text); margin: 0; line-height: 1.2; }
.brand-text span { font-size: 0.7rem; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.05em; }
.collapsed .brand-text { display: none; }

.sidebar-nav { flex: 1; overflow-y: auto; padding: 0.75rem; }
.nav-section { margin-bottom: 1.25rem; }
.nav-section-title { display: block; font-size: 0.65rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.08em; color: var(--text-dim); padding: 0.5rem 0.75rem 0.4rem; white-space: nowrap; overflow: hidden; }
.collapsed .nav-section-title { font-size: 0; padding: 0.3rem; }

.nav-item { display: flex; align-items: center; gap: 0.75rem; padding: 0.65rem 0.75rem; border-radius: 10px; color: var(--text-muted); text-decoration: none; font-size: 0.875rem; font-weight: 500; transition: all 0.2s ease; margin-bottom: 2px; white-space: nowrap; overflow: hidden; }
.nav-item svg { width: 20px; height: 20px; flex-shrink: 0; }
.nav-item:hover { background: var(--nav-hover); color: var(--text); }
.nav-item.active { background: var(--nav-active-bg); color: var(--nav-active-text); border: 1px solid var(--nav-active-border); }
.collapsed .nav-item { justify-content: center; padding: 0.65rem; }
.collapsed .nav-item span { display: none; }
.collapsed .badge { display: none; }

.badge { margin-left: auto; font-size: 0.6rem; padding: 0.15rem 0.45rem; border-radius: 6px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.03em; }
.badge.soon { background: rgba(250,204,21,0.12); color: #facc15; border: 1px solid rgba(250,204,21,0.2); }

.sidebar-footer { padding: 0.75rem; border-top: 1px solid var(--border); display: flex; flex-direction: column; gap: 0.5rem; align-items: stretch; }
.logout-btn { color: var(--c-danger) !important; font-family: inherit; }
.logout-btn:hover { background: rgba(239,68,68,0.1) !important; }
.collapse-btn { background: none; border: 1px solid var(--border); border-radius: 8px; color: var(--text-dim); cursor: pointer; padding: 0.4rem; display: flex; align-items: center; justify-content: center; transition: all 0.2s; }
.collapse-btn:hover { background: var(--nav-hover); color: var(--text); border-color: var(--border-hover); }

/* ===== MAIN ===== */
.main-wrapper { flex: 1; margin-left: 260px; display: flex; flex-direction: column; min-height: 100vh; transition: margin-left 0.3s cubic-bezier(0.4,0,0.2,1); }
.collapsed .main-wrapper { margin-left: 72px; }

.topbar { height: 64px; background: var(--topbar-bg); backdrop-filter: blur(12px); border-bottom: 1px solid var(--border); display: flex; align-items: center; padding: 0 1.5rem; gap: 1rem; position: sticky; top: 0; z-index: 20; }
.menu-toggle { display: none; background: none; border: none; color: var(--text-muted); cursor: pointer; padding: 0.5rem; border-radius: 8px; transition: all 0.2s; }
.menu-toggle:hover { background: var(--nav-hover); color: var(--text); }
.topbar-title h2 { font-size: 1.1rem; font-weight: 600; color: var(--text); margin: 0; }
.topbar-actions { margin-left: auto; display: flex; align-items: center; gap: 0.75rem; }

.theme-toggle { background: none; border: 1px solid var(--border); border-radius: 8px; color: var(--text-dim); cursor: pointer; padding: 0.4rem; display: flex; align-items: center; justify-content: center; transition: all 0.2s; }
.theme-toggle:hover { background: var(--nav-hover); color: var(--text); border-color: var(--border-hover); }

/* Flash */
.flash-message { margin: 1rem 1.5rem 0; padding: 0.75rem 1rem; border-radius: 10px; display: flex; align-items: center; gap: 0.5rem; font-size: 0.875rem; animation: slideDown 0.3s ease; }
.flash-message.success { background: var(--flash-bg); border: 1px solid var(--flash-border); color: var(--flash-text); }
@keyframes slideDown { from { opacity: 0; transform: translateY(-10px); } to { opacity: 1; transform: translateY(0); } }
.flash-enter-active { animation: slideDown 0.3s ease; }
.flash-leave-active { animation: slideDown 0.3s ease reverse; }

.main-content { flex: 1; padding: 1.5rem; }

/* ===== MOBILE ===== */
@media (max-width: 768px) {
  .sidebar { transform: translateX(-100%); width: 260px !important; }
  .sidebar.open { transform: translateX(0); }
  .sidebar-overlay { display: block; }
  .main-wrapper { margin-left: 0 !important; }
  .menu-toggle { display: block; }
  .desktop-only { display: none; }
}
</style>
