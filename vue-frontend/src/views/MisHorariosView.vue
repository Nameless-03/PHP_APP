<template>
  <DashboardLayout title="Mis Horarios">
    
    <!-- VISTA PRINCIPAL (MENÚ) -->
    <v-fade-transition leave-absolute>
      <v-row v-if="currentView === 'menu'" justify="center" align="start" class="mt-4">
        <v-col cols="12" md="10" lg="8">
          <v-card class="elevation-4 rounded-xl overflow-hidden border-card">
            <v-card-text class="pa-0">
              <div class="brand-header pa-6 text-center text-white">
                <h1 class="text-h4 font-weight-bold mb-2">Gestión de Agenda</h1>
                <p class="text-subtitle-1 opacity-90">Selecciona qué deseas configurar</p>
              </div>
            </v-card-text>

            <v-card-text class="pa-8">
              <v-row>
                <!-- Opcion 1: Disponibilidad -->
                <v-col cols="12" md="6">
                  <v-hover v-slot="{ isHovering, props }">
                    <v-card
                      v-bind="props"
                      :elevation="isHovering ? 8 : 2"
                      class="h-100 rounded-xl cursor-pointer transition-swing border-panel d-flex flex-column align-center text-center pa-6"
                      @click="currentView = 'disponibilidad'"
                    >
                      <v-avatar color="primary" size="80" variant="tonal" class="mb-4">
                        <v-icon size="40">mdi-calendar-clock</v-icon>
                      </v-avatar>
                      <h2 class="text-h6 font-weight-bold mb-2 text-grey-darken-3">Configurar Disponibilidad</h2>
                      <p class="text-body-2 text-medium-emphasis">
                        Define tus horarios de trabajo semanales habituales, días de descanso y pausas entre turnos.
                      </p>
                    </v-card>
                  </v-hover>
                </v-col>

                <!-- Opcion 2: Reglas de Agenda (Excepciones) -->
                <v-col cols="12" md="6">
                  <v-hover v-slot="{ isHovering, props }">
                    <v-card
                      v-bind="props"
                      :elevation="isHovering ? 8 : 2"
                      class="h-100 rounded-xl cursor-pointer transition-swing border-panel d-flex flex-column align-center text-center pa-6"
                      @click="abrirExcepciones"
                    >
                      <v-avatar color="secondary" size="80" variant="tonal" class="mb-4">
                        <v-icon size="40">mdi-calendar-alert</v-icon>
                      </v-avatar>
                      <h2 class="text-h6 font-weight-bold mb-2 text-grey-darken-3">Configurar Reglas de Agenda</h2>
                      <p class="text-body-2 text-medium-emphasis">
                        Añade feriados, vacaciones o días excepcionales donde tu disponibilidad cambia temporalmente.
                      </p>
                    </v-card>
                  </v-hover>
                </v-col>
              </v-row>
            </v-card-text>
          </v-card>
        </v-col>
      </v-row>
    </v-fade-transition>

    <!-- VISTA: DISPONIBILIDAD SEMANAL -->
    <v-fade-transition leave-absolute>
      <v-row v-if="currentView === 'disponibilidad'" justify="center" align="start" class="mt-4">
        <v-col cols="12" md="10" lg="8">
          <v-btn
            variant="text"
            prepend-icon="mdi-arrow-left"
            class="mb-4 text-none font-weight-bold"
            @click="currentView = 'menu'"
          >
            Volver al menú
          </v-btn>

          <v-card class="elevation-4 rounded-xl overflow-hidden border-card">
            <v-card-text class="pa-0">
              <div class="brand-header pa-6 text-white d-flex align-center">
                <v-icon size="36" class="mr-4">mdi-calendar-clock</v-icon>
                <div>
                  <h1 class="text-h5 font-weight-bold mb-0">Disponibilidad Semanal</h1>
                  <p class="text-subtitle-2 opacity-90 mb-0">Horarios fijos de atención</p>
                </div>
              </div>
            </v-card-text>

            <v-card-text class="pa-6">
              <v-alert v-if="error" type="error" variant="tonal" class="mb-6 rounded-lg" closable @click:close="error = ''">
                {{ error }}
              </v-alert>

              <v-form @submit.prevent="saveHorarios" ref="formDisponibilidad">
                <v-expansion-panels variant="accordion" class="custom-panels">
                  <v-expansion-panel v-for="(dia, index) in diasSemana" :key="dia.value" class="mb-2 rounded-lg border-panel">
                    <v-expansion-panel-title class="d-flex align-center font-weight-medium text-grey-darken-3">
                      <v-switch v-model="dia.activo" color="secondary" hide-details density="compact" class="mr-4 mt-0" @click.stop></v-switch>
                      <span :class="{'text-secondary font-weight-bold': dia.activo}">{{ dia.label }}</span>
                      <v-spacer></v-spacer>
                      <v-chip size="small" :color="dia.activo ? 'success' : 'grey'" variant="tonal">
                        {{ dia.activo ? 'Disponible' : 'Cerrado' }}
                      </v-chip>
                    </v-expansion-panel-title>

                    <v-expansion-panel-text v-if="dia.activo">
                      <v-row class="mt-2">
                        <v-col cols="12" sm="6" md="3">
                          <v-text-field v-model="dia.hora_inicio" label="Hora Inicio" type="time" variant="outlined" density="comfortable" color="primary" :rules="[v => !!v || 'Requerido']"></v-text-field>
                        </v-col>
                        <v-col cols="12" sm="6" md="3">
                          <v-text-field v-model="dia.hora_fin" label="Hora Fin" type="time" variant="outlined" density="comfortable" color="primary" :rules="[v => !!v || 'Requerido', v => !dia.hora_inicio || v > dia.hora_inicio || 'Mayor a inicio']"></v-text-field>
                        </v-col>
                        <v-col cols="12" sm="6" md="3">
                          <v-text-field v-model="dia.pausa_minutos" label="Pausa (min)" type="number" variant="outlined" density="comfortable" color="primary" min="0" hint="Descanso"></v-text-field>
                        </v-col>
                        <v-col cols="12" sm="6" md="3">
                          <v-text-field v-model="dia.buffer_minutos" label="Buffer (min)" type="number" variant="outlined" density="comfortable" color="primary" min="0" hint="Tiempo entre turnos"></v-text-field>
                        </v-col>
                      </v-row>
                    </v-expansion-panel-text>
                  </v-expansion-panel>
                </v-expansion-panels>

                <v-divider class="my-6"></v-divider>

                <div class="d-flex justify-end gap-4">
                  <v-btn color="secondary" size="large" class="rounded-lg text-none px-8 font-weight-bold" type="submit" :loading="isLoading" prepend-icon="mdi-content-save">
                    Guardar Horarios
                  </v-btn>
                </div>
              </v-form>
            </v-card-text>
          </v-card>
        </v-col>
      </v-row>
    </v-fade-transition>

    <!-- VISTA: REGLAS DE AGENDA (EXCEPCIONES) -->
    <v-fade-transition leave-absolute>
      <v-row v-if="currentView === 'excepciones'" justify="center" align="start" class="mt-4">
        <v-col cols="12" md="10" lg="8">
          <v-btn
            variant="text"
            prepend-icon="mdi-arrow-left"
            class="mb-4 text-none font-weight-bold"
            @click="currentView = 'menu'"
          >
            Volver al menú
          </v-btn>

          <v-card class="elevation-4 rounded-xl overflow-hidden border-card">
            <v-card-text class="pa-0">
              <div class="brand-header pa-6 text-white d-flex align-center" style="background: linear-gradient(135deg, #7A5C3D 0%, #A6987A 100%);">
                <v-icon size="36" class="mr-4">mdi-calendar-alert</v-icon>
                <div>
                  <h1 class="text-h5 font-weight-bold mb-0">Reglas y Excepciones</h1>
                  <p class="text-subtitle-2 opacity-90 mb-0">Gestiona feriados y vacaciones</p>
                </div>
              </div>
            </v-card-text>

            <v-card-text class="pa-6">
              <v-alert v-if="errorExcepciones" type="error" variant="tonal" class="mb-6 rounded-lg" closable @click:close="errorExcepciones = ''">
                {{ errorExcepciones }}
              </v-alert>

              <!-- Agregar Nueva Regla -->
              <h3 class="text-h6 font-weight-bold mb-4 text-primary">Agregar Nueva Regla</h3>
              <v-form @submit.prevent="saveExcepcion" ref="formExcepcion" class="mb-8">
                <v-row align="center">
                  <v-col cols="12" md="4">
                    <v-text-field
                      v-model="nuevaExcepcion.fecha"
                      label="Fecha"
                      type="date"
                      variant="outlined"
                      density="comfortable"
                      color="primary"
                      :rules="[v => !!v || 'La fecha es obligatoria']"
                    ></v-text-field>
                  </v-col>
                  <v-col cols="12" md="4">
                    <v-text-field
                      v-model="nuevaExcepcion.motivo"
                      label="Motivo (Ej: Navidad)"
                      variant="outlined"
                      density="comfortable"
                      color="primary"
                      :rules="[v => !!v || 'El motivo es obligatorio']"
                    ></v-text-field>
                  </v-col>
                  <v-col cols="12" md="2">
                    <v-switch
                      v-model="nuevaExcepcion.disponible"
                      color="success"
                      :label="nuevaExcepcion.disponible ? 'Disponible' : 'Cerrado'"
                      hide-details
                      class="mt-0"
                    ></v-switch>
                  </v-col>
                  <v-col cols="12" md="2" class="text-right">
                    <v-btn color="secondary" type="submit" :loading="isLoading" block class="text-none font-weight-bold rounded-lg" size="large">
                      Añadir
                    </v-btn>
                  </v-col>
                </v-row>
              </v-form>

              <v-divider class="mb-6"></v-divider>

              <!-- Lista de Reglas -->
              <h3 class="text-h6 font-weight-bold mb-4 text-primary d-flex align-center">
                <v-icon class="mr-2">mdi-format-list-bulleted</v-icon>
                Reglas Configuradas
              </h3>
              
              <v-list bg-color="transparent" class="pa-0">
                <template v-for="(exc, index) in excepciones" :key="exc.id">
                  <v-card class="mb-3 rounded-lg border-panel" elevation="0">
                    <v-card-text class="d-flex align-center pa-4">
                      <v-avatar :color="exc.disponible ? 'success' : 'error'" variant="tonal" size="48" class="mr-4">
                        <v-icon>{{ exc.disponible ? 'mdi-calendar-check' : 'mdi-calendar-remove' }}</v-icon>
                      </v-avatar>
                      <div class="flex-grow-1">
                        <h4 class="text-subtitle-1 font-weight-bold text-grey-darken-3 mb-1">{{ formatDate(exc.fecha) }}</h4>
                        <p class="text-body-2 text-medium-emphasis mb-0">{{ exc.motivo }}</p>
                      </div>
                      <v-chip :color="exc.disponible ? 'success' : 'error'" size="small" class="mr-4 font-weight-bold">
                        {{ exc.disponible ? 'Disponible' : 'No Disponible' }}
                      </v-chip>
                      <v-btn icon color="error" variant="text" @click="deleteExcepcion(exc.id)" :loading="isLoading">
                        <v-icon>mdi-delete</v-icon>
                      </v-btn>
                    </v-card-text>
                  </v-card>
                </template>
                
                <div v-if="excepciones.length === 0" class="text-center pa-8 opacity-60">
                  <v-icon size="48" color="grey">mdi-calendar-blank</v-icon>
                  <p class="mt-2 text-body-1">No hay reglas ni excepciones configuradas aún.</p>
                </div>
              </v-list>

            </v-card-text>
          </v-card>
        </v-col>
      </v-row>
    </v-fade-transition>

    <!-- Snackbar for success -->
    <v-snackbar v-model="snackbar.show" :color="snackbar.color" :timeout="3000" location="top">
      {{ snackbar.text }}
      <template v-slot:actions>
        <v-btn variant="text" @click="snackbar.show = false">Cerrar</v-btn>
      </template>
    </v-snackbar>

  </DashboardLayout>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import DashboardLayout from '../components/DashboardLayout.vue'

// --- ESTADO GENERAL ---
const currentView = ref('menu') // 'menu', 'disponibilidad', 'excepciones'
const isLoading = ref(false)
const snackbar = ref({ show: false, text: '', color: 'success' })

// --- UTILIDADES ---
const getAuthHeaders = () => {
  const token = localStorage.getItem('auth_token')
  return {
    'Content-Type': 'application/json',
    'Accept': 'application/json',
    'Authorization': `Bearer ${token}`
  }
}

const getCurrentUserId = () => {
  const userStr = localStorage.getItem('user')
  return userStr ? JSON.parse(userStr).id : null
}

const formatDate = (dateString) => {
  const options = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' };
  const d = new Date(dateString + 'T00:00:00'); // Evita problemas de timezone
  return d.toLocaleDateString('es-ES', options).replace(/^\w/, c => c.toUpperCase());
}

// --- LOGICA DE DISPONIBILIDAD SEMANAL ---
const formDisponibilidad = ref(null)
const error = ref('')

const diasSemana = ref([
  { value: 'lunes', label: 'Lunes', activo: false, id: null, hora_inicio: '09:00', hora_fin: '17:00', pausa_minutos: 0, buffer_minutos: 0 },
  { value: 'martes', label: 'Martes', activo: false, id: null, hora_inicio: '09:00', hora_fin: '17:00', pausa_minutos: 0, buffer_minutos: 0 },
  { value: 'miercoles', label: 'Miércoles', activo: false, id: null, hora_inicio: '09:00', hora_fin: '17:00', pausa_minutos: 0, buffer_minutos: 0 },
  { value: 'jueves', label: 'Jueves', activo: false, id: null, hora_inicio: '09:00', hora_fin: '17:00', pausa_minutos: 0, buffer_minutos: 0 },
  { value: 'viernes', label: 'Viernes', activo: false, id: null, hora_inicio: '09:00', hora_fin: '17:00', pausa_minutos: 0, buffer_minutos: 0 },
  { value: 'sabado', label: 'Sábado', activo: false, id: null, hora_inicio: '10:00', hora_fin: '14:00', pausa_minutos: 0, buffer_minutos: 0 },
  { value: 'domingo', label: 'Domingo', activo: false, id: null, hora_inicio: '10:00', hora_fin: '14:00', pausa_minutos: 0, buffer_minutos: 0 },
])

const cargarHorarios = async () => {
  const userId = getCurrentUserId()
  if (!userId) return

  try {
    const response = await fetch(`http://localhost:8000/api/disponibilidad/${userId}`, { headers: getAuthHeaders() })
    if (response.ok) {
      const data = await response.json()
      const disponibilidades = data.data || []

      diasSemana.value.forEach(dia => { dia.activo = false; dia.id = null })
      disponibilidades.forEach(disp => {
        const diaObj = diasSemana.value.find(d => d.value === disp.dia_semana)
        if (diaObj) {
          diaObj.activo = true
          diaObj.id = disp.id
          diaObj.hora_inicio = disp.hora_inicio.substring(0, 5)
          diaObj.hora_fin = disp.hora_fin.substring(0, 5)
          diaObj.pausa_minutos = disp.pausa_minutos || 0
          diaObj.buffer_minutos = disp.buffer_minutos || 0
        }
      })
    }
  } catch (err) {
    console.error('Error al cargar horarios', err)
  }
}

const saveHorarios = async () => {
  const { valid } = await formDisponibilidad.value.validate()
  if (!valid) {
    error.value = 'Revisa los errores en el formulario.'
    return
  }

  isLoading.value = true
  error.value = ''

  try {
    const promises = []

    for (const dia of diasSemana.value) {
      if (dia.activo) {
        const payload = {
          dia_semana: dia.value,
          hora_inicio: dia.hora_inicio,
          hora_fin: dia.hora_fin,
          pausa_minutos: parseInt(dia.pausa_minutos),
          buffer_minutos: parseInt(dia.buffer_minutos)
        }

        const url = dia.id ? `http://localhost:8000/api/disponibilidad/${dia.id}` : `http://localhost:8000/api/disponibilidad`
        const method = dia.id ? 'PUT' : 'POST'

        promises.push(
          fetch(url, { method, headers: getAuthHeaders(), body: JSON.stringify(payload) })
            .then(async res => { if (!res.ok) throw new Error(await res.text()) })
        )
      } else if (dia.id) {
        promises.push(
          fetch(`http://localhost:8000/api/disponibilidad/${dia.id}`, { method: 'DELETE', headers: getAuthHeaders() })
            .then(async res => { if (!res.ok) throw new Error(await res.text()) })
        )
      }
    }

    await Promise.all(promises)
    snackbar.value = { show: true, text: 'Disponibilidad guardada con éxito', color: 'success' }
    await cargarHorarios()
  } catch (err) {
    error.value = 'Ocurrió un error al guardar. Verifica los datos e intenta de nuevo.'
    console.error(err)
  } finally {
    isLoading.value = false
  }
}

// --- LOGICA DE EXCEPCIONES ---
const formExcepcion = ref(null)
const errorExcepciones = ref('')
const excepciones = ref([])

const nuevaExcepcion = ref({
  fecha: '',
  motivo: '',
  disponible: false
})

const abrirExcepciones = () => {
  currentView.value = 'excepciones'
  cargarExcepciones()
}

const cargarExcepciones = async () => {
  const userId = getCurrentUserId()
  if (!userId) return

  isLoading.value = true
  try {
    const response = await fetch(`http://localhost:8000/api/excepciones-agenda/${userId}`, { headers: getAuthHeaders() })
    if (response.ok) {
      const data = await response.json()
      excepciones.value = data.data || []
    }
  } catch (err) {
    console.error('Error al cargar excepciones', err)
  } finally {
    isLoading.value = false
  }
}

const saveExcepcion = async () => {
  const { valid } = await formExcepcion.value.validate()
  if (!valid) return

  isLoading.value = true
  errorExcepciones.value = ''

  try {
    const response = await fetch(`http://localhost:8000/api/excepciones-agenda`, {
      method: 'POST',
      headers: getAuthHeaders(),
      body: JSON.stringify({
        fecha: nuevaExcepcion.value.fecha,
        motivo: nuevaExcepcion.value.motivo,
        disponible: nuevaExcepcion.value.disponible
      })
    })

    const data = await response.json()

    if (!response.ok) {
      throw new Error(data.message || 'Error al guardar la regla')
    }

    snackbar.value = { show: true, text: 'Regla agregada exitosamente', color: 'success' }
    formExcepcion.value.reset()
    nuevaExcepcion.value.disponible = false
    await cargarExcepciones()

  } catch (err) {
    errorExcepciones.value = err.message
  } finally {
    isLoading.value = false
  }
}

const deleteExcepcion = async (id) => {
  if (!confirm('¿Estás seguro de que deseas eliminar esta regla?')) return

  isLoading.value = true
  try {
    const response = await fetch(`http://localhost:8000/api/excepciones-agenda/${id}`, {
      method: 'DELETE',
      headers: getAuthHeaders()
    })

    if (!response.ok) throw new Error('Error al eliminar')

    snackbar.value = { show: true, text: 'Regla eliminada', color: 'success' }
    await cargarExcepciones()
  } catch (err) {
    console.error(err)
    snackbar.value = { show: true, text: 'No se pudo eliminar la regla', color: 'error' }
  } finally {
    isLoading.value = false
  }
}

onMounted(() => {
  cargarHorarios()
})
</script>

<style scoped>
.brand-header {
  background: linear-gradient(135deg, #8C6D46 0%, #A6987A 100%);
}

.border-card {
  border: 1px solid rgba(140, 109, 70, 0.1);
}

.custom-panels {
  background-color: transparent !important;
}

.border-panel {
  border: 1px solid rgba(140, 109, 70, 0.2);
  transition: all 0.3s ease;
}

.border-panel:hover {
  border-color: rgba(140, 109, 70, 0.5);
  box-shadow: 0 4px 12px rgba(140, 109, 70, 0.05);
}

.gap-4 {
  gap: 16px;
}
</style>
