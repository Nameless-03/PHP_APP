<template>
  <v-container fluid class="pa-0" style="min-height: 100vh;">
    <v-row no-gutters style="min-height: 100vh;">
      
      <!-- Left Panel -->
      <v-col cols="12" md="5" class="brand-sidebar d-none d-md-flex flex-column align-center justify-center text-center pa-10">
        <div class="decorative-circle circle-1"></div>
        <div class="decorative-circle circle-2"></div>
        
        <div style="z-index: 1;">
          <v-icon size="80" color="white" class="mb-6 float-animation">mdi-briefcase-variant-outline</v-icon>
          <h1 class="text-h3 font-weight-bold text-white mb-4">Bienvenido</h1>
          <p class="text-h6 text-white font-weight-light opacity-80 px-4">
            Conecta. Organiza. Crece. La plataforma líder para profesionales.
          </p>
        </div>
      </v-col>

      <!-- Right Panel -->
      <v-col cols="12" md="7" class="d-flex align-center justify-center bg-grey-lighten-4 pa-6">
        <v-card class="form-card pa-8 rounded-xl elevation-3" width="100%" max-width="480">
          <div class="text-center mb-8">
            <h2 class="text-h4 font-weight-bold text-grey-darken-4 mb-2">Iniciar Sesión</h2>
            <p class="text-body-1 text-grey-darken-1">Ingresa tus credenciales para continuar</p>
          </div>

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
              <router-link to="/forgot-password" class="text-decoration-none text-primary font-weight-medium">¿Olvidaste tu contraseña?</router-link>
            </div>

            <v-alert v-if="error" type="error" variant="tonal" class="mb-6 rounded-lg">
              {{ error }}
            </v-alert>

            <v-btn
              type="submit"
              color="primary"
              size="x-large"
              block
              :loading="isLoading"
              class="text-none font-weight-bold mb-6 rounded-lg elevation-3 gradient-btn"
            >
              Iniciar Sesión
              <v-icon end>mdi-login</v-icon>
            </v-btn>

            <div class="d-flex align-center my-6">
              <v-divider></v-divider>
              <span class="mx-4 text-grey-darken-1 text-body-2 text-uppercase">o</span>
              <v-divider></v-divider>
            </div>

            <v-btn
              @click="loginWithGoogle"
              color="white"
              variant="outlined"
              size="x-large"
              block
              class="text-none font-weight-bold mb-6 rounded-lg google-btn"
            >
              <v-avatar size="24" class="mr-2">
                <v-img src="https://developers.google.com/static/identity/images/g-logo.png" alt="Google Logo"></v-img>
              </v-avatar>
              Iniciar sesión con Google
            </v-btn>

            <div class="text-center text-body-1 text-grey-darken-1">
              ¿No tienes una cuenta?
              <router-link to="/register" class="text-decoration-none text-primary font-weight-bold ml-1">
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
import { ref, onMounted } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import { useFormRules } from '../composables/useFormRules'

const router = useRouter()
const route = useRoute()
const form = ref(null)

const email = ref('')
const password = ref('')
const showPassword = ref(false)
const remember = ref(false)
const isLoading = ref(false)
const error = ref('')

const { required, email: emailRule } = useFormRules()

const rules = {
  required,
  email: emailRule
}

const handleLogin = async () => {
  const { valid } = await form.value.validate()
  
  if (!valid) return

  isLoading.value = true
  error.value = ''

  try {
    const response = await fetch('/api/auth/login', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json'
      },
      body: JSON.stringify({
        email: email.value,
        password: password.value
      })
    })

    const data = await response.json()

    if (!response.ok) {
      throw new Error(data.message || 'Error de autenticación')
    }
    
    // Guardar token y datos del usuario según la opción "Recordarme"
    // Si recordar: localStorage (persiste entre sesiones del navegador)
    // Si NO recordar: sessionStorage (se borra al cerrar el navegador)
    const storage = remember.value ? localStorage : sessionStorage
    storage.setItem('auth_token', data.token)
    storage.setItem('user', JSON.stringify(data.user))
    // Guardar indicador de dónde está el token
    localStorage.setItem('auth_remember', remember.value ? 'localStorage' : 'sessionStorage')
    
    // Despachar evento para notificar cambio de sesión
    window.dispatchEvent(new Event('user-updated'))
    
    console.log('Login exitoso:', data)
    router.push('/dashboard')
  } catch (err) {
    error.value = 'Credenciales incorrectas o problema de conexión. Por favor, intenta de nuevo.'
  } finally {
    isLoading.value = false
  }
}

onMounted(() => {
  // Check for Google OAuth callback parameters in URL
  const googleAuth = route.query.google_auth
  const urlError = route.query.error

  if (urlError) {
    if (urlError === 'google_auth_error') {
      error.value = 'Hubo un problema al autenticar con Google. Por favor, intenta de nuevo.'
    } else if (urlError === 'database_error') {
      error.value = 'Error al registrar el usuario en la base de datos.'
    } else if (urlError === 'account_deactivated') {
      error.value = 'Tu cuenta está desactivada. Contacta al administrador.'
    } else {
      error.value = 'Error de autenticación con redes sociales.'
    }
    // Clean query parameters from URL
    router.replace({ query: {} })
  } else if (googleAuth === '1') {
    // Read token and user from cookies (set by Laravel to avoid huge Location headers)
    const getCookie = (name) => {
      const match = document.cookie.match(new RegExp('(?:^|; )' + name.replace(/([\.$?*|{}\(\)\[\]\\\/\+^])/g, '\\$1') + '=([^;]*)'))
      return match ? decodeURIComponent(match[1]) : null
    }

    const token = getCookie('google_auth_token')
    const userRaw = getCookie('google_auth_user')

    if (token && userRaw) {
      try {
        const userData = JSON.parse(userRaw)
        localStorage.setItem('auth_token', token)
        localStorage.setItem('user', JSON.stringify(userData))

        // Limpiar las cookies temporales
        document.cookie = 'google_auth_token=; Max-Age=0; path=/'
        document.cookie = 'google_auth_user=; Max-Age=0; path=/'

        // Despachar evento para notificar cambio de sesión
        window.dispatchEvent(new Event('user-updated'))

        console.log('Login con Google exitoso:', userData)
        router.push('/dashboard')
      } catch (err) {
        error.value = 'Error al procesar la información de inicio de sesión.'
        console.error(err)
      }
    } else {
      error.value = 'No se pudo completar el inicio de sesión con Google.'
    }
    router.replace({ query: {} })
  }
})

const loginWithGoogle = () => {
  window.location.href = '/api/auth/google/redirect'
}
</script>

<style scoped>
.brand-sidebar {
  background: linear-gradient(135deg, #8C6D46 0%, #A6987A 100%);
  position: relative;
  overflow: hidden;
}

.decorative-circle {
  position: absolute;
  border-radius: 50%;
  background: linear-gradient(135deg, rgba(255,255,255,0.1) 0%, rgba(255,255,255,0) 100%);
  backdrop-filter: blur(5px);
  z-index: 0;
}

.circle-1 {
  width: 300px;
  height: 300px;
  top: -50px;
  left: -100px;
}

.circle-2 {
  width: 400px;
  height: 400px;
  bottom: -150px;
  right: -100px;
}

.float-animation {
  animation: float 6s ease-in-out infinite;
}

@keyframes float {
  0% { transform: translateY(0px); }
  50% { transform: translateY(-15px); }
  100% { transform: translateY(0px); }
}

.form-card {
  background-color: #ffffff;
  transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.form-card:hover {
  box-shadow: 0 12px 30px rgba(0, 0, 0, 0.08) !important;
}

.gradient-btn {
  background: linear-gradient(135deg, #8C6D46 0%, #A6987A 100%) !important;
  color: white !important;
  transition: transform 0.2s, box-shadow 0.2s;
}

.gradient-btn:hover {
  transform: translateY(-2px);
  box-shadow: 0 6px 20px rgba(140, 109, 70, 0.4) !important;
}

.google-btn {
  border-color: #e0e0e0 !important;
  color: #3c4043 !important;
  transition: background-color 0.2s, box-shadow 0.2s, transform 0.2s;
}

.google-btn:hover {
  background-color: #f8f9fa !important;
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05) !important;
}
</style>
