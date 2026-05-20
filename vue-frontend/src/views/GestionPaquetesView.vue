<template>
  <DashboardLayout title="Gestión de Paquetes">
    <v-row>
      <!-- Form to Add Package -->
      <v-col cols="12" lg="7">
        <v-card class="pa-8 rounded-xl elevation-2 h-100">
          <div class="d-flex align-center mb-6">
            <v-icon size="40" color="primary" class="mr-4">mdi-package-variant-closed-plus</v-icon>
            <div>
              <h2 class="text-h5 font-weight-bold mb-1">Crear Paquete de Sesiones</h2>
              <p class="text-body-2 text-medium-emphasis mb-0">
                Ofrece múltiples sesiones agrupadas de tus servicios con precios especiales para tus clientes.
              </p>
            </div>
          </div>

          <v-divider class="mb-6"></v-divider>

          <v-form @submit.prevent="savePackage" ref="form">
            <v-row>
              <v-col cols="12">
                <v-text-field
                  v-model="packageForm.nombre"
                  :rules="[rules.required]"
                  label="Nombre del Paquete"
                  placeholder="Ej: Paquete de 5 Sesiones de Consultoría"
                  variant="outlined"
                  prepend-inner-icon="mdi-format-title"
                  color="primary"
                ></v-text-field>
              </v-col>

              <v-col cols="12">
                <v-textarea
                  v-model="packageForm.descripcion"
                  :rules="[rules.required]"
                  label="Descripción del Paquete"
                  placeholder="Explica qué incluye este paquete, las condiciones, políticas de cancelación..."
                  variant="outlined"
                  prepend-inner-icon="mdi-text-box-outline"
                  color="primary"
                  auto-grow
                  rows="3"
                ></v-textarea>
              </v-col>

              <v-col cols="12" md="6">
                <v-text-field
                  v-model="packageForm.cantidad_sesiones"
                  :rules="[rules.required, rules.isInteger, rules.minOne]"
                  label="Cantidad de Sesiones Incluidas"
                  placeholder="Ej: 5"
                  variant="outlined"
                  type="number"
                  prepend-inner-icon="mdi-numeric"
                  color="primary"
                ></v-text-field>
              </v-col>

              <v-col cols="12" md="6">
                <v-text-field
                  v-model="packageForm.precio"
                  :rules="[rules.required, rules.isNumber]"
                  label="Precio Total del Paquete (USD)"
                  placeholder="0.00"
                  prefix="$"
                  variant="outlined"
                  prepend-inner-icon="mdi-currency-usd"
                  color="primary"
                ></v-text-field>
              </v-col>

              <v-col cols="12" md="6">
                <v-text-field
                  v-model="packageForm.vencimiento"
                  :rules="[rules.isInteger]"
                  label="Vencimiento / Validez (Días)"
                  placeholder="Ej: 90 (Vacío si no vence)"
                  variant="outlined"
                  type="number"
                  prepend-inner-icon="mdi-calendar-range"
                  color="primary"
                ></v-text-field>
              </v-col>

              <v-col cols="12" md="6">
                <!-- Select Multiple Services -->
                <v-select
                  v-model="packageForm.servicios"
                  :items="servicesList"
                  item-title="nombre"
                  item-value="id"
                  label="Servicios Asociados"
                  placeholder="Selecciona los servicios..."
                  multiple
                  chips
                  variant="outlined"
                  prepend-inner-icon="mdi-layers-outline"
                  color="primary"
                  :rules="[rules.requiredArray]"
                ></v-select>
              </v-col>
            </v-row>

            <!-- Alerts -->
            <v-alert v-if="successMsg" type="success" variant="tonal" class="mt-4 rounded-lg animate-fade">
              {{ successMsg }}
            </v-alert>
            <v-alert v-if="errorMsg" type="error" variant="tonal" class="mt-4 rounded-lg animate-fade">
              {{ errorMsg }}
            </v-alert>

            <div class="d-flex justify-end mt-6">
              <v-btn
                variant="outlined"
                color="grey-darken-1"
                class="mr-4 px-6 text-none font-weight-bold"
                @click="resetForm"
              >
                Limpiar
              </v-btn>
              <v-btn
                type="submit"
                color="primary"
                :loading="isLoading"
                class="px-8 text-none font-weight-bold elevation-2"
              >
                Guardar Paquete
                <v-icon end>mdi-content-save</v-icon>
              </v-btn>
            </div>
          </v-form>
        </v-card>
      </v-col>

      <!-- Side panel: List of current packages -->
      <v-col cols="12" lg="5">
        <v-card class="pa-6 rounded-xl elevation-1 h-100 bg-grey-lighten-4">
          <h3 class="text-h6 font-weight-bold mb-4 d-flex align-center">
            <v-icon start color="primary" class="mr-2">mdi-package-variant</v-icon>
            Paquetes Publicados
          </h3>

          <v-list bg-color="transparent" class="pa-0">
            <template v-for="(item, index) in publishedPackages" :key="item.id">
              <v-card class="mb-4 rounded-xl border position-relative" elevation="0" color="white">
                <v-card-text class="pa-5">
                  <div class="d-flex justify-space-between align-start mb-2">
                    <div>
                      <h4 class="text-subtitle-1 font-weight-bold text-primary mb-1">{{ item.nombre }}</h4>
                      <div class="d-flex align-center gap-2 flex-wrap">
                        <v-chip size="x-small" color="primary" variant="flat" class="font-weight-bold text-uppercase">
                          {{ item.cantidad_sesiones }} sesiones
                        </v-chip>
                        <v-chip v-if="item.vencimiento" size="x-small" color="orange-darken-1" variant="tonal" class="font-weight-bold">
                          Vence en {{ item.vencimiento }} días
                        </v-chip>
                        <v-chip v-else size="x-small" color="green-darken-1" variant="tonal" class="font-weight-bold">
                          Sin Vencimiento
                        </v-chip>
                      </div>
                    </div>
                    <div class="d-flex align-center">
                      <span class="text-h6 font-weight-black text-success mr-2">
                        ${{ item.precio }}
                      </span>
                      <span class="text-caption text-medium-emphasis">USD</span>
                    </div>
                  </div>

                  <p class="text-body-2 text-medium-emphasis mb-3 text-justify">{{ item.descripcion }}</p>

                  <v-divider class="my-3"></v-divider>

                  <div>
                    <span class="text-caption font-weight-bold text-grey-darken-1 mb-2 d-block">
                      <v-icon size="small" class="mr-1">mdi-link-variant</v-icon>
                      Servicios Incluidos:
                    </span>
                    <div class="d-flex flex-wrap gap-1">
                      <v-chip
                        v-for="s in item.servicios"
                        :key="s.id"
                        size="small"
                        color="secondary"
                        variant="tonal"
                        class="mr-1 mb-1 font-weight-medium"
                      >
                        {{ s.nombre }}
                      </v-chip>
                    </div>
                  </div>

                  <div class="d-flex justify-end mt-4">
                    <v-btn
                      variant="text"
                      color="error"
                      density="comfortable"
                      class="text-none font-weight-bold"
                      prepend-icon="mdi-trash-can-outline"
                      @click="confirmDelete(item)"
                    >
                      Eliminar Paquete
                    </v-btn>
                  </div>
                </v-card-text>
              </v-card>
            </template>

            <div v-if="publishedPackages.length === 0" class="text-center pa-12 opacity-60">
              <v-icon size="64" color="grey-lighten-1">mdi-package-variant-closed-remove</v-icon>
              <p class="mt-3 text-body-1 font-weight-medium">Aún no tienes paquetes de sesiones creados.</p>
              <p class="text-caption text-medium-emphasis">Completa el formulario de la izquierda para publicar tu primer paquete.</p>
            </div>
          </v-list>
        </v-card>
      </v-col>
    </v-row>

    <!-- Delete Confirmation Dialog -->
    <v-dialog v-model="deleteDialog" max-width="400">
      <v-card class="rounded-xl pa-4">
        <v-card-title class="text-h6 font-weight-bold text-error">
          <v-icon start color="error" class="mr-2">mdi-alert-circle-outline</v-icon>
          ¿Eliminar Paquete?
        </v-card-title>
        <v-card-text class="text-body-1 py-2">
          ¿Estás seguro de que deseas eliminar el paquete <strong>"{{ selectedPackage?.nombre }}"</strong>?
          Esta acción no se puede deshacer y los clientes ya no podrán adquirirlo.
        </v-card-text>
        <v-card-actions class="justify-end pt-4">
          <v-btn variant="outlined" color="grey-darken-1" class="text-none font-weight-bold px-4" @click="deleteDialog = false">
            Cancelar
          </v-btn>
          <v-btn color="error" class="text-none font-weight-bold px-6 elevation-1" @click="deletePackage">
            Eliminar
          </v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>
  </DashboardLayout>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import DashboardLayout from '../components/DashboardLayout.vue'

const form = ref(null)
const isLoading = ref(false)
const successMsg = ref('')
const errorMsg = ref('')

const packageForm = ref({
  nombre: '',
  descripcion: '',
  cantidad_sesiones: '',
  precio: '',
  vencimiento: '',
  servicios: []
})

const servicesList = ref([])
const publishedPackages = ref([])

// Delete modal state
const deleteDialog = ref(false)
const selectedPackage = ref(null)

const rules = {
  required: value => !!value || 'Este campo es obligatorio.',
  requiredArray: value => (Array.isArray(value) && value.length > 0) || 'Debes asociar al menos un servicio.',
  isNumber: value => {
    const pattern = /^\d+(\.\d{1,2})?$/
    return pattern.test(value) || 'Debe ser un número válido (ej: 299.99).'
  },
  isInteger: value => {
    if (!value) return true // Vencimiento opcional
    const pattern = /^\d+$/
    return pattern.test(value) || 'Debe ser un número entero.'
  },
  minOne: value => parseInt(value) >= 1 || 'Debe incluir al menos 1 sesión.'
}

const loadData = async () => {
  const token = localStorage.getItem('auth_token')
  const userStr = localStorage.getItem('user')
  if (!token || !userStr) return

  const user = JSON.parse(userStr)

  try {
    // 1. Cargar servicios del profesional
    const servicesResponse = await fetch(`http://localhost:8000/api/servicios?id_profesional=${user.id}`, {
      headers: {
        'Authorization': `Bearer ${token}`,
        'Accept': 'application/json'
      }
    })
    if (servicesResponse.ok) {
      const data = await servicesResponse.json()
      servicesList.value = data.data || []
    }

    // 2. Cargar paquetes del profesional
    const packagesResponse = await fetch('http://localhost:8000/api/paquetes', {
      headers: {
        'Authorization': `Bearer ${token}`,
        'Accept': 'application/json'
      }
    })
    if (packagesResponse.ok) {
      const data = await packagesResponse.json()
      publishedPackages.value = data.data || []
    }
  } catch (error) {
    console.error('Error al cargar datos:', error)
  }
}

onMounted(async () => {
  await loadData()
})

const savePackage = async () => {
  const { valid } = await form.value.validate()
  if (!valid) {
    errorMsg.value = 'Por favor, corrige los errores en el formulario.'
    successMsg.value = ''
    return
  }

  isLoading.value = true
  errorMsg.value = ''
  successMsg.value = ''

  const token = localStorage.getItem('auth_token')
  const payload = {
    nombre: packageForm.value.nombre,
    descripcion: packageForm.value.descripcion,
    cantidad_sesiones: parseInt(packageForm.value.cantidad_sesiones),
    precio: parseFloat(packageForm.value.precio),
    servicios: packageForm.value.servicios
  }

  if (packageForm.value.vencimiento) {
    payload.vencimiento = parseInt(packageForm.value.vencimiento)
  }

  try {
    const response = await fetch('http://localhost:8000/api/paquetes', {
      method: 'POST',
      headers: {
        'Authorization': `Bearer ${token}`,
        'Content-Type': 'application/json',
        'Accept': 'application/json'
      },
      body: JSON.stringify(payload)
    })

    const data = await response.json()

    if (!response.ok) {
      throw new Error(data.message || 'Ocurrió un error al guardar el paquete')
    }

    successMsg.value = '¡Paquete guardado y publicado exitosamente!'
    resetForm()
    await loadData() // Recargar para mostrar en la lista derecha con relaciones
  } catch (err) {
    errorMsg.value = err.message || 'Error al guardar el paquete. Intenta de nuevo.'
  } finally {
    isLoading.value = false
    setTimeout(() => { successMsg.value = '' }, 4000)
  }
}

const confirmDelete = (pkg) => {
  selectedPackage.value = pkg
  deleteDialog.value = true
}

const deletePackage = async () => {
  if (!selectedPackage.value) return

  const token = localStorage.getItem('auth_token')
  try {
    const response = await fetch(`http://localhost:8000/api/paquetes/${selectedPackage.value.id}`, {
      method: 'DELETE',
      headers: {
        'Authorization': `Bearer ${token}`,
        'Accept': 'application/json'
      }
    })

    if (!response.ok) {
      throw new Error('Error al eliminar el paquete')
    }

    publishedPackages.value = publishedPackages.value.filter(p => p.id !== selectedPackage.value.id)
    deleteDialog.value = false
    selectedPackage.value = null
    successMsg.value = 'Paquete eliminado con éxito.'
  } catch (err) {
    errorMsg.value = err.message || 'Error al eliminar el paquete.'
  } finally {
    setTimeout(() => { successMsg.value = '' }, 3000)
  }
}

const resetForm = () => {
  form.value.reset()
  packageForm.value.vencimiento = ''
  packageForm.value.servicios = []
  errorMsg.value = ''
  successMsg.value = ''
}
</script>

<style scoped>
.border {
  border: 1px solid rgba(0, 0, 0, 0.08) !important;
}
.gap-1 {
  gap: 4px;
}
.gap-2 {
  gap: 8px;
}
.animate-fade {
  animation: fadeIn 0.3s ease-in-out;
}
@keyframes fadeIn {
  from { opacity: 0; transform: translateY(-5px); }
  to { opacity: 1; transform: translateY(0); }
}
</style>
