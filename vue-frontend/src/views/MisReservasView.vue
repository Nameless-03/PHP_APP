<template>
  <DashboardLayout title="Mis Reservas">
    <!-- MENÚ PRINCIPAL -->
    <v-fade-transition leave-absolute>
      <v-row v-if="currentView === 'menu'" justify="center" align="start" class="mt-4">
        <v-col cols="12" md="10" lg="8">
          <v-card class="elevation-4 rounded-xl overflow-hidden border-card">
            <v-card-text class="pa-0">
              <div class="brand-header pa-6 text-center text-white">
                <h1 class="text-h4 font-weight-bold mb-2">Gestión de Reservas</h1>
                <p class="text-subtitle-1 opacity-90">
                  {{ isCliente ? 'Reserva turnos con profesionales y revisa tu historial' : 'Gestiona las solicitudes de tus clientes' }}
                </p>
              </div>
            </v-card-text>

            <v-card-text class="pa-8">
              <v-row v-if="isCliente">
                <!-- CLIENTE: Reservar Turno -->
                <v-col cols="12" md="6">
                  <v-hover v-slot="{ isHovering, props }">
                    <v-card v-bind="props" :elevation="isHovering ? 8 : 2" class="h-100 rounded-xl cursor-pointer transition-swing border-panel d-flex flex-column align-center text-center pa-6" @click="currentView = 'reservar'">
                      <v-avatar color="primary" size="80" variant="tonal" class="mb-4">
                        <v-icon size="40">mdi-calendar-search</v-icon>
                      </v-avatar>
                      <h2 class="text-h6 font-weight-bold mb-2 text-grey-darken-3">Reservar Turno</h2>
                      <p class="text-body-2 text-medium-emphasis">Busca servicios y encuentra el horario ideal para ti.</p>
                    </v-card>
                  </v-hover>
                </v-col>

                <!-- CLIENTE: Mis Reservas -->
                <v-col cols="12" md="6">
                  <v-hover v-slot="{ isHovering, props }">
                    <v-card v-bind="props" :elevation="isHovering ? 8 : 2" class="h-100 rounded-xl cursor-pointer transition-swing border-panel d-flex flex-column align-center text-center pa-6" @click="abrirHistorial">
                      <v-avatar color="secondary" size="80" variant="tonal" class="mb-4">
                        <v-icon size="40">mdi-clipboard-text-clock</v-icon>
                      </v-avatar>
                      <h2 class="text-h6 font-weight-bold mb-2 text-grey-darken-3">Mis Reservas</h2>
                      <p class="text-body-2 text-medium-emphasis">Revisa el estado de tus reservas pendientes o pasadas.</p>
                    </v-card>
                  </v-hover>
                </v-col>
              </v-row>

              <v-row v-else>
                <!-- PROFESIONAL: Confirmar Reservas -->
                <v-col cols="12" md="6">
                  <v-hover v-slot="{ isHovering, props }">
                    <v-card v-bind="props" :elevation="isHovering ? 8 : 2" class="h-100 rounded-xl cursor-pointer transition-swing border-panel d-flex flex-column align-center text-center pa-6" @click="abrirPendientes">
                      <v-avatar color="warning" size="80" variant="tonal" class="mb-4">
                        <v-icon size="40">mdi-calendar-question</v-icon>
                      </v-avatar>
                      <h2 class="text-h6 font-weight-bold mb-2 text-grey-darken-3">Reservas Pendientes</h2>
                      <p class="text-body-2 text-medium-emphasis">Confirma o rechaza las nuevas solicitudes de turnos.</p>
                    </v-card>
                  </v-hover>
                </v-col>

                <!-- PROFESIONAL: Historial -->
                <v-col cols="12" md="6">
                  <v-hover v-slot="{ isHovering, props }">
                    <v-card v-bind="props" :elevation="isHovering ? 8 : 2" class="h-100 rounded-xl cursor-pointer transition-swing border-panel d-flex flex-column align-center text-center pa-6" @click="abrirHistorial">
                      <v-avatar color="secondary" size="80" variant="tonal" class="mb-4">
                        <v-icon size="40">mdi-clipboard-text-clock</v-icon>
                      </v-avatar>
                      <h2 class="text-h6 font-weight-bold mb-2 text-grey-darken-3">Historial</h2>
                      <p class="text-body-2 text-medium-emphasis">Revisa tu agenda y todas las reservas gestionadas.</p>
                    </v-card>
                  </v-hover>
                </v-col>
              </v-row>
            </v-card-text>
          </v-card>
        </v-col>
      </v-row>
    </v-fade-transition>

    <!-- CLIENTE: RESERVAR TURNO -->
    <v-fade-transition leave-absolute>
      <v-row v-if="currentView === 'reservar'" justify="center" align="start" class="mt-4">
        <v-col cols="12" md="10" lg="8">
          <v-btn variant="text" prepend-icon="mdi-arrow-left" class="mb-4 text-none font-weight-bold" @click="currentView = 'menu'">
            Volver al menú
          </v-btn>

          <v-card class="elevation-4 rounded-xl overflow-hidden border-card">
            <v-card-text class="pa-0">
              <div class="brand-header pa-6 text-white d-flex align-center">
                <v-icon size="36" class="mr-4">mdi-calendar-search</v-icon>
                <div>
                  <h1 class="text-h5 font-weight-bold mb-0">Reservar Nuevo Turno</h1>
                  <p class="text-subtitle-2 opacity-90 mb-0">Elige un servicio, fecha y hora</p>
                </div>
              </div>
            </v-card-text>

            <v-card-text class="pa-6">
              <v-alert v-if="errorReserva" type="error" variant="tonal" class="mb-6 rounded-lg" closable @click:close="errorReserva = ''">{{ errorReserva }}</v-alert>

              <v-form @submit.prevent="confirmarReserva" ref="formReserva">
                <!-- Paso 1: Seleccionar Servicio -->
                <v-select
                  v-model="reservaData.id_servicio"
                  :items="serviciosList"
                  item-title="nombre"
                  item-value="id"
                  label="1. Selecciona un Servicio"
                  variant="outlined"
                  color="primary"
                  class="mb-4"
                  :rules="[v => !!v || 'Debes seleccionar un servicio']"
                  @update:modelValue="reservaData.fecha = ''; turnosDisponibles = []; reservaData.hora = ''"
                ></v-select>

                <!-- Paso 2: Seleccionar Fecha -->
                <v-text-field
                  v-if="reservaData.id_servicio"
                  v-model="reservaData.fecha"
                  label="2. Selecciona una Fecha"
                  type="date"
                  variant="outlined"
                  color="primary"
                  class="mb-4"
                  :min="minDate"
                  @update:modelValue="buscarTurnos"
                  :rules="[v => !!v || 'Selecciona una fecha']"
                ></v-text-field>

                <!-- Paso 3: Turnos Disponibles -->
                <div v-if="reservaData.fecha && cargandoTurnos" class="text-center py-4">
                  <v-progress-circular indeterminate color="primary"></v-progress-circular>
                </div>

                <div v-if="reservaData.fecha && !cargandoTurnos" class="mb-6">
                  <p class="text-subtitle-2 font-weight-bold mb-2">3. Horarios Disponibles:</p>
                  <v-chip-group v-model="reservaData.hora" selected-class="bg-secondary text-white font-weight-bold" column>
                    <v-chip v-for="hora in turnosDisponibles" :key="hora" :value="hora" size="large" variant="outlined" class="mr-2 mb-2 font-weight-medium">
                      {{ hora }}
                    </v-chip>
                  </v-chip-group>
                  <p v-if="turnosDisponibles.length === 0" class="text-error mt-2">
                    <v-icon size="small">mdi-alert-circle</v-icon> No hay turnos disponibles para esta fecha.
                  </p>
                </div>

                <v-divider class="my-6"></v-divider>

                <v-btn color="secondary" type="submit" size="large" block class="text-none font-weight-bold rounded-lg" :loading="isLoading" :disabled="!reservaData.hora">
                  Confirmar Reserva
                </v-btn>
              </v-form>
            </v-card-text>
          </v-card>
        </v-col>
      </v-row>
    </v-fade-transition>

    <!-- PROFESIONAL: RESERVAS PENDIENTES -->
    <v-fade-transition leave-absolute>
      <v-row v-if="currentView === 'pendientes'" justify="center" align="start" class="mt-4">
        <v-col cols="12" md="10" lg="8">
          <v-btn variant="text" prepend-icon="mdi-arrow-left" class="mb-4 text-none font-weight-bold" @click="currentView = 'menu'">
            Volver al menú
          </v-btn>

          <v-card class="elevation-4 rounded-xl overflow-hidden border-card">
            <v-card-text class="pa-0">
              <div class="brand-header pa-6 text-white d-flex align-center" style="background: linear-gradient(135deg, #7A5C3D 0%, #A6987A 100%);">
                <v-icon size="36" class="mr-4">mdi-calendar-question</v-icon>
                <div>
                  <h1 class="text-h5 font-weight-bold mb-0">Reservas Pendientes</h1>
                  <p class="text-subtitle-2 opacity-90 mb-0">Confirma o rechaza nuevas solicitudes</p>
                </div>
              </div>
            </v-card-text>

            <v-card-text class="pa-6 bg-grey-lighten-4">
              <div v-if="cargandoHistorial" class="text-center py-8">
                <v-progress-circular indeterminate color="primary"></v-progress-circular>
              </div>

              <v-row v-else-if="reservasPendientes.length > 0">
                <v-col cols="12" v-for="reserva in reservasPendientes" :key="reserva.id">
                  <v-card class="rounded-lg border-panel" elevation="0">
                    <v-card-text class="d-flex align-center flex-wrap">
                      <v-avatar color="warning" variant="tonal" size="56" class="mr-4">
                        <v-icon>mdi-clock-alert</v-icon>
                      </v-avatar>
                      <div class="flex-grow-1 mr-4">
                        <h4 class="text-h6 font-weight-bold text-grey-darken-3 mb-1">{{ formatDateObj(reserva.fecha_hora_inicio) }}</h4>
                        <p class="text-body-2 mb-0">
                          <strong>Cliente:</strong> {{ reserva.cliente?.usuario?.nombre }} {{ reserva.cliente?.usuario?.apellido }} <br>
                          <strong>Servicio:</strong> {{ reserva.servicio?.nombre }}
                        </p>
                      </div>
                      <div class="d-flex gap-2 mt-3 mt-sm-0">
                        <v-btn color="error" variant="tonal" @click="cambiarEstadoReserva(reserva.id, 'cancelada')" :loading="isLoading" class="text-none font-weight-bold">
                          Rechazar
                        </v-btn>
                        <v-btn color="success" @click="cambiarEstadoReserva(reserva.id, 'confirmada')" :loading="isLoading" class="text-none font-weight-bold">
                          Confirmar
                        </v-btn>
                      </div>
                    </v-card-text>
                  </v-card>
                </v-col>
              </v-row>
              
              <div v-else class="text-center py-12 opacity-60">
                <v-icon size="64" color="grey">mdi-check-all</v-icon>
                <h3 class="mt-4 text-h6">¡Estás al día!</h3>
                <p class="text-body-1">No tienes reservas pendientes de confirmación.</p>
              </div>
            </v-card-text>
          </v-card>
        </v-col>
      </v-row>
    </v-fade-transition>

    <!-- AMBOS: HISTORIAL DE RESERVAS -->
    <v-fade-transition leave-absolute>
      <v-row v-if="currentView === 'historial'" justify="center" align="start" class="mt-4">
        <v-col cols="12" md="10" lg="8">
          <v-btn variant="text" prepend-icon="mdi-arrow-left" class="mb-4 text-none font-weight-bold" @click="currentView = 'menu'">
            Volver al menú
          </v-btn>

          <v-card class="elevation-4 rounded-xl overflow-hidden border-card">
            <v-card-text class="pa-0">
              <div class="brand-header pa-6 text-white d-flex align-center" style="background: linear-gradient(135deg, #624b2f 0%, #8C6D46 100%);">
                <v-icon size="36" class="mr-4">mdi-clipboard-text-clock</v-icon>
                <div>
                  <h1 class="text-h5 font-weight-bold mb-0">Historial de Reservas</h1>
                  <p class="text-subtitle-2 opacity-90 mb-0">Todas tus reservas</p>
                </div>
              </div>
            </v-card-text>

            <v-card-text class="pa-6">
              <div v-if="cargandoHistorial" class="text-center py-8">
                <v-progress-circular indeterminate color="primary"></v-progress-circular>
              </div>

              <v-table v-else-if="reservasHistorial.length > 0" class="border-panel rounded-lg">
                <thead>
                  <tr>
                    <th class="text-left font-weight-bold">Fecha y Hora</th>
                    <th class="text-left font-weight-bold">Servicio</th>
                    <th class="text-left font-weight-bold">{{ isCliente ? 'Profesional' : 'Cliente' }}</th>
                    <th class="text-left font-weight-bold">Estado</th>
                    <th v-if="isCliente" class="text-center font-weight-bold">Acciones</th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-for="reserva in reservasHistorial" :key="reserva.id">
                    <td class="font-weight-medium">{{ formatDateShort(reserva.fecha_hora_inicio) }}</td>
                    <td>{{ reserva.servicio?.nombre }}</td>
                    <td>
                      <span v-if="isCliente">{{ reserva.servicio?.profesional?.usuario?.nombre }}</span>
                      <span v-else>{{ reserva.cliente?.usuario?.nombre }} {{ reserva.cliente?.usuario?.apellido }}</span>
                    </td>
                    <td>
                      <v-chip size="small" :color="getColorEstado(reserva.estado)" class="font-weight-bold text-uppercase">
                        {{ reserva.estado }}
                      </v-chip>
                    </td>
                    <td v-if="isCliente" class="text-center">
                      <v-btn v-if="reserva.estado === 'pendiente' || reserva.estado === 'confirmada'" color="error" variant="text" size="small" @click="cambiarEstadoReserva(reserva.id, 'cancelada')">
                        Cancelar
                      </v-btn>
                    </td>
                  </tr>
                </tbody>
              </v-table>
              
              <div v-else class="text-center py-12 opacity-60">
                <v-icon size="64" color="grey">mdi-folder-open-outline</v-icon>
                <p class="mt-4 text-body-1">Aún no hay registros en tu historial.</p>
              </div>
            </v-card-text>
          </v-card>
        </v-col>
      </v-row>
    </v-fade-transition>

    <v-snackbar v-model="snackbar.show" :color="snackbar.color" :timeout="3000" location="top">
      {{ snackbar.text }}
      <template v-slot:actions>
        <v-btn variant="text" @click="snackbar.show = false">Cerrar</v-btn>
      </template>
    </v-snackbar>
  </DashboardLayout>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue'
import DashboardLayout from '../components/DashboardLayout.vue'

const currentView = ref('menu')
const isLoading = ref(false)
const snackbar = ref({ show: false, text: '', color: 'success' })
const isCliente = ref(true)

const getAuthHeaders = () => ({
  'Content-Type': 'application/json',
  'Accept': 'application/json',
  'Authorization': `Bearer ${localStorage.getItem('auth_token')}`
})

// === Carga Inicial ===
onMounted(() => {
  const user = JSON.parse(localStorage.getItem('user') || '{}')
  isCliente.value = user.role !== 'profesional'
})

// === CLIENTE: RESERVAR TURNO ===
const formReserva = ref(null)
const errorReserva = ref('')
const serviciosList = ref([])
const turnosDisponibles = ref([])
const cargandoTurnos = ref(false)

const reservaData = ref({
  id_servicio: null,
  fecha: '',
  hora: ''
})

const minDate = new Date().toISOString().split('T')[0]

const cargarServicios = async () => {
  try {
    const res = await fetch('http://localhost:8000/api/servicios', { headers: getAuthHeaders() })
    if (res.ok) {
      const data = await res.json()
      serviciosList.value = data.data || data
    }
  } catch (err) { console.error(err) }
}

const buscarTurnos = async () => {
  if (!reservaData.value.id_servicio || !reservaData.value.fecha) return
  
  cargandoTurnos.value = true
  turnosDisponibles.value = []
  
  try {
    const res = await fetch(`http://localhost:8000/api/servicios/${reservaData.value.id_servicio}/turnos?fecha=${reservaData.value.fecha}`, {
      headers: getAuthHeaders()
    })
    if (res.ok) {
      const data = await res.json()
      turnosDisponibles.value = data.data || []
    }
  } catch (err) { console.error(err) } finally {
    cargandoTurnos.value = false
  }
}

const confirmarReserva = async () => {
  const { valid } = await formReserva.value.validate()
  if (!valid || !reservaData.value.hora) {
    errorReserva.value = 'Completa todos los pasos'
    return
  }

  isLoading.value = true
  try {
    const dateTime = `${reservaData.value.fecha} ${reservaData.value.hora}:00`
    const res = await fetch('http://localhost:8000/api/reservas', {
      method: 'POST',
      headers: getAuthHeaders(),
      body: JSON.stringify({
        id_servicio: reservaData.value.id_servicio,
        fecha_hora_inicio: dateTime,
      })
    })

    if (!res.ok) {
      const errData = await res.json()
      throw new Error(errData.message || 'Error al reservar')
    }

    snackbar.value = { show: true, text: '¡Turno reservado exitosamente!', color: 'success' }
    currentView.value = 'menu'
    reservaData.value = { id_servicio: null, fecha: '', hora: '' }
  } catch (err) {
    errorReserva.value = err.message
  } finally {
    isLoading.value = false
  }
}

if (isCliente.value) {
  // Cargar servicios si entra en la vista de reserva
  // En onMounted es mas fácil, pero dejémoslo ondemand
}

// === HISTORIAL Y PENDIENTES ===
const reservasBackend = ref([])
const cargandoHistorial = ref(false)

const reservasPendientes = computed(() => reservasBackend.value.filter(r => r.estado === 'pendiente'))
const reservasHistorial = computed(() => {
  if (isCliente.value) return reservasBackend.value // Cliente ve todas en historial
  return reservasBackend.value.filter(r => r.estado !== 'pendiente') // Profesional ve solo procesadas
})

const cargarHistorialReservas = async () => {
  cargandoHistorial.value = true
  try {
    const res = await fetch('http://localhost:8000/api/reservas', { headers: getAuthHeaders() })
    if (res.ok) {
      const data = await res.json()
      reservasBackend.value = data.data || []
    }
  } catch (err) { console.error(err) } finally {
    cargandoHistorial.value = false
  }
}

const abrirHistorial = () => {
  currentView.value = 'historial'
  cargarHistorialReservas()
}

const abrirPendientes = () => {
  currentView.value = 'pendientes'
  cargarHistorialReservas()
}

const cambiarEstadoReserva = async (id, estado) => {
  if (!confirm(`¿Estás seguro de que quieres cambiar la reserva a ${estado.toUpperCase()}?`)) return
  
  isLoading.value = true
  try {
    const res = await fetch(`http://localhost:8000/api/reservas/${id}/estado`, {
      method: 'PATCH',
      headers: getAuthHeaders(),
      body: JSON.stringify({ estado })
    })

    if (!res.ok) throw new Error('Error al cambiar estado')

    snackbar.value = { show: true, text: `Reserva ${estado}`, color: 'success' }
    await cargarHistorialReservas()
  } catch (err) {
    snackbar.value = { show: true, text: err.message, color: 'error' }
  } finally {
    isLoading.value = false
  }
}

// Watchers manuales para lazy loading
import { watch } from 'vue'
watch(currentView, (newVal) => {
  if (newVal === 'reservar' && serviciosList.value.length === 0) {
    cargarServicios()
  }
})

// --- Utils ---
const formatDateObj = (dateString) => {
  const d = new Date(dateString)
  return d.toLocaleString('es-ES', { weekday: 'long', year: 'numeric', month: 'short', day: 'numeric', hour: '2-digit', minute:'2-digit' })
}

const formatDateShort = (dateString) => {
  const d = new Date(dateString)
  return d.toLocaleString('es-ES', { day: '2-digit', month: '2-digit', year:'numeric', hour: '2-digit', minute:'2-digit' })
}

const getColorEstado = (estado) => {
  const map = { pendiente: 'warning', confirmada: 'success', cancelada: 'error', pagada: 'primary', finalizada: 'grey' }
  return map[estado] || 'grey'
}

</script>

<style scoped>
.brand-header { background: linear-gradient(135deg, #8C6D46 0%, #A6987A 100%); }
.border-card { border: 1px solid rgba(140, 109, 70, 0.1); }
.border-panel { border: 1px solid rgba(140, 109, 70, 0.2); transition: all 0.3s ease; }
.border-panel:hover { border-color: rgba(140, 109, 70, 0.5); box-shadow: 0 4px 12px rgba(140, 109, 70, 0.05); }
.gap-2 { gap: 8px; }
</style>
