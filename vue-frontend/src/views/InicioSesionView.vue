<template>
  <v-container fluid class="fill-height pa-0">
    <v-row no-gutters class="fill-height">
      
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
              <a href="#" class="text-decoration-none text-primary font-weight-medium">¿Olvidaste tu contraseña?</a>
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
</style>
