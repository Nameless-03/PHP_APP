<template>
  <DashboardLayout title="Gestión de Paquetes">
    <!-- Header visual -->
    <v-row class="mb-6">
      <v-col cols="12">
        <BannerHeader
          title="Gestión de Paquetes de Sesiones"
          subtitle="Crea y administra paquetes promocionales de sesiones agrupadas para tus servicios. Ofrece mejores tarifas y fideliza a tus clientes."
          icon="mdi-package-variant"
        />
      </v-col>
    </v-row>

    <v-row>
      <!-- Left Panel: Create Package (Expansible) & Accept Pending Payments -->
      <v-col cols="12" lg="7">
        
        <!-- Option 1: Create Package (Expansion Panel, collapsed by default) -->
        <v-expansion-panels class="mb-6 rounded-xl overflow-hidden border">
          <v-expansion-panel bg-color="white" class="rounded-xl">
            <v-expansion-panel-title class="py-4">
              <div class="d-flex align-center">
                <v-icon color="primary" size="28" class="mr-3">mdi-package-variant-closed-plus</v-icon>
                <div class="text-left">
                  <h3 class="text-subtitle-1 font-weight-bold mb-0 text-grey-darken-3">Crear Nuevo Paquete</h3>
                  <span class="text-caption text-medium-emphasis">Haz clic para expandir y configurar un paquete promocional</span>
                </div>
              </div>
            </v-expansion-panel-title>
            
            <v-expansion-panel-text class="pa-4 bg-grey-lighten-5">
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
                    <v-select
                      v-model="selectedServices"
                      :items="servicesList"
                      item-title="name"
                      item-value="id"
                      label="Seleccionar Servicios"
                      placeholder="Selecciona uno o más servicios..."
                      multiple
                      chips
                      variant="outlined"
                      prepend-inner-icon="mdi-layers-outline"
                      color="primary"
                      :rules="[rules.requiredArray]"
                      @update:modelValue="syncSelectedServices"
                    ></v-select>
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

                  <!-- Configuration list for selected services sessions -->
                  <v-col cols="12" v-if="packageForm.servicios.length > 0">
                    <h4 class="text-subtitle-2 font-weight-bold text-grey-darken-3 mb-2">
                      Configurar sesiones para cada servicio:
                    </h4>
                    <v-card variant="outlined" class="pa-4 rounded-xl mb-4 bg-white border">
                      <v-row v-for="(s, index) in packageForm.servicios" :key="s.id" class="align-center py-2">
                        <v-col cols="12" sm="7" class="py-1">
                          <div class="font-weight-bold text-grey-darken-4 text-body-2">{{ s.nombre }}</div>
                          <div class="text-caption text-medium-emphasis">Precio base: ${{ s.precio }} USD</div>
                        </v-col>
                        <v-col cols="12" sm="5" class="py-1">
                          <v-text-field
                            v-model.number="s.cantidad_sesiones"
                            label="Cantidad de Sesiones"
                            type="number"
                            min="1"
                            variant="outlined"
                            density="compact"
                            hide-details
                            color="primary"
                            prepend-inner-icon="mdi-numeric"
                            @update:modelValue="validateServiceSessions(s)"
                          ></v-text-field>
                        </v-col>
                        <v-col cols="12" v-if="index < packageForm.servicios.length - 1" class="py-0">
                          <v-divider></v-divider>
                        </v-col>
                      </v-row>
                    </v-card>
                  </v-col>

                  <v-col cols="12">
                    <v-text-field
                      v-model="packageForm.descuento"
                      :rules="[rules.required, rules.isNumber]"
                      label="Descuento en el precio final (USD)"
                      placeholder="0.00"
                      prefix="$"
                      variant="outlined"
                      prepend-inner-icon="mdi-tag-outline"
                      color="primary"
                    ></v-text-field>
                  </v-col>

                  <!-- Real-time price summary -->
                  <v-col cols="12" v-if="packageForm.servicios.length > 0">
                    <v-card color="amber-lighten-5" variant="flat" class="pa-4 rounded-xl border border-amber-lighten-3 d-flex flex-column gap-2">
                      <div class="d-flex justify-space-between align-center">
                        <span class="text-body-2 text-grey-darken-3">Subtotal original:</span>
                        <strong class="text-body-2 text-grey-darken-3">${{ totalOriginalPrice.toFixed(2) }} USD</strong>
                      </div>
                      <div class="d-flex justify-space-between align-center text-error">
                        <span class="text-body-2">Descuento aplicado:</span>
                        <strong class="text-body-2">- ${{ (parseFloat(packageForm.descuento) || 0).toFixed(2) }} USD</strong>
                      </div>
                      <v-divider class="my-1"></v-divider>
                      <div class="d-flex justify-space-between align-center">
                        <span class="text-subtitle-1 font-weight-bold text-grey-darken-4">Precio Final del Paquete:</span>
                        <strong class="text-h5 text-success font-weight-black">${{ finalCalculatedPrice.toFixed(2) }} USD</strong>
                      </div>
                      <div class="d-flex justify-space-between align-center text-caption text-medium-emphasis">
                        <span>Sesiones totales del paquete:</span>
                        <strong class="font-weight-bold text-primary">{{ totalSessionsCount }} sesiones</strong>
                      </div>
                    </v-card>
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
                    class="px-8 text-none font-weight-bold elevation-2 text-white"
                  >
                    Guardar Paquete
                    <v-icon end>mdi-content-save</v-icon>
                  </v-btn>
                </div>
              </v-form>
            </v-expansion-panel-text>
          </v-expansion-panel>
        </v-expansion-panels>

        <!-- Option 2: Pending Packages Payments -->
        <v-card class="pa-6 rounded-xl elevation-2 border">
          <div class="d-flex align-center mb-4">
            <v-icon size="32" color="warning" class="mr-3">mdi-credit-card-clock-outline</v-icon>
            <div>
              <h3 class="text-h6 font-weight-bold text-grey-darken-3 mb-1">Aceptar Pagos de Paquetes Pendientes</h3>
              <p class="text-caption text-medium-emphasis mb-0">
                Confirma el pago de clientes para habilitar las sesiones de sus paquetes.
              </p>
            </div>
          </div>

          <v-divider class="mb-4"></v-divider>

          <div v-if="loadingPendientes" class="text-center py-6">
            <v-progress-circular indeterminate color="primary"></v-progress-circular>
          </div>

          <template v-else>
            <v-list bg-color="transparent" class="pa-0" v-if="comprasPendientesList.length > 0">
              <v-card
                v-for="compra in comprasPendientesList"
                :key="compra.id"
                class="mb-3 rounded-lg border bg-amber-lighten-5 card-border"
                elevation="0"
              >
                <v-card-text class="pa-4 d-flex align-center justify-space-between flex-wrap gap-2">
                  <div>
                    <strong class="text-subtitle-2 text-grey-darken-4 d-block">{{ compra.paquete?.nombre }}</strong>
                    <div class="text-caption text-grey-darken-2">
                      Cliente: <strong>{{ compra.cliente?.nombre || 'Cliente' }}</strong>
                    </div>
                    <div class="text-caption text-medium-emphasis">
                      Adquirido el: {{ formatDate(compra.fecha_compra) }}
                    </div>
                    <v-chip size="x-small" color="warning" class="mt-1 font-weight-black">
                      Pago: ${{ compra.pagos?.[0]?.monto || compra.paquete?.precio }} USD ({{ compra.pagos?.[0]?.metodo || 'efectivo' }})
                    </v-chip>
                  </div>
                  <div class="d-flex gap-2">
                    <v-btn
                      color="success"
                      size="small"
                      class="text-none font-weight-bold text-white rounded-lg px-4"
                      prepend-icon="mdi-cash-check"
                      @click="aprobarPagoPaquete(compra)"
                      :loading="isSubmittingPago"
                    >
                      Aprobar Pago
                    </v-btn>
                    <v-btn
                      color="error"
                      size="small"
                      variant="outlined"
                      class="text-none font-weight-bold rounded-lg"
                      prepend-icon="mdi-close-circle-outline"
                      @click="rechazarPagoPaquete(compra)"
                      :loading="isSubmittingPago"
                    >
                      Rechazar
                    </v-btn>
                  </div>
                </v-card-text>
              </v-card>
            </v-list>
            <div v-else class="text-center py-8 opacity-60 bg-grey-lighten-4 rounded-xl border">
              <v-icon size="40" color="grey">mdi-cash-register</v-icon>
              <p class="mt-2 text-caption font-weight-medium mb-0">No tienes cobros de paquetes pendientes.</p>
            </div>
          </template>
        </v-card>
      </v-col>

      <!-- Right Panel: List of current packages -->
      <v-col cols="12" lg="5">
        <v-card class="pa-6 rounded-xl elevation-1 h-100 bg-grey-lighten-4 border">
          <h3 class="text-h6 font-weight-bold mb-4 d-flex align-center">
            <v-icon start color="primary" class="mr-2">mdi-package-variant</v-icon>
            Paquetes Publicados
          </h3>

          <v-list bg-color="transparent" class="pa-0">
            <template v-for="item in publishedPackages" :key="item.id">
              <v-card class="mb-4 rounded-xl border position-relative" elevation="0" color="white" :style="!item.activo ? 'opacity: 0.6;' : ''">
                <v-card-text class="pa-5">
                  <div class="d-flex justify-space-between align-start mb-2">
                    <div>
                      <div class="d-flex align-center gap-2 flex-wrap mb-1">
                        <h4 class="text-subtitle-1 font-weight-bold text-primary mb-0">{{ item.nombre }}</h4>
                        <v-chip size="x-small" :color="item.activo ? 'success' : 'error'" variant="tonal" class="font-weight-bold">
                          {{ item.activo ? 'Activo' : 'Deshabilitado' }}
                        </v-chip>
                      </div>
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
                    <div class="text-right">
                      <div class="d-flex align-center justify-end">
                        <span class="text-h6 font-weight-black text-success mr-1">
                          ${{ item.precio }}
                        </span>
                        <span class="text-caption text-medium-emphasis">USD</span>
                      </div>
                      <div class="text-caption text-error font-weight-medium" v-if="item.descuento > 0">
                        Descto: -${{ item.descuento }}
                      </div>
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
                        {{ s.nombre }} ({{ s.cantidad_sesiones }} {{ s.cantidad_sesiones === 1 ? 'sesión' : 'sesiones' }})
                      </v-chip>
                    </div>
                    <div class="text-caption text-error font-weight-bold mt-2" v-if="!item.activo">
                      <v-icon start color="error" size="small">mdi-alert-circle</v-icon>
                      Este paquete está deshabilitado porque uno de sus servicios está inactivo o fue eliminado.
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
              <p class="mt-3 text-body-1 font-weight-medium">Aún no tienes paquetes creados.</p>
              <p class="text-caption text-medium-emphasis">Expande el formulario superior para crear y publicar tu primer paquete.</p>
            </div>
          </v-list>
        </v-card>
      </v-col>
    </v-row>

    <!-- Delete Confirmation Dialog -->
    <ConfirmationDialog
      v-model="deleteDialog"
      title="¿Eliminar Paquete?"
      confirm-text="Eliminar"
      confirm-color="error"
      icon="mdi-alert-circle-outline"
      @confirm="deletePackage"
    >
      ¿Estás seguro de que deseas eliminar el paquete <strong>"{{ selectedPackage?.nombre }}"</strong>?
      Esta acción no se puede deshacer y los clientes ya no podrán adquirirlo.
    </ConfirmationDialog>
  </DashboardLayout>
</template>

<script setup>
import { ref, onMounted, onUnmounted, computed } from 'vue'
import DashboardLayout from '../components/DashboardLayout.vue'
import BannerHeader from '../components/BannerHeader.vue'
import ConfirmationDialog from '../components/ConfirmationDialog.vue'
import { useAuth } from '../composables/useAuth'
import { useFormRules } from '../composables/useFormRules'

const form = ref(null)
const isLoading = ref(false)
const successMsg = ref('')
const errorMsg = ref('')

const packageForm = ref({
  nombre: '',
  descripcion: '',
  descuento: '0',
  vencimiento: '',
  servicios: []
})

const selectedServices = ref([])
const servicesList = ref([])
const publishedPackages = ref([])
const comprasPendientesList = ref([])
const loadingPendientes = ref(false)
const isSubmittingPago = ref(false)

// Delete modal state
const deleteDialog = ref(false)
const selectedPackage = ref(null)

const { required, requiredArray, isNumber, isInteger, minOne } = useFormRules()

const rules = {
  required,
  requiredArray,
  isNumber,
  isInteger,
  minOne
}

const { token, user, getAuthHeaders } = useAuth()

// Real-time calculations
const syncSelectedServices = () => {
  const currentMap = new Map(packageForm.value.servicios.map(s => [s.id, s]))
  
  packageForm.value.servicios = selectedServices.value.map(id => {
    if (currentMap.has(id)) {
      return currentMap.get(id)
    }
    const found = servicesList.value.find(s => s.id === id)
    return {
      id: id,
      nombre: found ? found.nombre : '',
      precio: found ? parseFloat(found.precio) : 0,
      cantidad_sesiones: 1
    }
  })
}

const validateServiceSessions = (s) => {
  if (!s.cantidad_sesiones || s.cantidad_sesiones < 1) {
    s.cantidad_sesiones = 1
  }
}

const totalOriginalPrice = computed(() => {
  return packageForm.value.servicios.reduce((sum, s) => {
    return sum + (s.precio * (parseInt(s.cantidad_sesiones) || 0))
  }, 0)
})

const finalCalculatedPrice = computed(() => {
  const discount = parseFloat(packageForm.value.descuento) || 0
  return Math.max(0, totalOriginalPrice.value - discount)
})

const totalSessionsCount = computed(() => {
  return packageForm.value.servicios.reduce((sum, s) => {
    return sum + (parseInt(s.cantidad_sesiones) || 0)
  }, 0)
})

const loadData = async () => {
  if (!token.value || !user.value) return

  try {
    // 1. Cargar servicios del profesional
    const servicesResponse = await fetch(`/api/servicios?id_profesional=${user.value.id}&incluir_inactivos=1`, {
      headers: getAuthHeaders()
    })
    if (servicesResponse.ok) {
      const data = await servicesResponse.json()
      servicesList.value = (data.data || []).map(s => ({
        id: s.id,
        name: s.nombre,
        precio: s.precio
      }))
    }

    // 2. Cargar paquetes del profesional
    const packagesResponse = await fetch(`/api/paquetes?id_profesional=${user.value.id}`, {
      headers: getAuthHeaders()
    })
    if (packagesResponse.ok) {
      const data = await packagesResponse.json()
      publishedPackages.value = data.data || []
    }
  } catch (error) {
    console.error('Error al cargar datos:', error)
  }
}

const loadComprasPendientes = async () => {
  if (!token.value || !user.value) return
  loadingPendientes.value = true
  try {
    const res = await fetch('/api/paquetes-pendientes', { headers: getAuthHeaders() })
    if (res.ok) {
      const data = await res.json()
      comprasPendientesList.value = data.data || []
    }
  } catch (error) {
    console.error('Error al cargar compras pendientes de paquetes:', error)
  } finally {
    loadingPendientes.value = false
  }
}

const handleRealtimeUpdate = (event) => {
  console.log('Real-time event received in GestionPaquetesView:', event.detail)
  loadComprasPendientes()
}

onMounted(async () => {
  await loadData()
  await loadComprasPendientes()
  window.addEventListener('reserva-actualizada', handleRealtimeUpdate)
})

onUnmounted(() => {
  window.removeEventListener('reserva-actualizada', handleRealtimeUpdate)
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

  const payload = {
    nombre: packageForm.value.nombre,
    descripcion: packageForm.value.descripcion,
    descuento: parseFloat(packageForm.value.descuento || 0),
    servicios: packageForm.value.servicios.map(s => ({
      id: s.id,
      cantidad_sesiones: parseInt(s.cantidad_sesiones || 1)
    }))
  }

  if (packageForm.value.vencimiento) {
    payload.vencimiento = parseInt(packageForm.value.vencimiento)
  }

  try {
    const response = await fetch('/api/paquetes', {
      method: 'POST',
      headers: getAuthHeaders(),
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

const aprobarPagoPaquete = async (compra) => {
  isSubmittingPago.value = true
  try {
    const res = await fetch(`/api/paquetes-pendientes/${compra.id}/aprobar`, {
      method: 'POST',
      headers: getAuthHeaders()
    })
    const data = await res.json()
    if (!res.ok) throw new Error(data.message || 'Error al aprobar el pago')

    successMsg.value = 'Pago aprobado y sesiones habilitadas para el cliente.'
    await loadComprasPendientes()
    window.dispatchEvent(new CustomEvent('update-pending-counts'))
  } catch (err) {
    errorMsg.value = err.message || 'Error al aprobar el pago.'
  } finally {
    isSubmittingPago.value = false
    setTimeout(() => { successMsg.value = ''; errorMsg.value = '' }, 4000)
  }
}

const rechazarPagoPaquete = async (compra) => {
  if (!confirm(`¿Estás seguro de que deseas rechazar la compra del paquete "${compra.paquete?.nombre}" de ${compra.cliente?.nombre}?`)) return
  isSubmittingPago.value = true
  try {
    const res = await fetch(`/api/mis-paquetes/${compra.id}`, {
      method: 'DELETE',
      headers: getAuthHeaders()
    })
    const data = await res.json()
    if (!res.ok) throw new Error(data.message || 'Error al rechazar el pago')

    successMsg.value = 'Compra de paquete rechazada y eliminada.'
    await loadComprasPendientes()
    window.dispatchEvent(new CustomEvent('update-pending-counts'))
  } catch (err) {
    errorMsg.value = err.message || 'Error al rechazar la compra.'
  } finally {
    isSubmittingPago.value = false
    setTimeout(() => { successMsg.value = ''; errorMsg.value = '' }, 4000)
  }
}

const confirmDelete = (pkg) => {
  selectedPackage.value = pkg
  deleteDialog.value = true
}

const deletePackage = async () => {
  if (!selectedPackage.value) return

  try {
    const response = await fetch(`/api/paquetes/${selectedPackage.value.id}`, {
      method: 'DELETE',
      headers: getAuthHeaders()
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
  packageForm.value.descuento = '0'
  packageForm.value.servicios = []
  selectedServices.value = []
  errorMsg.value = ''
  successMsg.value = ''
}

const formatDate = (dateStr) => {
  if (!dateStr) return 'N/D'
  return new Date(dateStr).toLocaleDateString('es-ES', {
    day: '2-digit',
    month: '2-digit',
    year: 'numeric'
  })
}
</script>

<style scoped>
.bg-gradient {
  background: linear-gradient(135deg, #8C6D46 0%, #A6987A 100%);
}
.border {
  border: 1px solid rgba(0, 0, 0, 0.08) !important;
}
.card-border {
  border: 1px solid rgba(255, 152, 0, 0.25) !important;
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
