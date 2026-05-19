<template>
  <v-container fluid class="fill-height auth-container pa-0">
    <v-row no-gutters class="fill-height">
      <!-- Right Panel: Form (Swapped for variety) -->
      <v-col cols="12" md="6" class="d-flex align-center justify-center form-panel">
        <v-card class="glass-card pa-10" width="100%" max-width="500" elevation="0">
          <h2 class="text-h4 font-weight-bold mb-2 text-primary">Crear Cuenta</h2>
          <p class="text-body-1 text-medium-emphasis mb-6">Únete a nuestra plataforma profesional</p>

          <v-tabs v-model="roleTab" color="primary" grow class="mb-6 rounded-lg bg-white elevation-1">
            <v-tab value="cliente" class="text-none font-weight-bold">Soy Cliente</v-tab>
            <v-tab value="profesional" class="text-none font-weight-bold">Soy Profesional</v-tab>
          </v-tabs>

          <v-form @submit.prevent="handleRegister" ref="form">
            <v-text-field
              v-model="formData.nombre"
              :rules="[rules.required]"
              label="Nombre Completo"
              prepend-inner-icon="mdi-account-outline"
              variant="outlined"
              color="primary"
              class="mb-2"
              bg-color="white"
            ></v-text-field>

            <v-text-field
              v-model="formData.email"
              :rules="[rules.required, rules.email]"
              label="Correo Electrónico"
              prepend-inner-icon="mdi-email-outline"
              variant="outlined"
              color="primary"
              class="mb-2"
              bg-color="white"
            ></v-text-field>

            <v-text-field
              v-model="formData.password"
              :rules="[rules.required, rules.minLength]"
              :type="showPassword ? 'text' : 'password'"
              label="Contraseña"
              prepend-inner-icon="mdi-lock-outline"
              :append-inner-icon="showPassword ? 'mdi-eye-off' : 'mdi-eye'"
              @click:append-inner="showPassword = !showPassword"
              variant="outlined"
              color="primary"
              class="mb-2"
              bg-color="white"
            ></v-text-field>

            <v-text-field
              v-model="formData.password_confirmation"
              :rules="[rules.required, rules.matchPassword]"
              type="password"
              label="Confirmar Contraseña"
              prepend-inner-icon="mdi-lock-check-outline"
              variant="outlined"
              color="primary"
              class="mb-6"
              bg-color="white"
            ></v-text-field>

            <!-- Specific fields for Profesional -->
            <v-expand-transition>
              <div v-if="roleTab === 'profesional'">
                <v-select
                  v-model="formData.modalidad_preferida"
                  :items="['presencial', 'remota', 'hibrida']"
                  label="Modalidad Preferida"
                  variant="outlined"
                  color="primary"
                  bg-color="white"
                  class="mb-6"
                ></v-select>
              </div>
            </v-expand-transition>

            <v-btn
              type="submit"
              color="primary"
              size="x-large"
              block
              :loading="isLoading"
              class="text-none font-weight-bold mb-6 rounded-lg elevation-2"
            >
              Registrarse
            </v-btn>

            <v-alert v-if="error" type="error" variant="tonal" class="mb-4">
              {{ error }}
            </v-alert>

            <div class="text-center text-body-1">
              ¿Ya tienes una cuenta?
              <router-link to="/login" class="text-decoration-none text-primary font-weight-bold">
                Inicia sesión
              </router-link>
            </div>
          </v-form>
        </v-card>
      </v-col>

      <!-- Left Panel: Branding / Imagery -->
      <v-col cols="12" md="6" class="d-none d-md-flex align-center justify-center brand-panel">
        <div class="text-center px-10">
          <v-icon size="80" color="white" class="mb-6">mdi-rocket-launch-outline</v-icon>
          <h1 class="text-h3 font-weight-bold text-white mb-4">Impulsa tu Carrera</h1>
          <p class="text-h6 text-white font-weight-light opacity-80">
            Únete a miles de profesionales y clientes que ya están conectando.
          </p>
        </div>
      </v-col>
    </v-row>
  </v-container>
</template>

<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'

const router = useRouter()
const form = ref(null)

const roleTab = ref('cliente')
const showPassword = ref(false)
const isLoading = ref(false)
const error = ref('')

const formData = ref({
  nombre: '',
  email: '',
  password: '',
  password_confirmation: '',
  modalidad_preferida: 'presencial',
})

const rules = {
  required: value => !!value || 'Requerido.',
  email: value => {
    const pattern = /^(([^<>()[\]\\.,;:\s@"]+(\.[^<>()[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/
    return pattern.test(value) || 'Email inválido.'
  },
  minLength: value => (value && value.length >= 8) || 'Mínimo 8 caracteres.',
  matchPassword: value => value === formData.value.password || 'Las contraseñas no coinciden.',
}

const handleRegister = async () => {
  const { valid } = await form.value.validate()
  
  if (!valid) return

  isLoading.value = true
  error.value = ''

  try {
    // Simulating API Call to Laravel Backend
    // const endpoint = roleTab.value === 'cliente' ? '/api/auth/register/cliente' : '/api/auth/register/profesional'
    // const response = await fetch(endpoint, { ... })
    await new Promise(resolve => setTimeout(resolve, 1500))
    
    console.log(`Registrando ${roleTab.value}:`, formData.value)
    router.push('/dashboard')
  } catch (err) {
    error.value = 'Ocurrió un error al registrar. Intenta de nuevo.'
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
  background: linear-gradient(135deg, #A6987A 0%, #8C6D46 100%);
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
