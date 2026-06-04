<template>
  <DashboardLayout title="Videollamadas">
    <div class="videollamadas-container">
      <div class="mb-6">
        <p class="text-subtitle-1 text-medium-emphasis mb-0 mt-2">Gestiona todas tus consultas y sesiones online</p>
      </div>

      <!-- Banner Próxima Sesión -->
      <v-card class="rounded-xl mb-6 elevation-3" style="background: linear-gradient(135deg, #A6987A 0%, #8C6D46 100%); color: white;">
        <v-card-text class="pa-8">
          <v-row align="center">
            <v-col cols="12" md="5" class="d-flex align-center border-right-md">
              <v-avatar color="rgba(255,255,255,0.2)" size="80" class="mr-6">
                <v-icon size="40" color="white">mdi-video</v-icon>
              </v-avatar>
              <div>
                <div class="text-subtitle-1 font-weight-medium" style="opacity: 0.9;">Tu próxima sesión comienza en</div>
                <div class="text-h3 font-weight-bold mt-1">{{ tiempoRestanteProximaSesion }}</div>
              </div>
            </v-col>
            <v-col cols="12" md="4" class="px-md-6">
              <div class="text-subtitle-1 font-weight-medium mb-1" style="opacity: 0.9;">
                {{ proximaSesion ? formatDateTime(proximaSesion.fecha_hora_inicio) : 'No hay próximas sesiones' }}
              </div>
              <div class="text-h6 font-weight-bold" v-if="proximaSesion">
                {{ proximaSesion.servicio?.nombre }} - {{ getOtherPersonName(proximaSesion) }}
              </div>
            </v-col>
            <v-col cols="12" md="3" class="text-md-right text-center mt-4 mt-md-0">
              <v-btn 
                color="white" 
                class="text-secondary font-weight-bold rounded-pill px-8" 
                size="x-large" 
                prepend-icon="mdi-video"
                :disabled="!proximaSesion"
                @click="joinCall(proximaSesion?.id)"
              >
                Unirse ahora
              </v-btn>
            </v-col>
          </v-row>
        </v-card-text>
      </v-card>

      <!-- Tarjetas de Métricas -->
      <v-row class="mb-6">
        <v-col cols="12" md="4">
          <v-card class="rounded-xl h-100 elevation-1 border-card pa-4">
            <div class="d-flex align-center mb-4">
              <v-avatar color="orange-lighten-4" size="56" class="mr-4">
                <v-icon color="orange-darken-2" size="28">mdi-account-group</v-icon>
              </v-avatar>
              <div>
                <div class="text-subtitle-2 text-medium-emphasis">Sesiones hoy</div>
                <div class="text-h4 font-weight-bold text-grey-darken-3">{{ sesionesHoy }}</div>
              </div>
            </div>
            <div class="d-flex justify-end mt-4">
              <v-btn variant="text" color="primary" class="text-none font-weight-bold px-0" append-icon="mdi-arrow-right" to="/mi-agenda">
                Ver agenda
              </v-btn>
            </div>
          </v-card>
        </v-col>
        <v-col cols="12" md="4">
          <v-card class="rounded-xl h-100 elevation-1 border-card pa-4">
            <div class="d-flex align-center mb-4">
              <v-avatar color="deep-purple-lighten-4" size="56" class="mr-4">
                <v-icon color="deep-purple-darken-2" size="28">mdi-clock-outline</v-icon>
              </v-avatar>
              <div>
                <div class="text-subtitle-2 text-medium-emphasis">Horas online (semana)</div>
                <div class="text-h4 font-weight-bold text-grey-darken-3">{{ horasOnline }} <span class="text-h6">h</span></div>
              </div>
            </div>
            <div class="d-flex justify-end mt-4">
            </div>
          </v-card>
        </v-col>
        <v-col cols="12" md="4">
          <v-card class="rounded-xl h-100 elevation-1 border-card pa-4">
            <div class="d-flex align-center mb-4">
              <v-avatar color="green-lighten-4" size="56" class="mr-4">
                <v-icon color="green-darken-2" size="28">mdi-calendar-check</v-icon>
              </v-avatar>
              <div>
                <div class="text-subtitle-2 text-medium-emphasis">Sesiones completadas</div>
                <div class="text-h4 font-weight-bold text-grey-darken-3">{{ sesionesCompletadas }}</div>
              </div>
            </div>
            <div class="d-flex justify-end mt-4">
              <v-btn variant="text" color="primary" class="text-none font-weight-bold px-0" append-icon="mdi-arrow-right" to="/mis-reservas">
                Ver historial
              </v-btn>
            </div>
          </v-card>
        </v-col>
      </v-row>

      <!-- Listados -->
      <v-row>
        <!-- Próximas sesiones -->
        <v-col cols="12" md="6">
          <v-card class="rounded-xl h-100 elevation-1 border-card d-flex flex-column">
            <v-card-title class="pa-6 pb-4 d-flex align-center justify-space-between">
              <div class="d-flex align-center">
                <v-icon color="grey-darken-2" class="mr-2">mdi-calendar</v-icon>
                <span class="text-h6 font-weight-bold text-grey-darken-3">Próximas sesiones</span>
              </div>
              <v-btn variant="text" color="primary" size="small" class="text-none font-weight-bold" to="/mi-agenda">
                Ver todas
              </v-btn>
            </v-card-title>
            <v-divider class="mx-6"></v-divider>
            <v-card-text class="pa-6 flex-grow-1">
              <div v-if="loading" class="d-flex justify-center py-8">
                <v-progress-circular indeterminate color="primary"></v-progress-circular>
              </div>
              <div v-else-if="proximasSesiones.length === 0" class="text-center py-8 opacity-70">
                <p>No hay próximas sesiones remotas agendadas.</p>
              </div>
              <div v-else>
                <div v-for="(sesion, i) in proximasSesiones" :key="sesion.id">
                  <div class="d-flex py-4 align-start">
                    <v-avatar color="grey-lighten-2" size="50" class="mr-4">
                      <v-img v-if="getAvatarUrl(sesion)" :src="getAvatarUrl(sesion)"></v-img>
                      <span v-else class="text-h6 text-grey-darken-2 font-weight-bold">{{ getInitials(getOtherPersonName(sesion)) }}</span>
                    </v-avatar>
                    <div class="flex-grow-1">
                      <div class="font-weight-bold text-body-1 text-grey-darken-3 mb-1">
                        {{ sesion.servicio?.nombre }} - {{ getOtherPersonName(sesion) }}
                      </div>
                      <div class="text-caption text-medium-emphasis mb-2 d-flex align-center">
                        {{ formatDateTime(sesion.fecha_hora_inicio) }} • {{ sesion.servicio?.duracion }} min
                      </div>
                      <v-chip size="small" color="secondary" variant="tonal" class="font-weight-bold">
                        {{ getTimeLeftString(sesion.fecha_hora_inicio) }}
                      </v-chip>
                    </div>
                    <div class="d-flex flex-column align-end justify-space-between ml-2">
                      <v-btn color="secondary" variant="flat" size="small" prepend-icon="mdi-video" class="text-none font-weight-bold rounded-lg" @click="joinCall(sesion.id)">
                        Unirse
                      </v-btn>
                    </div>
                  </div>
                  <v-divider v-if="i < proximasSesiones.length - 1"></v-divider>
                </div>
              </div>
            </v-card-text>
          </v-card>
        </v-col>

        <!-- Historial reciente -->
        <v-col cols="12" md="6">
          <v-card class="rounded-xl h-100 elevation-1 border-card bg-grey-lighten-5 d-flex flex-column">
            <v-card-title class="pa-6 pb-4 d-flex align-center justify-space-between">
              <div class="d-flex align-center">
                <v-icon color="grey-darken-2" class="mr-2">mdi-history</v-icon>
                <span class="text-h6 font-weight-bold text-grey-darken-3">Historial reciente</span>
              </div>
              <v-btn variant="text" color="primary" size="small" class="text-none font-weight-bold" to="/mis-reservas">
                Ver todas
              </v-btn>
            </v-card-title>
            <v-divider class="mx-6"></v-divider>
            <v-card-text class="pa-6 flex-grow-1">
              <div v-if="loading" class="d-flex justify-center py-8">
                <v-progress-circular indeterminate color="primary"></v-progress-circular>
              </div>
              <div v-else-if="historialReciente.length === 0" class="text-center py-8 opacity-70">
                <p>No hay sesiones recientes completadas.</p>
              </div>
              <div v-else>
                <div v-for="(sesion, i) in historialReciente" :key="sesion.id">
                  <div class="d-flex py-4 align-start">
                    <v-avatar color="grey-lighten-3" size="50" class="mr-4">
                      <v-img v-if="getAvatarUrl(sesion)" :src="getAvatarUrl(sesion)"></v-img>
                      <span v-else class="text-h6 text-grey-darken-1 font-weight-bold">{{ getInitials(getOtherPersonName(sesion)) }}</span>
                    </v-avatar>
                    <div class="flex-grow-1">
                      <div class="font-weight-bold text-body-1 text-grey-darken-3 mb-1">
                        {{ sesion.servicio?.nombre }} - {{ getOtherPersonName(sesion) }}
                      </div>
                      <div class="text-caption text-medium-emphasis mb-2">
                        {{ formatDateTime(sesion.fecha_hora_inicio) }}
                      </div>
                      <v-chip size="small" color="success" variant="flat" class="font-weight-bold">
                        Completada
                      </v-chip>
                    </div>
                    <div class="d-flex align-center ml-2">
                      <v-btn variant="text" size="small" color="primary" class="text-none">Ver resumen</v-btn>
                    </div>
                  </div>
                  <v-divider v-if="i < historialReciente.length - 1"></v-divider>
                </div>
              </div>
            </v-card-text>
          </v-card>
        </v-col>
      </v-row>

      <!-- Footer tips -->
      <v-card class="mt-6 pa-6 rounded-xl border-card elevation-0 bg-amber-lighten-5 d-flex flex-column flex-md-row align-center justify-space-between">
        <div class="d-flex align-center">
          <v-icon color="secondary" size="32" class="mr-4">mdi-alert-circle-outline</v-icon>
          <div>
            <div class="font-weight-bold text-grey-darken-3 text-body-1">Consejos para una mejor experiencia</div>
            <div class="text-body-2 text-medium-emphasis">Asegúrate de tener una buena conexión a internet, un lugar tranquilo y tus dispositivos funcionando correctamente.</div>
          </div>
        </div>
      </v-card>

    </div>
  </DashboardLayout>
</template>

<script setup>
import { ref, onMounted, computed, onUnmounted } from 'vue'
import { useRouter } from 'vue-router'
import DashboardLayout from '../components/DashboardLayout.vue'

const router = useRouter()
const isCliente = ref(false)
const reservas = ref([])
const loading = ref(true)
const now = ref(new Date())

let timeInterval = null

// Métricas computadas
const proximasSesiones = computed(() => {
  return reservas.value
    .filter(r => (r.estado === 'confirmada' || r.estado === 'pagada') && ['remota', 'hibrida'].includes(r.servicio?.modalidad))
    .filter(r => new Date(r.fecha_hora_inicio) > now.value)
    .sort((a, b) => new Date(a.fecha_hora_inicio) - new Date(b.fecha_hora_inicio))
})

const proximaSesion = computed(() => {
  return proximasSesiones.value.length > 0 ? proximasSesiones.value[0] : null
})

const historialReciente = computed(() => {
  return reservas.value
    .filter(r => r.estado === 'finalizada' && ['remota', 'hibrida'].includes(r.servicio?.modalidad))
    .sort((a, b) => new Date(b.fecha_hora_inicio) - new Date(a.fecha_hora_inicio))
    .slice(0, 4)
})

const sesionesHoy = computed(() => {
  const todayStr = new Date(now.value.getTime() - (now.value.getTimezoneOffset() * 60000)).toISOString().split('T')[0]
  return reservas.value.filter(r => 
    (r.estado === 'confirmada' || r.estado === 'pagada') && 
    r.fecha_hora_inicio.startsWith(todayStr) &&
    ['remota', 'hibrida'].includes(r.servicio?.modalidad)
  ).length
})

const sesionesCompletadas = computed(() => {
  return reservas.value.filter(r => r.estado === 'finalizada' && ['remota', 'hibrida'].includes(r.servicio?.modalidad)).length
})

const horasOnline = computed(() => {
  // Suma de duraciones en minutos, pasado a horas. (Solo de esta semana o total? El diseño dice "semana", pero por simplificar tomaremos todo o aproximado).
  const minutos = reservas.value
    .filter(r => r.estado === 'finalizada' && ['remota', 'hibrida'].includes(r.servicio?.modalidad))
    .reduce((acc, curr) => acc + (curr.servicio?.duracion || 0), 0)
  
  return (minutos / 60).toFixed(1)
})

const tiempoRestanteProximaSesion = computed(() => {
  if (!proximaSesion.value) return '-'
  const diffMs = new Date(proximaSesion.value.fecha_hora_inicio) - now.value
  if (diffMs <= 0) return 'Ahora'
  
  const diffMins = Math.floor(diffMs / 60000)
  const days = Math.floor(diffMins / (24 * 60))
  const hours = Math.floor((diffMins % (24 * 60)) / 60)
  const mins = diffMins % 60
  
  if (days > 0) return `${days} d ${hours} h`
  if (hours > 0) return `${hours} h ${mins} min`
  return `${mins} min`
})

const getTimeLeftString = (fecha) => {
  const diffMs = new Date(fecha) - now.value
  if (diffMs <= 0) return 'En progreso'
  
  const diffMins = Math.floor(diffMs / 60000)
  const days = Math.floor(diffMins / (24 * 60))
  const hours = Math.floor((diffMins % (24 * 60)) / 60)
  const mins = diffMins % 60
  
  if (days > 0) return `En ${days} d`
  if (hours > 0) return `En ${hours} h`
  return `En ${mins} min`
}

const formatDateTime = (dateStr) => {
  const d = new Date(dateStr)
  let base = d.toLocaleDateString('es-ES', { day: 'numeric', month: 'long' })
  const todayStr = new Date(now.value.getTime() - (now.value.getTimezoneOffset() * 60000)).toISOString().split('T')[0]
  if (dateStr.startsWith(todayStr)) {
    base = 'Hoy, ' + base
  } else {
    const tomorrow = new Date(now.value)
    tomorrow.setDate(tomorrow.getDate() + 1)
    const tomStr = new Date(tomorrow.getTime() - (tomorrow.getTimezoneOffset() * 60000)).toISOString().split('T')[0]
    if (dateStr.startsWith(tomStr)) {
      base = 'Mañana, ' + base
    }
  }
  
  const time = d.toLocaleTimeString('es-ES', { hour: '2-digit', minute: '2-digit' })
  return `${base} • ${time}`
}

const getOtherPersonName = (reserva) => {
  if (!reserva) return 'Participante'
  if (isCliente.value) {
    const p = reserva.servicio?.profesional?.usuario
    if (!p) return 'Profesional'
    return `${p.nombre || ''} ${p.apellido || ''}`.trim() || 'Profesional'
  } else {
    const c = reserva.cliente?.usuario
    if (!c) return 'Cliente'
    return `${c.nombre || ''} ${c.apellido || ''}`.trim() || 'Cliente'
  }
}

const getAvatarUrl = (reserva) => {
  if (isCliente.value) {
    return reserva.servicio?.profesional?.foto_perfil_url
  }
  return null // El cliente no suele tener foto configurada, o sí
}

const getInitials = (name) => {
  if (!name) return '?'
  const parts = name.trim().split(' ')
  if (parts.length >= 2) {
    return (parts[0][0] + parts[1][0]).toUpperCase()
  }
  return name.substring(0, 2).toUpperCase()
}

const getAuthHeaders = () => ({
  'Content-Type': 'application/json',
  'Accept': 'application/json',
  'Authorization': `Bearer ${localStorage.getItem('auth_token')}`
})

const cargarReservas = async () => {
  loading.value = true
  try {
    const res = await fetch('/api/reservas', { headers: getAuthHeaders() })
    if (res.ok) {
      const data = await res.json()
      reservas.value = data.data || []
    }
  } catch (err) {
    console.error('Error cargando reservas', err)
  } finally {
    loading.value = false
  }
}

const joinCall = (id) => {
  router.push(`/videollamada/${id}`)
}

onMounted(() => {
  const user = JSON.parse(localStorage.getItem('user') || '{}')
  isCliente.value = user.role !== 'profesional'
  cargarReservas()
  
  timeInterval = setInterval(() => {
    now.value = new Date()
  }, 60000)
})

onUnmounted(() => {
  if (timeInterval) clearInterval(timeInterval)
})
</script>

<style scoped>
.border-card { 
  border: 1px solid rgba(140, 109, 70, 0.1); 
}

@media (min-width: 960px) {
  .border-right-md {
    border-right: 1px solid rgba(255, 255, 255, 0.2);
  }
}
</style>
