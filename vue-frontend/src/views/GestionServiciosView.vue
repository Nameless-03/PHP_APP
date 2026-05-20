<template>
  <DashboardLayout title="Gestión de Servicios">
    <v-row>
      <!-- Form to Add/Edit Service -->
      <v-col cols="12" lg="7">
        <v-card class="pa-8 rounded-xl elevation-2 h-100">
          <div class="d-flex align-center mb-6">
            <v-icon size="40" color="primary" class="mr-4">{{ isEditing ? 'mdi-briefcase-edit' : 'mdi-briefcase-plus' }}</v-icon>
            <div>
              <h2 class="text-h5 font-weight-bold mb-1">{{ isEditing ? 'Editar Servicio' : 'Publicar Nuevo Servicio' }}</h2>
              <p class="text-body-2 text-medium-emphasis mb-0">
                {{ isEditing ? 'Modifica los detalles de tu servicio seleccionado.' : 'Define los detalles del servicio que ofreces a los clientes.' }}
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
                <v-combobox
                  v-model="service.category"
                  :items="categoriesList"
                  item-title="nombre"
                  item-value="id"
                  label="Categoría del Servicio"
                  placeholder="Selecciona una categoría o escribe una nueva..."
                  variant="outlined"
                  prepend-inner-icon="mdi-shape-outline"
                  color="primary"
                  :rules="[rules.required]"
                  hide-no-data
                ></v-combobox>
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

              <v-col cols="12" v-if="service.modality !== 'remota'">
                <v-text-field
                  v-model="service.location"
                  label="Ubicación del Servicio"
                  placeholder="Ej: Av. Corrientes 1234, Buenos Aires, Argentina"
                  variant="outlined"
                  prepend-inner-icon="mdi-map-marker-outline"
                  color="primary"
                  hint="Dirección donde se presta el servicio. Será visible para los clientes en el mapa."
                  persistent-hint
                ></v-text-field>
              </v-col>

              <v-col cols="12">
                <div class="d-flex align-center justify-space-between pa-4 rounded-lg bg-grey-lighten-4">
                  <div class="d-flex align-center">
                    <v-icon :color="service.active ? 'success' : 'grey'" class="mr-3" size="28">
                      {{ service.active ? 'mdi-check-circle' : 'mdi-close-circle' }}
                    </v-icon>
                    <div>
                      <div class="text-subtitle-2 font-weight-bold">
                        {{ service.active ? 'Servicio Activo' : 'Servicio Inactivo' }}
                      </div>
                      <div class="text-caption text-medium-emphasis">
                        {{ service.active ? 'Los clientes podrán ver y reservar este servicio.' : 'El servicio no será visible para los clientes.' }}
                      </div>
                    </div>
                  </div>
                  <v-switch
                    v-model="service.active"
                    color="success"
                    hide-details
                    inset
                  ></v-switch>
                </div>
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
                @click="isEditing ? cancelEdit() : resetForm()"
              >
                {{ isEditing ? 'Cancelar' : 'Limpiar' }}
              </v-btn>
              <v-btn
                type="submit"
                color="primary"
                :loading="isLoading"
                class="px-8 text-none font-weight-bold elevation-2"
              >
                {{ isEditing ? 'Actualizar Servicio' : 'Guardar Servicio' }}
                <v-icon end>{{ isEditing ? 'mdi-content-save-edit' : 'mdi-content-save' }}</v-icon>
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
              <v-card class="mb-3 rounded-lg border" elevation="0" color="white" :style="!item.active ? 'opacity: 0.6' : ''">
                <v-card-text class="pa-4">
                  <div class="d-flex justify-space-between align-center mb-1">
                    <div class="d-flex align-center" style="gap: 8px;">
                      <h4 class="text-subtitle-1 font-weight-bold text-primary">{{ item.name }}</h4>
                      <v-chip size="x-small" :color="item.active ? 'success' : 'grey'" variant="tonal" class="font-weight-bold">
                        {{ item.active ? 'Activo' : 'Inactivo' }}
                      </v-chip>
                    </div>
                    <v-chip size="small" :color="getModalityColor(item.modality)" variant="tonal" class="font-weight-bold text-uppercase" style="font-size: 0.7rem;">
                      {{ item.modality }}
                    </v-chip>
                  </div>
                  <div v-if="item.category" class="d-flex align-center mb-2">
                    <v-icon size="x-small" class="mr-1" color="grey">mdi-shape-outline</v-icon>
                    <span class="text-caption text-medium-emphasis">{{ item.category }}</span>
                  </div>
                  <p class="text-body-2 text-medium-emphasis text-truncate mb-2">{{ item.description }}</p>
                  <div v-if="item.location" class="d-flex align-center text-caption text-medium-emphasis mb-2">
                    <v-icon size="small" class="mr-1" color="primary">mdi-map-marker</v-icon>
                    {{ item.location }}
                  </div>
                  <v-divider class="my-2"></v-divider>
                  <div class="d-flex justify-space-between align-center">
                    <span class="text-caption font-weight-medium text-grey-darken-1">
                      <v-icon size="small" class="mr-1">mdi-clock-outline</v-icon> {{ item.duration }} min
                      <span class="text-subtitle-2 font-weight-bold text-success ml-3">
                        ${{ item.price }} USD
                      </span>
                    </span>
                    <div class="d-flex" style="gap: 4px;">
                      <v-btn
                        icon="mdi-pencil-outline"
                        variant="text"
                        size="x-small"
                        color="primary"
                        @click="editService(item)"
                        title="Editar servicio"
                      ></v-btn>
                      <v-btn
                        icon="mdi-trash-can-outline"
                        variant="text"
                        size="x-small"
                        color="error"
                        @click="deleteService(item)"
                        title="Eliminar servicio"
                      ></v-btn>
                    </div>
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
  modality: 'presencial',
  location: '',
  active: true
})

const categoriesList = ref([])
const publishedServices = ref([])
const isEditing = ref(false)
const editingServiceId = ref(null)

const loadCategories = async () => {
  try {
    const response = await fetch('http://localhost:8000/api/categorias', {
      headers: {
        'Accept': 'application/json'
      }
    })
    if (response.ok) {
      const data = await response.json()
      categoriesList.value = data.data || data
    }
  } catch (error) {
    console.error('Error loading categories:', error)
  }
}

onMounted(async () => {
  const token = localStorage.getItem('auth_token')
  if (!token) return

  // Load categories first
  await loadCategories()

  let idProfesional = null
  const userStr = localStorage.getItem('user')
  if (userStr) {
    try {
      const user = JSON.parse(userStr)
      idProfesional = user.id
    } catch (e) {}
  }

  try {
    const url = idProfesional
      ? `http://localhost:8000/api/servicios?id_profesional=${idProfesional}&incluir_inactivos=1`
      : 'http://localhost:8000/api/servicios'
      
    const response = await fetch(url, {
      headers: {
        'Authorization': `Bearer ${token}`,
        'Accept': 'application/json'
      }
    })
    
    if (response.ok) {
      const data = await response.json()
      const apiServices = data.data || data
      publishedServices.value = apiServices.map(s => ({
        id: s.id,
        name: s.nombre,
        description: s.descripcion,
        duration: s.duracion,
        price: s.precio,
        modality: s.modalidad,
        location: s.ubicacion || '',
        active: s.activo !== undefined ? s.activo : true,
        category: s.categoria?.nombre || 'Sin categoría',
        categoryObj: s.categoria ? { id: s.categoria.id, nombre: s.categoria.nombre } : (s.id_categoria ? { id: s.id_categoria, nombre: 'Categoría ' + s.id_categoria } : null)
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
    const url = isEditing.value 
      ? `http://localhost:8000/api/servicios/${editingServiceId.value}`
      : 'http://localhost:8000/api/servicios'
      
    const resolvedCategoryId = service.value.category 
      ? (typeof service.value.category === 'object' ? (service.value.category.id || service.value.category.nombre) : service.value.category)
      : 1

    const response = await fetch(url, {
      method: isEditing.value ? 'PUT' : 'POST',
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
        ubicacion: service.value.modality !== 'remota' ? service.value.location : null,
        activo: service.value.active,
        id_categoria: resolvedCategoryId
      })
    })

    const data = await response.json()

    if (!response.ok) {
      throw new Error(data.message || 'Error al guardar el servicio')
    }
    
    const s = data.data || data
    
    if (isEditing.value) {
      const idx = publishedServices.value.findIndex(item => item.id === editingServiceId.value)
      if (idx !== -1) {
        publishedServices.value[idx] = {
          id: s.id,
          name: s.nombre,
          description: s.descripcion,
          duration: s.duracion,
          price: s.precio,
          modality: s.modalidad,
          location: s.ubicacion || '',
          active: s.activo !== undefined ? s.activo : true,
          category: s.categoria?.nombre || 'Sin categoría',
          categoryObj: s.categoria ? { id: s.categoria.id, nombre: s.categoria.nombre } : (s.id_categoria ? { id: s.id_categoria, nombre: 'Categoría ' + s.id_categoria } : null)
        }
      }
      successMsg.value = 'Servicio actualizado exitosamente.'
      cancelEdit()
    } else {
      publishedServices.value.unshift({
        id: s.id,
        name: s.nombre,
        description: s.descripcion,
        duration: s.duracion,
        price: s.precio,
        modality: s.modalidad,
        location: s.ubicacion || '',
        active: s.activo !== undefined ? s.activo : true,
        category: s.categoria?.nombre || 'Sin categoría',
        categoryObj: s.categoria ? { id: s.categoria.id, nombre: s.categoria.nombre } : (s.id_categoria ? { id: s.id_categoria, nombre: 'Categoría ' + s.id_categoria } : null)
      })
      successMsg.value = 'Servicio guardado exitosamente.'
      form.value.reset()
      service.value.modality = 'presencial'
      service.value.active = true
      service.value.category = null
    }
    
  } catch (err) {
    errorMsg.value = err.message || 'Error al guardar el servicio. Intenta de nuevo.'
  } finally {
    isLoading.value = false
    setTimeout(() => { successMsg.value = '' }, 3000)
  }
}

const editService = (item) => {
  isEditing.value = true
  editingServiceId.value = item.id
  service.value = {
    name: item.name,
    description: item.description,
    duration: String(item.duration),
    price: String(item.price),
    modality: item.modality,
    location: item.location || '',
    active: item.active,
    category: item.categoryObj || null
  }
}

const cancelEdit = () => {
  isEditing.value = false
  editingServiceId.value = null
  resetForm()
}

const deleteService = async (item) => {
  if (!confirm(`¿Estás seguro de que deseas eliminar el servicio "${item.name}"?`)) {
    return
  }
  
  const token = localStorage.getItem('auth_token')
  try {
    const response = await fetch(`http://localhost:8000/api/servicios/${item.id}`, {
      method: 'DELETE',
      headers: {
        'Authorization': `Bearer ${token}`,
        'Accept': 'application/json'
      }
    })
    
    if (response.ok) {
      publishedServices.value = publishedServices.value.filter(s => s.id !== item.id)
      successMsg.value = 'Servicio eliminado exitosamente.'
      setTimeout(() => { successMsg.value = '' }, 3000)
      if (isEditing.value && editingServiceId.value === item.id) {
        cancelEdit()
      }
    } else {
      throw new Error('Error al eliminar el servicio')
    }
  } catch (err) {
    errorMsg.value = err.message || 'Error al eliminar el servicio.'
    setTimeout(() => { errorMsg.value = '' }, 3000)
  }
}

const resetForm = () => {
  form.value.reset()
  service.value.modality = 'presencial'
  service.value.active = true
  service.value.category = null
  errorMsg.value = ''
  successMsg.value = ''
}
</script>

<style scoped>
.border {
  border: 1px solid rgba(0,0,0,0.08) !important;
}
</style>
