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
  </DashboardLayout>
</template>

<script setup>
import { ref, onMounted, computed, watch } from 'vue'
import DashboardLayout from '../components/DashboardLayout.vue'

// --- ESTADO ---
const fechaSeleccionada = ref(new Date())
const reservas = ref([])
const cargando = ref(true)
const isCliente = ref(false)

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
</style>
