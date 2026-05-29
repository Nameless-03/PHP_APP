<template>
  <DashboardLayout title="Adquirir Paquetes de Sesiones">
    <!-- Header visual -->
    <v-row class="mb-6">
      <v-col cols="12">
        <v-card class="pa-8 rounded-xl elevation-2 bg-gradient text-white">
          <div class="d-flex align-center flex-wrap">
            <v-avatar color="white" size="64" class="mr-6 elevation-2 text-primary font-weight-black">
              <v-icon size="36" color="primary">mdi-package-variant</v-icon>
            </v-avatar>
            <div>
              <h1 class="text-h4 font-weight-bold mb-2">Comprar Paquetes de Sesiones</h1>
              <p class="text-body-1 opacity-80 mb-0">
                Adquiere paquetes diseñados por profesionales para obtener múltiples sesiones con tarifas preferenciales y condiciones exclusivas.
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
    <v-row v-else-if="packages.length === 0" justify="center">
      <v-col cols="12" md="8" class="text-center">
        <v-card class="pa-10 text-center rounded-xl elevation-1 bg-white">
          <v-icon size="80" color="grey-lighten-1" class="mb-4">mdi-package-variant-closed-remove</v-icon>
          <h3 class="text-h5 font-weight-bold text-grey-darken-2 mb-2">No hay paquetes disponibles</h3>
          <p class="text-body-1 text-medium-emphasis">
            Actualmente ningún profesional ha publicado paquetes de sesiones. ¡Vuelve más tarde!
          </p>
        </v-card>
      </v-col>
    </v-row>

    <!-- Packages Grid -->
    <v-row v-else>
      <v-col cols="12" sm="6" md="4" v-for="item in packages" :key="item.id">
        <v-hover v-slot="{ isHovering, props }">
          <v-card
            v-bind="props"
            :elevation="isHovering ? 8 : 2"
            class="rounded-xl border h-100 d-flex flex-column card-hover position-relative"
          >
            <div class="pa-6 flex-grow-1">
              <!-- Top Row: Badges and Pricing -->
              <div class="d-flex justify-space-between align-start mb-4">
                <v-chip size="small" color="primary" variant="flat" class="font-weight-black text-uppercase">
                  {{ item.cantidad_sesiones }} sesiones
                </v-chip>
                <div class="d-flex align-center">
                  <span class="text-h5 font-weight-black text-success mr-1">
                    ${{ item.precio }}
                  </span>
                  <span class="text-caption text-medium-emphasis font-weight-bold">USD</span>
                </div>
              </div>

              <!-- Title & Expiry -->
              <h3 class="text-h6 font-weight-bold text-grey-darken-4 mb-2 line-clamp-1">
                {{ item.nombre }}
              </h3>

              <div class="d-flex align-center mb-4">
                <v-icon size="small" :color="item.vencimiento ? 'orange' : 'green'" class="mr-1">
                  {{ item.vencimiento ? 'mdi-clock-alert-outline' : 'mdi-infinity' }}
                </v-icon>
                <span class="text-caption font-weight-bold text-medium-emphasis">
                  {{ item.vencimiento ? `Vence en ${item.vencimiento} días` : 'Sin Vencimiento' }}
                </span>
              </div>

              <!-- Description -->
              <p class="text-body-2 text-medium-emphasis mb-4 line-clamp-3 text-justify">
                {{ item.descripcion }}
              </p>

              <v-divider class="mb-4"></v-divider>

              <!-- Professional details -->
              <div class="d-flex align-center mb-4" v-if="item.id_profesional">
                <v-avatar size="36" color="primary-lighten-1" class="mr-3 text-white font-weight-bold text-caption">
                  {{ item.id_profesional ? 'PR' : 'PR' }}
                </v-avatar>
                <div>
                  <div class="text-subtitle-2 font-weight-medium text-grey-darken-3">
                    Ofrecido por Profesional
                  </div>
                  <div class="d-flex align-center text-caption text-warning font-weight-bold">
                    <v-icon size="small" class="mr-1">mdi-star</v-icon>
                    5.0 (Excelente)
                  </div>
                </div>
              </div>

              <!-- Services Included -->
              <div class="mt-2">
                <span class="text-caption font-weight-bold text-grey-darken-1 mb-2 d-block">
                  <v-icon size="small" class="mr-1">mdi-briefcase-check-outline</v-icon>
                  Servicios Válidos:
                </span>
                <div class="d-flex flex-wrap gap-1">
                  <v-chip
                    v-for="s in item.servicios"
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
            </div>

            <!-- Purchase Button -->
            <div class="pa-4 bg-grey-lighten-4 mt-auto">
              <v-btn
                block
                color="primary"
                class="text-none font-weight-bold rounded-lg elevation-1 py-5 d-flex align-center justify-center"
                prepend-icon="mdi-cart-outline"
                @click="openPurchaseDialog(item)"
              >
                Adquirir Paquete
              </v-btn>
            </div>
          </v-card>
        </v-hover>
      </v-col>
    </v-row>

    <!-- Purchase Dialog -->
    <v-dialog v-model="purchaseDialog" max-width="500" persistent>
      <v-card class="rounded-xl overflow-hidden pa-0">
        <div class="dialog-header pa-6 text-white text-center">
          <v-icon size="48" class="mb-2">mdi-shield-check-outline</v-icon>
          <h3 class="text-h5 font-weight-bold">Confirmar Adquisición</h3>
          <p class="text-subtitle-2 opacity-80 mb-0">Estás por adquirir un paquete de sesiones</p>
        </div>

        <v-form ref="formPagoRef" @submit.prevent="processPurchase">
          <v-card-text class="pa-6" style="max-height: 65vh; overflow-y: auto;">
            <v-alert v-if="dialogError" type="error" variant="tonal" class="mb-4 rounded-lg animate-fade">
              <div class="font-weight-bold mb-1">Error al procesar el pago</div>
              <div class="text-body-2 mb-2">{{ dialogError }}</div>
              <div class="d-flex gap-2" v-if="compraCreada">
                <v-btn size="small" color="error" variant="elevated" @click="dialogError = ''" class="text-none">Reintentar</v-btn>
                <v-btn size="small" color="error" variant="outlined" @click="cancelarCompraDesdePago" :loading="isSubmitting" class="text-none">Cancelar Compra</v-btn>
              </div>
            </v-alert>

            <!-- Summary info -->
            <div class="bg-grey-lighten-4 pa-4 rounded-xl border mb-6">
              <div class="d-flex justify-space-between align-center mb-2">
                <span class="text-body-2 text-medium-emphasis">Paquete:</span>
                <strong class="text-body-1 text-grey-darken-3">{{ selectedPackage?.nombre }}</strong>
              </div>
              <div class="d-flex justify-space-between align-center mb-2">
                <span class="text-body-2 text-medium-emphasis">Sesiones Incluidas:</span>
                <strong class="text-body-1 text-primary">{{ selectedPackage?.cantidad_sesiones }} sesiones</strong>
              </div>
              <v-divider class="my-2"></v-divider>
              <div class="d-flex justify-space-between align-center">
                <span class="text-subtitle-1 font-weight-bold">Total a pagar:</span>
                <strong class="text-h5 text-success font-weight-black">${{ selectedPackage?.precio }} USD</strong>
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
              @click="closePurchaseDialog"
            >
              Cancelar
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
import { useRouter } from 'vue-router'
import DashboardLayout from '../components/DashboardLayout.vue'

const router = useRouter()
const isLoading = ref(true)
const packages = ref([])

// Dialog and flow states
const purchaseDialog = ref(false)
const selectedPackage = ref(null)
const paymentMethod = ref('paypal')
const simulateError = ref(false)
const isSubmitting = ref(false)
const dialogError = ref('')
const compraCreada = ref(null)
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

const snackbar = ref({ show: false, text: '', color: 'success' })

const loadPackages = async () => {
  isLoading.value = true
  const token = localStorage.getItem('auth_token')
  if (!token) return

  try {
    const response = await fetch('http://localhost:8000/api/paquetes', {
      headers: {
        'Authorization': `Bearer ${token}`,
        'Accept': 'application/json'
      }
    })
    
    if (response.ok) {
      const data = await response.json()
      packages.value = data.data || []
    }
  } catch (error) {
    console.error('Error al cargar paquetes:', error)
  } finally {
    isLoading.value = false
  }
}

onMounted(async () => {
  await loadPackages()
})

const openPurchaseDialog = (pkg) => {
  selectedPackage.value = pkg
  paymentMethod.value = 'paypal'
  simulateError.value = false
  dialogError.value = ''
  compraCreada.value = null
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

const closePurchaseDialog = () => {
  if (isSubmitting.value) return
  purchaseDialog.value = false
  selectedPackage.value = null
  compraCreada.value = null
}

const processPurchase = async () => {
  if (!selectedPackage.value) return

  if (formPagoRef.value) {
    const { valid } = await formPagoRef.value.validate()
    if (!valid) return
  }

  isSubmitting.value = true
  dialogError.value = ''

  const token = localStorage.getItem('auth_token')

  try {
    let response;
    let data;

    if (compraCreada.value) {
      // Retrying payment for an existing pending purchase
      const payload = {
        id_compra: compraCreada.value.id,
        monto: parseFloat(selectedPackage.value.precio),
        metodo: paymentMethod.value,
        simular_error: simulateError.value,
        detalles_pago: { ...datosPago.value }
      }

      response = await fetch('http://localhost:8000/api/pagos', {
        method: 'POST',
        headers: {
          'Authorization': `Bearer ${token}`,
          'Content-Type': 'application/json',
          'Accept': 'application/json'
        },
        body: JSON.stringify(payload)
      })

      data = await response.json()
      if (!response.ok) throw new Error(data.message || 'Error al procesar el pago')

      // Wait a moment for simulated pasarela feedback
      await new Promise(resolve => setTimeout(resolve, 1500))

      const pagoResult = data.data
      if (pagoResult.estado === 'completado') {
        snackbar.value = {
          show: true,
          text: '¡Compra completada con éxito! Las sesiones han sido habilitadas.',
          color: 'success'
        }
        purchaseDialog.value = false
        selectedPackage.value = null
        compraCreada.value = null
        setTimeout(() => { router.push('/mis-paquetes') }, 1500)
      } else {
        throw new Error('El pago fue rechazado por la pasarela de pagos.')
      }
    } else {
      // First-time package acquisition
      const payload = {
        metodo: paymentMethod.value,
        simular_error: simulateError.value,
        detalles_pago: { ...datosPago.value }
      }

      response = await fetch(`http://localhost:8000/api/paquetes/${selectedPackage.value.id}/comprar`, {
        method: 'POST',
        headers: {
          'Authorization': `Bearer ${token}`,
          'Content-Type': 'application/json',
          'Accept': 'application/json'
        },
        body: JSON.stringify(payload)
      })

      data = await response.json()
      if (!response.ok) throw new Error(data.message || 'Error al procesar la compra')

      // Wait a moment for simulated pasarela feedback
      await new Promise(resolve => setTimeout(resolve, 1500))

      const compra = data.data
      const pago = compra?.pagos?.[0]

      if (pago && pago.estado === 'completado') {
        snackbar.value = {
          show: true,
          text: '¡Compra completada con éxito! Las sesiones han sido habilitadas.',
          color: 'success'
        }
        purchaseDialog.value = false
        selectedPackage.value = null
        compraCreada.value = null
        setTimeout(() => { router.push('/mis-paquetes') }, 1500)
      } else {
        compraCreada.value = compra
        throw new Error('El pago fue rechazado por la pasarela de pagos.')
      }
    }
  } catch (err) {
    dialogError.value = err.message || 'No se pudo procesar la transacción. Intenta nuevamente.'
  } finally {
    isSubmitting.value = false
  }
}

const cancelarCompraDesdePago = async () => {
  if (!compraCreada.value) return
  isSubmitting.value = true
  const token = localStorage.getItem('auth_token')
  try {
    const res = await fetch(`http://localhost:8000/api/mis-paquetes/${compraCreada.value.id}`, {
      method: 'DELETE',
      headers: {
        'Authorization': `Bearer ${token}`,
        'Accept': 'application/json'
      }
    })

    if (!res.ok) throw new Error((await res.json()).message || 'Error al cancelar la compra')

    snackbar.value = { show: true, text: 'Compra de paquete cancelada y eliminada.', color: 'error' }
    purchaseDialog.value = false
    selectedPackage.value = null
    compraCreada.value = null
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
        const precio = parseFloat(selectedPackage.value?.precio || 0)
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
          
          let response;
          let resData;

          if (compraCreada.value) {
            const payload = {
              id_compra: compraCreada.value.id,
              monto: parseFloat(selectedPackage.value.precio),
              metodo: 'paypal',
              simular_error: false,
              detalles_pago: {
                paypal_order_id: details.id,
                paypal_email: details.payer.email_address,
                paypal_payer_id: details.payer.payer_id
              }
            }

            response = await fetch('http://localhost:8000/api/pagos', {
              method: 'POST',
              headers: {
                'Authorization': `Bearer ${token}`,
                'Content-Type': 'application/json',
                'Accept': 'application/json'
              },
              body: JSON.stringify(payload)
            })
            resData = await response.json()
          } else {
            const payload = {
              metodo: 'paypal',
              simular_error: false,
              detalles_pago: {
                paypal_order_id: details.id,
                paypal_email: details.payer.email_address,
                paypal_payer_id: details.payer.payer_id
              }
            }

            response = await fetch(`http://localhost:8000/api/paquetes/${selectedPackage.value.id}/comprar`, {
              method: 'POST',
              headers: {
                'Authorization': `Bearer ${token}`,
                'Content-Type': 'application/json',
                'Accept': 'application/json'
              },
              body: JSON.stringify(payload)
            })
            resData = await response.json()
          }

          if (!response.ok) throw new Error(resData.message || 'Error al completar la compra en nuestro servidor')

          snackbar.value = { show: true, text: '¡Compra de paquete completada con éxito!', color: 'success' }
          purchaseDialog.value = false
          selectedPackage.value = null
          compraCreada.value = null
          setTimeout(() => { router.push('/mis-paquetes') }, 1500)
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
</script>

<style scoped>
.bg-gradient {
  background: linear-gradient(135deg, #8C6D46 0%, #A6987A 100%);
}
.dialog-header {
  background: linear-gradient(135deg, #8C6D46 0%, #A6987A 100%);
}
.card-hover {
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
  border: 1px solid rgba(0, 0, 0, 0.08) !important;
}
.card-hover:hover {
  transform: translateY(-5px);
  box-shadow: 0 12px 25px -8px rgba(0, 0, 0, 0.18) !important;
  border-color: rgba(var(--v-theme-primary), 0.2) !important;
}
.border-red {
  border: 1px solid rgba(244, 67, 54, 0.2);
}
.gap-1 {
  gap: 4px;
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
.animate-fade {
  animation: fadeIn 0.3s ease-in-out;
}
@keyframes fadeIn {
  from { opacity: 0; transform: translateY(-5px); }
  to { opacity: 1; transform: translateY(0); }
}
</style>
