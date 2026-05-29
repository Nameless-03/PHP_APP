<template>
  <DashboardLayout title="Mis Paquetes Adquiridos">
    <!-- Header visual -->
    <v-row class="mb-6">
      <v-col cols="12">
        <v-card class="pa-8 rounded-xl elevation-2 bg-gradient text-white">
          <div class="d-flex align-center flex-wrap">
            <v-avatar color="white" size="64" class="mr-6 elevation-2 text-primary font-weight-black">
              <v-icon size="36" color="primary">mdi-briefcase-account</v-icon>
            </v-avatar>
            <div>
              <h1 class="text-h4 font-weight-bold mb-2">Mi Inventario de Paquetes</h1>
              <p class="text-body-1 opacity-80 mb-0">
                Consulta tus paquetes activos, revisa tus sesiones disponibles y haz seguimiento a tus transacciones.
              </p>
            </div>
          </div>
        </v-card>
      </v-col>
    </v-row>

    <!-- Skeleton Loader while fetching -->
    <v-row v-if="isLoading">
      <v-col cols="12" sm="6" md="4" v-for="i in 6" :key="i">
        <v-skeleton-loader type="card, article"></v-skeleton-loader>
      </v-col>
    </v-row>

    <!-- Empty State -->
    <v-row v-else-if="purchases.length === 0" justify="center">
      <v-col cols="12" md="8" class="text-center">
        <v-card class="pa-10 text-center rounded-xl elevation-1 bg-white">
          <v-avatar size="100" color="primary-lighten-5" class="mb-4">
            <v-icon size="64" color="primary">mdi-package-variant-closed-remove</v-icon>
          </v-avatar>
          <h3 class="text-h5 font-weight-bold text-grey-darken-2 mb-2">No tienes paquetes adquiridos</h3>
          <p class="text-body-1 text-medium-emphasis mb-6">
            Aún no has comprado ningún paquete de sesiones. Explora las ofertas de nuestros profesionales y adquiere tu primer paquete con descuento.
          </p>
          <v-btn color="primary" class="text-none font-weight-bold rounded-lg px-6 elevation-1" to="/comprar-paquetes">
            Ver Paquetes Disponibles
            <v-icon end>mdi-arrow-right</v-icon>
          </v-btn>
        </v-card>
      </v-col>
    </v-row>

    <!-- Purchases List -->
    <v-row v-else>
      <v-col cols="12" sm="6" md="4" v-for="compra in purchases" :key="compra.id">
        <v-card class="rounded-xl border h-100 d-flex flex-column elevation-1 card-border position-relative">
          <div class="pa-6 flex-grow-1">
            <!-- Header: Package Name & Status -->
            <div class="d-flex justify-space-between align-start mb-4">
              <h3 class="text-subtitle-1 font-weight-black text-grey-darken-4 line-clamp-1 pr-2" style="max-width: 70%;">
                {{ compra.paquete?.nombre || 'Paquete Adquirido' }}
              </h3>
              <v-chip size="x-small" :color="getStatusColor(compra.estado)" variant="flat" class="font-weight-black text-uppercase">
                {{ compra.estado }}
              </v-chip>
            </div>

            <!-- Sessions remaining visual tracker -->
            <div class="bg-grey-lighten-4 pa-4 rounded-xl border mb-4 text-center">
              <div class="text-caption text-medium-emphasis font-weight-bold mb-1">SESIONES DISPONIBLES</div>
              <div class="d-flex justify-center align-baseline mb-2">
                <span class="text-h3 font-weight-black text-primary">{{ compra.sesiones_disponibles }}</span>
                <span class="text-h6 text-medium-emphasis font-weight-bold mx-1">/</span>
                <span class="text-h5 text-medium-emphasis font-weight-medium">{{ compra.paquete?.cantidad_sesiones || 0 }}</span>
              </div>
              <v-progress-linear
                :model-value="(compra.sesiones_disponibles / (compra.paquete?.cantidad_sesiones || 1)) * 100"
                color="primary"
                height="6"
                rounded
                class="mt-1"
              ></v-progress-linear>
            </div>

            <!-- Date and payment details -->
            <div class="text-caption text-medium-emphasis mb-3">
              <v-icon size="small" class="mr-1">mdi-calendar-check</v-icon>
              Adquirido el: <strong>{{ formatDate(compra.fecha_compra) }}</strong>
            </div>

            <v-divider class="my-4"></v-divider>

            <!-- Services Included -->
            <div class="mb-4">
              <span class="text-caption font-weight-bold text-grey-darken-1 mb-2 d-block">
                <v-icon size="small" class="mr-1">mdi-briefcase-check-outline</v-icon>
                Servicios Válidos:
              </span>
              <div class="d-flex flex-wrap gap-1">
                <v-chip
                  v-for="s in compra.paquete?.servicios || []"
                  :key="s.id"
                  size="x-small"
                  color="secondary"
                  variant="tonal"
                  class="font-weight-medium"
                >
                  {{ s.nombre }}
                </v-chip>
              </div>
            </div>

            <!-- Payment details details -->
            <div v-if="compra.pagos && compra.pagos.length > 0" class="bg-grey-lighten-5 pa-3 rounded-lg border text-caption">
              <div class="d-flex justify-space-between align-center mb-1">
                <span class="text-medium-emphasis">Pago Ref:</span>
                <span class="font-weight-bold text-truncate" style="max-width: 120px;">{{ compra.pagos[0].referencia_externa || 'N/D' }}</span>
              </div>
              <div class="d-flex justify-space-between align-center">
                <span class="text-medium-emphasis">Monto y Canal:</span>
                <span class="font-weight-bold text-success">${{ compra.pagos[0].monto }} USD ({{ compra.pagos[0].metodo }})</span>
              </div>
            </div>

          </div>
          
          <!-- Quick actions -->
          <div class="pa-4 bg-grey-lighten-4 mt-auto border-t">
            <template v-if="compra.estado === 'pendiente'">
              <div class="d-flex gap-2 w-100">
                <v-btn
                  color="success"
                  variant="elevated"
                  class="text-none font-weight-bold rounded-lg text-white"
                  prepend-icon="mdi-credit-card"
                  style="flex: 1;"
                  @click="abrirPagarPaquete(compra)"
                >
                  Pagar
                </v-btn>
                <v-btn
                  color="error"
                  variant="outlined"
                  class="text-none font-weight-bold rounded-lg"
                  prepend-icon="mdi-trash-can-outline"
                  @click="cancelarCompra(compra.id)"
                  :loading="isSubmitting"
                >
                  Cancelar
                </v-btn>
              </div>
            </template>
            <template v-else>
              <v-btn
                block
                color="secondary"
                variant="tonal"
                class="text-none font-weight-bold rounded-lg"
                prepend-icon="mdi-calendar-plus"
                to="/mis-reservas"
                :disabled="compra.estado !== 'activo' || compra.sesiones_disponibles <= 0"
              >
                Reservar con Paquete
              </v-btn>
            </template>
          </div>
        </v-card>
      </v-col>
    </v-row>

    <!-- MODAL PAGAR PAQUETE PENDIENTE -->
    <v-dialog v-model="purchaseDialog" max-width="500" persistent>
      <v-card class="rounded-xl overflow-hidden pa-0">
        <div class="dialog-header pa-6 text-white text-center">
          <v-icon size="48" class="mb-2">mdi-shield-check-outline</v-icon>
          <h3 class="text-h5 font-weight-bold">Pagar Paquete</h3>
          <p class="text-subtitle-2 opacity-80 mb-0">Completa tu pago de forma segura</p>
        </div>

        <v-form ref="formPagoRef" @submit.prevent="processPurchase">
          <v-card-text class="pa-6" style="max-height: 65vh; overflow-y: auto;">
            <v-alert v-if="dialogError" type="error" variant="tonal" class="mb-4 rounded-lg animate-fade">
              <div class="font-weight-bold mb-1">Error al procesar el pago</div>
              <div class="text-body-2 mb-2">{{ dialogError }}</div>
              <div class="d-flex gap-2">
                <v-btn size="small" color="error" variant="elevated" @click="dialogError = ''" class="text-none">Reintentar</v-btn>
                <v-btn size="small" color="error" variant="outlined" @click="cancelarCompraDesdePago" :loading="isSubmitting" class="text-none">Cancelar Compra</v-btn>
              </div>
            </v-alert>

            <!-- Summary info -->
            <div class="bg-grey-lighten-4 pa-4 rounded-xl border mb-6" v-if="selectedPurchase">
              <div class="d-flex justify-space-between align-center mb-2">
                <span class="text-body-2 text-medium-emphasis">Paquete:</span>
                <strong class="text-body-1 text-grey-darken-3">{{ selectedPurchase.paquete?.nombre }}</strong>
              </div>
              <div class="d-flex justify-space-between align-center mb-2">
                <span class="text-body-2 text-medium-emphasis">Sesiones Incluidas:</span>
                <strong class="text-body-1 text-primary">{{ selectedPurchase.paquete?.cantidad_sesiones }} sesiones</strong>
              </div>
              <v-divider class="my-2"></v-divider>
              <div class="d-flex justify-space-between align-center">
                <span class="text-subtitle-1 font-weight-bold">Total a pagar:</span>
                <strong class="text-h5 text-success font-weight-black">${{ selectedPurchase.paquete?.precio }} USD</strong>
              </div>
            </div>

            <!-- Select Payment Method -->
            <div v-if="!dialogError">
              <h4 class="text-subtitle-2 font-weight-bold text-grey-darken-3 mb-3">1. Método de Pago</h4>
              <v-radio-group v-model="paymentMethod" inline class="mb-4">
                <v-row>
                  <v-col cols="12" sm="4" class="py-1">
                    <v-radio label="PayPal" value="paypal" color="primary" class="font-weight-medium"></v-radio>
                  </v-col>
                  <v-col cols="12" sm="4" class="py-1">
                    <v-radio label="Transferencia" value="transferencia" color="primary" class="font-weight-medium"></v-radio>
                  </v-col>
                  <v-col cols="12" sm="4" class="py-1">
                    <v-radio label="Efectivo" value="efectivo" color="primary" class="font-weight-medium"></v-radio>
                  </v-col>
                </v-row>
              </v-radio-group>

              <!-- DETALLES DE PAGO SEGÚN MÉTODO SELECCIONADO -->
              <h4 class="text-subtitle-2 font-weight-bold text-grey-darken-3 mb-2">2. Completa los Datos de Pago</h4>
              <v-expand-transition>
                <div v-if="paymentMethod === 'paypal'" class="pa-4 mb-4 rounded-xl border bg-blue-lighten-5">
                  <div class="d-flex align-center mb-3">
                    <v-icon color="blue-darken-3" class="mr-2">mdi-paypal</v-icon>
                    <span class="text-subtitle-2 font-weight-bold text-blue-darken-3">Pasarela de Pago PayPal</span>
                  </div>

                  <!-- Botones Oficiales del SDK de PayPal -->
                  <div v-if="cargandoPaypalSdk" class="text-center py-4">
                    <v-progress-circular indeterminate color="blue"></v-progress-circular>
                    <div class="text-caption text-blue mt-2">Iniciando pasarela de PayPal...</div>
                  </div>
                  <div v-else-if="paypalClientId" id="paypal-button-container" class="mt-2"></div>

                  <div v-else>
                    <div class="text-caption text-grey-darken-3 mb-3 bg-white pa-3 rounded border">
                      Nota: No se detectó configuración de PayPal Sandbox en el servidor. Mostrando simulador directo:
                    </div>
                    <v-text-field
                      v-model="datosPago.paypal_email"
                      label="Correo Electrónico de PayPal"
                      type="email"
                      variant="outlined"
                      density="comfortable"
                      color="blue"
                      class="mb-2"
                      :rules="[v => !!v || 'El correo es obligatorio', v => /.+@.+\..+/.test(v) || 'Correo no válido']"
                      required
                    ></v-text-field>
                    <v-text-field
                      v-model="datosPago.paypal_password"
                      label="Contraseña de PayPal"
                      type="password"
                      variant="outlined"
                      density="comfortable"
                      color="blue"
                      hide-details
                      :rules="[v => !!v || 'La contraseña es obligatoria']"
                      required
                    ></v-text-field>
                  </div>
                </div>

                <div v-if="paymentMethod === 'transferencia'" class="pa-4 mb-4 rounded-xl border bg-orange-lighten-5">
                  <div class="d-flex align-center mb-3">
                    <v-icon color="orange-darken-3" class="mr-2">mdi-bank</v-icon>
                    <span class="text-subtitle-2 font-weight-bold text-orange-darken-3">Datos de Transferencia</span>
                  </div>
                  <div class="text-caption text-grey-darken-3 mb-3 bg-white pa-3 rounded border">
                    <strong>CBU de Destino:</strong> 0000003100012345678901<br>
                    <strong>Alias:</strong> centro.estetica.alias<br>
                    <strong>Titular:</strong> Centro de Estética S.A.
                  </div>
                  <v-text-field
                    v-model="datosPago.transferencia_titular"
                    label="Nombre del Titular de la cuenta"
                    variant="outlined"
                    density="comfortable"
                    color="orange"
                    class="mb-2"
                    :rules="[v => !!v || 'El nombre es obligatorio']"
                    required
                  ></v-text-field>
                  <v-text-field
                    v-model="datosPago.transferencia_cbu"
                    label="CBU o CVU de Origen"
                    variant="outlined"
                    density="comfortable"
                    color="orange"
                    hide-details
                    :rules="[v => !!v || 'El CBU/CVU es obligatorio', v => /^\d{22}$/.test(v) || 'Debe tener exactamente 22 números']"
                    required
                  ></v-text-field>
                </div>

                <div v-if="paymentMethod === 'efectivo'" class="pa-4 mb-4 rounded-xl border bg-green-lighten-5">
                  <div class="d-flex align-center">
                    <v-icon color="green-darken-3" class="mr-2">mdi-cash-multiple</v-icon>
                    <span class="text-subtitle-2 font-weight-bold text-green-darken-3">Pago en Efectivo</span>
                  </div>
                  <div class="text-caption text-grey-darken-3 mt-2">
                    No se requiere ingresar datos bancarios o virtuales. Realizarás el pago en persona directamente al profesional al momento de tus sesiones.
                  </div>
                </div>
              </v-expand-transition>

              <!-- Error simulation switch -->
              <v-divider class="my-4"></v-divider>
              <div class="d-flex align-center justify-space-between bg-red-lighten-5 pa-3 rounded-lg border-red">
                <div>
                  <div class="text-caption font-weight-bold text-red-darken-3">Simulador de Pruebas</div>
                  <div class="text-caption text-red-darken-2">Activa para probar flujo de pago fallido</div>
                </div>
                <v-switch
                  v-model="simulateError"
                  color="error"
                  hide-details
                  density="compact"
                ></v-switch>
              </div>
            </div>
          </v-card-text>

          <v-card-actions class="pa-6 pt-0 d-flex justify-end">
            <v-btn
              variant="outlined"
              color="grey-darken-1"
              class="mr-3 px-6 text-none font-weight-bold"
              :disabled="isSubmitting"
              @click="purchaseDialog = false"
            >
              Cerrar
            </v-btn>
            <v-btn
              v-if="!dialogError && (paymentMethod !== 'paypal' || !paypalClientId)"
              type="submit"
              color="primary"
              class="px-8 text-none font-weight-bold elevation-2 text-white"
              :loading="isSubmitting"
            >
              Confirmar Pago
              <v-icon end>mdi-check-circle-outline</v-icon>
            </v-btn>
          </v-card-actions>
        </v-form>
      </v-card>
    </v-dialog>

    <!-- Global Snackbar -->
    <v-snackbar v-model="snackbar.show" :color="snackbar.color" :timeout="4000" location="top">
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

const isLoading = ref(true)
const purchases = ref([])

// Dialog and flow states
const purchaseDialog = ref(false)
const selectedPurchase = ref(null)
const paymentMethod = ref('paypal')
const simulateError = ref(false)
const isSubmitting = ref(false)
const dialogError = ref('')
const snackbar = ref({ show: false, text: '', color: 'success' })
const formPagoRef = ref(null)
const datosPago = ref({
  paypal_email: '',
  paypal_password: '',
  transferencia_titular: '',
  transferencia_cbu: ''
})
const paypalLoaded = ref(false)
const cargandoPaypalSdk = ref(false)
const paypalClientId = ref('')

const loadPurchases = async () => {
  isLoading.value = true
  const token = localStorage.getItem('auth_token')
  if (!token) return

  try {
    const response = await fetch('http://localhost:8000/api/mis-paquetes', {
      headers: {
        'Authorization': `Bearer ${token}`,
        'Accept': 'application/json'
      }
    })
    
    if (response.ok) {
      const data = await response.json()
      purchases.value = data.data || []
    }
  } catch (error) {
    console.error('Error al cargar inventario de compras:', error)
  } finally {
    isLoading.value = false
  }
}

onMounted(async () => {
  await loadPurchases()
})

const abrirPagarPaquete = (compra) => {
  selectedPurchase.value = compra
  paymentMethod.value = 'paypal'
  simulateError.value = false
  dialogError.value = ''
  datosPago.value = {
    paypal_email: '',
    paypal_password: '',
    transferencia_titular: '',
    transferencia_cbu: ''
  }
  if (formPagoRef.value) {
    formPagoRef.value.resetValidation()
  }
  purchaseDialog.value = true
}

const processPurchase = async () => {
  if (!selectedPurchase.value) return

  if (formPagoRef.value) {
    const { valid } = await formPagoRef.value.validate()
    if (!valid) return
  }

  isSubmitting.value = true
  dialogError.value = ''

  const token = localStorage.getItem('auth_token')
  const payload = {
    id_compra: selectedPurchase.value.id,
    monto: parseFloat(selectedPurchase.value.paquete?.precio || 0),
    metodo: paymentMethod.value,
    simular_error: simulateError.value,
    detalles_pago: { ...datosPago.value }
  }

  try {
    const response = await fetch('http://localhost:8000/api/pagos', {
      method: 'POST',
      headers: {
        'Authorization': `Bearer ${token}`,
        'Content-Type': 'application/json',
        'Accept': 'application/json'
      },
      body: JSON.stringify(payload)
    })

    const data = await response.json()
    if (!response.ok) throw new Error(data.message || 'Error al procesar el pago')

    // Wait a brief moment to simulate pasarela feedback
    await new Promise(resolve => setTimeout(resolve, 1500))

    const pagoResult = data.data
    if (pagoResult.estado === 'completado') {
      snackbar.value = { show: true, text: '¡Pago completado con éxito! El paquete ha sido habilitado.', color: 'success' }
      purchaseDialog.value = false
      selectedPurchase.value = null
      loadPurchases()
    } else {
      throw new Error('El pago fue rechazado por la pasarela de pagos.')
    }
  } catch (err) {
    dialogError.value = err.message || 'No se pudo procesar el pago. Intenta nuevamente.'
  } finally {
    isSubmitting.value = false
  }
}

const cancelarCompra = async (id) => {
  if (!confirm('¿Estás seguro de que deseas cancelar la adquisición de este paquete?')) return
  isSubmitting.value = true
  const token = localStorage.getItem('auth_token')
  try {
    const res = await fetch(`http://localhost:8000/api/mis-paquetes/${id}`, {
      method: 'DELETE',
      headers: {
        'Authorization': `Bearer ${token}`,
        'Accept': 'application/json'
      }
    })

    if (!res.ok) throw new Error((await res.json()).message || 'Error al cancelar la compra')

    snackbar.value = { show: true, text: 'Compra de paquete cancelada y eliminada.', color: 'error' }
    loadPurchases()
  } catch (err) {
    snackbar.value = { show: true, text: err.message, color: 'error' }
  } finally {
    isSubmitting.value = false
  }
}

const cancelarCompraDesdePago = async () => {
  if (!selectedPurchase.value) return
  isSubmitting.value = true
  const token = localStorage.getItem('auth_token')
  try {
    const res = await fetch(`http://localhost:8000/api/mis-paquetes/${selectedPurchase.value.id}`, {
      method: 'DELETE',
      headers: {
        'Authorization': `Bearer ${token}`,
        'Accept': 'application/json'
      }
    })

    if (!res.ok) throw new Error((await res.json()).message || 'Error al cancelar la compra')

    snackbar.value = { show: true, text: 'Compra de paquete cancelada y eliminada.', color: 'error' }
    purchaseDialog.value = false
    selectedPurchase.value = null
    loadPurchases()
  } catch (err) {
    dialogError.value = err.message
  } finally {
    isSubmitting.value = false
  }
}

import { watch } from 'vue'

const cargarPaypalSdk = async () => {
  if (paypalLoaded.value) return true
  cargandoPaypalSdk.value = true
  try {
    const token = localStorage.getItem('auth_token')
    const res = await fetch('http://localhost:8000/api/config/paypal', {
      headers: { 'Authorization': `Bearer ${token}` }
    })
    if (res.ok) {
      const data = await res.json()
      const cid = data.client_id || ''
      if (cid && cid !== 'your_paypal_client_id_here') {
        paypalClientId.value = cid
        if (!document.getElementById('paypal-sdk-script')) {
          return new Promise((resolve) => {
            const script = document.createElement('script')
            script.id = 'paypal-sdk-script'
            script.src = `https://www.paypal.com/sdk/js?client-id=${cid}&currency=USD`
            script.onload = () => {
              paypalLoaded.value = true
              cargandoPaypalSdk.value = false
              resolve(true)
            }
            script.onerror = () => {
              cargandoPaypalSdk.value = false
              resolve(false)
            }
            document.head.appendChild(script)
          })
        } else {
          paypalLoaded.value = true
          cargandoPaypalSdk.value = false
          return true
        }
      }
    }
  } catch (err) {
    console.error('Error al cargar el SDK de PayPal:', err)
  }
  cargandoPaypalSdk.value = false
  return false
}

const renderizarBotonesPaypal = () => {
  if (!window.paypal || !paypalClientId.value) return
  
  setTimeout(() => {
    const container = document.getElementById('paypal-button-container')
    if (!container) return
    container.innerHTML = ''

    window.paypal.Buttons({
      createOrder: (data, actions) => {
        const precio = parseFloat(selectedPurchase.value?.paquete?.precio || 0)
        return actions.order.create({
          purchase_units: [{
            amount: {
              value: precio.toFixed(2)
            }
          }]
        })
      },
      onApprove: async (data, actions) => {
        isSubmitting.value = true
        dialogError.value = ''
        const token = localStorage.getItem('auth_token')
        try {
          const details = await actions.order.capture()
          const payload = {
            id_compra: selectedPurchase.value.id,
            monto: parseFloat(selectedPurchase.value.paquete?.precio || 0),
            metodo: 'paypal',
            simular_error: false,
            detalles_pago: {
              paypal_order_id: details.id,
              paypal_email: details.payer.email_address,
              paypal_payer_id: details.payer.payer_id
            }
          }

          const response = await fetch('http://localhost:8000/api/pagos', {
            method: 'POST',
            headers: {
              'Authorization': `Bearer ${token}`,
              'Content-Type': 'application/json',
              'Accept': 'application/json'
            },
            body: JSON.stringify(payload)
          })

          const resData = await response.json()
          if (!response.ok) throw new Error(resData.message || 'Error al completar la compra en nuestro servidor')

          snackbar.value = { show: true, text: '¡Compra de paquete completada con éxito!', color: 'success' }
          purchaseDialog.value = false
          selectedPurchase.value = null
          loadPurchases()
        } catch (err) {
          dialogError.value = err.message || 'Error al procesar el pago de PayPal'
        } finally {
          isSubmitting.value = false
        }
      },
      onError: (err) => {
        dialogError.value = 'Ocurrió un error en la pasarela de PayPal o se canceló el cobro.'
      }
    }).render('#paypal-button-container')
  }, 150)
}

watch([paymentMethod, purchaseDialog], async ([nuevoMetodo, estaAbierto]) => {
  if (estaAbierto && nuevoMetodo === 'paypal') {
    const cargado = await cargarPaypalSdk()
    if (cargado) {
      renderizarBotonesPaypal()
    }
  }
})

const getStatusColor = (status) => {
  switch (status) {
    case 'activo': return 'success'
    case 'agotado': return 'grey'
    case 'vencido': return 'error'
    default: return 'warning'
  }
}

const formatDate = (dateStr) => {
  if (!dateStr) return 'N/D'
  return new Date(dateStr).toLocaleDateString('es-ES', {
    day: '2-digit',
    month: '2-digit',
    year: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  })
}
</script>

<style scoped>
.bg-gradient {
  background: linear-gradient(135deg, #8C6D46 0%, #A6987A 100%);
}
.card-border {
  border: 1px solid rgba(0, 0, 0, 0.08) !important;
}
.border-t {
  border-top: 1px solid rgba(0, 0, 0, 0.05) !important;
}
.gap-1 {
  gap: 4px;
}
.gap-2 {
  gap: 8px;
}
.line-clamp-1 {
  display: -webkit-box;
  -webkit-line-clamp: 1;
  -webkit-box-orient: vertical;
  overflow: hidden;
}
.dialog-header {
  background: linear-gradient(135deg, #8C6D46 0%, #A6987A 100%);
}
.border-red {
  border: 1px solid rgba(244, 67, 54, 0.2);
}
</style>
