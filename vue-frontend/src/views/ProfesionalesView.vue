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

    <!-- Grid de Profesionales -->
    <v-row v-else>
      <v-col cols="12" sm="6" md="4" lg="3" v-for="prof in profesionales" :key="prof.id_usuario">
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
              Ver Perfil y Servicios
              <v-icon end size="16">mdi-chevron-right</v-icon>
            </v-btn>
          </div>
        </v-card>
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
          <v-tab value="reviews" class="text-none font-weight-bold">
            <v-icon start>mdi-comment-text-multiple-outline</v-icon>
            Calificaciones ({{ opinionesList.length }})
          </v-tab>
        </v-tabs>

        <v-card-text class="pa-6 bg-grey-lighten-4" style="height: 55vh; overflow-y: auto;">
          <v-window v-model="tab">
            <!-- Window: About -->
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
                    <div class="d-flex align-center bg-grey-lighten-5 pa-4 rounded-lg border">
                      <v-avatar color="error" variant="tonal" class="mr-3"><v-icon>mdi-map-marker-radius</v-icon></v-avatar>
                      <div>
                        <div class="text-caption text-medium-emphasis">Ubicación</div>
                        <strong class="text-body-1 text-grey-darken-3">{{ profesionalDetalle.ubicacion || 'No especificada' }}</strong>
                      </div>
                    </div>
                  </v-col>
                </v-row>
              </v-card>
            </v-window-item>

            <!-- Window: Services -->
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
                          @click="reservarServicio(service.id)"
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

            <!-- Window: Reviews -->
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
  </DashboardLayout>
</template>

<script setup>
import { ref, onMounted, watch } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import DashboardLayout from '../components/DashboardLayout.vue'
import ReviewCard from '../components/ReviewCard.vue'
import { useAuth } from '../composables/useAuth.js'

const route = useRoute()
const router = useRouter()
const { getAuthHeaders } = useAuth()

const isLoading = ref(true)
const profesionales = ref([])
const tab = ref('about')

// Detail modal states
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
      
      // Load reviews
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
  // Push route query parameter so URL updates, which triggers watch
  router.push({ name: 'profesionales', query: { id } })
}

// Watch query parameters for modal trigger
watch(() => route.query.id, (newId) => {
  if (newId) {
    fetchProfesionalDetalle(parseInt(newId))
  } else {
    dialogDetalle.value = false
    profesionalDetalle.value = null
  }
})

// Watch dialog close to clean query params
watch(dialogDetalle, (isOpen) => {
  if (!isOpen && route.query.id) {
    router.push({ name: 'profesionales' })
  }
})

onMounted(async () => {
  await fetchProfesionales()
  
  // If ?id=XX is in URL on load
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

const reservarServicio = (id_servicio) => {
  dialogDetalle.value = false
  router.push({ name: 'mis-reservas', query: { action: 'reservar', servicio: id_servicio } })
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
</style>
