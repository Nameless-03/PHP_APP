<template>
  <v-container fluid class="fill-height pa-0">
    <v-row no-gutters class="fill-height">
      <!-- Left Panel -->
      <v-col cols="12" md="5" class="brand-sidebar d-none d-md-flex flex-column align-center justify-center text-center pa-10">
        <div class="decorative-circle circle-1"></div>
        <div class="decorative-circle circle-2"></div>
        
        <div style="z-index: 1;">
          <v-icon size="80" color="white" class="mb-6 float-animation">mdi-shield-lock-outline</v-icon>
          <h1 class="text-h3 font-weight-bold text-white mb-4">Seguridad</h1>
          <p class="text-h6 text-white font-weight-light opacity-80 px-4">
            Crea una nueva contraseña segura para proteger tu cuenta y tus datos.
          </p>
        </div>
      </v-col>

      <!-- Right Panel -->
      <v-col cols="12" md="7" class="d-flex align-center justify-center bg-grey-lighten-4 pa-6">
        <v-card class="form-card pa-8 rounded-xl elevation-3" width="100%" max-width="480">
          <div class="text-center mb-8">
            <h2 class="text-h4 font-weight-bold text-grey-darken-4 mb-2">Restablecer Contraseña</h2>
            <p class="text-body-1 text-grey-darken-1">Ingresa tu nueva contraseña a continuación</p>
          </div>

          <v-form @submit.prevent="handleResetPassword" ref="form">
            <v-text-field
              v-model="email"
              label="Correo Electrónico"
              prepend-inner-icon="mdi-email-outline"
              variant="outlined"
              color="primary"
              class="mb-4"
              bg-color="grey-lighten-3"
              readonly
            ></v-text-field>

            <v-text-field
              v-model="password"
              :rules="[rules.required, rules.minLength]"
              :type="showPassword ? 'text' : 'password'"
              label="Nueva Contraseña"
              prepend-inner-icon="mdi-lock-outline"
              :append-inner-icon="showPassword ? 'mdi-eye-off' : 'mdi-eye'"
              @click:append-inner="showPassword = !showPassword"
              variant="outlined"
              color="primary"
              class="mb-4"
              bg-color="white"
            ></v-text-field>

            <v-text-field
              v-model="passwordConfirmation"
              :rules="[rules.required, passwordMatchRule]"
              :type="showPasswordConfirm ? 'text' : 'password'"
              label="Confirmar Contraseña"
              prepend-inner-icon="mdi-lock-check-outline"
              :append-inner-icon="showPasswordConfirm ? 'mdi-eye-off' : 'mdi-eye'"
              @click:append-inner="showPasswordConfirm = !showPasswordConfirm"
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
              Guardar Contraseña
              <v-icon end>mdi-content-save-outline</v-icon>
            </v-btn>

            <div class="text-center" v-if="successMsg">
              <v-btn color="primary" variant="text" to="/login" class="text-none font-weight-bold">
                Ir a Iniciar Sesión
              </v-btn>
            </div>
          </v-form>
        </v-card>
      </v-col>
    </v-row>
  </v-container>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useFormRules } from '../composables/useFormRules'

const route = useRoute()
const router = useRouter()
const form = ref(null)

const email = ref('')
const token = ref('')
const password = ref('')
const passwordConfirmation = ref('')
const showPassword = ref(false)
const showPasswordConfirm = ref(false)

const isLoading = ref(false)
const error = ref('')
const successMsg = ref('')

const { required } = useFormRules()

const rules = {
  required,
  minLength: v => (v && v.length >= 8) || 'Mínimo 8 caracteres',
}

const passwordMatchRule = v => v === password.value || 'Las contraseñas no coinciden'

onMounted(() => {
  email.value = route.query.email || ''
  token.value = route.query.token || ''

  if (!token.value) {
    error.value = 'El enlace de recuperación es inválido o falta el token.'
  }
})

const handleResetPassword = async () => {
  const { valid } = await form.value.validate()
  if (!valid) return

  isLoading.value = true
  error.value = ''
  successMsg.value = ''

  try {
    const response = await fetch('/api/auth/reset-password', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json'
      },
      body: JSON.stringify({
        email: email.value,
        token: token.value,
        password: password.value,
        password_confirmation: passwordConfirmation.value
      })
    })

    const data = await response.json()

    if (!response.ok) {
      throw new Error(data.message || 'Error al restablecer contraseña. El token puede haber expirado.')
    }
    
    successMsg.value = '¡Tu contraseña ha sido restablecida con éxito!'
    setTimeout(() => {
        router.push('/login')
    }, 3000)
  } catch (err) {
    error.value = err.message || 'No pudimos procesar tu solicitud.'
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
