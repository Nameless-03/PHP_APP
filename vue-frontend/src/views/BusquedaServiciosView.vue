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
                  <v-tooltip v-if="service.ubicacion" text="Ver en el mapa" location="top">
                    <template v-slot:activator="{ props }">
                      <span 
                        v-bind="props" 
                        class="location-link d-inline-flex align-center" 
                        @click.stop="openMap(service)"
                      >
                        <v-icon size="small" class="mr-1" color="primary">mdi-map-marker</v-icon>
                        <span class="text-primary">{{ service.ubicacion }}</span>
                      </span>
                    </template>
                  </v-tooltip>
                  <span v-else-if="!service.ubicacion"></span>
                </div>
                <v-btn block color="primary" class="text-none font-weight-bold elevation-1 rounded-lg">
                  Reservar Turno
                </v-btn>
              </div>
            </v-card>
          </v-col>
        </v-row>
      </v-col>
    </v-row>

    <!-- Map Dialog -->
    <v-dialog v-model="mapDialog" max-width="700" persistent>
      <v-card class="rounded-xl overflow-hidden">
        <div class="map-dialog-header pa-4 d-flex align-center justify-space-between">
          <div class="d-flex align-center">
            <v-icon color="white" class="mr-3" size="28">mdi-map-marker-radius</v-icon>
            <div>
              <div class="text-h6 font-weight-bold text-white">{{ selectedService?.nombre }}</div>
              <div class="text-caption text-white" style="opacity: 0.85;">{{ selectedService?.ubicacion }}</div>
            </div>
          </div>
          <v-btn icon variant="text" @click="closeMap" size="small">
            <v-icon color="white">mdi-close</v-icon>
          </v-btn>
        </div>

        <!-- Loading State -->
        <div v-if="mapLoading" class="d-flex flex-column align-center justify-center" style="height: 400px;">
          <v-progress-circular indeterminate color="primary" size="48" class="mb-4"></v-progress-circular>
          <div class="text-body-1 text-medium-emphasis">Buscando ubicación...</div>
        </div>

        <!-- Error State -->
        <div v-else-if="mapError" class="d-flex flex-column align-center justify-center pa-8" style="height: 400px;">
          <v-icon size="64" color="warning" class="mb-4">mdi-map-marker-question</v-icon>
          <div class="text-h6 font-weight-bold text-grey-darken-2 mb-2">Ubicación no encontrada</div>
          <div class="text-body-2 text-medium-emphasis text-center mb-4">
            No se pudo encontrar la dirección:<br/>
            <strong>"{{ selectedService?.ubicacion }}"</strong>
          </div>
          <v-btn color="primary" variant="tonal" class="text-none" @click="closeMap">
            Cerrar
          </v-btn>
        </div>

        <!-- Map Container -->
        <div v-else id="map-container" style="height: 400px; width: 100%;"></div>

        <div v-if="!mapLoading && !mapError" class="pa-4 bg-grey-lighten-4">
          <div class="d-flex align-center justify-space-between">
            <div class="d-flex align-center">
              <v-icon size="small" class="mr-2" color="primary">mdi-information-outline</v-icon>
              <span class="text-caption text-medium-emphasis">Ubicación aproximada basada en la dirección</span>
            </div>
            <v-btn 
              v-if="mapCoords" 
              size="small" 
              color="primary" 
              variant="tonal" 
              class="text-none"
              :href="`https://www.google.com/maps/search/?api=1&query=${mapCoords.lat},${mapCoords.lng}`"
              target="_blank"
            >
              <v-icon size="small" class="mr-1">mdi-google-maps</v-icon>
              Abrir en Google Maps
            </v-btn>
          </div>
        </div>
      </v-card>
    </v-dialog>

  </DashboardLayout>
</template>

<script setup>
import { ref, onMounted, watch, nextTick } from 'vue'
import { useRoute } from 'vue-router'
import DashboardLayout from '../components/DashboardLayout.vue'
import L from 'leaflet'
import 'leaflet/dist/leaflet.css'

const route = useRoute()
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

// Map state
const mapDialog = ref(false)
const mapLoading = ref(false)
const mapError = ref(false)
const selectedService = ref(null)
const mapCoords = ref(null)
let mapInstance = null

const fetchServices = async () => {
  isLoading.value = true
  const token = localStorage.getItem('auth_token')
  if (!token) return

  try {
    const queryParams = new URLSearchParams()
    
    if (filters.value.keyword) queryParams.append('keyword', filters.value.keyword)
    if (filters.value.precio_max && filters.value.precio_max < 500) queryParams.append('precio_max', filters.value.precio_max)
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

// ===== Map Functions =====
const openMap = async (service) => {
  selectedService.value = service
  mapDialog.value = true
  mapLoading.value = true
  mapError.value = false
  mapCoords.value = null

  try {
    // Geocode the address using Nominatim (OpenStreetMap free API)
    const address = encodeURIComponent(service.ubicacion)
    const response = await fetch(
      `https://nominatim.openstreetmap.org/search?format=json&q=${address}&limit=1`,
      { headers: { 'Accept-Language': 'es' } }
    )
    const results = await response.json()

    if (results.length === 0) {
      mapError.value = true
      return
    }

    const { lat, lon } = results[0]
    mapCoords.value = { lat: parseFloat(lat), lng: parseFloat(lon) }
    mapLoading.value = false

    // Wait for DOM to render the map container
    await nextTick()
    setTimeout(() => initMap(), 100)
  } catch (error) {
    console.error('Geocoding error:', error)
    mapError.value = true
  } finally {
    mapLoading.value = false
  }
}

const initMap = () => {
  const container = document.getElementById('map-container')
  if (!container || !mapCoords.value) return

  // Destroy previous map instance if it exists
  if (mapInstance) {
    mapInstance.remove()
    mapInstance = null
  }

  const { lat, lng } = mapCoords.value

  mapInstance = L.map('map-container').setView([lat, lng], 15)

  L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>',
    maxZoom: 19
  }).addTo(mapInstance)

  // Custom marker icon
  const customIcon = L.divIcon({
    html: `<div class="custom-marker">
             <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="36" height="36" fill="#8C6D46">
               <path d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7zm0 9.5c-1.38 0-2.5-1.12-2.5-2.5s1.12-2.5 2.5-2.5 2.5 1.12 2.5 2.5-1.12 2.5-2.5 2.5z"/>
             </svg>
           </div>`,
    className: 'custom-div-icon',
    iconSize: [36, 36],
    iconAnchor: [18, 36],
    popupAnchor: [0, -36]
  })

  const marker = L.marker([lat, lng], { icon: customIcon }).addTo(mapInstance)

  // Popup with service info
  const popupContent = `
    <div style="font-family: Inter, sans-serif; min-width: 200px;">
      <div style="font-weight: 700; font-size: 14px; color: #333; margin-bottom: 4px;">
        ${selectedService.value.nombre}
      </div>
      <div style="font-size: 12px; color: #666; margin-bottom: 6px;">
        📍 ${selectedService.value.ubicacion}
      </div>
      <div style="display: flex; gap: 12px; font-size: 12px; color: #888;">
        <span>⏱ ${selectedService.value.duracion} min</span>
        <span style="color: #2e7d32; font-weight: 600;">$${selectedService.value.precio}</span>
      </div>
    </div>
  `
  marker.bindPopup(popupContent).openPopup()

  // Force map to recalculate its size
  setTimeout(() => mapInstance.invalidateSize(), 200)
}

const closeMap = () => {
  mapDialog.value = false
  if (mapInstance) {
    mapInstance.remove()
    mapInstance = null
  }
  selectedService.value = null
  mapCoords.value = null
}

const getModalityColor = (modality) => {
  switch (modality) {
    case 'remota': return 'info'
    case 'presencial': return 'primary'
    case 'hibrida': return 'deep-purple'
    default: return 'grey'
  }
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
.location-link {
  cursor: pointer;
  transition: all 0.2s ease;
  border-radius: 4px;
  padding: 2px 4px;
  margin: -2px -4px;
}
.location-link:hover {
  background-color: rgba(var(--v-theme-primary), 0.08);
}
.map-dialog-header {
  background: linear-gradient(135deg, #8C6D46 0%, #6B5235 100%);
}
</style>

<style>
/* Leaflet overrides (unscoped needed) */
.custom-div-icon {
  background: transparent;
  border: none;
}
.custom-marker {
  filter: drop-shadow(0 3px 4px rgba(0,0,0,0.3));
  animation: marker-bounce 0.5s ease-out;
}
@keyframes marker-bounce {
  0% { transform: translateY(-20px); opacity: 0; }
  60% { transform: translateY(4px); }
  100% { transform: translateY(0); opacity: 1; }
}
.leaflet-popup-content-wrapper {
  border-radius: 12px !important;
  box-shadow: 0 8px 24px rgba(0,0,0,0.15) !important;
}
.leaflet-popup-tip {
  box-shadow: 0 4px 8px rgba(0,0,0,0.1) !important;
}
</style>
