<template>
  <DashboardLayout title="Paquetes Adquiridos">
    <!-- Header visual -->
    <v-row class="mb-6">
      <v-col cols="12">
        <v-card class="pa-8 rounded-xl elevation-2 bg-gradient text-white">
          <div class="d-flex align-center flex-wrap">
            <v-avatar color="white" size="64" class="mr-6 elevation-2 text-primary font-weight-black">
              <v-icon size="36" color="primary">mdi-briefcase-account</v-icon>
            </v-avatar>
            <div>
              <h1 class="text-h4 font-weight-bold mb-2">Inventario de Paquetes</h1>
              <p class="text-body-1 opacity-80 mb-0">
                Consulta tus paquetes adquiridos, revisa tus sesiones disponibles por servicio y haz seguimiento a tus transacciones.
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

            <!-- Sessions remaining breakdown per service -->
            <div class="bg-grey-lighten-4 pa-4 rounded-xl border mb-4">
              <template v-if="compra.servicios_tracker && compra.servicios_tracker.length > 0">
                <div class="text-caption text-medium-emphasis font-weight-bold mb-2 text-center">SESIONES DISPONIBLES POR SERVICIO</div>
                <div v-for="tracker in compra.servicios_tracker" :key="tracker.id" class="mb-3">
                  <div class="d-flex justify-space-between align-center text-body-2 mb-1">
                    <span class="font-weight-medium text-grey-darken-3">{{ tracker.nombre }}</span>
                    <span class="font-weight-bold text-primary">{{ tracker.sesiones_disponibles }} / {{ tracker.sesiones_totales }}</span>
                  </div>
                  <v-progress-linear
                    :model-value="(tracker.sesiones_disponibles / (tracker.sesiones_totales || 1)) * 100"
                    color="primary"
                    height="6"
                    rounded
                  ></v-progress-linear>
                </div>
              </template>
              <template v-else>
                <div class="text-caption text-medium-emphasis font-weight-bold mb-2 text-center">SESIONES DISPONIBLES</div>
                <div v-for="s in compra.paquete?.servicios || []" :key="s.id" class="mb-3">
                  <div class="d-flex justify-space-between align-center text-body-2 mb-1">
                    <span class="font-weight-medium text-grey-darken-3">{{ s.nombre }}</span>
                    <span class="font-weight-bold text-medium-emphasis">0 / {{ s.cantidad_sesiones }} (Pendiente)</span>
                  </div>
                  <v-progress-linear
                    :model-value="0"
                    color="grey"
                    height="6"
                    rounded
                  ></v-progress-linear>
                </div>
              </template>
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
                  class="font-weight-medium cursor-pointer"
                  @click.stop="buscarServicio(s.nombre)"
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
                  @click="abrirCancelarCompra(compra.id)"
                  :loading="isSubmitting"
                >
                  Cancelar
                </v-btn>
              </div>
            </template>
            <template v-else-if="compra.estado === 'activo'">
              <div class="d-flex flex-column gap-2 w-100">
                <v-btn
                  block
                  color="secondary"
                  variant="tonal"
                  class="text-none font-weight-bold rounded-lg"
                  prepend-icon="mdi-calendar-clock"
                  :disabled="compra.sesiones_disponibles <= 0"
                  @click="abrirAgendarServicio(compra)"
                >
                  Elegir fecha del servicio
                </v-btn>
                <v-btn
                  block
                  color="error"
                  variant="outlined"
                  class="text-none font-weight-bold rounded-lg mt-1"
                  prepend-icon="mdi-cancel"
                  @click="abrirCancelarPaquete(compra)"
                >
                  Cancelar Paquete
                </v-btn>
              </div>
            </template>
            <template v-else>
              <div class="d-flex flex-column gap-2 w-100">
                <div class="text-center text-caption text-medium-emphasis py-1 font-weight-bold text-uppercase mb-1">
                  Paquete {{ compra.estado }}
                </div>
                <v-btn
                  block
                  color="grey-darken-1"
                  variant="outlined"
                  class="text-none font-weight-bold rounded-lg"
                  prepend-icon="mdi-delete-sweep-outline"
                  @click="abrirLimpiarPaquete(compra.id)"
                  :loading="isSubmitting"
                >
                  Limpiar del Historial
                </v-btn>
              </div>
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
                  <v-col cols="12" sm="6" class="py-1">
                    <v-radio label="PayPal" value="paypal" color="primary" class="font-weight-medium"></v-radio>
                  </v-col>
                  <v-col cols="12" sm="6" class="py-1">
                    <v-radio label="Efectivo" value="efectivo" color="primary" class="font-weight-medium"></v-radio>
                  </v-col>
                </v-row>
              </v-radio-group>

              <!-- DETALLES DE PAGO SEGÚN MÉTODO SELECCIONADO -->
              <h4 class="text-subtitle-2 font-weight-bold text-grey-darken-3 mb-2">2. Completa los Datos de Pago</h4>
              <v-expand-transition>
                <div v-if="paymentMethod === 'paypal'" class="pa-4 mb-4 rounded-xl border payment-box-paypal">
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
                      color="secondary"
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
                      color="secondary"
                      hide-details
                      :rules="[v => !!v || 'La contraseña es obligatoria']"
                      required
                    ></v-text-field>
                  </div>
                </div>

                <div v-if="paymentMethod === 'transferencia'" class="pa-4 mb-4 rounded-xl border payment-box-transferencia">
                  <div class="d-flex align-center mb-3">
                    <v-icon color="secondary" class="mr-2">mdi-bank</v-icon>
                    <span class="text-subtitle-2 font-weight-bold text-secondary">Datos de Transferencia</span>
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
                    color="secondary"
                    class="mb-2"
                    :rules="[v => !!v || 'El nombre es obligatorio']"
                    required
                  ></v-text-field>
                  <v-text-field
                    v-model="datosPago.transferencia_cbu"
                    label="CBU o CVU de Origen"
                    variant="outlined"
                    density="comfortable"
                    color="secondary"
                    hide-details
                    :rules="[v => !!v || 'El CBU/CVU es obligatorio', v => /^\d{22}$/.test(v) || 'Debe tener exactamente 22 números']"
                    required
                  ></v-text-field>
                </div>

                <div v-if="paymentMethod === 'efectivo'" class="pa-4 mb-4 rounded-xl border payment-box-efectivo">
                  <div class="d-flex align-center">
                    <v-icon color="success" class="mr-2">mdi-cash-multiple</v-icon>
                    <span class="text-subtitle-2 font-weight-bold text-success">Pago en Efectivo</span>
                  </div>
                  <div class="text-caption text-grey-darken-3 mt-2">
                    No se requiere ingresar datos bancarios o virtuales. Realizarás el pago en persona directamente al profesional al momento de tus sesiones.
                  </div>
                </div>
              </v-expand-transition>

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

    <!-- MODAL CANCELAR PAQUETE ACTIVO -->
    <v-dialog v-model="cancelarDialog" max-width="500" persistent>
      <v-card class="rounded-xl overflow-hidden pa-0">
        <div class="dialog-header-error bg-red pa-6 text-white text-center">
          <v-icon size="48" class="mb-2">mdi-alert-circle-outline</v-icon>
          <h3 class="text-h5 font-weight-bold">Cancelar Paquete</h3>
          <p class="text-subtitle-2 opacity-80 mb-0">Esta acción no se puede deshacer</p>
        </div>

        <v-card-text class="pa-6">
          <v-alert type="warning" variant="tonal" class="mb-4 rounded-lg text-left" color="warning">
            <strong>Atención:</strong> Al cancelar este paquete, se anularán de forma definitiva todas las sesiones restantes sin derecho a reembolso o reclamo.
          </v-alert>

          <div class="bg-grey-lighten-4 pa-4 rounded-xl border mb-6" v-if="selectedCancelPurchase">
            <div class="d-flex justify-space-between align-center mb-2">
              <span class="text-body-2 text-medium-emphasis">Paquete:</span>
              <strong class="text-body-1 text-grey-darken-3">{{ selectedCancelPurchase.paquete?.nombre }}</strong>
            </div>
            <div class="d-flex justify-space-between align-center mb-2">
              <span class="text-body-2 text-medium-emphasis">Sesiones Restantes:</span>
              <strong class="text-body-1 text-red font-weight-bold">{{ selectedCancelPurchase.sesiones_disponibles }} sesiones</strong>
            </div>
          </div>

          <v-checkbox
            v-model="confirmCancelCheckbox"
            color="error"
            label="Comprendo que perderé las sesiones restantes y confirmo la cancelación definitiva del paquete."
            hide-details
            class="mt-2"
          ></v-checkbox>
        </v-card-text>

        <v-card-actions class="pa-6 pt-0 d-flex justify-end">
          <v-btn
            variant="outlined"
            color="grey-darken-1"
            class="mr-3 px-6 text-none font-weight-bold"
            :disabled="isSubmitting"
            @click="cancelarDialog = false"
          >
            Cerrar
          </v-btn>
          <v-btn
            color="error"
            class="px-8 text-none font-weight-bold elevation-2 text-white"
            :loading="isSubmitting"
            :disabled="!confirmCancelCheckbox"
            @click="ejecutarCancelarPaquete"
          >
            Confirmar Cancelación
            <v-icon end>mdi-check-circle-outline</v-icon>
          </v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>

    <!-- MODAL AGENDAR SERVICIO DE PAQUETE -->
    <v-dialog v-model="dialogAgendar" max-width="550" persistent>
      <v-card class="rounded-xl overflow-hidden pa-0">
        <div class="dialog-header pa-6 text-white text-center">
          <v-icon size="48" class="mb-2">mdi-calendar-clock</v-icon>
          <h3 class="text-h5 font-weight-bold">Elegir fecha del servicio</h3>
          <p class="text-subtitle-2 opacity-80 mb-0">Reserva una sesión usando tu paquete</p>
        </div>

        <v-card-text class="pa-6" style="max-height: 70vh; overflow-y: auto;">
          <v-alert v-if="errorAgendar" type="error" variant="tonal" class="mb-4 rounded-lg">
            {{ errorAgendar }}
          </v-alert>

          <div v-if="selectedAgendarCompra">
            <!-- Paso 1: Seleccionar Servicio -->
            <div class="mb-4">
              <label class="text-subtitle-2 font-weight-bold text-grey-darken-3 mb-2 d-block">1. Selecciona el servicio a utilizar:</label>
              <v-select
                v-model="selectedServiceId"
                :items="serviciosDisponiblesParaReserva"
                item-title="nombre"
                item-value="id"
                label="Servicios disponibles en este paquete"
                variant="outlined"
                density="comfortable"
                color="primary"
                no-data-text="No hay servicios con sesiones disponibles"
              ></v-select>
            </div>

            <!-- Paso 2: Seleccionar Fecha -->
            <v-expand-transition>
              <div v-if="selectedServiceId" class="mb-4">
                <label class="text-subtitle-2 font-weight-bold text-grey-darken-3 mb-2 d-block">2. Selecciona la fecha:</label>
                <v-text-field
                  v-model="selectedDate"
                  type="date"
                  label="Fecha de la reserva"
                  variant="outlined"
                  density="comfortable"
                  color="primary"
                  :min="minDate"
                  @change="buscarTurnosDisponibles"
                ></v-text-field>
              </div>
            </v-expand-transition>

            <!-- Paso 3: Seleccionar Hora -->
            <v-expand-transition>
              <div v-if="selectedServiceId && selectedDate" class="mb-4">
                <label class="text-subtitle-2 font-weight-bold text-grey-darken-3 mb-2 d-block">3. Selecciona la hora:</label>
                
                <div v-if="cargandoSlots" class="text-center py-4">
                  <v-progress-circular indeterminate color="primary"></v-progress-circular>
                  <div class="text-caption text-primary mt-2">Buscando horarios disponibles...</div>
                </div>
                
                <div v-else-if="slotsDisponibles.length > 0">
                  <div class="d-flex flex-wrap gap-2 justify-start">
                    <v-btn
                      v-for="slot in slotsDisponibles"
                      :key="slot"
                      :color="selectedTime === slot ? 'primary' : 'grey-lighten-3'"
                      variant="flat"
                      class="text-none font-weight-bold rounded-lg"
                      @click="selectedTime = slot"
                    >
                      {{ slot }}
                    </v-btn>
                  </div>
                </div>

                <v-alert v-else type="info" variant="tonal" class="rounded-lg" color="warning">
                  No hay horarios disponibles para esta fecha. Por favor, selecciona otro día o revisa la disponibilidad del profesional.
                </v-alert>
              </div>
            </v-expand-transition>
          </div>
        </v-card-text>

        <v-card-actions class="pa-6 pt-0 d-flex justify-end">
          <v-btn
            variant="outlined"
            color="grey-darken-1"
            class="mr-3 px-6 text-none font-weight-bold"
            :disabled="isSubmittingAgendar"
            @click="cerrarAgendarServicio"
          >
            Cancelar
          </v-btn>
          <v-btn
            color="secondary"
            class="px-8 text-none font-weight-bold elevation-2 text-white"
            :loading="isSubmittingAgendar"
            :disabled="!selectedServiceId || !selectedDate || !selectedTime"
            @click="confirmarAgendarServicio"
          >
            Confirmar Cita
            <v-icon end>mdi-calendar-check</v-icon>
          </v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>

    <!-- MODAL CONFIRMAR LIMPIAR PAQUETE -->
    <v-dialog v-model="limpiarDialog" max-width="500" persistent>
      <v-card class="rounded-xl overflow-hidden pa-0">
        <div class="dialog-header-warning pa-6 text-white text-center">
          <v-icon size="48" class="mb-2">mdi-delete-alert-outline</v-icon>
          <h3 class="text-h5 font-weight-bold">Remover del Inventario</h3>
          <p class="text-subtitle-2 opacity-80 mb-0">Confirma la eliminación del paquete</p>
        </div>

        <v-card-text class="pa-6 text-center text-body-1 text-grey-darken-3">
          ¿Estás seguro de que deseas remover este paquete de tu inventario?
          <div class="text-caption text-medium-emphasis mt-2">
            Esta acción lo quitará definitivamente de tu historial de adquisiciones y no se podrá deshacer.
          </div>
        </v-card-text>

        <v-card-actions class="pa-6 pt-0 d-flex justify-end">
          <v-btn
            variant="outlined"
            color="grey-darken-1"
            class="mr-3 px-6 text-none font-weight-bold"
            :disabled="isSubmitting"
            @click="limpiarDialog = false"
          >
            Cancelar
          </v-btn>
          <v-btn
            color="warning"
            class="px-8 text-none font-weight-bold elevation-2 text-white"
            :loading="isSubmitting"
            @click="ejecutarLimpiarPaquete"
          >
            Remover
            <v-icon end>mdi-delete</v-icon>
          </v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>

    <!-- MODAL CONFIRMAR CANCELAR COMPRA PENDIENTE -->
    <v-dialog v-model="cancelarCompraPendienteDialog" max-width="500" persistent>
      <v-card class="rounded-xl overflow-hidden pa-0">
        <div class="dialog-header-error pa-6 text-white text-center">
          <v-icon size="48" class="mb-2">mdi-alert-circle-outline</v-icon>
          <h3 class="text-h5 font-weight-bold">Cancelar Adquisición</h3>
          <p class="text-subtitle-2 opacity-80 mb-0">Confirma la cancelación</p>
        </div>

        <v-card-text class="pa-6 text-center text-body-1 text-grey-darken-3">
          ¿Estás seguro de que deseas cancelar la adquisición de este paquete?
          <div class="text-caption text-medium-emphasis mt-2">
            Esta acción cancelará la solicitud de compra y eliminará el paquete pendiente de tu lista.
          </div>
        </v-card-text>

        <v-card-actions class="pa-6 pt-0 d-flex justify-end">
          <v-btn
            variant="outlined"
            color="grey-darken-1"
            class="mr-3 px-6 text-none font-weight-bold"
            :disabled="isSubmitting"
            @click="cancelarCompraPendienteDialog = false"
          >
            Cancelar
          </v-btn>
          <v-btn
            color="error"
            class="px-8 text-none font-weight-bold elevation-2 text-white"
            :loading="isSubmitting"
            @click="ejecutarCancelarCompra"
          >
            Confirmar
            <v-icon end>mdi-check-circle-outline</v-icon>
          </v-btn>
        </v-card-actions>
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
import { ref, onMounted, watch, computed } from 'vue'
import { useRouter } from 'vue-router'
import DashboardLayout from '../components/DashboardLayout.vue'

const router = useRouter()
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

// Cancellation states
const cancelarDialog = ref(false)
const selectedCancelPurchase = ref(null)
const confirmCancelCheckbox = ref(false)

// Booking / Agendar states
const dialogAgendar = ref(false)
const selectedAgendarCompra = ref(null)
const selectedServiceId = ref(null)
const selectedDate = ref('')
const selectedTime = ref('')
const cargandoSlots = ref(false)
const slotsDisponibles = ref([])
const errorAgendar = ref('')
const isSubmittingAgendar = ref(false)

const minDate = new Date(new Date().getTime() - (new Date().getTimezoneOffset() * 60000)).toISOString().split('T')[0]

const buscarServicio = (nombre) => {
  router.push({ name: 'search', query: { q: nombre } })
}

const loadPurchases = async () => {
  isLoading.value = true
  const token = localStorage.getItem('auth_token')
  if (!token) return

  try {
    const response = await fetch('/api/mis-paquetes', {
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
    const response = await fetch('/api/pagos', {
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
    if (pagoResult.estado === 'completado' || (pagoResult.estado === 'pendiente' && paymentMethod.value === 'efectivo')) {
      snackbar.value = {
        show: true,
        text: paymentMethod.value === 'efectivo'
          ? '¡Solicitud de pago en efectivo registrada! Esperando aprobación del profesional.'
          : '¡Pago completado con éxito! El paquete ha sido habilitado.',
        color: 'success'
      }
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

const cancelarCompraPendienteDialog = ref(false)
const compraPendienteACancelarId = ref(null)

const abrirCancelarCompra = (id) => {
  compraPendienteACancelarId.value = id
  cancelarCompraPendienteDialog.value = true
}

const ejecutarCancelarCompra = async () => {
  if (!compraPendienteACancelarId.value) return
  isSubmitting.value = true
  const token = localStorage.getItem('auth_token')
  try {
    const res = await fetch(`/api/mis-paquetes/${compraPendienteACancelarId.value}`, {
      method: 'DELETE',
      headers: {
        'Authorization': `Bearer ${token}`,
        'Accept': 'application/json'
      }
    })

    if (!res.ok) throw new Error((await res.json()).message || 'Error al cancelar la compra')

    snackbar.value = { show: true, text: 'Compra de paquete cancelada y eliminada.', color: 'error' }
    cancelarCompraPendienteDialog.value = false
    compraPendienteACancelarId.value = null
    loadPurchases()
  } catch (err) {
    snackbar.value = { show: true, text: err.message, color: 'error' }
  } finally {
    isSubmitting.value = false
  }
}

const limpiarDialog = ref(false)
const packageToLimpiarId = ref(null)

const abrirLimpiarPaquete = (id) => {
  packageToLimpiarId.value = id
  limpiarDialog.value = true
}

const ejecutarLimpiarPaquete = async () => {
  if (!packageToLimpiarId.value) return
  isSubmitting.value = true
  const token = localStorage.getItem('auth_token')
  try {
    const res = await fetch(`/api/mis-paquetes/${packageToLimpiarId.value}`, {
      method: 'DELETE',
      headers: {
        'Authorization': `Bearer ${token}`,
        'Accept': 'application/json'
      }
    })

    if (!res.ok) throw new Error((await res.json()).message || 'Error al eliminar el paquete')

    snackbar.value = { show: true, text: 'El paquete ha sido eliminado de tu historial.', color: 'success' }
    limpiarDialog.value = false
    packageToLimpiarId.value = null
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
    const res = await fetch(`/api/mis-paquetes/${selectedPurchase.value.id}`, {
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

// Active Package Cancellation methods
const abrirCancelarPaquete = (compra) => {
  selectedCancelPurchase.value = compra
  confirmCancelCheckbox.value = false
  cancelarDialog.value = true
}

const ejecutarCancelarPaquete = async () => {
  if (!selectedCancelPurchase.value || !confirmCancelCheckbox.value) return
  isSubmitting.value = true
  const token = localStorage.getItem('auth_token')
  try {
    const res = await fetch(`/api/mis-paquetes/${selectedCancelPurchase.value.id}/cancelar`, {
      method: 'POST',
      headers: {
        'Authorization': `Bearer ${token}`,
        'Accept': 'application/json'
      }
    })

    const data = await res.json()
    if (!res.ok) throw new Error(data.message || 'Error al cancelar el paquete')

    snackbar.value = { show: true, text: 'El paquete ha sido cancelado. Sesiones restantes anuladas.', color: 'error' }
    cancelarDialog.value = false
    selectedCancelPurchase.value = null
    loadPurchases()
  } catch (err) {
    snackbar.value = { show: true, text: err.message, color: 'error' }
  } finally {
    isSubmitting.value = false
  }
}

// Booking / Agendar methods
const abrirAgendarServicio = (compra) => {
  selectedAgendarCompra.value = compra
  selectedServiceId.value = null
  selectedDate.value = ''
  selectedTime.value = ''
  slotsDisponibles.value = []
  errorAgendar.value = ''
  dialogAgendar.value = true
}

const cerrarAgendarServicio = () => {
  dialogAgendar.value = false
  selectedAgendarCompra.value = null
  selectedServiceId.value = null
  selectedDate.value = ''
  selectedTime.value = ''
  slotsDisponibles.value = []
  errorAgendar.value = ''
}

const serviciosDisponiblesParaReserva = computed(() => {
  if (!selectedAgendarCompra.value) return []
  return (selectedAgendarCompra.value.servicios_tracker || []).filter(t => t.sesiones_disponibles > 0).map(t => ({
    id: t.id,
    nombre: `${t.nombre} (${t.sesiones_disponibles} de ${t.sesiones_totales} sesiones libres)`
  }))
})

const buscarTurnosDisponibles = async () => {
  if (!selectedServiceId.value || !selectedDate.value) return
  cargandoSlots.value = true
  slotsDisponibles.value = []
  selectedTime.value = ''

  const token = localStorage.getItem('auth_token')
  try {
    const res = await fetch(`/api/servicios/${selectedServiceId.value}/turnos?fecha=${selectedDate.value}`, {
      headers: {
        'Authorization': `Bearer ${token}`,
        'Accept': 'application/json'
      }
    })
    if (res.ok) {
      const data = await res.json()
      slotsDisponibles.value = data.data || []
    } else {
      throw new Error('No se pudieron obtener los turnos disponibles.')
    }
  } catch (err) {
    console.error(err)
  } finally {
    cargandoSlots.value = false
  }
}

watch(selectedServiceId, () => {
  buscarTurnosDisponibles()
})

const confirmarAgendarServicio = async () => {
  if (!selectedServiceId.value || !selectedDate.value || !selectedTime.value || !selectedAgendarCompra.value) return
  isSubmittingAgendar.value = true
  errorAgendar.value = ''

  const token = localStorage.getItem('auth_token')
  const payload = {
    id_servicio: selectedServiceId.value,
    fecha_hora_inicio: `${selectedDate.value} ${selectedTime.value}:00`,
    id_compra_paquete: selectedAgendarCompra.value.id
  }

  try {
    const res = await fetch('/api/reservas', {
      method: 'POST',
      headers: {
        'Authorization': `Bearer ${token}`,
        'Content-Type': 'application/json',
        'Accept': 'application/json'
      },
      body: JSON.stringify(payload)
    })

    const data = await res.json()
    if (!res.ok) throw new Error(data.message || 'Error al agendar la cita')

    snackbar.value = {
      show: true,
      text: '¡Cita agendada exitosamente con tu paquete!',
      color: 'success'
    }
    cerrarAgendarServicio()
    loadPurchases()
  } catch (err) {
    errorAgendar.value = err.message || 'No se pudo reservar el turno. Intenta nuevamente.'
  } finally {
    isSubmittingAgendar.value = false
  }
}

const cargarPaypalSdk = async () => {
  if (paypalLoaded.value) return true
  cargandoPaypalSdk.value = true
  try {
    const token = localStorage.getItem('auth_token')
    const res = await fetch('/api/config/paypal', {
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

          const response = await fetch('/api/pagos', {
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
    case 'cancelado': return 'error'
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
.dialog-header-warning {
  background: linear-gradient(135deg, #FF9800 0%, #FFB74D 100%);
}
.dialog-header-error {
  background: #f44336;
}
.border-red {
  border: 1px solid rgba(244, 67, 54, 0.2);
}
.payment-box-paypal {
  background-color: rgba(0, 48, 135, 0.04) !important;
  border: 1px solid rgba(0, 48, 135, 0.12) !important;
}
.payment-box-transferencia {
  background-color: rgba(140, 109, 70, 0.04) !important;
  border: 1px solid rgba(140, 109, 70, 0.15) !important;
}
.payment-box-efectivo {
  background-color: rgba(76, 175, 80, 0.04) !important;
  border: 1px solid rgba(76, 175, 80, 0.15) !important;
}
</style>
