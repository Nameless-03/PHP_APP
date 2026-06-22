<template>
  <DashboardLayout title="Profesionales">
    <!-- Header visual -->
    <v-row class="mb-6">
      <v-col cols="12">
        <v-card class="pa-8 rounded-xl elevation-2 bg-gradient text-white">
          <div class="d-flex align-center flex-wrap">
            <v-avatar color="white" size="64" class="mr-6 elevation-2 text-primary font-weight-black">
              <v-icon size="36" color="primary">mdi-account-group</v-icon>
            </v-avatar>
            <div>
              <h1 class="text-h4 font-weight-bold mb-2">Nuestros Profesionales</h1>
              <p class="text-body-1 opacity-80 mb-0">
                Conoce a los expertos registrados en nuestra plataforma. Revisa sus perfiles, trayectoria, opiniones de clientes y agenda sus servicios directamente.
              </p>
            </div>
          </div>
        </v-card>
      </v-col>
    </v-row>

    <!-- Loading state -->
    <v-row v-if="isLoading">
      <v-col cols="12" sm="6" md="4" lg="3" v-for="i in 8" :key="i">
        <v-skeleton-loader type="card, list-item-two-line"></v-skeleton-loader>
      </v-col>
    </v-row>

    <!-- Empty state -->
    <v-row v-else-if="profesionales.length === 0" justify="center">
      <v-col cols="12" md="6" class="text-center py-12">
        <v-icon size="80" color="grey-lighten-1" class="mb-4">mdi-account-off-outline</v-icon>
        <h3 class="text-h5 font-weight-bold text-grey-darken-2 mb-2">No hay profesionales registrados</h3>
        <p class="text-body-1 text-medium-emphasis">Vuelve a consultar más tarde.</p>
      </v-col>
    </v-row>

    <!-- Grilla y Filtros -->
    <v-row v-else>
      <!-- Sidebar de Filtros -->
      <v-col cols="12" md="3">
        <v-card class="pa-5 rounded-xl border-card mb-4 bg-grey-lighten-4" elevation="0">
          <div class="d-flex align-center mb-4">
            <v-icon color="primary" class="mr-2">mdi-filter-variant</v-icon>
            <h3 class="text-h6 font-weight-bold">Filtros</h3>
          </div>

          <v-text-field
            v-model="filtroNombre"
            label="Buscar por nombre"
            prepend-inner-icon="mdi-magnify"
            variant="outlined"
            density="comfortable"
            color="primary"
            bg-color="white"
            class="mb-4"
            hide-details
            clearable
          ></v-text-field>

          <v-select
            v-model="filtroModalidad"
            :items="['Cualquiera', 'presencial', 'remota', 'hibrida']"
            label="Modalidad preferida"
            prepend-inner-icon="mdi-laptop"
            variant="outlined"
            density="comfortable"
            color="primary"
            bg-color="white"
            class="mb-4"
            hide-details
          ></v-select>

          <v-card-subtitle class="px-0 pt-0 pb-2 text-primary font-weight-bold">Reputación mínima</v-card-subtitle>
          <div class="d-flex align-center justify-space-between mb-4">
            <v-rating
              v-model="filtroReputacion"
              color="warning"
              active-color="warning"
              half-increments
              hover
              size="small"
              density="compact"
            ></v-rating>
            <span class="text-caption font-weight-bold">{{ filtroReputacion }} / 5</span>
          </div>

          <v-btn
            block
            color="grey-darken-3"
            variant="flat"
            class="text-none font-weight-bold"
            @click="limpiarFiltros"
            prepend-icon="mdi-refresh"
          >
            Limpiar Filtros
          </v-btn>
        </v-card>
      </v-col>

      <!-- Resultados -->
      <v-col cols="12" md="9">
        <v-row v-if="filteredProfesionales.length > 0">
          <v-col cols="12" sm="6" lg="4" v-for="prof in filteredProfesionales" :key="prof.id_usuario">
            <v-card 
              class="rounded-xl elevation-1 h-100 d-flex flex-column card-hover position-relative overflow-hidden"
              @click="verDetalleProfesional(prof.id_usuario)"
            >
              <div class="pa-5 flex-grow-1 text-center">
                <!-- Avatar -->
                <v-avatar size="100" class="elevation-3 mb-4 mx-auto border-avatar" color="primary">
                  <v-img v-if="prof.foto_perfil" :src="prof.foto_perfil" alt="Foto de perfil"></v-img>
                  <span v-else class="text-h4 text-white font-weight-bold">{{ prof.nombre.substring(0, 2).toUpperCase() }}</span>
                </v-avatar>

                <!-- Info -->
                <h3 class="text-h6 font-weight-bold text-grey-darken-4 mb-1 line-clamp-1">
                  {{ prof.nombre }}
                </h3>
                
                <div class="d-flex align-center justify-center text-warning font-weight-bold text-caption mb-3">
                  <v-icon size="small" class="mr-1">mdi-star</v-icon>
                  {{ prof.reputacion?.toFixed(1) || '0.0' }}
                </div>

                <v-chip size="x-small" color="primary" variant="tonal" class="mb-3 font-weight-bold text-uppercase">
                  <v-icon start size="12">mdi-laptop</v-icon>
                  {{ prof.modalidad_preferida }}
                </v-chip>

                <p class="text-body-2 text-medium-emphasis mb-4 line-clamp-3 text-left-align" style="min-height: 60px;">
                  {{ prof.descripcion || 'Sin descripción disponible.' }}
                </p>

                <v-divider class="mb-4"></v-divider>

                <div class="d-flex align-center text-caption text-medium-emphasis justify-space-between px-1">
                  <span class="d-flex align-center">
                    <v-icon size="small" color="primary" class="mr-1">mdi-briefcase-outline</v-icon>
                    {{ prof.experiencia || 'N/D' }}
                  </span>
                  <span class="d-flex align-center text-truncate" style="max-width: 130px;">
                    <v-icon size="small" color="error" class="mr-1">mdi-map-marker-outline</v-icon>
                    {{ prof.ubicacion || 'No especificada' }}
                  </span>
                </div>
              </div>

              <div class="pa-4 bg-grey-lighten-5 mt-auto">
                <v-btn 
                  block 
                  color="primary" 
                  variant="flat" 
                  class="text-none font-weight-bold rounded-lg"
                >
                  Ver Perfil
                  <v-icon end size="16">mdi-chevron-right</v-icon>
                </v-btn>
              </div>
            </v-card>
          </v-col>
        </v-row>
        <v-row v-else justify="center">
          <v-col cols="12" class="text-center py-12">
            <v-icon size="64" color="grey-lighten-1" class="mb-4">mdi-magnify-close</v-icon>
            <h3 class="text-h6 font-weight-bold text-grey-darken-2 mb-2">No se encontraron profesionales</h3>
            <p class="text-body-2 text-medium-emphasis">Intenta ajustar los filtros de búsqueda.</p>
          </v-col>
        </v-row>
      </v-col>
    </v-row>

    <!-- MODAL DE DETALLE DEL PROFESIONAL -->
    <v-dialog v-model="dialogDetalle" max-width="850" scrollable>
      <v-card v-if="profesionalDetalle" class="rounded-xl overflow-hidden dialog-layout">
        <!-- Header -->
        <div class="brand-header pa-6 text-white position-relative">
          <div class="d-flex justify-end position-absolute" style="top: 12px; right: 12px; z-index: 10;">
            <v-btn icon variant="text" color="white" @click="dialogDetalle = false" size="small">
              <v-icon>mdi-close</v-icon>
            </v-btn>
          </div>

          <div class="d-flex align-center flex-wrap mt-2">
            <v-avatar size="100" class="elevation-4 mr-6 border-avatar-white" color="white">
              <v-img v-if="profesionalDetalle.foto_perfil" :src="profesionalDetalle.foto_perfil" alt="Foto de perfil"></v-img>
              <span v-else class="text-h4 text-primary font-weight-bold">{{ profesionalDetalle.nombre.substring(0, 2).toUpperCase() }}</span>
            </v-avatar>
            
            <div class="flex-grow-1">
              <h2 class="text-h4 font-weight-bold mb-1">{{ profesionalDetalle.nombre }}</h2>
              <p class="text-subtitle-1 opacity-90 mb-2 d-flex align-center">
                <v-icon start size="18" class="mr-1">mdi-email-outline</v-icon>
                {{ profesionalDetalle.email }}
              </p>
              
              <div class="d-flex flex-wrap gap-2">
                <v-chip size="small" color="white" variant="flat" class="text-primary font-weight-bold">
                  <v-icon start size="14">mdi-star</v-icon>
                  {{ profesionalDetalle.reputacion?.toFixed(1) || '0.0' }} Calificación
                </v-chip>
                <v-chip size="small" color="white" variant="outlined" class="font-weight-bold text-uppercase">
                  <v-icon start size="14">mdi-laptop</v-icon>
                  {{ profesionalDetalle.modalidad_preferida }}
                </v-chip>
              </div>
            </div>
          </div>
        </div>

        <v-tabs v-model="tab" color="primary" grow class="border-b bg-white">
          <v-tab value="about" class="text-none font-weight-bold">
            <v-icon start>mdi-account-card-details-outline</v-icon>
            Acerca de
          </v-tab>
          <v-tab value="services" class="text-none font-weight-bold">
            <v-icon start>mdi-briefcase-outline</v-icon>
            Servicios ({{ profesionalDetalle.servicios?.length || 0 }})
          </v-tab>
          <v-tab value="packages" class="text-none font-weight-bold">
            <v-icon start>mdi-package-variant-closed</v-icon>
            Paquetes ({{ profesionalDetalle.paquetes?.length || 0 }})
          </v-tab>
          <v-tab value="reviews" class="text-none font-weight-bold">
            <v-icon start>mdi-comment-text-multiple-outline</v-icon>
            Calificaciones ({{ opinionesList.length }})
          </v-tab>
        </v-tabs>

        <v-card-text class="pa-6 bg-grey-lighten-4" style="height: 55vh; overflow-y: auto;">
          <v-window v-model="tab">
            <!-- Pestaña: Acerca de -->
            <v-window-item value="about">
              <v-card class="pa-6 rounded-xl border mb-4" elevation="0" color="white">
                <h3 class="text-subtitle-1 font-weight-bold text-primary mb-3">Descripción Profesional</h3>
                <p class="text-body-1 text-grey-darken-3 mb-6" style="line-height: 1.6; white-space: pre-line;">
                  {{ profesionalDetalle.descripcion || 'Sin descripción disponible.' }}
                </p>

                <h3 class="text-subtitle-1 font-weight-bold text-primary mb-3">Trayectoria y Ubicación</h3>
                <v-row>
                  <v-col cols="12" sm="6">
                    <div class="d-flex align-center bg-grey-lighten-5 pa-4 rounded-lg border">
                      <v-avatar color="primary" variant="tonal" class="mr-3"><v-icon>mdi-briefcase-check-outline</v-icon></v-avatar>
                      <div>
                        <div class="text-caption text-medium-emphasis">Experiencia</div>
                        <strong class="text-body-1 text-grey-darken-3">{{ profesionalDetalle.experiencia || 'No especificada' }}</strong>
                      </div>
                    </div>
                  </v-col>
                  <v-col cols="12" sm="6">
                    <v-tooltip v-if="profesionalDetalle.ubicacion" text="Ver en el mapa" location="top">
                      <template v-slot:activator="{ props }">
                        <div 
                          v-bind="props"
                          class="d-flex align-center bg-grey-lighten-5 pa-4 rounded-lg border cursor-pointer hover-location-box"
                          @click="openMap(profesionalDetalle)"
                        >
                          <v-avatar color="error" variant="tonal" class="mr-3"><v-icon>mdi-map-marker-radius</v-icon></v-avatar>
                          <div>
                            <div class="text-caption text-medium-emphasis">Ubicación</div>
                            <strong class="text-body-1 text-primary text-decoration-underline">{{ profesionalDetalle.ubicacion }}</strong>
                          </div>
                        </div>
                      </template>
                    </v-tooltip>
                    <div 
                      v-else
                      class="d-flex align-center bg-grey-lighten-5 pa-4 rounded-lg border"
                    >
                      <v-avatar color="grey" variant="tonal" class="mr-3"><v-icon>mdi-map-marker-off</v-icon></v-avatar>
                      <div>
                        <div class="text-caption text-medium-emphasis">Ubicación</div>
                        <strong class="text-body-1 text-grey-darken-3">No especificada</strong>
                      </div>
                    </div>
                  </v-col>
                </v-row>
              </v-card>
            </v-window-item>

            <!-- Pestaña: Servicios -->
            <v-window-item value="services">
              <div v-if="profesionalDetalle.servicios && profesionalDetalle.servicios.length > 0">
                <v-row>
                  <v-col cols="12" md="6" v-for="service in profesionalDetalle.servicios" :key="service.id">
                    <v-card class="rounded-xl border pa-4 h-100 d-flex flex-column" elevation="0" color="white">
                      <div class="d-flex justify-space-between align-start mb-2">
                        <v-chip size="x-small" :color="getModalityColor(service.modalidad)" variant="tonal" class="font-weight-bold text-uppercase">
                          {{ service.modalidad }}
                        </v-chip>
                        <strong class="text-h6 text-success">${{ service.precio }}</strong>
                      </div>

                      <h4 class="text-subtitle-1 font-weight-bold text-grey-darken-4 mb-2">{{ service.nombre }}</h4>
                      <p class="text-caption text-medium-emphasis mb-4 line-clamp-3">{{ service.descripcion }}</p>

                      <v-divider class="my-3 mt-auto"></v-divider>

                      <div class="d-flex align-center justify-space-between">
                        <span class="text-caption text-medium-emphasis">
                          <v-icon size="small" class="mr-1">mdi-clock-outline</v-icon>
                          {{ service.duracion }} min
                        </span>
                        <v-btn 
                          size="small" 
                          color="primary" 
                          class="text-none font-weight-bold rounded-lg"
                          @click="reservarServicio(service)"
                        >
                          Reservar
                        </v-btn>
                      </div>
                    </v-card>
                  </v-col>
                </v-row>
              </div>
              <v-card v-else class="pa-8 text-center rounded-xl border bg-white" elevation="0">
                <v-icon size="48" color="grey" class="mb-2">mdi-briefcase-off-outline</v-icon>
                <p class="text-body-1 text-medium-emphasis mb-0">Este profesional aún no ofrece servicios.</p>
              </v-card>
            </v-window-item>

            <!-- Pestaña: Paquetes -->
            <v-window-item value="packages">
              <div v-if="profesionalDetalle.paquetes && profesionalDetalle.paquetes.length > 0">
                <v-row>
                  <v-col cols="12" md="6" v-for="paquete in profesionalDetalle.paquetes" :key="paquete.id">
                    <v-card class="rounded-xl border pa-4 h-100 d-flex flex-column" elevation="0" color="white">
                      <div class="d-flex justify-space-between align-start mb-2">
                        <v-chip size="x-small" color="secondary" variant="tonal" class="font-weight-bold text-uppercase">
                          <v-icon start size="12">mdi-layers</v-icon>
                          {{ paquete.cantidad_sesiones }} Sesiones
                        </v-chip>
                        <div class="text-right">
                          <span class="text-caption text-decoration-line-through text-grey mr-1" v-if="paquete.descuento > 0">${{ paquete.precio }}</span>
                          <strong class="text-h6 text-success">${{ paquete.precio - paquete.descuento }}</strong>
                        </div>
                      </div>

                      <h4 class="text-subtitle-1 font-weight-bold text-grey-darken-4 mb-2">{{ paquete.nombre }}</h4>
                      <p class="text-caption text-medium-emphasis mb-4 line-clamp-3">{{ paquete.descripcion }}</p>

                      <v-divider class="my-3 mt-auto"></v-divider>

                      <div class="d-flex align-center justify-space-between">
                        <span class="text-caption text-medium-emphasis">
                          Vence en {{ paquete.vencimiento }} días
                        </span>
                        <v-btn 
                          size="small" 
                          color="primary" 
                          class="text-none font-weight-bold rounded-lg"
                          @click="reservarPaquete(paquete)"
                        >
                          Comprar / Reservar
                        </v-btn>
                      </div>
                    </v-card>
                  </v-col>
                </v-row>
              </div>
              <v-card v-else class="pa-8 text-center rounded-xl border bg-white" elevation="0">
                <v-icon size="48" color="grey" class="mb-2">mdi-package-variant-off</v-icon>
                <p class="text-body-1 text-medium-emphasis mb-0">Este profesional aún no ofrece paquetes de sesiones.</p>
              </v-card>
            </v-window-item>

            <!-- Pestaña: Opiniones -->
            <v-window-item value="reviews">
              <div v-if="cargandoOpiniones" class="text-center py-8">
                <v-progress-circular indeterminate color="primary"></v-progress-circular>
                <div class="text-caption text-medium-emphasis mt-2">Cargando comentarios...</div>
              </div>

              <div v-else-if="opinionesList.length > 0">
                <v-row>
                  <v-col cols="12" md="6" v-for="op in opinionesList" :key="op.id">
                    <ReviewCard
                      :cliente-nombre="op.cliente_nombre"
                      :fecha="op.fecha"
                      :puntuacion="op.puntuacion"
                      :comentario="op.comentario"
                    />
                  </v-col>
                </v-row>
              </div>

              <v-card v-else class="pa-8 text-center rounded-xl border bg-white" elevation="0">
                <v-icon size="48" color="grey" class="mb-2">mdi-comment-text-multiple-outline</v-icon>
                <p class="text-body-1 text-medium-emphasis mb-1 font-weight-bold">Sin opiniones</p>
                <p class="text-caption text-medium-emphasis mb-0">Este profesional aún no ha recibido opiniones escritas.</p>
              </v-card>
            </v-window-item>
          </v-window>
        </v-card-text>

        <!-- Footer -->
        <v-card-actions class="pa-4 bg-grey-lighten-3 justify-end">
          <v-btn 
            color="primary" 
            variant="elevated" 
            @click="dialogDetalle = false" 
            class="text-none font-weight-bold px-6 rounded-lg text-white"
          >
            Cerrar Perfil
          </v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>

    <!-- Map Dialog -->
    <v-dialog v-model="mapDialog" max-width="700" persistent>
      <v-card class="rounded-xl overflow-hidden">
        <div class="map-dialog-header pa-4 d-flex align-center justify-space-between">
          <div class="d-flex align-center">
            <v-icon color="white" class="mr-3" size="28">mdi-map-marker-radius</v-icon>
            <div>
              <div class="text-h6 font-weight-bold text-white">{{ selectedProfessional?.nombre }}</div>
              <div class="text-caption text-white" style="opacity: 0.85;">{{ selectedProfessional?.ubicacion }}</div>
            </div>
          </div>
          <v-btn icon variant="text" @click="closeMap" size="small">
            <v-icon color="white">mdi-close</v-icon>
          </v-btn>
        </div>

        <!-- Estado de Carga -->
        <div v-if="mapLoading" class="d-flex flex-column align-center justify-center" style="height: 400px;">
          <v-progress-circular indeterminate color="primary" size="48" class="mb-4"></v-progress-circular>
          <div class="text-body-1 text-medium-emphasis">Buscando ubicación...</div>
        </div>

        <!-- Estado de Error -->
        <div v-else-if="mapError" class="d-flex flex-column align-center justify-center pa-8" style="height: 400px;">
          <v-icon size="64" color="warning" class="mb-4">mdi-map-marker-question</v-icon>
          <div class="text-h6 font-weight-bold text-grey-darken-2 mb-2">Ubicación no encontrada</div>
          <div class="text-body-2 text-medium-emphasis text-center mb-4">
            No se pudo encontrar la dirección:<br/>
            <strong>"{{ selectedProfessional?.ubicacion }}"</strong>
          </div>
          <v-btn color="primary" variant="tonal" class="text-none" @click="closeMap">
            Cerrar
          </v-btn>
        </div>

        <!-- Contenedor del Mapa -->
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
import { ref, onMounted, watch, nextTick, computed } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import DashboardLayout from '../components/DashboardLayout.vue'
import ReviewCard from '../components/ReviewCard.vue'
import { useAuth } from '../composables/useAuth.js'
import L from 'leaflet'
import 'leaflet/dist/leaflet.css'

const route = useRoute()
const router = useRouter()
const { getAuthHeaders } = useAuth()

const isLoading = ref(true)
const profesionales = ref([])
const tab = ref('about')
const navegandoIntencionalmente = ref(false)

// Filtros
const filtroNombre = ref('')
const filtroModalidad = ref('Cualquiera')
const filtroReputacion = ref(0)

const filteredProfesionales = computed(() => {
  return profesionales.value.filter(prof => {
    // Filtrar por nombre
    if (filtroNombre.value && !prof.nombre.toLowerCase().includes(filtroNombre.value.toLowerCase())) {
      return false
    }
    // Filtrar por modalidad
    if (filtroModalidad.value !== 'Cualquiera' && prof.modalidad_preferida !== filtroModalidad.value) {
      return false
    }
    // Filtrar por reputación
    if (filtroReputacion.value > 0 && (prof.reputacion || 0) < filtroReputacion.value) {
      return false
    }
    return true
  })
})

const limpiarFiltros = () => {
  filtroNombre.value = ''
  filtroModalidad.value = 'Cualquiera'
  filtroReputacion.value = 0
}

// Estados del modal de detalle
const dialogDetalle = ref(false)
const profesionalDetalle = ref(null)
const opinionesList = ref([])
const cargandoOpiniones = ref(false)

const fetchProfesionales = async () => {
  isLoading.value = true
  try {
    const res = await fetch('/api/profesionales', {
      headers: getAuthHeaders()
    })
    if (res.ok) {
      const data = await res.json()
      profesionales.value = data.data || []
    }
  } catch (error) {
    console.error('Error fetching professionals list:', error)
  } finally {
    isLoading.value = false
  }
}

const fetchProfesionalDetalle = async (id) => {
  try {
    const res = await fetch(`/api/profesionales/${id}`, {
      headers: getAuthHeaders()
    })
    if (res.ok) {
      const data = await res.json()
      profesionalDetalle.value = data.data
      dialogDetalle.value = true
      tab.value = 'about'
      
      // Cargar opiniones
      fetchOpiniones(id)
    }
  } catch (error) {
    console.error('Error fetching professional details:', error)
  }
}

const fetchOpiniones = async (id) => {
  cargandoOpiniones.value = true
  opinionesList.value = []
  try {
    const res = await fetch(`/api/profesionales/${id}/calificaciones`, {
      headers: getAuthHeaders()
    })
    if (res.ok) {
      const data = await res.json()
      opinionesList.value = data.data || []
    }
  } catch (error) {
    console.error('Error fetching opinions:', error)
  } finally {
    cargandoOpiniones.value = false
  }
}

const verDetalleProfesional = (id) => {
  // Hacer push del parámetro query a la ruta para actualizar URL y disparar watcher
  router.push({ name: 'profesionales', query: { id } })
}

// Observar parámetros de query para activar el modal
watch(() => route.query.id, (newId) => {
  if (newId) {
    fetchProfesionalDetalle(parseInt(newId))
  } else {
    dialogDetalle.value = false
    profesionalDetalle.value = null
  }
})

// Observar cierre del diálogo para limpiar parámetros
watch(dialogDetalle, (isOpen) => {
  if (!isOpen && route.query.id) {
    // Si se está navegando intencionalmente (ej: al presionar Reservar)
    // no redirigir de vuelta a la lista de profesionales
    if (!navegandoIntencionalmente.value) {
      router.push({ name: 'profesionales' })
    }
    navegandoIntencionalmente.value = false
  }
})

onMounted(async () => {
  await fetchProfesionales()
  
  // Si ?id=XX está en la URL al cargar
  if (route.query.id) {
    fetchProfesionalDetalle(parseInt(route.query.id))
  }
})

const getModalityColor = (modality) => {
  switch (modality) {
    case 'remota': return 'info'
    case 'presencial': return 'primary'
    case 'hibrida': return 'deep-purple'
    default: return 'grey'
  }
}

const reservarServicio = (service) => {
  // Marcar que estamos navegando intencionalmente para evitar que el
  // watcher del diálogo sobreescriba nuestra navegación
  navegandoIntencionalmente.value = true
  dialogDetalle.value = false
  // Navegar directamente a Reservas con el servicio pre-seleccionado
  router.push({ name: 'mis-reservas', query: { action: 'reservar', servicio: service.id } })
}

const reservarPaquete = (paquete) => {
  navegandoIntencionalmente.value = true
  dialogDetalle.value = false
  router.push({ name: 'comprar-paquetes', query: { q: paquete.nombre } })
}

// ===== Funciones del Mapa =====
const mapDialog = ref(false)
const mapLoading = ref(false)
const mapError = ref(false)
const selectedProfessional = ref(null)
const mapCoords = ref(null)
const mapBoundingBox = ref(null)
let mapInstance = null

const openMap = async (prof) => {
  selectedProfessional.value = prof
  mapDialog.value = true
  mapLoading.value = true
  mapError.value = false
  mapCoords.value = null
  mapBoundingBox.value = null

  try {
    const address = encodeURIComponent(prof.ubicacion)
    const response = await fetch(
      `https://nominatim.openstreetmap.org/search?format=json&q=${address}&limit=1`,
      { headers: { 'Accept-Language': 'es' } }
    )
    const results = await response.json()

    if (results.length === 0) {
      mapError.value = true
      return
    }

    const firstResult = results[0]
    const { lat, lon, place_rank, boundingbox } = firstResult
    mapCoords.value = { lat: parseFloat(lat), lng: parseFloat(lon) }
    mapBoundingBox.value = boundingbox
    
    // Determinar nivel de zoom de forma inteligente para fallback:
    // Si place_rank >= 26 es una calle o edificio específico -> zoom 16
    // Si está entre 17 y 25 es un barrio o código postal -> zoom 14
    // Si es 16 es una ciudad -> zoom 13
    // Si < 16 es un departamento o país -> zoom 12
    let zoomLevel = 16
    if (place_rank !== undefined) {
      if (place_rank < 16) {
        zoomLevel = 12
      } else if (place_rank === 16) {
        zoomLevel = 13
      } else if (place_rank < 26) {
        zoomLevel = 14
      }
    }
    
    mapLoading.value = false

    await nextTick()
    setTimeout(() => initMap(zoomLevel), 100)
  } catch (error) {
    console.error('Geocoding error:', error)
    mapError.value = true
  } finally {
    mapLoading.value = false
  }
}

const initMap = (zoomLevel) => {
  const container = document.getElementById('map-container')
  if (!container || !mapCoords.value) return

  if (mapInstance) {
    mapInstance.remove()
    mapInstance = null
  }

  const { lat, lng } = mapCoords.value
  mapInstance = L.map('map-container')

  L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>',
    maxZoom: 19
  }).addTo(mapInstance)

  // Ajustar la vista con el bounding box de forma inteligente
  if (mapBoundingBox.value && mapBoundingBox.value.length === 4) {
    const bounds = [
      [parseFloat(mapBoundingBox.value[0]), parseFloat(mapBoundingBox.value[2])],
      [parseFloat(mapBoundingBox.value[1]), parseFloat(mapBoundingBox.value[3])]
    ]
    const latDiff = Math.abs(bounds[0][0] - bounds[1][0])
    const lngDiff = Math.abs(bounds[0][1] - bounds[1][1])

    // Si el área es muy pequeña (ej. dirección exacta/casa), centrar con zoom 16.
    // De lo contrario, ajustar los límites al área general (fitBounds).
    if (latDiff < 0.005 && lngDiff < 0.005) {
      mapInstance.setView([lat, lng], 16)
    } else {
      mapInstance.fitBounds(bounds, { maxZoom: 16, padding: [10, 10] })
    }
  } else {
    mapInstance.setView([lat, lng], zoomLevel)
  }

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

  const popupContent = `
    <div style="font-family: Inter, sans-serif; min-width: 200px;">
      <div style="font-weight: 700; font-size: 14px; color: #333; margin-bottom: 4px;">
        ${selectedProfessional.value.nombre}
      </div>
      <div style="font-size: 12px; color: #666;">
        📍 ${selectedProfessional.value.ubicacion}
      </div>
    </div>
  `
  marker.bindPopup(popupContent).openPopup()

  setTimeout(() => mapInstance.invalidateSize(), 200)
}

const closeMap = () => {
  mapDialog.value = false
  if (mapInstance) {
    mapInstance.remove()
    mapInstance = null
  }
  selectedProfessional.value = null
  mapCoords.value = null
  mapBoundingBox.value = null
}
</script>

<style scoped>
.bg-gradient {
  background: linear-gradient(135deg, #8C6D46 0%, #A6987A 100%);
}
.brand-header {
  background: linear-gradient(135deg, #8C6D46 0%, #A6987A 100%);
}
.border-avatar {
  border: 3px solid rgba(140, 109, 70, 0.2);
}
.border-avatar-white {
  border: 3px solid white;
}
.card-hover {
  cursor: pointer;
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
  border: 1px solid transparent;
}
.card-hover:hover {
  transform: translateY(-4px);
  box-shadow: 0 12px 20px -8px rgba(0, 0, 0, 0.15) !important;
  border-color: rgba(140, 109, 70, 0.3);
}
.line-clamp-1 {
  display: -webkit-box;
  -webkit-line-clamp: 1;
  -webkit-box-orient: vertical;
  overflow: hidden;
}
.line-clamp-3 {
  display: -webkit-box;
  -webkit-line-clamp: 3;
  -webkit-box-orient: vertical;
  overflow: hidden;
}
.gap-2 {
  gap: 8px;
}
.border-card {
  border: 1px solid rgba(140, 109, 70, 0.1);
}
.text-left-align {
  text-align: left;
}
.dialog-layout {
  border-radius: 20px;
}
.italic {
  font-style: italic;
}
.hover-location-box {
  cursor: pointer;
  transition: all 0.2s ease-in-out;
}
.hover-location-box:hover {
  background-color: rgba(var(--v-theme-primary), 0.08) !important;
  border-color: rgba(140, 109, 70, 0.3) !important;
}
.map-dialog-header {
  background: linear-gradient(135deg, #8C6D46 0%, #6B5235 100%);
}
/* Sobrescritura de Leaflet */
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
</style>
