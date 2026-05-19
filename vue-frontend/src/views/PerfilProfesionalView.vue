<template>
  <DashboardLayout title="Mi Perfil Profesional">
    <v-row justify="center">
      <v-col cols="12" lg="10">
        <v-card class="pa-8 rounded-xl elevation-2">
          <div class="d-flex align-center mb-6">
            <v-avatar color="primary" size="80" class="mr-6 elevation-2">
              <span class="text-h4 text-white font-weight-bold">PR</span>
            </v-avatar>
            <div>
              <h2 class="text-h5 font-weight-bold mb-1">Información del Perfil</h2>
              <p class="text-body-2 text-medium-emphasis mb-0">
                Actualiza tus datos para que los clientes te conozcan mejor.
              </p>
            </div>
          </div>

          <v-divider class="mb-8"></v-divider>

          <v-form @submit.prevent="saveProfile" ref="form">
            <v-row>
              <!-- Personal/Business Data -->
              <v-col cols="12">
                <h3 class="text-subtitle-1 font-weight-bold text-primary mb-4">
                  <v-icon start color="primary">mdi-card-account-details</v-icon>
                  Datos Personales y Empresariales
                </h3>
              </v-col>
              <v-col cols="12" md="6">
                <v-text-field
                  v-model="profile.name"
                  :rules="[rules.required]"
                  label="Nombre Completo / Empresa"
                  variant="outlined"
                  prepend-inner-icon="mdi-domain"
                  color="primary"
                ></v-text-field>
              </v-col>
              <v-col cols="12" md="6">
                <v-text-field
                  v-model="profile.specialty"
                  :rules="[rules.required]"
                  label="Especialidad o Rubro principal"
                  variant="outlined"
                  prepend-inner-icon="mdi-star-circle-outline"
                  color="primary"
                ></v-text-field>
              </v-col>
              
              <v-col cols="12">
                <v-textarea
                  v-model="profile.description"
                  :rules="[rules.required]"
                  label="Descripción General"
                  placeholder="Cuéntanos sobre tu experiencia y los servicios que ofreces..."
                  variant="outlined"
                  prepend-inner-icon="mdi-text"
                  color="primary"
                  auto-grow
                  rows="3"
                ></v-textarea>
              </v-col>

              <!-- Location & Contact -->
              <v-col cols="12" class="mt-4">
                <h3 class="text-subtitle-1 font-weight-bold text-primary mb-4">
                  <v-icon start color="primary">mdi-map-marker-radius</v-icon>
                  Ubicación y Contacto
                </h3>
              </v-col>
              <v-col cols="12" md="6">
                <v-text-field
                  v-model="profile.location"
                  :rules="[rules.required]"
                  label="Ubicación Geográfica (Ciudad, País)"
                  variant="outlined"
                  prepend-inner-icon="mdi-map-marker-outline"
                  color="primary"
                ></v-text-field>
              </v-col>
              <v-col cols="12" md="6">
                <v-text-field
                  v-model="profile.phone"
                  :rules="[rules.required]"
                  label="Teléfono de Contacto"
                  variant="outlined"
                  prepend-inner-icon="mdi-phone-outline"
                  color="primary"
                ></v-text-field>
              </v-col>
              <v-col cols="12">
                <v-text-field
                  v-model="profile.website"
                  label="Sitio Web (Opcional)"
                  variant="outlined"
                  prepend-inner-icon="mdi-web"
                  color="primary"
                  placeholder="https://www.tuempresa.com"
                ></v-text-field>
              </v-col>
            </v-row>

            <!-- Alerts & Actions -->
            <v-alert v-if="successMsg" type="success" variant="tonal" class="mt-4 rounded-lg">
              {{ successMsg }}
            </v-alert>
            <v-alert v-if="errorMsg" type="error" variant="tonal" class="mt-4 rounded-lg">
              {{ errorMsg }}
            </v-alert>

            <div class="d-flex justify-end mt-8">
              <v-btn
                variant="outlined"
                color="grey-darken-1"
                class="mr-4 px-6 text-none font-weight-bold"
                @click="resetForm"
              >
                Cancelar
              </v-btn>
              <v-btn
                type="submit"
                color="primary"
                :loading="isLoading"
                class="px-8 text-none font-weight-bold elevation-2"
              >
                Guardar Cambios
                <v-icon end>mdi-content-save</v-icon>
              </v-btn>
            </div>
          </v-form>
        </v-card>
      </v-col>
    </v-row>
  </DashboardLayout>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import DashboardLayout from '../components/DashboardLayout.vue'

const form = ref(null)
const isLoading = ref(false)
const successMsg = ref('')
const errorMsg = ref('')

const profile = ref({
  name: '',
  specialty: '',
  description: '',
  location: '',
  phone: '',
  website: ''
})

const rules = {
  required: value => !!value || 'Este campo es requerido.'
}

onMounted(async () => {
  const token = localStorage.getItem('auth_token')
  if (!token) return

  try {
    const response = await fetch('http://localhost:8000/api/auth/me', {
      headers: {
        'Authorization': `Bearer ${token}`,
        'Accept': 'application/json'
      }
    })
    
    if (response.ok) {
      const data = await response.json()
      if (data.user) {
        profile.value.name = data.user.nombre || ''
        profile.value.specialty = data.user.profesional?.especialidad || ''
        profile.value.description = data.user.profesional?.descripcion || ''
        profile.value.location = data.user.profesional?.ubicacion || ''
        profile.value.phone = data.user.profesional?.telefono || ''
        profile.value.website = data.user.profesional?.sitio_web || ''
      }
    }
  } catch (error) {
    console.error('Error fetching user profile:', error)
  }
})

const saveProfile = async () => {
  const { valid } = await form.value.validate()
  
  if (!valid) {
    errorMsg.value = 'Por favor, completa correctamente todos los campos obligatorios.'
    successMsg.value = ''
    return
  }

  isLoading.value = true
  errorMsg.value = ''
  successMsg.value = ''

  const token = localStorage.getItem('auth_token')
  const userStr = localStorage.getItem('user')
  let userId = ''
  
  if (userStr) {
    const user = JSON.parse(userStr)
    userId = user.id
  }

  try {
    const response = await fetch(`http://localhost:8000/api/usuarios/${userId}`, {
      method: 'PUT',
      headers: {
        'Authorization': `Bearer ${token}`,
        'Content-Type': 'application/json',
        'Accept': 'application/json'
      },
      body: JSON.stringify({
        nombre: profile.value.name,
        especialidad: profile.value.specialty,
        descripcion: profile.value.description,
        ubicacion: profile.value.location,
        telefono: profile.value.phone,
        sitio_web: profile.value.website
      })
    })

    const data = await response.json()

    if (!response.ok) {
      throw new Error(data.message || 'Error al actualizar el perfil')
    }

    console.log('Perfil guardado:', data)
    successMsg.value = '¡Perfil actualizado exitosamente!'
  } catch (err) {
    errorMsg.value = err.message || 'Ocurrió un error al guardar el perfil. Intenta de nuevo.'
  } finally {
    isLoading.value = false
    setTimeout(() => { successMsg.value = '' }, 3000)
  }
}

const resetForm = () => {
  form.value.reset()
}
</script>

<style scoped>
</style>
