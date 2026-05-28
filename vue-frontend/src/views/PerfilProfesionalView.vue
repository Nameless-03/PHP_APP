<template>
  <DashboardLayout title="Mi Perfil Profesional">
    <v-row justify="center">
      <v-col cols="12" lg="10">
        <v-card class="pa-8 rounded-xl elevation-2">
          <div class="d-flex align-center mb-6">
            <div class="position-relative mr-6">
              <v-avatar color="primary" size="80" class="elevation-2 cursor-pointer avatar-hover" @click="triggerFileInput">
                <v-img v-if="previewUrl || fotoPerfilUrl" :src="previewUrl || fotoPerfilUrl" alt="Foto de perfil"></v-img>
                <span v-else class="text-h4 text-white font-weight-bold">{{ userInitials }}</span>
                <div class="avatar-overlay d-flex align-center justify-center">
                  <v-icon color="white">mdi-camera</v-icon>
                </div>
              </v-avatar>
              <!-- Hidden input for file selection -->
              <input
                ref="fileInput"
                type="file"
                accept="image/*"
                class="d-none"
                @change="onFileSelected"
              />
            </div>
            <div>
              <h2 class="text-h5 font-weight-bold mb-1">Información del Perfil</h2>
              <p class="text-body-2 text-medium-emphasis mb-0">
                Actualiza tus datos para que los clientes te conozcan mejor.
              </p>
              <div v-if="!fotoPerfilUrl && !previewUrl" class="d-flex align-center mt-2 text-caption text-primary font-weight-medium animate-pulse">
                <v-icon size="small" class="mr-1" color="primary">mdi-camera-plus-outline</v-icon>
                Haz clic en el círculo para subir tu foto de perfil
              </div>
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
                  v-model="profile.experiencia"
                  :rules="[rules.required]"
                  label="Experiencia Profesional (Ej. 5 años)"
                  variant="outlined"
                  prepend-inner-icon="mdi-briefcase-outline"
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
                  Ubicación y Preferencias
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
                <v-select
                  v-model="profile.modalidad"
                  :items="['presencial', 'remota', 'hibrida']"
                  :rules="[rules.required]"
                  label="Modalidad Preferida"
                  variant="outlined"
                  prepend-inner-icon="mdi-laptop"
                  color="primary"
                ></v-select>
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
  experiencia: '',
  description: '',
  location: '',
  modalidad: 'presencial'
})

const fotoPerfilUrl = ref(null)
const previewUrl = ref(null)
const selectedFile = ref(null)
const fileInput = ref(null)
const userInitials = ref('PR')

const triggerFileInput = () => {
  fileInput.value.click()
}

const onFileSelected = (event) => {
  const file = event.target.files[0]
  if (file) {
    selectedFile.value = file
    previewUrl.value = URL.createObjectURL(file)
  }
}

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
        profile.value.experiencia = data.user.profesional?.experiencia || ''
        profile.value.description = data.user.profesional?.descripcion || ''
        profile.value.location = data.user.profesional?.ubicacion || ''
        profile.value.modalidad = data.user.profesional?.modalidad_preferida || 'presencial'
        fotoPerfilUrl.value = data.user.profesional?.foto_perfil_url || null
        if (data.user.nombre) {
          userInitials.value = data.user.nombre.substring(0, 2).toUpperCase()
        }
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
    const formData = new FormData()
    formData.append('_method', 'PUT')
    formData.append('nombre', profile.value.name)
    formData.append('experiencia', profile.value.experiencia)
    formData.append('descripcion', profile.value.description)
    formData.append('ubicacion', profile.value.location)
    formData.append('modalidad_preferida', profile.value.modalidad)
    if (selectedFile.value) {
      formData.append('foto_perfil', selectedFile.value)
    }

    const response = await fetch(`http://localhost:8000/api/usuarios/${userId}`, {
      method: 'POST', // Use POST with _method=PUT to allow file uploads under standard PHP/Laravel configurations
      headers: {
        'Authorization': `Bearer ${token}`,
        'Accept': 'application/json'
        // 'Content-Type' must be omitted when sending FormData so the browser generates the correct boundary
      },
      body: formData
    })

    const data = await response.json()

    if (!response.ok) {
      throw new Error(data.message || 'Error al actualizar el perfil')
    }

    console.log('Perfil guardado:', data)
    successMsg.value = '¡Perfil actualizado exitosamente!'
    
    // Update local storage and notify Layout of changes
    if (data.data) {
      localStorage.setItem('user', JSON.stringify(data.data))
      window.dispatchEvent(new Event('user-updated'))
      
      // Update local state references
      fotoPerfilUrl.value = data.data.profesional?.foto_perfil_url || null
      previewUrl.value = null
      selectedFile.value = null
      if (data.data.nombre) {
        userInitials.value = data.data.nombre.substring(0, 2).toUpperCase()
      }
    }
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
.avatar-hover {
  position: relative;
  overflow: hidden;
}
.avatar-overlay {
  position: absolute;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background-color: rgba(0, 0, 0, 0.4);
  opacity: 0;
  transition: opacity 0.2s ease;
  border-radius: 50%;
}
.avatar-hover:hover .avatar-overlay {
  opacity: 1;
}
@keyframes pulse {
  0% { opacity: 0.85; }
  50% { opacity: 1; }
  100% { opacity: 0.85; }
}
.animate-pulse {
  animation: pulse 2s infinite ease-in-out;
}
</style>
