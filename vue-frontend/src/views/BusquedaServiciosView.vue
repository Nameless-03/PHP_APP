<template>
  <DashboardLayout title="Buscar Servicios">
    <v-row>
      <!-- Panel de Filtros -->
      <v-col cols="12" md="3">
        <v-card class="rounded-xl elevation-2 pa-4 position-sticky" style="top: 20px;">
          <h3 class="text-h6 font-weight-bold mb-4 d-flex align-center">
            <v-icon color="primary" class="mr-2">mdi-filter-variant</v-icon>
            Filtros
          </h3>
          <v-divider class="mb-4"></v-divider>

          <!-- Búsqueda Principal -->
          <v-text-field
            v-model="filters.keyword"
            label="Buscar por palabra clave"
            placeholder="Ej. Abogado, Diseño..."
            variant="outlined"
            density="comfortable"
            prepend-inner-icon="mdi-magnify"
            color="primary"
            class="mb-4"
            clearable
            hide-details
          ></v-text-field>

          <!-- Rango de Precios -->
          <div class="mb-4">
            <div class="text-subtitle-2 text-medium-emphasis mb-2">Precio máximo (USD)</div>
            <v-slider
              v-model="filters.precio_max"
              :min="0"
              :max="500"
              :step="10"
              thumb-label
              color="primary"
              track-color="grey-lighten-2"
              hide-details
            >
              <template v-slot:append>
                <v-text-field
                  v-model="filters.precio_max"
                  type="number"
                  style="width: 80px"
                  density="compact"
                  hide-details
                  variant="outlined"
                ></v-text-field>
              </template>
            </v-slider>
          </div>

          <!-- Modalidad -->
          <div class="mb-4">
            <div class="text-subtitle-2 text-medium-emphasis mb-2">Modalidad</div>
            <v-select
              v-model="filters.modalidad"
              :items="modalidades"
              item-title="title"
              item-value="value"
              label="Cualquiera"
              variant="outlined"
              density="comfortable"
              color="primary"
              hide-details
              clearable
            ></v-select>
          </div>

          <!-- Calificación Mínima -->
          <div class="mb-4">
            <div class="text-subtitle-2 text-medium-emphasis mb-2">Calificación Mínima</div>
            <v-rating
              v-model="filters.reputacion"
              color="warning"
              active-color="warning"
              hover
              half-increments
              density="compact"
            ></v-rating>
            <div class="text-caption text-medium-emphasis text-center mt-1">
              {{ filters.reputacion }} estrellas o más
            </div>
          </div>
          
          <v-btn 
            block 
            variant="tonal" 
            color="primary" 
            class="text-none font-weight-bold"
            @click="resetFilters"
          >
            Limpiar Filtros
          </v-btn>
        </v-card>
      </v-col>

      <!-- Resultados -->
      <v-col cols="12" md="9">
        <div class="d-flex justify-space-between align-center mb-6">
          <h2 class="text-h5 font-weight-bold text-grey-darken-4">Resultados ({{ services.length }})</h2>
        </div>

        <!-- Skeleton Loader -->
        <v-row v-if="isLoading">
          <v-col cols="12" sm="6" lg="4" v-for="i in 6" :key="i">
            <v-skeleton-loader type="card, article"></v-skeleton-loader>
          </v-col>
        </v-row>

        <!-- Empty State -->
        <v-card v-else-if="services.length === 0" class="pa-10 text-center rounded-xl elevation-1 bg-grey-lighten-4">
          <v-icon size="80" color="grey-lighten-1" class="mb-4">mdi-magnify-close</v-icon>
          <h3 class="text-h5 font-weight-bold text-grey-darken-2 mb-2">No se encontraron servicios</h3>
          <p class="text-body-1 text-medium-emphasis">
            Intenta ajustar tus filtros o probar con otras palabras clave.
          </p>
          <v-btn color="primary" class="mt-4 text-none font-weight-bold" @click="resetFilters">
            Ver Todos los Servicios
          </v-btn>
        </v-card>

        <!-- Service Cards -->
        <v-row v-else>
          <v-col cols="12" sm="6" lg="4" v-for="service in services" :key="service.id">
            <v-card class="rounded-xl elevation-1 h-100 d-flex flex-column card-hover">
              <div class="pa-4 flex-grow-1">
                <div class="d-flex justify-space-between align-start mb-2">
                  <v-chip size="small" :color="getModalityColor(service.modalidad)" variant="tonal" class="font-weight-bold text-uppercase">
                    {{ service.modalidad }}
                  </v-chip>
                  <div class="text-h6 font-weight-black text-success">
                    ${{ service.precio }}
                  </div>
                </div>
                
                <h4 class="text-h6 font-weight-bold text-grey-darken-4 mb-2 line-clamp-2">
                  {{ service.nombre }}
                </h4>
                
                <p class="text-body-2 text-medium-emphasis mb-4 line-clamp-3">
                  {{ service.descripcion }}
                </p>

                <v-divider class="mb-3"></v-divider>

                <div class="d-flex align-center">
                  <v-avatar size="36" color="primary-lighten-1" class="mr-3">
                    <span class="text-caption font-weight-bold text-white">
                      {{ service.profesional?.nombre ? service.profesional.nombre.substring(0, 2).toUpperCase() : 'PR' }}
                    </span>
                  </v-avatar>
                  <div>
                    <div class="text-subtitle-2 font-weight-medium text-truncate" style="max-width: 140px;">
                      {{ service.profesional?.nombre || 'Profesional Anónimo' }}
                    </div>
                    <div class="d-flex align-center text-caption text-warning font-weight-bold">
                      <v-icon size="small" class="mr-1">mdi-star</v-icon>
                      {{ service.profesional?.reputacion?.toFixed(1) || '0.0' }}
                    </div>
                  </div>
                </div>
              </div>

              <div class="pa-4 bg-grey-lighten-4 mt-auto">
                <div class="d-flex align-center text-caption text-medium-emphasis mb-3">
                  <v-icon size="small" class="mr-1">mdi-clock-outline</v-icon> {{ service.duracion }} min
                  <span v-if="service.ubicacion" class="mx-2">•</span>
                  <v-icon v-if="service.ubicacion" size="small" class="mr-1">mdi-map-marker-outline</v-icon> {{ service.ubicacion }}
                </div>
                <v-btn block color="primary" class="text-none font-weight-bold elevation-1 rounded-lg" @click="reservarServicio(service.id)">
                  Reservar Turno
                </v-btn>
              </div>
            </v-card>
          </v-col>
        </v-row>
      </v-col>
    </v-row>
  </DashboardLayout>
</template>

<script setup>
import { ref, onMounted, watch } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import DashboardLayout from '../components/DashboardLayout.vue'

const route = useRoute()
const router = useRouter()
const isLoading = ref(true)
const services = ref([])
let searchTimeout = null

const modalidades = [
  { title: 'Cualquiera', value: null },
  { title: 'Presencial', value: 'presencial' },
  { title: 'Remota', value: 'remota' },
  { title: 'Híbrida', value: 'hibrida' }
]

const filters = ref({
  keyword: route.query.q || '',
  precio_max: 500,
  modalidad: null,
  reputacion: 0
})

const fetchServices = async () => {
  isLoading.value = true
  const token = localStorage.getItem('auth_token')
  if (!token) return

  try {
    const queryParams = new URLSearchParams()
    
    if (filters.value.keyword) queryParams.append('keyword', filters.value.keyword)
    if (filters.value.precio_max !== null && filters.value.precio_max !== '' && filters.value.precio_max < 500) queryParams.append('precio_max', filters.value.precio_max)
    if (filters.value.modalidad) queryParams.append('modalidad', filters.value.modalidad)
    if (filters.value.reputacion > 0) queryParams.append('reputacion', filters.value.reputacion)

    const response = await fetch(`http://localhost:8000/api/servicios?${queryParams.toString()}`, {
      headers: {
        'Authorization': `Bearer ${token}`,
        'Accept': 'application/json'
      }
    })
    
    if (response.ok) {
      const data = await response.json()
      services.value = data.data || data
    }
  } catch (error) {
    console.error('Error fetching search results:', error)
  } finally {
    isLoading.value = false
  }
}

// Watch filters for real-time debounce search
watch(filters, () => {
  if (searchTimeout) clearTimeout(searchTimeout)
  searchTimeout = setTimeout(() => {
    fetchServices()
  }, 500) // 500ms debounce
}, { deep: true })

onMounted(() => {
  fetchServices()
})

const resetFilters = () => {
  filters.value = {
    keyword: '',
    precio_max: 500,
    modalidad: null,
    reputacion: 0
  }
}

const getModalityColor = (modality) => {
  switch (modality) {
    case 'remota': return 'info'
    case 'presencial': return 'primary'
    case 'hibrida': return 'deep-purple'
    default: return 'grey'
  }
}

const reservarServicio = (id_servicio) => {
  router.push({ name: 'mis-reservas', query: { action: 'reservar', servicio: id_servicio } })
}
</script>

<style scoped>
.card-hover {
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
  border: 1px solid transparent;
}
.card-hover:hover {
  transform: translateY(-4px);
  box-shadow: 0 12px 20px -8px rgba(0, 0, 0, 0.15) !important;
  border-color: rgba(var(--v-theme-primary), 0.2);
}
.line-clamp-2 {
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}
.line-clamp-3 {
  display: -webkit-box;
  -webkit-line-clamp: 3;
  -webkit-box-orient: vertical;
  overflow: hidden;
}
.position-sticky {
  position: sticky;
  z-index: 10;
}
</style>
