<template>
  <DashboardLayout title="Mi Agenda">
    <v-row class="mt-4" justify="center">
      <v-col cols="12" xl="10">
        <div class="brand-header pa-6 mb-6 rounded-xl elevation-3 text-white d-flex align-center" style="background: linear-gradient(135deg, #A6987A 0%, #8C6D46 100%);">
          <v-icon size="40" class="mr-4">mdi-calendar-multiselect</v-icon>
          <div>
            <h1 class="text-h4 font-weight-bold mb-1">Mi Agenda</h1>
            <p class="text-subtitle-1 opacity-90 mb-0">Planifica y visualiza tu día</p>
          </div>
        </div>

        <v-row>
          <!-- Panel Izquierdo: Calendario -->
          <v-col cols="12" md="5" lg="4">
            <v-card class="elevation-4 rounded-xl border-card h-100">
              <v-card-text class="pa-0">
                <v-date-picker 
                  v-model="fechaSeleccionada" 
                  color="primary"
                  elevation="0"
                  class="w-100 rounded-xl"
                  title="Selecciona una fecha"
                  hide-header
                ></v-date-picker>
              </v-card-text>
              <v-divider></v-divider>
              <v-card-text class="bg-grey-lighten-4 pa-4 text-center">
                <p class="text-subtitle-2 font-weight-bold mb-1 text-primary">
                  {{ formatDateLong(fechaSeleccionada) }}
                </p>
                <v-chip size="small" :color="turnosDelDia.length > 0 ? 'success' : 'grey'" class="font-weight-bold">
                  {{ turnosDelDia.length }} {{ turnosDelDia.length === 1 ? 'Turno' : 'Turnos' }}
                </v-chip>
              </v-card-text>
            </v-card>
          </v-col>

          <!-- Panel Derecho: Cronograma del Día -->
          <v-col cols="12" md="7" lg="8">
            <v-card class="elevation-4 rounded-xl border-card h-100 d-flex flex-column">
              <v-card-title class="pa-6 pb-2 d-flex align-center">
                <v-icon class="mr-3" color="primary">mdi-format-list-bulleted</v-icon>
                <span class="text-h6 font-weight-bold text-grey-darken-3">Cronograma del Día</span>
                <v-spacer></v-spacer>
                <v-btn v-if="turnosDelDia.length > 0" icon variant="text" size="small" color="grey" @click="cargarReservas">
                  <v-icon>mdi-refresh</v-icon>
                </v-btn>
              </v-card-title>
              
              <v-divider class="mx-4"></v-divider>

              <v-card-text class="pa-6 flex-grow-1 overflow-auto bg-grey-lighten-5">
                <div v-if="cargando" class="text-center py-12">
                  <v-progress-circular indeterminate color="primary" size="64"></v-progress-circular>
                  <p class="mt-4 text-medium-emphasis">Cargando agenda...</p>
                </div>

                <div v-else-if="turnosDelDia.length > 0">
                  <div class="timeline-container">
                    <div 
                      v-for="(reserva, index) in turnosDelDia" 
                      :key="reserva.id" 
                      class="d-flex mb-4"
                    >
                      <!-- Columna de Hora -->
                      <div class="mr-4 text-right pt-2" style="min-width: 65px;">
                        <strong class="text-subtitle-1 font-weight-bold text-primary">{{ formatTime(reserva.fecha_hora_inicio) }}</strong>
                        <div class="text-caption text-medium-emphasis">{{ reserva.servicio?.duracion }} min</div>
                      </div>
                      
                      <!-- Columna de Línea/Punto -->
                      <div class="d-flex flex-column align-center mr-4 pt-3">
                        <v-icon :color="getColorEstado(reserva.estado)" size="16">mdi-circle</v-icon>
                        <div v-if="index !== turnosDelDia.length - 1" class="flex-grow-1 mt-2" style="width: 2px; background-color: rgba(140, 109, 70, 0.2); min-height: 50px;"></div>
                      </div>
                      
                      <!-- Columna de Tarjeta -->
                      <v-card class="elevation-2 rounded-lg border-panel flex-grow-1">
                        <v-card-text class="pa-4">
                          <div class="d-flex justify-space-between align-start mb-2">
                            <h4 class="text-subtitle-1 font-weight-bold text-grey-darken-3 mb-0">
                              {{ reserva.servicio?.nombre }}
                            </h4>
                            <v-chip size="x-small" :color="getColorEstado(reserva.estado)" class="text-uppercase font-weight-bold">
                              {{ reserva.estado }}
                            </v-chip>
                          </div>
                          
                          <!-- Modalidad y Ubicación -->
                          <div class="d-flex align-center flex-wrap mb-3" style="gap: 8px;">
                            <v-chip 
                              size="x-small" 
                              :color="getModalityColor(reserva.servicio?.modalidad)" 
                              variant="tonal" 
                              class="font-weight-bold text-uppercase"
                            >
                              {{ reserva.servicio?.modalidad || 'Presencial' }}
                            </v-chip>
                            <v-tooltip v-if="reserva.servicio?.ubicacion" text="Ver en el mapa" location="top">
                              <template v-slot:activator="{ props }">
                                <span 
                                  v-bind="props" 
                                  class="location-link d-inline-flex align-center cursor-pointer" 
                                  @click.stop="openMap(reserva.servicio)"
                                >
                                  <v-icon size="x-small" color="primary" class="mr-1">mdi-map-marker</v-icon>
                                  <span class="text-primary font-weight-bold">{{ reserva.servicio.ubicacion }}</span>
                                </span>
                              </template>
                            </v-tooltip>
                          </div>
                          
                          <div class="d-flex align-center mt-2">
                            <v-avatar color="grey-lighten-3" size="32" class="mr-3">
                              <v-icon size="20" color="grey-darken-1">mdi-account</v-icon>
                            </v-avatar>
                            <div>
                              <p class="mb-0 text-body-2 font-weight-medium">
                                {{ isCliente ? reserva.servicio?.profesional?.usuario?.nombre : reserva.cliente?.usuario?.nombre }}
                                {{ isCliente ? '' : reserva.cliente?.usuario?.apellido }}
                              </p>
                              <p class="mb-0 text-caption text-medium-emphasis">
                                {{ isCliente ? 'Profesional' : 'Cliente' }}
                              </p>
                            </div>
                          </div>
                        </v-card-text>
                      </v-card>
                    </div>
                  </div>
                </div>

                <div v-else class="text-center py-12 px-4 d-flex flex-column align-center justify-center h-100 opacity-70">
                  <v-avatar color="success" variant="tonal" size="100" class="mb-4">
                    <v-icon size="50">mdi-coffee-outline</v-icon>
                  </v-avatar>
                  <h3 class="text-h6 font-weight-bold text-grey-darken-2">Tu día está libre</h3>
                  <p class="text-body-1 text-medium-emphasis">No tienes ningún turno programado para esta fecha.</p>
                </div>
              </v-card-text>
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
import { ref, onMounted, computed, watch, nextTick } from 'vue'
import DashboardLayout from '../components/DashboardLayout.vue'
import L from 'leaflet'
import 'leaflet/dist/leaflet.css'

// --- ESTADO ---
const fechaSeleccionada = ref(new Date())
const reservas = ref([])
const cargando = ref(true)
const isCliente = ref(false)

// Map state
const mapDialog = ref(false)
const mapLoading = ref(false)
const mapError = ref(false)
const selectedService = ref(null)
const mapCoords = ref(null)
let mapInstance = null

// --- COMPUTADAS ---
const turnosDelDia = computed(() => {
  if (!fechaSeleccionada.value) return []
  
  // Convertir fechaSeleccionada a YYYY-MM-DD para comparar localmente
  const offset = fechaSeleccionada.value.getTimezoneOffset()
  const fechaStr = new Date(fechaSeleccionada.value.getTime() - (offset*60*1000)).toISOString().split('T')[0]
  
  return reservas.value.filter(r => {
    // ignorar las canceladas o rechazadas para que la agenda se vea limpia, o mostrarlas pero grises.
    // vamos a mostrar todas para tener la info completa, o quizas esconder las canceladas.
    if(r.estado === 'cancelada' || r.estado === 'rechazada') return false;
    
    return r.fecha_hora_inicio.startsWith(fechaStr)
  }).sort((a,b) => new Date(a.fecha_hora_inicio) - new Date(b.fecha_hora_inicio))
})

// --- MÉTODOS ---
const getAuthHeaders = () => ({
  'Content-Type': 'application/json',
  'Accept': 'application/json',
  'Authorization': `Bearer ${localStorage.getItem('auth_token')}`
})

const cargarReservas = async () => {
  cargando.value = true
  try {
    const res = await fetch('http://localhost:8000/api/reservas', { headers: getAuthHeaders() })
    if (res.ok) {
      const data = await res.json()
      reservas.value = data.data || []
    }
  } catch (err) {
    console.error('Error cargando agenda', err)
  } finally {
    cargando.value = false
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

// --- UTILS ---
const formatTime = (dateStr) => {
  const d = new Date(dateStr)
  return d.toLocaleTimeString('es-ES', { hour: '2-digit', minute: '2-digit' })
}

const formatDateLong = (dateObj) => {
  if (!dateObj) return ''
  return dateObj.toLocaleDateString('es-ES', { weekday: 'long', day: 'numeric', month: 'long', year: 'numeric' })
    .replace(/^\w/, c => c.toUpperCase())
}

const getColorEstado = (estado) => ({ 
  pendiente: 'warning', 
  confirmada: 'success', 
  cancelada: 'error', 
  pagada: 'primary', 
  finalizada: 'grey' 
}[estado] || 'grey')

const getModalityColor = (modality) => {
  switch (modality) {
    case 'remota': return 'info'
    case 'presencial': return 'primary'
    case 'hibrida': return 'deep-purple'
    default: return 'grey'
  }
}

onMounted(() => {
  const user = JSON.parse(localStorage.getItem('user') || '{}')
  isCliente.value = user.role !== 'profesional'
  cargarReservas()
})
</script>

<style scoped>
.border-card { 
  border: 1px solid rgba(140, 109, 70, 0.1); 
}
.border-panel { 
  border: 1px solid rgba(140, 109, 70, 0.15); 
}
/* Ocultar barra de desplazamiento para que se vea mas limpio pero mantenga el scroll */
.overflow-auto::-webkit-scrollbar {
  width: 6px;
}
.overflow-auto::-webkit-scrollbar-thumb {
  background-color: rgba(140, 109, 70, 0.2);
  border-radius: 10px;
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
