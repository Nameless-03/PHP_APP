<template>
  <v-container fluid class="fill-height pa-0">
    <v-row no-gutters class="fill-height">
      
      <!-- Left Panel -->
      <v-col cols="12" md="5" class="brand-sidebar d-none d-md-flex flex-column align-center justify-center text-center pa-10">
        <div class="decorative-circle circle-1"></div>
        <div class="decorative-circle circle-2"></div>
        
        <div style="z-index: 1;">
          <v-icon size="80" color="white" class="mb-6 float-animation">mdi-rocket-launch</v-icon>
          <h1 class="text-h3 font-weight-bold text-white mb-4">Únete a Nosotros</h1>
          <p class="text-h6 text-white font-weight-light opacity-80 px-4">
            Crea tu cuenta para empezar a conectar con clientes y profesionales de todo el mundo.
          </p>
        </div>
      </v-col>

      <!-- Right Panel -->
      <v-col cols="12" md="7" class="d-flex align-center justify-center bg-grey-lighten-4 pa-6">
        <v-card class="form-card pa-8 rounded-xl elevation-3" width="100%" max-width="550">
          <div class="text-center mb-6">
            <h2 class="text-h4 font-weight-bold text-grey-darken-4 mb-2">Crear Cuenta</h2>
            <p class="text-body-1 text-grey-darken-1">Comienza tu viaje con nosotros hoy mismo.</p>
          </div>

          <!-- Role Selection using Vuetify Button Toggle -->
          <v-btn-toggle
            v-model="roleTab"
            mandatory
            color="primary"
            class="w-100 mb-6 custom-toggle rounded-lg elevation-0 bg-grey-lighten-3"
          >
            <v-btn value="cliente" class="flex-grow-1 text-none font-weight-bold">
              <v-icon start>mdi-account</v-icon>
              Soy Cliente
            </v-btn>
            <v-btn value="profesional" class="flex-grow-1 text-none font-weight-bold">
              <v-icon start>mdi-briefcase</v-icon>
              Soy Profesional
            </v-btn>
          </v-btn-toggle>

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

            <v-row>
              <v-col cols="12" sm="6" class="pb-0">
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
                  bg-color="white"
                ></v-text-field>
              </v-col>
              <v-col cols="12" sm="6" class="pb-0">
                <v-text-field
                  v-model="formData.password_confirmation"
                  :rules="[rules.required, rules.matchPassword]"
                  type="password"
                  label="Confirmar"
                  prepend-inner-icon="mdi-lock-check-outline"
                  variant="outlined"
                  color="primary"
                  class="mb-2"
                  bg-color="white"
                ></v-text-field>
              </v-col>
            </v-row>

            <!-- Specific fields for Profesional -->
            <v-expand-transition>
              <div v-if="roleTab === 'profesional'" class="mt-2">
                <v-select
                  v-model="formData.modalidad_preferida"
                  :items="['presencial', 'remota', 'hibrida']"
                  label="Modalidad Preferida"
                  prepend-inner-icon="mdi-domain"
                  variant="outlined"
                  color="primary"
                  bg-color="white"
                  class="mb-2"
                ></v-select>
              </div>
            </v-expand-transition>

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
              Registrarse
              <v-icon end>mdi-arrow-right</v-icon>
            </v-btn>

            <div class="text-center text-body-1 text-grey-darken-1">
              ¿Ya tienes una cuenta?
              <router-link to="/login" class="text-decoration-none text-primary font-weight-bold ml-1">
                Inicia sesión
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
.brand-sidebar {
  background: linear-gradient(135deg, #4F46E5 0%, #312E81 100%);
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

.custom-toggle .v-btn.v-btn--active {
  background-color: #ffffff;
  color: #4F46E5 !important;
  box-shadow: 0 2px 8px rgba(0,0,0,0.05) !important;
}

.gradient-btn {
  background: linear-gradient(135deg, #4F46E5 0%, #4338CA 100%) !important;
  transition: transform 0.2s, box-shadow 0.2s;
}

.gradient-btn:hover {
  transform: translateY(-2px);
  box-shadow: 0 6px 20px rgba(79, 70, 229, 0.4) !important;
}
</style>
