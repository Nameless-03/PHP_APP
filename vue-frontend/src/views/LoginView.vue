<template>
  <v-container fluid class="fill-height auth-container pa-0">
    <v-row no-gutters class="fill-height">
      <!-- Left Panel: Branding / Imagery -->
      <v-col cols="12" md="6" class="d-none d-md-flex align-center justify-center brand-panel">
        <div class="text-center px-10">
          <v-icon size="80" color="white" class="mb-6">mdi-briefcase-variant-outline</v-icon>
          <h1 class="text-h3 font-weight-bold text-white mb-4">Business Meetings</h1>
          <p class="text-h6 text-white font-weight-light opacity-80">
            Conecta. Organiza. Crece. La plataforma líder para profesionales.
          </p>
        </div>
      </v-col>

      <!-- Right Panel: Form -->
      <v-col cols="12" md="6" class="d-flex align-center justify-center form-panel">
        <v-card class="glass-card pa-10" width="100%" max-width="480" elevation="0">
          <h2 class="text-h4 font-weight-bold mb-2 text-primary">Bienvenido de vuelta</h2>
          <p class="text-body-1 text-medium-emphasis mb-8">Ingresa tus credenciales para continuar</p>

          <v-form @submit.prevent="handleLogin" ref="form">
            <v-text-field
              v-model="email"
              :rules="[rules.required, rules.email]"
              label="Correo Electrónico"
              prepend-inner-icon="mdi-email-outline"
              variant="outlined"
              color="primary"
              class="mb-2"
              bg-color="white"
            ></v-text-field>

            <v-text-field
              v-model="password"
              :rules="[rules.required]"
              :type="showPassword ? 'text' : 'password'"
              label="Contraseña"
              prepend-inner-icon="mdi-lock-outline"
              :append-inner-icon="showPassword ? 'mdi-eye-off' : 'mdi-eye'"
              @click:append-inner="showPassword = !showPassword"
              variant="outlined"
              color="primary"
              bg-color="white"
            ></v-text-field>

            <div class="d-flex align-center justify-space-between mb-6">
              <v-checkbox v-model="remember" label="Recordarme" color="primary" hide-details></v-checkbox>
              <a href="#" class="text-decoration-none text-primary font-weight-medium">¿Olvidaste tu contraseña?</a>
            </div>

            <v-btn
              type="submit"
              color="primary"
              size="x-large"
              block
              :loading="isLoading"
              class="text-none font-weight-bold mb-6 rounded-lg elevation-2"
            >
              Iniciar Sesión
            </v-btn>

            <v-alert v-if="error" type="error" variant="tonal" class="mb-4">
              {{ error }}
            </v-alert>

            <div class="text-center text-body-1">
              ¿No tienes una cuenta?
              <router-link to="/register" class="text-decoration-none text-primary font-weight-bold">
                Regístrate aquí
              </router-link>
            </div>
          </v-form>
        </v-card>
      </v-col>
    </v-row>
  </v-container>
</template>

<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'

const router = useRouter()
const form = ref(null)

const email = ref('')
const password = ref('')
const showPassword = ref(false)
const remember = ref(false)
const isLoading = ref(false)
const error = ref('')

const rules = {
  required: value => !!value || 'Este campo es requerido.',
  email: value => {
    const pattern = /^(([^<>()[\]\\.,;:\s@"]+(\.[^<>()[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/
    return pattern.test(value) || 'Correo electrónico inválido.'
  },
}

const handleLogin = async () => {
  const { valid } = await form.value.validate()
  
  if (!valid) return

  isLoading.value = true
  error.value = ''

  try {
    // Simulating API Call to Laravel Backend
    // const response = await fetch('/api/auth/login', { ... })
    await new Promise(resolve => setTimeout(resolve, 1500))
    
    console.log('Login attempt:', { email: email.value, password: password.value })
    router.push('/dashboard')
  } catch (err) {
    error.value = 'Credenciales incorrectas. Por favor, intenta de nuevo.'
  } finally {
    isLoading.value = false
  }
}
</script>

<style scoped>
.auth-container {
  background-color: #FDFBF5;
}

.brand-panel {
  background: linear-gradient(135deg, #8C6D46 0%, #A6987A 100%);
  position: relative;
  overflow: hidden;
}

.brand-panel::before {
  content: '';
  position: absolute;
  top: 0; left: 0; right: 0; bottom: 0;
  background: url('https://www.transparenttextures.com/patterns/cubes.png');
  opacity: 0.1;
}

.form-panel {
  background-color: #FDFBF5;
}

.glass-card {
  background: rgba(255, 255, 255, 0.7) !important;
  backdrop-filter: blur(10px);
  border: 1px solid rgba(255, 255, 255, 0.5);
  border-radius: 24px !important;
  box-shadow: 0 8px 32px 0 rgba(140, 109, 70, 0.1) !important;
}
</style>
