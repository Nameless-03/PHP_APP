<template>
  <v-container fluid class="fill-height pa-0">
    <v-row no-gutters class="fill-height">
      <!-- Left Panel -->
      <v-col cols="12" md="5" class="brand-sidebar d-none d-md-flex flex-column align-center justify-center text-center pa-10">
        <div class="decorative-circle circle-1"></div>
        <div class="decorative-circle circle-2"></div>
        
        <div style="z-index: 1;">
          <v-icon size="80" color="white" class="mb-6 float-animation">mdi-lock-reset</v-icon>
          <h1 class="text-h3 font-weight-bold text-white mb-4">Recuperación</h1>
          <p class="text-h6 text-white font-weight-light opacity-80 px-4">
            No te preocupes, te ayudaremos a recuperar el acceso a tu cuenta rápidamente.
          </p>
        </div>
      </v-col>

      <!-- Right Panel -->
      <v-col cols="12" md="7" class="d-flex align-center justify-center bg-grey-lighten-4 pa-6">
        <v-card class="form-card pa-8 rounded-xl elevation-3" width="100%" max-width="480">
          <div class="text-center mb-8">
            <h2 class="text-h4 font-weight-bold text-grey-darken-4 mb-2">Olvidé mi Contraseña</h2>
            <p class="text-body-1 text-grey-darken-1">Ingresa tu correo para recibir un enlace de recuperación</p>
          </div>

          <v-form @submit.prevent="handleForgotPassword" ref="form">
            <v-text-field
              v-model="email"
              :rules="[rules.required, rules.email]"
              label="Correo Electrónico"
              prepend-inner-icon="mdi-email-outline"
              variant="outlined"
              color="primary"
              class="mb-6"
              bg-color="white"
            ></v-text-field>

            <v-alert v-if="error" type="error" variant="tonal" class="mb-6 rounded-lg">
              {{ error }}
            </v-alert>

            <v-alert v-if="successMsg" type="success" variant="tonal" class="mb-6 rounded-lg">
              {{ successMsg }}
            </v-alert>

            <v-btn
              type="submit"
              color="primary"
              size="x-large"
              block
              :loading="isLoading"
              :disabled="!!successMsg"
              class="text-none font-weight-bold mb-6 rounded-lg elevation-3 gradient-btn"
            >
              Enviar Enlace
              <v-icon end>mdi-send-outline</v-icon>
            </v-btn>

            <div class="text-center text-body-1 text-grey-darken-1">
              ¿Recordaste tu contraseña?
              <router-link to="/login" class="text-decoration-none text-primary font-weight-bold ml-1">
                Iniciar Sesión
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
import { useFormRules } from '../composables/useFormRules'

const form = ref(null)
const email = ref('')
const isLoading = ref(false)
const error = ref('')
const successMsg = ref('')

const { required, email: emailRule } = useFormRules()

const rules = {
  required,
  email: emailRule
}

const handleForgotPassword = async () => {
  const { valid } = await form.value.validate()
  if (!valid) return

  isLoading.value = true
  error.value = ''
  successMsg.value = ''

  try {
    const response = await fetch('/api/auth/forgot-password', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json'
      },
      body: JSON.stringify({ email: email.value })
    })

    const data = await response.json()

    if (!response.ok) {
      throw new Error(data.message || 'Error al solicitar recuperación.')
    }
    
    successMsg.value = 'Se ha enviado un enlace de recuperación a tu correo electrónico. Por favor, revisa tu bandeja de entrada o la carpeta de spam.'
  } catch (err) {
    error.value = err.message || 'No pudimos procesar tu solicitud en este momento.'
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
