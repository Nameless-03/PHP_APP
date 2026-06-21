<template>
  <DashboardLayout title="Perfil">
    <v-row justify="center">
      <v-col cols="12" lg="10">
        <!-- Header visual -->
        <BannerHeader
          title="Perfil Profesional"
          subtitle="Gestiona tu información pública, reputación, datos de contacto y preferencias de atención."
          icon="mdi-account-circle"
          class="mb-6"
        />

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
              <!-- Input oculto para la selección de archivo -->
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
              <!-- Datos Personales/Empresariales -->
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

              <!-- Ubicación y Contacto -->
              <v-col cols="12" class="mt-4">
                <h3 class="text-subtitle-1 font-weight-bold text-primary mb-4">
                  <v-icon start color="primary">mdi-map-marker-radius</v-icon>
                  Ubicación y Contacto
                </h3>
              </v-col>
              <v-col cols="12" md="4">
                <v-text-field
                  v-model="profile.location"
                  :rules="[rules.required]"
                  label="Ubicación Geográfica (Ciudad, País)"
                  variant="outlined"
                  prepend-inner-icon="mdi-map-marker-outline"
                  color="primary"
                ></v-text-field>
              </v-col>
              <v-col cols="12" md="4">
                <v-text-field
                  v-model="profile.telefono"
                  label="Teléfono / WhatsApp (ej. +5491122334455)"
                  variant="outlined"
                  prepend-inner-icon="mdi-whatsapp"
                  color="primary"
                ></v-text-field>
              </v-col>
              <v-col cols="12" md="4">
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

            <!-- Alertas y Acciones -->
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

        <!-- Sección de Opiniones y Calificaciones -->
        <v-card class="pa-8 rounded-xl elevation-2 mt-6">
          <h3 class="text-h5 font-weight-bold mb-4 text-grey-darken-4 d-flex align-center">
            <v-icon start color="warning" class="mr-2">mdi-star-circle</v-icon>
            Opiniones de mis Clientes
          </h3>
          <v-divider class="mb-6"></v-divider>

          <div v-if="cargandoOpiniones" class="text-center py-8">
            <v-progress-circular indeterminate color="primary"></v-progress-circular>
          </div>

          <div v-else-if="opiniones.length > 0">
            <v-row>
              <v-col cols="12" md="6" v-for="op in opiniones" :key="op.id">
                <ReviewCard
                  :cliente-nombre="op.cliente_nombre"
                  :fecha="op.fecha"
                  :puntuacion="op.puntuacion"
                  :comentario="op.comentario"
                />
              </v-col>
            </v-row>
          </div>

          <div v-else class="text-center py-8 opacity-60">
            <v-icon size="48" color="grey" class="mb-2">mdi-comment-text-multiple-outline</v-icon>
            <p class="mt-2 text-body-1 mb-0">Aún no has recibido opiniones de tus clientes.</p>
          </div>
        </v-card>
      </v-col>
    </v-row>
  </DashboardLayout>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import DashboardLayout from '../components/DashboardLayout.vue'
import BannerHeader from '../components/BannerHeader.vue'
import ReviewCard from '../components/ReviewCard.vue'
import { useAuth } from '../composables/useAuth'
import { useFormRules } from '../composables/useFormRules'

const form = ref(null)
const isLoading = ref(false)
const successMsg = ref('')
const errorMsg = ref('')

const opiniones = ref([])
const cargandoOpiniones = ref(false)

const profile = ref({
  name: '',
  experiencia: '',
  description: '',
  location: '',
  telefono: '',
  modalidad: 'presencial'
})

const { token, user: userState, getAuthHeaders } = useAuth()

const cargarOpiniones = async (userId) => {
  cargandoOpiniones.value = true
  try {
    const response = await fetch(`/api/profesionales/${userId}/calificaciones`, {
      headers: getAuthHeaders()
    })
    if (response.ok) {
      const data = await response.json()
      opiniones.value = data.data || []
    }
  } catch (error) {
    console.error('Error fetching reviews:', error)
  } finally {
    cargandoOpiniones.value = false
  }
}

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

const { required } = useFormRules()
const rules = {
  required
}

onMounted(async () => {
  if (!token.value) return

  // Cargar opiniones del profesional
  if (userState.value?.id) {
    cargarOpiniones(userState.value.id)
  }

  try {
    const response = await fetch('/api/auth/me', {
      headers: getAuthHeaders()
    })
    
    if (response.ok) {
      const data = await response.json()
      if (data.user) {
        profile.value.name = data.user.nombre || ''
        profile.value.experiencia = data.user.profesional?.experiencia || ''
        profile.value.description = data.user.profesional?.descripcion || ''
        profile.value.location = data.user.profesional?.ubicacion || ''
        profile.value.telefono = data.user.profesional?.telefono || ''
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

  const userId = userState.value?.id

  try {
    const formData = new FormData()
    formData.append('_method', 'PUT')
    formData.append('nombre', profile.value.name)
    formData.append('experiencia', profile.value.experiencia)
    formData.append('descripcion', profile.value.description)
    formData.append('ubicacion', profile.value.location)
    formData.append('telefono', profile.value.telefono || '')
    formData.append('modalidad_preferida', profile.value.modalidad)
    if (selectedFile.value) {
      formData.append('foto_perfil', selectedFile.value)
    }

    const response = await fetch(`/api/usuarios/${userId}`, {
      method: 'POST', // Usar POST con _method=PUT para permitir la subida de archivos en configuraciones estándar de PHP/Laravel
      headers: {
        'Authorization': `Bearer ${token.value}`,
        'Accept': 'application/json'
        // 'Content-Type' debe ser omitido al enviar FormData para que el navegador genere el boundary correcto
      },
      body: formData
    })

    const data = await response.json()

    if (!response.ok) {
      throw new Error(data.message || 'Error al actualizar el perfil')
    }

    console.log('Perfil guardado:', data)
    successMsg.value = '¡Perfil actualizado exitosamente!'
    
    // Actualizar local storage y notificar al Layout sobre los cambios
    if (data.data) {
      localStorage.setItem('user', JSON.stringify(data.data))
      window.dispatchEvent(new Event('user-updated'))
      
      // Actualizar referencias de estado local
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
.bg-gradient {
  background: linear-gradient(135deg, #8C6D46 0%, #A6987A 100%);
}
.italic {
  font-style: italic;
}
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
