<template>
  <DashboardLayout title="Gestión de Servicios">
    <v-row>
      <!-- Form to Add/Edit Service -->
      <v-col cols="12" lg="7">
        <v-card class="pa-8 rounded-xl elevation-2 h-100">
          <div class="d-flex align-center mb-6">
            <v-icon size="40" color="primary" class="mr-4">mdi-briefcase-plus</v-icon>
            <div>
              <h2 class="text-h5 font-weight-bold mb-1">Publicar Nuevo Servicio</h2>
              <p class="text-body-2 text-medium-emphasis mb-0">
                Define los detalles del servicio que ofreces a los clientes.
              </p>
            </div>
          </div>

          <v-divider class="mb-6"></v-divider>

          <v-form @submit.prevent="saveService" ref="form">
            <v-row>
              <v-col cols="12">
                <v-text-field
                  v-model="service.name"
                  :rules="[rules.required]"
                  label="Nombre del Servicio"
                  placeholder="Ej: Consultoría de Desarrollo Web"
                  variant="outlined"
                  prepend-inner-icon="mdi-format-title"
                  color="primary"
                ></v-text-field>
              </v-col>

              <v-col cols="12">
                <v-textarea
                  v-model="service.description"
                  :rules="[rules.required]"
                  label="Descripción Detallada"
                  placeholder="Explica qué incluye el servicio, beneficios, entregables..."
                  variant="outlined"
                  prepend-inner-icon="mdi-text-box-outline"
                  color="primary"
                  auto-grow
                  rows="3"
                ></v-textarea>
              </v-col>

              <v-col cols="12" md="6">
                <v-text-field
                  v-model="service.duration"
                  :rules="[rules.required, rules.isInteger]"
                  label="Duración Estimada (Minutos)"
                  placeholder="Ej: 60"
                  variant="outlined"
                  type="number"
                  prepend-inner-icon="mdi-clock-outline"
                  color="primary"
                  bg-color="white"
                ></v-text-field>
              </v-col>

              <v-col cols="12" md="6">
                <v-text-field
                  v-model="service.price"
                  :rules="[rules.required, rules.isNumber]"
                  label="Precio (USD)"
                  placeholder="0.00"
                  prefix="$"
                  variant="outlined"
                  prepend-inner-icon="mdi-currency-usd"
                  color="primary"
                ></v-text-field>
              </v-col>

              <v-col cols="12">
                <p class="text-subtitle-2 text-medium-emphasis mb-2">Modalidad de Atención</p>
                <v-btn-toggle
                  v-model="service.modality"
                  mandatory
                  color="primary"
                  class="w-100 mb-2 rounded-lg elevation-0 bg-grey-lighten-4"
                  variant="outlined"
                >
                  <v-btn value="presencial" class="flex-grow-1 text-none font-weight-bold">
                    <v-icon start>mdi-account-group</v-icon>
                    Presencial
                  </v-btn>
                  <v-btn value="remota" class="flex-grow-1 text-none font-weight-bold">
                    <v-icon start>mdi-laptop</v-icon>
                    Remota
                  </v-btn>
                  <v-btn value="hibrida" class="flex-grow-1 text-none font-weight-bold">
                    <v-icon start>mdi-transit-connection-variant</v-icon>
                    Híbrida
                  </v-btn>
                </v-btn-toggle>
              </v-col>
            </v-row>

            <!-- Alerts -->
            <v-alert v-if="successMsg" type="success" variant="tonal" class="mt-4 rounded-lg">
              {{ successMsg }}
            </v-alert>
            <v-alert v-if="errorMsg" type="error" variant="tonal" class="mt-4 rounded-lg">
              {{ errorMsg }}
            </v-alert>

            <div class="d-flex justify-end mt-6">
              <v-btn
                variant="outlined"
                color="grey-darken-1"
                class="mr-4 px-6 text-none font-weight-bold"
                @click="resetForm"
              >
                Limpiar
              </v-btn>
              <v-btn
                type="submit"
                color="primary"
                :loading="isLoading"
                class="px-8 text-none font-weight-bold elevation-2"
              >
                Guardar Servicio
                <v-icon end>mdi-content-save</v-icon>
              </v-btn>
            </div>
          </v-form>
        </v-card>
      </v-col>

      <!-- Side panel: List of current services (Mocked) -->
      <v-col cols="12" lg="5">
        <v-card class="pa-6 rounded-xl elevation-1 h-100 bg-grey-lighten-4">
          <h3 class="text-h6 font-weight-bold mb-4">
            <v-icon start color="primary">mdi-format-list-bulleted</v-icon>
            Servicios Publicados
          </h3>
          
          <v-list bg-color="transparent" class="pa-0">
            <template v-for="(item, index) in publishedServices" :key="index">
              <v-card class="mb-3 rounded-lg border" elevation="0" color="white">
                <v-card-text class="pa-4">
                  <div class="d-flex justify-space-between align-center mb-1">
                    <h4 class="text-subtitle-1 font-weight-bold text-primary">{{ item.name }}</h4>
                    <v-chip size="small" :color="getModalityColor(item.modality)" variant="tonal" class="font-weight-bold text-uppercase" style="font-size: 0.7rem;">
                      {{ item.modality }}
                    </v-chip>
                  </div>
                  <p class="text-body-2 text-medium-emphasis text-truncate mb-2">{{ item.description }}</p>
                  <div class="d-flex justify-space-between align-center">
                    <span class="text-caption font-weight-medium text-grey-darken-1">
                      <v-icon size="small" class="mr-1">mdi-clock-outline</v-icon> {{ item.duration }} min
                    </span>
                    <span class="text-subtitle-2 font-weight-bold text-success">
                      ${{ item.price }} USD
                    </span>
                  </div>
                </v-card-text>
              </v-card>
            </template>
            
            <div v-if="publishedServices.length === 0" class="text-center pa-8 opacity-60">
              <v-icon size="48" color="grey">mdi-inbox-outline</v-icon>
              <p class="mt-2 text-body-2">Aún no tienes servicios publicados.</p>
            </div>
          </v-list>
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

const service = ref({
  name: '',
  description: '',
  duration: '',
  price: '',
  modality: 'presencial'
})

const publishedServices = ref([])

onMounted(async () => {
  const token = localStorage.getItem('auth_token')
  if (!token) return

  try {
    const response = await fetch('http://localhost:8000/api/servicios', {
      headers: {
        'Authorization': `Bearer ${token}`,
        'Accept': 'application/json'
      }
    })
    
    if (response.ok) {
      const data = await response.json()
      // Si la API devuelve los servicios en un array 'data'
      const apiServices = data.data || data
      publishedServices.value = apiServices.map(s => ({
        name: s.nombre,
        description: s.descripcion,
        duration: s.duracion,
        price: s.precio,
        modality: s.modalidad
      }))
    }
  } catch (error) {
    console.error('Error fetching services:', error)
  }
})

const rules = {
  required: value => !!value || 'Este campo es obligatorio.',
  isNumber: value => {
    const pattern = /^\d+(\.\d{1,2})?$/
    return pattern.test(value) || 'Debe ser un número válido (ej: 150.00).'
  },
  isInteger: value => {
    const pattern = /^\d+$/
    return pattern.test(value) || 'Debe ser un número entero (ej: 60).'
  }
}

const getModalityColor = (modality) => {
  if (modality === 'presencial') return 'indigo'
  if (modality === 'remota') return 'teal'
  return 'deep-orange'
}

const saveService = async () => {
  const { valid } = await form.value.validate()
  
  if (!valid) {
    errorMsg.value = 'Por favor, corrige los errores en el formulario.'
    successMsg.value = ''
    return
  }

  isLoading.value = true
  errorMsg.value = ''
  successMsg.value = ''

  const token = localStorage.getItem('auth_token')

  try {
    const response = await fetch('http://localhost:8000/api/servicios', {
      method: 'POST',
      headers: {
        'Authorization': `Bearer ${token}`,
        'Content-Type': 'application/json',
        'Accept': 'application/json'
      },
      body: JSON.stringify({
        nombre: service.value.name,
        descripcion: service.value.description,
        duracion: parseInt(service.value.duration),
        precio: parseFloat(service.value.price),
        modalidad: service.value.modality,
        id_categoria: 1
      })
    })

    const data = await response.json()

    if (!response.ok) {
      throw new Error(data.message || 'Error al guardar el servicio')
    }
    
    const s = data.data || data
    
    // Add to list and map properties to match UI expectations
    publishedServices.value.unshift({
      name: s.nombre,
      description: s.descripcion,
      duration: s.duracion,
      price: s.precio,
      modality: s.modalidad
    })
    
    successMsg.value = 'Servicio guardado exitosamente.'
    form.value.reset()
    service.value.modality = 'presencial'
    
  } catch (err) {
    errorMsg.value = err.message || 'Error al guardar el servicio. Intenta de nuevo.'
  } finally {
    isLoading.value = false
    setTimeout(() => { successMsg.value = '' }, 3000)
  }
}

const resetForm = () => {
  form.value.reset()
  service.value.modality = 'presencial'
  errorMsg.value = ''
  successMsg.value = ''
}
</script>

<style scoped>
.border {
  border: 1px solid rgba(0,0,0,0.08) !important;
}
</style>
