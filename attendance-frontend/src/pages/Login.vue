<template>
  <div class="auth-page">
    <div class="auth-container">
      <div class="auth-card">
        <h2>{{ isLogin ? 'Login' : 'Register' }}</h2>
        <p class="subtitle">School Attendance System</p>

        <div v-if="authStore.error" class="error-message">
          {{ authStore.error }}
        </div>

        <form @submit.prevent="handleSubmit">
          <div v-if="!isLogin" class="form-group">
            <label>Name</label>
            <input
              v-model="formData.name"
              type="text"
              class="form-control"
              placeholder="Enter your name"
              required
            />
          </div>

          <div class="form-group">
            <label>Email</label>
            <input
              v-model="formData.email"
              type="email"
              class="form-control"
              placeholder="Enter your email"
              required
            />
          </div>

          <div class="form-group">
            <label>Password</label>
            <input
              v-model="formData.password"
              type="password"
              class="form-control"
              placeholder="Enter your password"
              required
            />
          </div>

          <div v-if="!isLogin" class="form-group">
            <label>Confirm Password</label>
            <input
              v-model="formData.password_confirmation"
              type="password"
              class="form-control"
              placeholder="Confirm your password"
              required
            />
          </div>

          <button type="submit" class="btn btn-primary btn-block" :disabled="authStore.loading">
            {{ authStore.loading ? 'Please wait...' : (isLogin ? 'Login' : 'Register') }}
          </button>
        </form>

        <div class="auth-toggle">
          <p v-if="isLogin">
            Don't have an account?
            <a href="#" @click.prevent="toggleMode">Register here</a>
          </p>
          <p v-else>
            Already have an account?
            <a href="#" @click.prevent="toggleMode">Login here</a>
          </p>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '../stores/auth'

const router = useRouter()
const authStore = useAuthStore()

const isLogin = ref(true)
const formData = reactive({
  name: '',
  email: '',
  password: '',
  password_confirmation: ''
})

const toggleMode = () => {
  isLogin.value = !isLogin.value
  authStore.clearError()
  // Reset form
  formData.name = ''
  formData.email = ''
  formData.password = ''
  formData.password_confirmation = ''
}

const handleSubmit = async () => {
  try {
    if (isLogin.value) {
      await authStore.login({
        email: formData.email,
        password: formData.password
      })
    } else {
      await authStore.register({
        name: formData.name,
        email: formData.email,
        password: formData.password,
        password_confirmation: formData.password_confirmation
      })
    }
    
    // Redirect to dashboard on success
    router.push('/')
  } catch (error) {
    // Error is handled by the store
    console.error('Auth error:', error)
  }
}
</script>

<style scoped>
.auth-page {
  min-height: 100vh;
  display: flex;
  align-items: center;
  justify-content: center;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  padding: 2rem;
}

.auth-container {
  width: 100%;
  max-width: 450px;
}

.auth-card {
  background: white;
  border-radius: 12px;
  padding: 3rem 2.5rem;
  box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
}

.auth-card h2 {
  text-align: center;
  color: #2c3e50;
  margin-bottom: 0.5rem;
  font-size: 2rem;
}

.subtitle {
  text-align: center;
  color: #7f8c8d;
  margin-bottom: 2rem;
  font-size: 0.95rem;
}

.error-message {
  background: #fee;
  color: #c33;
  padding: 0.75rem 1rem;
  border-radius: 6px;
  margin-bottom: 1.5rem;
  font-size: 0.9rem;
  border-left: 4px solid #e74c3c;
}

.form-group {
  margin-bottom: 1.5rem;
}

.form-group label {
  display: block;
  margin-bottom: 0.5rem;
  font-weight: 600;
  color: #2c3e50;
  font-size: 0.95rem;
}

.form-control {
  width: 100%;
  padding: 0.75rem 1rem;
  border: 2px solid #e0e0e0;
  border-radius: 6px;
  font-size: 1rem;
  transition: all 0.3s;
}

.form-control:focus {
  outline: none;
  border-color: #667eea;
  box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
}

.btn-block {
  width: 100%;
  padding: 0.875rem;
  font-size: 1.05rem;
  font-weight: 600;
  margin-top: 0.5rem;
}

.btn:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

.auth-toggle {
  margin-top: 2rem;
  text-align: center;
  padding-top: 1.5rem;
  border-top: 1px solid #e0e0e0;
}

.auth-toggle p {
  color: #7f8c8d;
  font-size: 0.95rem;
}

.auth-toggle a {
  color: #667eea;
  text-decoration: none;
  font-weight: 600;
  transition: color 0.3s;
}

.auth-toggle a:hover {
  color: #764ba2;
  text-decoration: underline;
}
</style>
