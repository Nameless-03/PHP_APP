<template>
  <DashboardLayout title="Configuración de Disponibilidad">
    <v-row justify="center" align="start" class="mt-4">
      <v-col cols="12" md="10" lg="8">
        <v-card class="elevation-4 rounded-xl overflow-hidden border-card">
          <!-- Header -->
          <v-card-text class="pa-0">
            <div class="brand-header pa-6 text-center text-white">
              <h1 class="text-h4 font-weight-bold mb-2">Mis Horarios</h1>
              <p class="text-subtitle-1 opacity-90">Configura tu disponibilidad para recibir reservas</p>
            </div>
          </v-card-text>

          <v-card-text class="pa-6">
            <v-alert
              v-if="error"
              type="error"
              variant="tonal"
              class="mb-6 rounded-lg"
              closable
              @click:close="error = ''"
            >
              {{ error }}
            </v-alert>

            <v-form @submit.prevent="saveHorarios" ref="form">
              <v-expansion-panels variant="accordion" class="custom-panels">
                <v-expansion-panel
                  v-for="(dia, index) in diasSemana"
                  :key="dia.value"
                  class="mb-2 rounded-lg border-panel"
                >
                  <v-expansion-panel-title class="d-flex align-center font-weight-medium text-grey-darken-3">
                    <v-switch
                      v-model="dia.activo"
                      color="secondary"
                      hide-details
                      density="compact"
                      class="mr-4 mt-0"
                      @click.stop
                    ></v-switch>
                    <span :class="{'text-secondary font-weight-bold': dia.activo}">
                      {{ dia.label }}
                    </span>
                    <v-spacer></v-spacer>
                    <v-chip
                      size="small"
                      :color="dia.activo ? 'success' : 'grey'"
                      variant="tonal"
                    >
                      {{ dia.activo ? 'Disponible' : 'Cerrado' }}
                    </v-chip>
                  </v-expansion-panel-title>

                  <v-expansion-panel-text v-if="dia.activo">
                    <v-row class="mt-2">
                      <v-col cols="12" sm="6" md="3">
                        <v-text-field
                          v-model="dia.hora_inicio"
                          label="Hora Inicio"
                          type="time"
                          variant="outlined"
                          density="comfortable"
                          color="primary"
                          :rules="[v => !!v || 'Requerido']"
                        ></v-text-field>
                      </v-col>
                      <v-col cols="12" sm="6" md="3">
                        <v-text-field
                          v-model="dia.hora_fin"
                          label="Hora Fin"
                          type="time"
                          variant="outlined"
                          density="comfortable"
                          color="primary"
                          :rules="[
                            v => !!v || 'Requerido',
                            v => !dia.hora_inicio || v > dia.hora_inicio || 'Debe ser mayor a la hora de inicio'
                          ]"
                        ></v-text-field>
                      </v-col>
                      <v-col cols="12" sm="6" md="3">
                        <v-text-field
                          v-model="dia.pausa_minutos"
                          label="Pausa (min)"
                          type="number"
                          variant="outlined"
                          density="comfortable"
                          color="primary"
                          min="0"
                          hint="Descanso en la jornada"
                        ></v-text-field>
                      </v-col>
                      <v-col cols="12" sm="6" md="3">
                        <v-text-field
                          v-model="dia.buffer_minutos"
                          label="Buffer (min)"
                          type="number"
                          variant="outlined"
                          density="comfortable"
                          color="primary"
                          min="0"
                          hint="Tiempo entre turnos"
                        ></v-text-field>
                      </v-col>
                    </v-row>
                  </v-expansion-panel-text>
                </v-expansion-panel>
              </v-expansion-panels>

              <v-divider class="my-6"></v-divider>

              <div class="d-flex justify-end gap-4">
                <v-btn
                  variant="outlined"
                  color="grey-darken-1"
                  size="large"
                  class="rounded-lg text-none px-6"
                  @click="cargarHorarios"
                  :disabled="isLoading"
                >
                  Descartar Cambios
                </v-btn>
                <v-btn
                  color="secondary"
                  size="large"
                  class="rounded-lg text-none px-8 font-weight-bold"
                  type="submit"
                  :loading="isLoading"
                  prepend-icon="mdi-content-save"
                >
                  Guardar Horarios
                </v-btn>
              </div>
            </v-form>
          </v-card-text>
        </v-card>
      </v-col>
    </v-row>

    <!-- Snackbar for success -->
    <v-snackbar
      v-model="snackbar.show"
      :color="snackbar.color"
      :timeout="3000"
      location="top"
    >
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

const form = ref(null)
const error = ref('')
const isLoading = ref(false)
const snackbar = ref({ show: false, text: '', color: 'success' })

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
  if (userStr) {
    return JSON.parse(userStr).id
  }
  return null
}

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

  isLoading.value = true
  error.value = ''

  try {
    const response = await fetch(`http://localhost:8000/api/disponibilidad/${userId}`, {
      headers: getAuthHeaders()
    })

    if (!response.ok) throw new Error('Error al cargar horarios')

    const data = await response.json()
    const disponibilidades = data.data || []

    // Reset all to inactive first
    diasSemana.value.forEach(dia => {
      dia.activo = false
      dia.id = null
    })

    // Map fetched data
    disponibilidades.forEach(disp => {
      const diaObj = diasSemana.value.find(d => d.value === disp.dia_semana)
      if (diaObj) {
        diaObj.activo = true
        diaObj.id = disp.id
        // Extraer HH:mm si viene con segundos (ej: 09:00:00)
        diaObj.hora_inicio = disp.hora_inicio.substring(0, 5)
        diaObj.hora_fin = disp.hora_fin.substring(0, 5)
        diaObj.pausa_minutos = disp.pausa_minutos || 0
        diaObj.buffer_minutos = disp.buffer_minutos || 0
      }
    })
  } catch (err) {
    error.value = 'No se pudieron cargar tus horarios actuales.'
    console.error(err)
  } finally {
    isLoading.value = false
  }
}

const saveHorarios = async () => {
  const { valid } = await form.value.validate()
  if (!valid) {
    error.value = 'Por favor, corrige los errores en el formulario antes de guardar.'
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

        if (dia.id) {
          // UPDATE
          promises.push(
            fetch(`http://localhost:8000/api/disponibilidad/${dia.id}`, {
              method: 'PUT',
              headers: getAuthHeaders(),
              body: JSON.stringify(payload)
            }).then(async res => {
              if (!res.ok) throw new Error(await res.text())
            })
          )
        } else {
          // CREATE
          promises.push(
            fetch(`http://localhost:8000/api/disponibilidad`, {
              method: 'POST',
              headers: getAuthHeaders(),
              body: JSON.stringify(payload)
            }).then(async res => {
              if (!res.ok) throw new Error(await res.text())
            })
          )
        }
      } else {
        // DELETE si estaba activo antes
        if (dia.id) {
          promises.push(
            fetch(`http://localhost:8000/api/disponibilidad/${dia.id}`, {
              method: 'DELETE',
              headers: getAuthHeaders()
            }).then(async res => {
              if (!res.ok) throw new Error(await res.text())
            })
          )
        }
      }
    }

    await Promise.all(promises)

    snackbar.value = { show: true, text: 'Horarios guardados exitosamente', color: 'success' }
    await cargarHorarios() // Refresh to get new IDs
  } catch (err) {
    error.value = 'Error al guardar los horarios. Revisa la consola para más detalles.'
    console.error(err)
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
