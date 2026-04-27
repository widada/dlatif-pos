<template>
  <Head title="Login" />

  <div class="login-page">
    <div class="login-card">
      <div class="login-header">
        <div class="login-logo">
          <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" width="32" height="32">
            <path d="M11.47 3.84a.75.75 0 011.06 0l8.69 8.69a.75.75 0 101.06-1.06l-8.689-8.69a2.25 2.25 0 00-3.182 0l-8.69 8.69a.75.75 0 001.061 1.06l8.69-8.69z" />
            <path d="M12 5.432l8.159 8.159c.03.03.06.058.091.086v6.198c0 1.035-.84 1.875-1.875 1.875H15a.75.75 0 01-.75-.75v-4.5a.75.75 0 00-.75-.75h-3a.75.75 0 00-.75.75V21a.75.75 0 01-.75.75H5.625a1.875 1.875 0 01-1.875-1.875v-6.198a2.29 2.29 0 00.091-.086L12 5.432z" />
          </svg>
        </div>
        <h1>Dlatif Store</h1>
        <p>Masuk ke dashboard</p>
      </div>

      <form @submit.prevent="submit" class="login-form">
        <div v-if="form.errors.username" class="error-alert">
          {{ form.errors.username }}
        </div>

        <div class="form-group">
          <label for="username">Username</label>
          <input id="username" v-model="form.username" type="text" placeholder="username" autofocus autocomplete="username" />
        </div>

        <div class="form-group">
          <label for="password">Password</label>
          <div class="password-wrapper">
            <input id="password" v-model="form.password" :type="showPassword ? 'text' : 'password'" placeholder="••••••••" autocomplete="current-password" />
            <button type="button" class="toggle-pass" @click="showPassword = !showPassword" tabindex="-1">
              {{ showPassword ? '🙈' : '👁️' }}
            </button>
          </div>
        </div>

        <div class="form-options">
          <label class="remember-label">
            <input v-model="form.remember" type="checkbox" />
            <span>Ingat saya</span>
          </label>
        </div>

        <button type="submit" class="btn-login" :disabled="form.processing">
          <span v-if="form.processing" class="spinner"></span>
          <span v-else>Masuk</span>
        </button>
      </form>
    </div>

    <p class="login-footer">© {{ new Date().getFullYear() }} Dlatif Store</p>
  </div>
</template>

<script setup>
import { ref } from 'vue';
import { Head, useForm } from '@inertiajs/vue3';

const showPassword = ref(false);

const form = useForm({
  username: '',
  password: '',
  remember: false,
});

function submit() {
  form.post('/login', {
    onFinish: () => form.reset('password'),
  });
}
</script>

<style scoped>
.login-page {
  min-height: 100vh;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  padding: 1.5rem;
  background: var(--c-bg);
}

.login-card {
  width: 100%;
  max-width: 400px;
  background: var(--c-card);
  border: 1px solid var(--c-border);
  border-radius: 20px;
  padding: 2rem 1.75rem;
}

.login-header {
  text-align: center;
  margin-bottom: 1.5rem;
}

.login-logo {
  width: 56px;
  height: 56px;
  border-radius: 14px;
  background: linear-gradient(135deg, #a855f7, #ec4899);
  display: flex;
  align-items: center;
  justify-content: center;
  margin: 0 auto 0.75rem;
  color: white;
}

.login-header h1 {
  margin: 0;
  font-size: 1.35rem;
  font-weight: 800;
  color: var(--c-text);
}

.login-header p {
  margin: 0.25rem 0 0;
  font-size: 0.82rem;
  color: var(--c-text-dim);
}

.error-alert {
  background: rgba(239, 68, 68, 0.1);
  border: 1px solid rgba(239, 68, 68, 0.2);
  border-radius: 10px;
  padding: 0.65rem 0.85rem;
  color: var(--c-danger);
  font-size: 0.82rem;
  margin-bottom: 1rem;
  text-align: center;
}

.form-group {
  margin-bottom: 1rem;
}

.form-group label {
  display: block;
  font-size: 0.78rem;
  font-weight: 600;
  color: var(--c-text-muted);
  margin-bottom: 0.35rem;
}

.form-group input[type="text"],
.form-group input[type="password"] {
  width: 100%;
  background: var(--c-input-bg);
  border: 1px solid var(--c-input-border);
  border-radius: 10px;
  padding: 0.65rem 0.85rem;
  color: var(--c-text);
  font-size: 0.9rem;
  outline: none;
  transition: border-color 0.2s;
}

.form-group input:focus {
  border-color: rgba(168, 85, 247, 0.4);
}

.form-group input::placeholder {
  color: var(--c-text-faint);
}

.password-wrapper {
  position: relative;
}

.password-wrapper input {
  padding-right: 2.5rem;
}

.toggle-pass {
  position: absolute;
  right: 0.6rem;
  top: 50%;
  transform: translateY(-50%);
  background: none;
  border: none;
  cursor: pointer;
  font-size: 1rem;
  line-height: 1;
  padding: 0;
}

.form-options {
  display: flex;
  align-items: center;
  justify-content: space-between;
  margin-bottom: 1.25rem;
}

.remember-label {
  display: flex;
  align-items: center;
  gap: 0.4rem;
  font-size: 0.8rem;
  color: var(--c-text-muted);
  cursor: pointer;
}

.remember-label input[type="checkbox"] {
  accent-color: #a855f7;
  width: 15px;
  height: 15px;
}

.btn-login {
  width: 100%;
  padding: 0.75rem;
  background: linear-gradient(135deg, #a855f7, #ec4899);
  color: white;
  border: none;
  border-radius: 12px;
  font-size: 0.95rem;
  font-weight: 700;
  cursor: pointer;
  transition: all 0.2s;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 0.5rem;
}

.btn-login:hover:not(:disabled) {
  opacity: 0.9;
  box-shadow: 0 4px 20px rgba(168, 85, 247, 0.3);
}

.btn-login:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

.spinner {
  width: 18px;
  height: 18px;
  border: 2px solid rgba(255, 255, 255, 0.3);
  border-top-color: white;
  border-radius: 50%;
  animation: spin 0.6s linear infinite;
}

@keyframes spin { to { transform: rotate(360deg); } }

.login-footer {
  margin-top: 1.5rem;
  font-size: 0.72rem;
  color: var(--c-text-faint);
}
</style>
