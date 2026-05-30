<template>
  <DashboardLayout title="Mis Reservas">
    <!-- MENÚ PRINCIPAL -->
    <v-fade-transition leave-absolute>
      <v-row v-if="currentView === 'menu'" justify="center" align="start" class="mt-4">
        <v-col cols="12" md="10" lg="10">
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
              <v-row>
                <!-- 1. NUEVA RESERVA (Solo Cliente) -->
                <v-col cols="12" sm="6" v-if="isCliente">
                  <v-hover v-slot="{ isHovering, props }">
                    <v-card v-bind="props" :elevation="isHovering ? 8 : 2" class="h-100 rounded-xl cursor-pointer transition-swing border-panel d-flex flex-column align-center text-center pa-4" @click="currentView = 'reservar'">
                      <v-avatar color="primary" size="64" variant="tonal" class="mb-3">
                        <v-icon size="32">mdi-calendar-plus</v-icon>
                      </v-avatar>
                      <h2 class="text-h6 font-weight-bold mb-2 text-grey-darken-3">Nuevo Turno</h2>
                      <p class="text-caption text-medium-emphasis">Encuentra un horario ideal.</p>
                    </v-card>
                  </v-hover>
                </v-col>

                <!-- 1. PENDIENTES (Solo Profesional) -->
                <v-col cols="12" sm="6" v-else>
                  <v-hover v-slot="{ isHovering, props }">
                    <v-card v-bind="props" :elevation="isHovering ? 8 : 2" class="h-100 rounded-xl cursor-pointer transition-swing border-panel d-flex flex-column align-center text-center pa-4" @click="abrirVista('pendientes')">
                      <v-avatar color="warning" size="64" variant="tonal" class="mb-3">
                        <v-icon size="32">mdi-calendar-question</v-icon>
                      </v-avatar>
                      <h2 class="text-h6 font-weight-bold mb-2 text-grey-darken-3">Pendientes</h2>
                      <p class="text-caption text-medium-emphasis">Confirma solicitudes nuevas.</p>
                    </v-card>
                  </v-hover>
                </v-col>

                <!-- 2. REPROGRAMAR -->
                <v-col cols="12" sm="6">
                  <v-hover v-slot="{ isHovering, props }">
                    <v-card v-bind="props" :elevation="isHovering ? 8 : 2" class="h-100 rounded-xl cursor-pointer transition-swing border-panel d-flex flex-column align-center text-center pa-4" @click="abrirVista('reprogramar')">
                      <v-avatar color="info" size="64" variant="tonal" class="mb-3">
                        <v-icon size="32">mdi-calendar-sync</v-icon>
                      </v-avatar>
                      <h2 class="text-h6 font-weight-bold mb-2 text-grey-darken-3">Reprogramar</h2>
                      <p class="text-caption text-medium-emphasis">Cambia la fecha u hora de un turno.</p>
                    </v-card>
                  </v-hover>
                </v-col>

                <!-- 3. CANCELAR -->
                <v-col cols="12" sm="6">
                  <v-hover v-slot="{ isHovering, props }">
                    <v-card v-bind="props" :elevation="isHovering ? 8 : 2" class="h-100 rounded-xl cursor-pointer transition-swing border-panel d-flex flex-column align-center text-center pa-4" @click="abrirVista('cancelar')">
                      <v-avatar color="error" size="64" variant="tonal" class="mb-3">
                        <v-icon size="32">mdi-calendar-remove</v-icon>
                      </v-avatar>
                      <h2 class="text-h6 font-weight-bold mb-2 text-grey-darken-3">Cancelar</h2>
                      <p class="text-caption text-medium-emphasis">Anula una reserva activa.</p>
                    </v-card>
                  </v-hover>
                </v-col>

                <!-- 4. HISTORIAL -->
                <v-col cols="12" sm="6">
                  <v-hover v-slot="{ isHovering, props }">
                    <v-card v-bind="props" :elevation="isHovering ? 8 : 2" class="h-100 rounded-xl cursor-pointer transition-swing border-panel d-flex flex-column align-center text-center pa-4" @click="abrirVista('historial')">
                      <v-avatar color="secondary" size="64" variant="tonal" class="mb-3">
                        <v-icon size="32">mdi-clipboard-text-clock</v-icon>
                      </v-avatar>
                      <h2 class="text-h6 font-weight-bold mb-2 text-grey-darken-3">Historial</h2>
                      <p class="text-caption text-medium-emphasis">Revisa tu historial completo.</p>
                    </v-card>
                  </v-hover>
                </v-col>
              </v-row>

              <!-- Reservas Pendientes de Pago (Solo Cliente) -->
              <v-expand-transition>
                <div v-if="isCliente && reservasPendientesDePago.length > 0" class="mt-8">
                  <v-divider class="mb-6"></v-divider>
                  <h3 class="text-h6 font-weight-bold text-grey-darken-3 mb-4 d-flex align-center">
                    <v-icon color="warning" class="mr-2">mdi-credit-card-clock-outline</v-icon>
                    Tienes Turnos Pendientes de Pago
                  </h3>
                  
                  <v-row>
                    <v-col cols="12" md="6" v-for="reserva in reservasPendientesDePago" :key="reserva.id">
                      <v-card class="rounded-xl border bg-amber-lighten-5 pa-4 d-flex align-center justify-space-between" elevation="1">
                        <div class="mr-2">
                          <strong class="text-subtitle-1 text-grey-darken-4 d-block">{{ reserva.servicio?.nombre }}</strong>
                          <span class="text-caption text-medium-emphasis d-block mb-1">
                            {{ formatDateObj(reserva.fecha_hora_inicio) }}
                          </span>
                          <span class="text-caption text-grey-darken-2 d-block">
                            Profesional: <strong>{{ reserva.servicio?.profesional?.usuario?.nombre }} {{ reserva.servicio?.profesional?.usuario?.apellido || '' }}</strong>
                          </span>
                          <div class="text-subtitle-2 text-success font-weight-black mt-2">
                            Total: ${{ reserva.servicio?.precio }} USD
                          </div>
                        </div>
                        <v-btn
                          color="success"
                          class="text-white font-weight-bold text-none rounded-lg px-6"
                          prepend-icon="mdi-credit-card"
                          @click="abrirPagarReserva(reserva)"
                          elevation="2"
                        >
                          Pagar
                        </v-btn>
                      </v-card>
                    </v-col>
                  </v-row>
                </div>
              </v-expand-transition>
            </v-card-text>
          </v-card>
        </v-col>
      </v-row>
    </v-fade-transition>

    <!-- CLIENTE: RESERVAR TURNO (NUEVO) -->
    <v-fade-transition leave-absolute>
      <v-row v-if="currentView === 'reservar'" justify="center" align="start" class="mt-4">
        <v-col cols="12" md="10" lg="8">
          <v-btn variant="text" prepend-icon="mdi-arrow-left" class="mb-4 text-none font-weight-bold" @click="currentView = 'menu'">
            Volver al menú
          </v-btn>

          <v-card class="elevation-4 rounded-xl overflow-hidden border-card">
            <v-card-text class="pa-0">
              <div class="brand-header pa-6 text-white d-flex align-center">
                <v-icon size="36" class="mr-4">mdi-calendar-plus</v-icon>
                <div>
                  <h1 class="text-h5 font-weight-bold mb-0">Reservar Nuevo Turno</h1>
                  <p class="text-subtitle-2 opacity-90 mb-0">Elige un servicio, fecha y hora</p>
                </div>
              </div>
            </v-card-text>

            <v-card-text class="pa-6">
              <v-alert v-if="errorForm" type="error" variant="tonal" class="mb-6 rounded-lg" closable @click:close="errorForm = ''">{{ errorForm }}</v-alert>

              <v-form @submit.prevent="confirmarReserva" ref="formReserva">
                <v-select
                  v-model="formData.id_servicio"
                  :items="serviciosList"
                  item-title="nombre"
                  item-value="id"
                  label="1. Selecciona un Servicio"
                  variant="outlined"
                  color="primary"
                  class="mb-4"
                  :rules="[v => !!v || 'Requerido']"
                  @update:modelValue="formData.fecha = ''; turnosDisponibles = []; formData.hora = ''; pagarConPaquete = false; formData.id_compra_paquete = null"
                ></v-select>

                <!-- Selector Premium de Paquetes -->
                <div v-if="formData.id_servicio && paquetesAplicables.length > 0" class="mb-4 pa-4 rounded-xl border-panel bg-amber-lighten-5">
                  <div class="d-flex align-center mb-2">
                    <v-icon color="secondary" class="mr-2">mdi-package-variant-closed</v-icon>
                    <span class="text-subtitle-2 font-weight-bold text-grey-darken-3">¡Tienes paquetes disponibles para este servicio!</span>
                  </div>
                  
                  <v-checkbox
                    v-model="pagarConPaquete"
                    label="Pagar reserva utilizando una sesión de paquete"
                    color="secondary"
                    hide-details
                    class="mb-2"
                  ></v-checkbox>

                  <v-expand-transition>
                    <div v-if="pagarConPaquete">
                      <v-select
                        v-model="formData.id_compra_paquete"
                        :items="paquetesAplicables"
                        item-title="label"
                        item-value="id"
                        label="Selecciona tu paquete de sesiones"
                        variant="outlined"
                        color="secondary"
                        density="comfortable"
                        :rules="[v => !!v || 'Debes seleccionar un paquete']"
                      ></v-select>
                    </div>
                  </v-expand-transition>
                </div>

                <v-text-field
                  v-if="formData.id_servicio"
                  v-model="formData.fecha"
                  label="2. Selecciona una Fecha"
                  type="date"
                  variant="outlined"
                  color="primary"
                  class="mb-4"
                  :min="minDate"
                  @update:modelValue="buscarTurnos('nuevo')"
                  :rules="[v => !!v || 'Requerido']"
                ></v-text-field>

                <div v-if="formData.fecha && cargandoTurnos" class="text-center py-4">
                  <v-progress-circular indeterminate color="primary"></v-progress-circular>
                </div>

                <div v-if="formData.fecha && !cargandoTurnos" class="mb-6">
                  <p class="text-subtitle-2 font-weight-bold mb-2">3. Horarios Disponibles:</p>
                  <v-chip-group v-model="formData.hora" selected-class="bg-secondary text-white font-weight-bold" column>
                    <v-chip v-for="hora in turnosDisponibles" :key="hora" :value="hora" size="large" variant="outlined" class="mr-2 mb-2 font-weight-medium">
                      {{ hora }}
                    </v-chip>
                  </v-chip-group>
                  <p v-if="turnosDisponibles.length === 0" class="text-error mt-2">No hay turnos disponibles.</p>
                </div>

                <v-divider class="my-6"></v-divider>
                <v-btn color="secondary" type="submit" size="large" block class="text-none font-weight-bold rounded-lg" :loading="isLoading" :disabled="!formData.hora">
                  Confirmar Reserva
                </v-btn>
              </v-form>
            </v-card-text>
          </v-card>
        </v-col>
      </v-row>
    </v-fade-transition>

    <!-- AMBOS: REPROGRAMAR -->
    <v-fade-transition leave-absolute>
      <v-row v-if="currentView === 'reprogramar'" justify="center" align="start" class="mt-4">
        <v-col cols="12" md="10" lg="8">
          <v-btn variant="text" prepend-icon="mdi-arrow-left" class="mb-4 text-none font-weight-bold" @click="currentView = 'menu'; reservaSeleccionada = null">
            Volver al menú
          </v-btn>

          <v-card class="elevation-4 rounded-xl overflow-hidden border-card">
            <v-card-text class="pa-0">
              <div class="brand-header pa-6 text-white d-flex align-center" style="background: linear-gradient(135deg, #4A7A8C 0%, #7A9CA6 100%);">
                <v-icon size="36" class="mr-4">mdi-calendar-sync</v-icon>
                <div>
                  <h1 class="text-h5 font-weight-bold mb-0">Reprogramar Turno</h1>
                  <p class="text-subtitle-2 opacity-90 mb-0">Selecciona el turno que deseas modificar</p>
                </div>
              </div>
            </v-card-text>

            <v-card-text class="pa-6">
              <div v-if="cargandoRegistros" class="text-center py-8">
                <v-progress-circular indeterminate color="primary"></v-progress-circular>
              </div>

              <!-- Paso 1: Elegir Reserva -->
              <div v-else-if="!reservaSeleccionada">
                <div v-if="reservasActivas.length > 0">
                  <p class="text-subtitle-1 mb-4">Selecciona la reserva a reprogramar:</p>
                  <v-card v-for="reserva in reservasActivas" :key="reserva.id" class="mb-3 rounded-lg border-panel cursor-pointer" @click="seleccionarParaReprogramar(reserva)" hover>
                    <v-card-text class="d-flex align-center">
                      <v-avatar color="info" variant="tonal" class="mr-4"><v-icon>mdi-clock-edit-outline</v-icon></v-avatar>
                      <div>
                        <h4 class="font-weight-bold">{{ formatDateObj(reserva.fecha_hora_inicio) }}</h4>
                        <p class="mb-0 text-caption d-flex align-center flex-wrap gap-2">
                          {{ reserva.servicio?.nombre }} - {{ isCliente ? reserva.servicio?.profesional?.usuario?.nombre : reserva.cliente?.usuario?.nombre }}
                          <v-chip
                            v-if="reserva.compra_paquete"
                            size="x-small"
                            color="purple"
                            variant="flat"
                            class="text-white font-weight-bold"
                            prepend-icon="mdi-package-variant"
                          >
                            Paquete: {{ reserva.compra_paquete.paquete?.nombre }}
                          </v-chip>
                        </p>
                      </div>
                    </v-card-text>
                  </v-card>
                </div>
                <div v-else class="text-center py-8 opacity-60">
                  <v-icon size="48" color="grey">mdi-calendar-blank</v-icon>
                  <p class="mt-2">No tienes reservas activas (pendientes o confirmadas) para reprogramar.</p>
                </div>
              </div>

              <!-- Paso 2: Elegir Fecha y Hora -->
              <v-form v-else @submit.prevent="confirmarReprogramacion">
                <v-alert v-if="errorForm" type="error" variant="tonal" class="mb-6 rounded-lg" closable @click:close="errorForm = ''">{{ errorForm }}</v-alert>

                <div class="mb-6 bg-grey-lighten-4 pa-4 rounded-lg d-flex justify-space-between align-center">
                  <div>
                    <span class="text-caption text-grey-darken-1">Servicio a reprogramar:</span><br>
                    <strong class="text-primary">{{ reservaSeleccionada.servicio?.nombre }}</strong>
                  </div>
                  <v-btn size="small" variant="text" color="grey" @click="reservaSeleccionada = null">Cambiar</v-btn>
                </div>

                <v-text-field
                  v-model="formData.fecha"
                  label="Nueva Fecha"
                  type="date"
                  variant="outlined"
                  color="primary"
                  class="mb-4"
                  :min="minDate"
                  @update:modelValue="buscarTurnos('reprogramar')"
                  :rules="[v => !!v || 'Requerido']"
                ></v-text-field>

                <div v-if="formData.fecha && cargandoTurnos" class="text-center py-4">
                  <v-progress-circular indeterminate color="primary"></v-progress-circular>
                </div>

                <div v-if="formData.fecha && !cargandoTurnos" class="mb-6">
                  <p class="text-subtitle-2 font-weight-bold mb-2">Nuevos Horarios Disponibles:</p>
                  <v-chip-group v-model="formData.hora" selected-class="bg-info text-white font-weight-bold" column>
                    <v-chip v-for="hora in turnosDisponibles" :key="hora" :value="hora" size="large" variant="outlined" class="mr-2 mb-2 font-weight-medium">
                      {{ hora }}
                    </v-chip>
                  </v-chip-group>
                  <p v-if="turnosDisponibles.length === 0" class="text-error mt-2">No hay turnos disponibles para esta fecha.</p>
                </div>

                <v-divider class="my-6"></v-divider>
                <v-btn color="info" type="submit" size="large" block class="text-none font-weight-bold rounded-lg" :loading="isLoading" :disabled="!formData.hora">
                  Confirmar Reprogramación
                </v-btn>
              </v-form>

            </v-card-text>
          </v-card>
        </v-col>
      </v-row>
    </v-fade-transition>

    <!-- AMBOS: CANCELAR -->
    <v-fade-transition leave-absolute>
      <v-row v-if="currentView === 'cancelar'" justify="center" align="start" class="mt-4">
        <v-col cols="12" md="10" lg="8">
          <v-btn variant="text" prepend-icon="mdi-arrow-left" class="mb-4 text-none font-weight-bold" @click="currentView = 'menu'">
            Volver al menú
          </v-btn>

          <v-card class="elevation-4 rounded-xl overflow-hidden border-card">
            <v-card-text class="pa-0">
              <div class="brand-header pa-6 text-white d-flex align-center" style="background: linear-gradient(135deg, #8C4A4A 0%, #A67A7A 100%);">
                <v-icon size="36" class="mr-4">mdi-calendar-remove</v-icon>
                <div>
                  <h1 class="text-h5 font-weight-bold mb-0">Cancelar Turno</h1>
                  <p class="text-subtitle-2 opacity-90 mb-0">Selecciona el turno que deseas anular</p>
                </div>
              </div>
            </v-card-text>

            <v-card-text class="pa-6">
              <v-alert v-if="isCliente" type="info" variant="tonal" class="mb-6 rounded-lg text-body-2">
                <strong>Política:</strong> Las cancelaciones deben realizarse con al menos 10 horas de anticipación al turno.
              </v-alert>

              <div v-if="cargandoRegistros" class="text-center py-8">
                <v-progress-circular indeterminate color="primary"></v-progress-circular>
              </div>

              <div v-else-if="reservasActivas.length > 0">
                <v-card v-for="reserva in reservasActivas" :key="reserva.id" class="mb-3 rounded-lg border-panel">
                  <v-card-text class="d-flex align-center flex-wrap">
                    <v-avatar color="error" variant="tonal" size="48" class="mr-4"><v-icon>mdi-close-circle-outline</v-icon></v-avatar>
                    <div class="flex-grow-1 mr-4">
                      <h4 class="font-weight-bold">{{ formatDateObj(reserva.fecha_hora_inicio) }}</h4>
                      <p class="mb-0 text-caption d-flex align-center flex-wrap gap-2">
                        {{ reserva.servicio?.nombre }}
                        <v-chip
                          v-if="reserva.compra_paquete"
                          size="x-small"
                          color="purple"
                          variant="flat"
                          class="text-white font-weight-bold"
                          prepend-icon="mdi-package-variant"
                        >
                          Paquete: {{ reserva.compra_paquete.paquete?.nombre }}
                        </v-chip>
                      </p>
                    </div>
                    <v-btn color="error" variant="outlined" @click="cancelarReserva(reserva.id)" :loading="isLoading" class="mt-2 mt-sm-0 text-none">
                      Cancelar Turno
                    </v-btn>
                  </v-card-text>
                </v-card>
              </div>

              <div v-else class="text-center py-8 opacity-60">
                <v-icon size="48" color="grey">mdi-calendar-blank</v-icon>
                <p class="mt-2">No tienes reservas activas para cancelar.</p>
              </div>
            </v-card-text>
          </v-card>
        </v-col>
      </v-row>
    </v-fade-transition>

    <!-- PROFESIONAL: PENDIENTES -->
    <v-fade-transition leave-absolute>
      <v-row v-if="currentView === 'pendientes'" justify="center" align="start" class="mt-4">
        <v-col cols="12" md="10" lg="8">
          <v-btn variant="text" prepend-icon="mdi-arrow-left" class="mb-4 text-none font-weight-bold" @click="currentView = 'menu'">Volver</v-btn>
          <v-card class="elevation-4 rounded-xl border-card">
            <v-card-text class="pa-6 bg-grey-lighten-4">
              <h2 class="text-h5 font-weight-bold mb-4 text-primary">Solicitudes Pendientes</h2>
              <div v-if="cargandoRegistros" class="text-center py-8"><v-progress-circular indeterminate color="primary"></v-progress-circular></div>
              <v-row v-else-if="reservasPendientes.length > 0">
                <v-col cols="12" v-for="reserva in reservasPendientes" :key="reserva.id">
                  <v-card class="rounded-lg border-panel" elevation="0">
                    <v-card-text class="d-flex align-center flex-wrap">
                      <v-avatar color="warning" variant="tonal" size="48" class="mr-4"><v-icon>mdi-clock-alert</v-icon></v-avatar>
                      <div class="flex-grow-1 mr-4">
                        <h4 class="font-weight-bold text-grey-darken-3">{{ formatDateObj(reserva.fecha_hora_inicio) }}</h4>
                        <p class="text-body-2 mb-0 d-flex align-center flex-wrap gap-2">
                          {{ reserva.cliente?.usuario?.nombre }} {{ reserva.cliente?.usuario?.apellido }} - {{ reserva.servicio?.nombre }}
                          <v-chip
                            v-if="reserva.compra_paquete"
                            size="x-small"
                            color="purple"
                            variant="flat"
                            class="text-white font-weight-bold"
                            prepend-icon="mdi-package-variant"
                          >
                            Paquete: {{ reserva.compra_paquete.paquete?.nombre }}
                          </v-chip>
                        </p>
                      </div>
                      <div class="d-flex gap-2 mt-2 mt-sm-0">
                        <v-btn color="success" size="small" @click="cambiarEstadoReserva(reserva.id, 'confirmada')" :loading="isLoading">Confirmar</v-btn>
                      </div>
                    </v-card-text>
                  </v-card>
                </v-col>
              </v-row>
              <div v-else class="text-center py-12 opacity-60">¡Estás al día! No hay pendientes.</div>
            </v-card-text>
          </v-card>
        </v-col>
      </v-row>
    </v-fade-transition>

    <!-- AMBOS: HISTORIAL COMPLETO -->
    <v-fade-transition leave-absolute>
      <v-row v-if="currentView === 'historial'" justify="center" align="start" class="mt-4">
        <v-col cols="12" md="10" lg="8">
          <v-btn variant="text" prepend-icon="mdi-arrow-left" class="mb-4 text-none font-weight-bold" @click="currentView = 'menu'">Volver</v-btn>
          <v-card class="elevation-4 rounded-xl border-card">
            <v-card-text class="pa-6">
              <h2 class="text-h5 font-weight-bold mb-4 text-primary">Historial de Reservas</h2>
              <div style="overflow-x: auto; width: 100%;">
                <v-table v-else-if="reservasRegistros.length > 0" class="border-panel rounded-lg" style="min-width: 600px;">
                  <thead>
                    <tr>
                      <th class="text-left font-weight-bold">Fecha/Hora</th>
                      <th class="text-left font-weight-bold">Servicio</th>
                      <th class="text-left font-weight-bold">Estado</th>
                      <th class="text-left font-weight-bold">Acción</th>
                    </tr>
                  </thead>
                  <tbody>
                    <tr v-for="reserva in reservasRegistros" :key="reserva.id">
                      <td class="font-weight-medium">{{ formatDateShort(reserva.fecha_hora_inicio) }}</td>
                      <td>
                        <div class="d-flex flex-column">
                          <span>{{ reserva.servicio?.nombre }}</span>
                          <v-chip
                            v-if="reserva.compra_paquete"
                            size="x-small"
                            color="purple"
                            variant="flat"
                            class="mt-1 align-self-start text-white font-weight-bold"
                            prepend-icon="mdi-package-variant"
                          >
                            Paquete: {{ reserva.compra_paquete.paquete?.nombre }}
                          </v-chip>
                        </div>
                      </td>
                      <td><v-chip size="small" :color="getColorEstado(reserva.estado)">{{ reserva.estado }}</v-chip></td>
                      <td>
                        <div class="d-flex gap-2 flex-wrap">
                          <v-btn 
                            v-if="isCliente && puedeCalificar(reserva)" 
                            color="warning" 
                            size="small" 
                            variant="tonal"
                            @click="abrirCalificar(reserva)"
                          >
                            <v-icon left size="16" class="mr-1">mdi-star</v-icon> Calificar
                          </v-btn>
                          <v-btn 
                            v-if="isCliente && reserva.estado === 'pendiente' && !reserva.compra_paquete" 
                            color="success" 
                            size="small" 
                            variant="elevated"
                            class="text-white font-weight-bold text-none"
                            prepend-icon="mdi-credit-card"
                            @click="abrirPagarReserva(reserva)"
                          >
                            Pagar
                          </v-btn>
                        </div>
                      </td>
                    </tr>
                  </tbody>
                </v-table>
              </div>
              <div v-else-if="reservasRegistros.length === 0" class="text-center py-12 opacity-60">Historial vacío.</div>
            </v-card-text>
          </v-card>
        </v-col>
      </v-row>
    </v-fade-transition>

    <!-- MODAL CALIFICAR -->
    <v-dialog v-model="dialogCalificar" max-width="500">
      <v-card class="rounded-xl border-card">
        <v-card-text class="pa-0">
          <div class="brand-header pa-4 text-white d-flex align-center" style="background: linear-gradient(135deg, #E6A822 0%, #D48C00 100%);">
            <v-icon size="32" class="mr-3">mdi-star-circle</v-icon>
            <h2 class="text-h6 font-weight-bold mb-0">Calificar Servicio</h2>
          </div>
        </v-card-text>
        <v-card-text class="pa-6 text-center">
          <v-alert v-if="calificarError" type="error" variant="tonal" class="mb-4 rounded-lg text-left" closable @click:close="calificarError = ''">
            {{ calificarError }}
          </v-alert>

          <p class="text-body-1 mb-4">¿Qué te pareció el servicio <strong>{{ reservaACalificar?.servicio?.nombre }}</strong>?</p>
          
          <v-rating
            v-model="formCalificacion.puntuacion"
            color="warning"
            active-color="warning"
            hover
            half-increments="false"
            size="x-large"
            class="mb-4"
          ></v-rating>
          
          <v-textarea
            v-model="formCalificacion.comentario"
            label="Déjale una opinión escrita al profesional (obligatorio)"
            variant="outlined"
            rows="3"
            color="warning"
            counter="500"
            :rules="[v => !!v || 'El comentario es obligatorio', v => (v && v.length >= 3) || 'Mínimo 3 caracteres']"
            required
          ></v-textarea>
        </v-card-text>
        <v-card-actions class="pa-4 bg-grey-lighten-4 justify-end">
          <v-btn variant="text" color="grey" @click="dialogCalificar = false" class="text-none">Cancelar</v-btn>
          <v-btn color="warning" variant="elevated" @click="enviarCalificacion" :loading="isLoading" class="text-none font-weight-bold px-4" :disabled="!formCalificacion.puntuacion || !formCalificacion.comentario">
            Enviar Calificación
          </v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>

    <!-- MODAL PAGAR RESERVA -->
    <v-dialog v-model="dialogPagarReserva" max-width="500" persistent>
      <v-card class="rounded-xl border-card overflow-hidden">
        <v-card-text class="pa-0">
          <div class="brand-header pa-6 text-white text-center" style="background: linear-gradient(135deg, #4CAF50 0%, #2E7D32 100%);">
            <v-icon size="48" class="mb-2">mdi-shield-check-outline</v-icon>
            <h2 class="text-h5 font-weight-bold mb-0">Pagar Reserva</h2>
            <p class="text-subtitle-2 opacity-90 mb-0">Completa tu pago de forma segura</p>
          </div>
        </v-card-text>

        <v-form ref="formPagoRef" @submit.prevent="procesarPagoReserva">
          <v-card-text class="pa-6" style="max-height: 65vh; overflow-y: auto;">
            <!-- Aviso de Pre-reserva Temporal -->
            <v-alert type="info" variant="tonal" class="mb-4 rounded-lg text-body-2" density="comfortable" color="info" icon="mdi-information-outline">
              <strong>Pre-reserva Temporal:</strong> Tu turno ha sido pre-reservado. Completa el pago a continuación para activarlo definitivamente. Si sales del pago, se liberará el turno.
            </v-alert>

            <v-alert v-if="pagoError" type="error" variant="tonal" class="mb-4 rounded-lg animate-fade">
              <div class="font-weight-bold mb-1">Error al procesar el pago</div>
              <div class="text-body-2 mb-2">{{ pagoError }}</div>
              <div class="d-flex gap-2">
                <v-btn size="small" color="error" variant="elevated" @click="pagoError = ''" class="text-none">Reintentar</v-btn>
                <v-btn size="small" color="error" variant="outlined" @click="cancelarReservaDesdePago" :loading="isLoading" class="text-none">Cancelar Reserva</v-btn>
              </div>
            </v-alert>

            <!-- Detalles del Turno -->
            <div class="bg-grey-lighten-4 pa-4 rounded-xl border mb-6" v-if="reservaAPagar">
              <div class="d-flex justify-space-between align-center mb-2">
                <span class="text-caption text-medium-emphasis">Servicio:</span>
                <strong class="text-body-2 text-grey-darken-4">{{ reservaAPagar.servicio?.nombre }}</strong>
              </div>
              <div class="d-flex justify-space-between align-center mb-2">
                <span class="text-caption text-medium-emphasis">Profesional:</span>
                <strong class="text-body-2 text-grey-darken-4">
                  {{ reservaAPagar.servicio?.profesional?.usuario?.nombre }} {{ reservaAPagar.servicio?.profesional?.usuario?.apellido || '' }}
                </strong>
              </div>
              <div class="d-flex justify-space-between align-center mb-2">
                <span class="text-caption text-medium-emphasis">Fecha y Hora:</span>
                <strong class="text-body-2 text-grey-darken-4">{{ formatDateObj(reservaAPagar.fecha_hora_inicio) }}</strong>
              </div>
              <v-divider class="my-2"></v-divider>
              <div class="d-flex justify-space-between align-center">
                <span class="text-subtitle-2 font-weight-bold">Total a pagar:</span>
                <strong class="text-h5 text-success font-weight-black">${{ reservaAPagar.servicio?.precio }} USD</strong>
              </div>
            </div>

            <!-- Selector de Método de Pago -->
            <div v-if="!pagoError">
              <h4 class="text-subtitle-2 font-weight-bold text-grey-darken-3 mb-2">1. Selecciona un Método de Pago</h4>
              <v-radio-group v-model="metodoPago" inline class="mb-4">
                <v-row>
                  <v-col cols="12" sm="6" class="py-1">
                    <v-radio label="PayPal" value="paypal" color="success" class="font-weight-medium"></v-radio>
                  </v-col>
                  <v-col cols="12" sm="6" class="py-1">
                    <v-radio label="Transferencia" value="transferencia" color="success" class="font-weight-medium"></v-radio>
                  </v-col>
                  <v-col cols="12" sm="6" class="py-1">
                    <v-radio label="Efectivo" value="efectivo" color="success" class="font-weight-medium"></v-radio>
                  </v-col>
                  <v-col cols="12" sm="6" class="py-1">
                    <v-radio label="Otro" value="otro" color="success" class="font-weight-medium"></v-radio>
                  </v-col>
                </v-row>
              </v-radio-group>

              <!-- DETALLES DE PAGO SEGÚN MÉTODO SELECCIONADO -->
              <h4 class="text-subtitle-2 font-weight-bold text-grey-darken-3 mb-2">2. Completa los Datos de Pago</h4>
              <v-expand-transition>
                <div v-if="metodoPago === 'paypal'" class="pa-4 mb-4 rounded-xl border bg-blue-lighten-5">
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

                <div v-if="metodoPago === 'transferencia'" class="pa-4 mb-4 rounded-xl border bg-orange-lighten-5">
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

                <div v-if="metodoPago === 'otro'" class="pa-4 mb-4 rounded-xl border bg-purple-lighten-5">
                  <div class="d-flex align-center mb-3">
                    <v-icon color="purple-darken-3" class="mr-2">mdi-credit-card</v-icon>
                    <span class="text-subtitle-2 font-weight-bold text-purple-darken-3">Datos de tu Tarjeta</span>
                  </div>
                  <v-text-field
                    v-model="datosPago.tarjeta_nombre"
                    label="Nombre en la Tarjeta"
                    variant="outlined"
                    density="comfortable"
                    color="purple"
                    class="mb-2"
                    :rules="[v => !!v || 'El nombre es obligatorio']"
                    required
                  ></v-text-field>
                  <v-text-field
                    v-model="datosPago.tarjeta_numero"
                    label="Número de Tarjeta"
                    variant="outlined"
                    density="comfortable"
                    color="purple"
                    class="mb-2"
                    :rules="[v => !!v || 'El número de tarjeta es obligatorio', v => /^\d{16}$/.test(v) || 'Deben ser 16 dígitos']"
                    required
                  ></v-text-field>
                  <v-row>
                    <v-col cols="6" class="py-0 pr-1">
                      <v-text-field
                        v-model="datosPago.tarjeta_vence"
                        label="Vence (MM/AA)"
                        variant="outlined"
                        density="comfortable"
                        color="purple"
                        :rules="[v => !!v || 'Obligatorio', v => /^(0[1-9]|1[0-2])\/\d{2}$/.test(v) || 'MM/AA']"
                        required
                      ></v-text-field>
                    </v-col>
                    <v-col cols="6" class="py-0 pl-1">
                      <v-text-field
                        v-model="datosPago.tarjeta_cvv"
                        label="CVV"
                        type="password"
                        variant="outlined"
                        density="comfortable"
                        color="purple"
                        :rules="[v => !!v || 'Obligatorio', v => /^\d{3,4}$/.test(v) || '3-4 dígitos']"
                        required
                      ></v-text-field>
                    </v-col>
                  </v-row>
                </div>

                <div v-if="metodoPago === 'efectivo'" class="pa-4 mb-4 rounded-xl border bg-green-lighten-5">
                  <div class="d-flex align-center">
                    <v-icon color="green-darken-3" class="mr-2">mdi-cash-multiple</v-icon>
                    <span class="text-subtitle-2 font-weight-bold text-green-darken-3">Pago en Efectivo</span>
                  </div>
                  <div class="text-caption text-grey-darken-3 mt-2">
                    No se requiere ingresar datos bancarios o virtuales. Realizarás el pago en persona directamente al profesional al momento de tu sesión.
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
                  v-model="simularError"
                  color="error"
                  hide-details
                  density="compact"
                ></v-switch>
              </div>
            </div>
          </v-card-text>

          <v-card-actions class="pa-6 pt-0 d-flex justify-end">
            <v-btn variant="outlined" color="grey-darken-1" class="mr-3 px-6 text-none font-weight-bold" :disabled="cargandoPago" @click="dialogPagarReserva = false">
              Cerrar
            </v-btn>
            <v-btn v-if="!pagoError && (metodoPago !== 'paypal' || !paypalClientId)" type="submit" color="success" class="px-8 text-none font-weight-bold elevation-2 text-white" :loading="cargandoPago">
              Confirmar Pago
              <v-icon end>mdi-check-circle-outline</v-icon>
            </v-btn>
          </v-card-actions>
        </v-form>
      </v-card>
    </v-dialog>

    <v-snackbar v-model="snackbar.show" :color="snackbar.color" :timeout="4000" location="top">
      {{ snackbar.text }}
      <template v-slot:actions><v-btn variant="text" @click="snackbar.show = false">Cerrar</v-btn></template>
    </v-snackbar>
  </DashboardLayout>
</template>

<script setup>
import { ref, onMounted, computed, watch } from 'vue'
import { useRoute } from 'vue-router'
import DashboardLayout from '../components/DashboardLayout.vue'

const route = useRoute()

// Estado General
const currentView = ref('menu') // menu, reservar, pendientes, reprogramar, cancelar, historial
const isLoading = ref(false)
const snackbar = ref({ show: false, text: '', color: 'success' })
const isCliente = ref(true)

// Formularios
const errorForm = ref('')
const formData = ref({ id_servicio: null, fecha: '', hora: '', id_compra_paquete: null })
const pagarConPaquete = ref(false)
const reservaSeleccionada = ref(null)

// Modal Pagar Reserva
const dialogPagarReserva = ref(false)
const reservaAPagar = ref(null)
const metodoPago = ref('paypal')
const simularError = ref(false)
const cargandoPago = ref(false)
const pagoError = ref('')
const formPagoRef = ref(null)
const datosPago = ref({
  paypal_email: '',
  paypal_password: '',
  transferencia_titular: '',
  transferencia_cbu: '',
  tarjeta_nombre: '',
  tarjeta_numero: '',
  tarjeta_vence: '',
  tarjeta_cvv: ''
})

const paypalLoaded = ref(false)
const cargandoPaypalSdk = ref(false)
const paypalClientId = ref('')

// Modal Calificar
const dialogCalificar = ref(false)
const calificarError = ref('')
const reservaACalificar = ref(null)
const formCalificacion = ref({ puntuacion: 0, comentario: '' })
const calificacionesEnviadas = ref(new Set())

// Datos
const serviciosList = ref([])
const misPaquetesList = ref([])
const turnosDisponibles = ref([])
const cargandoTurnos = ref(false)
const reservasRegistros = ref([])
const cargandoRegistros = ref(false)

const minDate = new Date().toISOString().split('T')[0]

// Computada de Paquetes Aplicables
const paquetesAplicables = computed(() => {
  if (!formData.value.id_servicio) return []
  return misPaquetesList.value.filter(compra => {
    const servicios = compra.paquete?.servicios || []
    const contieneServicio = servicios.some(s => s.id === formData.value.id_servicio)
    return compra.estado === 'activo' && compra.sesiones_disponibles > 0 && contieneServicio
  }).map(compra => ({
    id: compra.id,
    label: `${compra.paquete?.nombre} (${compra.sesiones_disponibles} sesiones restantes)`,
    sesiones_disponibles: compra.sesiones_disponibles
  }))
})

const getAuthHeaders = () => ({
  'Content-Type': 'application/json',
  'Accept': 'application/json',
  'Authorization': `Bearer ${localStorage.getItem('auth_token')}`
})

onMounted(() => {
  const user = JSON.parse(localStorage.getItem('user') || '{}')
  isCliente.value = user.role !== 'profesional'
  
  cargarRegistros() // Cargar registros al inicio para poblar reservas pendientes de pago
  
  if (route.query.action === 'reservar' && isCliente.value) {
    currentView.value = 'reservar'
    if (route.query.servicio) {
      formData.value.id_servicio = parseInt(route.query.servicio)
    }
  }
})

// === FLUJOS DE VISTA ===
const abrirVista = (vista) => {
  currentView.value = vista
  cargarRegistros()
}

// === COMPUTADAS DE RESERVAS ===
const reservasActivas = computed(() => {
  return reservasRegistros.value.filter(r => r.estado === 'pendiente' || r.estado === 'confirmada')
})
const reservasPendientes = computed(() => {
  return reservasRegistros.value.filter(r => r.estado === 'pendiente')
})
const reservasPendientesDePago = computed(() => {
  return reservasRegistros.value.filter(r => r.estado === 'pendiente' && !r.compra_paquete)
})

// === CARGA DE DATOS ===
const cargarRegistros = async () => {
  cargandoRegistros.value = true
  try {
    const res = await fetch('http://localhost:8000/api/reservas', { headers: getAuthHeaders() })
    if (res.ok) {
      const data = await res.json()
      // Sort by date ascending for active/pendientes, descending for history
      reservasRegistros.value = (data.data || []).sort((a,b) => new Date(b.fecha_hora_inicio) - new Date(a.fecha_hora_inicio))
    }
  } catch (err) { console.error(err) } finally {
    cargandoRegistros.value = false
  }
}

const cargarServicios = async () => {
  try {
    const res = await fetch('http://localhost:8000/api/servicios', { headers: getAuthHeaders() })
    if (res.ok) {
      const data = await res.json()
      serviciosList.value = data.data || data
    }
  } catch (err) { console.error(err) }
}

const cargarMisPaquetes = async () => {
  try {
    const res = await fetch('http://localhost:8000/api/mis-paquetes', { headers: getAuthHeaders() })
    if (res.ok) {
      const data = await res.json()
      misPaquetesList.value = data.data || []
    }
  } catch (err) { console.error(err) }
}

watch(currentView, (newVal) => {
  if (newVal === 'reservar') {
    if (serviciosList.value.length === 0) cargarServicios()
    cargarMisPaquetes()
  }
})

const cargarPaypalSdk = async () => {
  if (paypalLoaded.value) return true
  cargandoPaypalSdk.value = true
  try {
    const res = await fetch('http://localhost:8000/api/config/paypal', { headers: getAuthHeaders() })
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
        const precio = parseFloat(reservaAPagar.value?.servicio?.precio || 0)
        return actions.order.create({
          purchase_units: [{
            amount: {
              value: precio.toFixed(2)
            }
          }]
        })
      },
      onApprove: async (data, actions) => {
        cargandoPago.value = true
        pagoError.value = ''
        try {
          const details = await actions.order.capture()
          const payload = {
            id_reserva: reservaAPagar.value.id,
            monto: parseFloat(reservaAPagar.value.servicio?.precio || 0),
            metodo: 'paypal',
            simular_error: false,
            detalles_pago: {
              paypal_order_id: details.id,
              paypal_email: details.payer.email_address,
              paypal_payer_id: details.payer.payer_id
            }
          }

          const res = await fetch('http://localhost:8000/api/pagos', {
            method: 'POST',
            headers: getAuthHeaders(),
            body: JSON.stringify(payload)
          })

          const resData = await res.json()
          if (!res.ok) throw new Error(resData.message || 'Error al procesar el pago')

          snackbar.value = { show: true, text: '¡Pago completado con éxito mediante PayPal!', color: 'success' }
          dialogPagarReserva.value = false
          cargarRegistros()
        } catch (err) {
          pagoError.value = err.message || 'Error en la pasarela de PayPal'
        } finally {
          cargandoPago.value = false
        }
      },
      onError: (err) => {
        pagoError.value = 'Ocurrió un error en la pasarela de PayPal o se canceló el cobro.'
      }
    }).render('#paypal-button-container')
  }, 150)
}

watch([metodoPago, dialogPagarReserva], async ([nuevoMetodo, estaAbierto]) => {
  if (estaAbierto && nuevoMetodo === 'paypal') {
    const cargado = await cargarPaypalSdk()
    if (cargado) {
      renderizarBotonesPaypal()
    }
  }
})

// === LOGICA: TURNOS ===
const buscarTurnos = async (contexto) => {
  const idServ = contexto === 'nuevo' ? formData.value.id_servicio : reservaSeleccionada.value?.id_servicio
  if (!idServ || !formData.value.fecha) return
  
  cargandoTurnos.value = true
  turnosDisponibles.value = []
  
  try {
    const res = await fetch(`http://localhost:8000/api/servicios/${idServ}/turnos?fecha=${formData.value.fecha}`, {
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

// === LOGICA: RESERVAR ===
const confirmarReserva = async () => {
  if (!formData.value.hora) return
  isLoading.value = true
  errorForm.value = ''

  try {
    const dateTime = `${formData.value.fecha} ${formData.value.hora}:00`
    const payload = {
      id_servicio: formData.value.id_servicio,
      fecha_hora_inicio: dateTime,
      id_compra_paquete: pagarConPaquete.value ? formData.value.id_compra_paquete : null
    }

    const res = await fetch('http://localhost:8000/api/reservas', {
      method: 'POST',
      headers: getAuthHeaders(),
      body: JSON.stringify(payload)
    })

    const data = await res.json()
    if (!res.ok) throw new Error(data.message || 'Error al reservar')

    snackbar.value = { show: true, text: '¡Turno reservado exitosamente!', color: 'success' }
    formData.value = { id_servicio: null, fecha: '', hora: '', id_compra_paquete: null }
    pagarConPaquete.value = false

    await cargarRegistros()

    if (payload.id_compra_paquete) {
      currentView.value = 'menu'
    } else {
      const reservaCreada = data.data
      abrirPagarReserva(reservaCreada)
    }
  } catch (err) {
    errorForm.value = err.message
  } finally {
    isLoading.value = false
  }
}

// === LOGICA: REPROGRAMAR ===
const seleccionarParaReprogramar = (reserva) => {
  reservaSeleccionada.value = reserva
  formData.value.fecha = ''
  formData.value.hora = ''
  turnosDisponibles.value = []
}

const confirmarReprogramacion = async () => {
  if (!formData.value.hora || !reservaSeleccionada.value) return
  isLoading.value = true
  errorForm.value = ''

  try {
    const dateTime = `${formData.value.fecha} ${formData.value.hora}:00`
    const res = await fetch(`http://localhost:8000/api/reservas/${reservaSeleccionada.value.id}/reprogramar`, {
      method: 'PATCH',
      headers: getAuthHeaders(),
      body: JSON.stringify({ fecha_hora_inicio: dateTime })
    })

    if (!res.ok) throw new Error((await res.json()).message || 'Error al reprogramar')

    snackbar.value = { show: true, text: 'Reserva reprogramada y devuelta a pendiente', color: 'info' }
    reservaSeleccionada.value = null
    formData.value = { id_servicio: null, fecha: '', hora: '' }
    cargarRegistros()
  } catch (err) {
    errorForm.value = err.message
  } finally {
    isLoading.value = false
  }
}

// === LOGICA: ESTADOS / CANCELAR ===
const cancelarReserva = async (id) => {
  if (!confirm('¿Estás seguro de que deseas cancelar este turno?')) return
  await cambiarEstadoReserva(id, 'cancelada')
}

const cambiarEstadoReserva = async (id, estado) => {
  isLoading.value = true
  try {
    const res = await fetch(`http://localhost:8000/api/reservas/${id}/estado`, {
      method: 'PATCH',
      headers: getAuthHeaders(),
      body: JSON.stringify({ estado })
    })

    if (!res.ok) throw new Error((await res.json()).message || 'Error al actualizar')

    snackbar.value = { show: true, text: estado === 'cancelada' ? 'Turno cancelado' : `Reserva ${estado}`, color: estado === 'cancelada' ? 'error' : 'success' }
    await cargarRegistros()
  } catch (err) {
    snackbar.value = { show: true, text: err.message, color: 'error' }
  } finally {
    isLoading.value = false
  }
}

// === LOGICA: CALIFICAR ===
const puedeCalificar = (reserva) => {
  if (calificacionesEnviadas.value.has(reserva.id)) return false
  const esFechaPasada = new Date(reserva.fecha_hora_fin || reserva.fecha_hora_inicio) < new Date()
  const estadoValido = ['finalizada', 'pagada', 'confirmada'].includes(reserva.estado)
  return estadoValido && esFechaPasada
}

const abrirCalificar = (reserva) => {
  reservaACalificar.value = reserva
  formCalificacion.value = { puntuacion: 0, comentario: '' }
  calificarError.value = ''
  dialogCalificar.value = true
}

const enviarCalificacion = async () => {
  if (!formCalificacion.value.puntuacion || !reservaACalificar.value) return
  
  if (!formCalificacion.value.comentario || formCalificacion.value.comentario.trim().length < 3) {
    calificarError.value = 'El comentario es obligatorio y debe tener al menos 3 caracteres.'
    return
  }
  
  isLoading.value = true
  calificarError.value = ''
  
  try {
    const res = await fetch(`http://localhost:8000/api/reservas/${reservaACalificar.value.id}/calificar`, {
      method: 'POST',
      headers: getAuthHeaders(),
      body: JSON.stringify(formCalificacion.value)
    })

    const data = await res.json()
    if (!res.ok) {
      throw new Error(data.message || 'Error al calificar')
    }

    snackbar.value = { show: true, text: '¡Gracias por calificar el servicio!', color: 'success' }
    calificacionesEnviadas.value.add(reservaACalificar.value.id)
    dialogCalificar.value = false
    reservaACalificar.value = null
  } catch (err) {
    if (err.message === 'Esta reserva ya fue calificada.') {
      calificacionesEnviadas.value.add(reservaACalificar.value.id)
      dialogCalificar.value = false
      snackbar.value = { show: true, text: err.message, color: 'error' }
    } else {
      calificarError.value = err.message
    }
  } finally {
    isLoading.value = false
  }
}

// === LOGICA: PAGAR RESERVA ===
const abrirPagarReserva = (reserva) => {
  reservaAPagar.value = reserva
  metodoPago.value = 'paypal'
  simularError.value = false
  pagoError.value = ''
  datosPago.value = {
    paypal_email: '',
    paypal_password: '',
    transferencia_titular: '',
    transferencia_cbu: '',
    tarjeta_nombre: '',
    tarjeta_numero: '',
    tarjeta_vence: '',
    tarjeta_cvv: ''
  }
  if (formPagoRef.value) {
    formPagoRef.value.resetValidation()
  }
  dialogPagarReserva.value = true
}

const procesarPagoReserva = async () => {
  if (!reservaAPagar.value) return
  
  if (formPagoRef.value) {
    const { valid } = await formPagoRef.value.validate()
    if (!valid) return
  }

  cargandoPago.value = true
  pagoError.value = ''

  try {
    const payload = {
      id_reserva: reservaAPagar.value.id,
      monto: parseFloat(reservaAPagar.value.servicio?.precio || 0),
      metodo: metodoPago.value,
      simular_error: simularError.value,
      detalles_pago: { ...datosPago.value }
    }

    const res = await fetch('http://localhost:8000/api/pagos', {
      method: 'POST',
      headers: getAuthHeaders(),
      body: JSON.stringify(payload)
    })

    const data = await res.json()

    if (!res.ok) {
      throw new Error(data.message || 'Error al procesar el pago')
    }

    // Wait a brief moment to simulate pasarela loading feedback if connection is sync
    await new Promise(resolve => setTimeout(resolve, 1500))

    // Check outcome of payment
    const pago = data.data
    if (pago.estado === 'completado') {
      snackbar.value = { show: true, text: '¡Pago completado con éxito! Tu turno ha sido reservado y pagado.', color: 'success' }
      dialogPagarReserva.value = false
      cargarRegistros()
    } else {
      throw new Error('El pago fue rechazado por la pasarela de pagos.')
    }
  } catch (err) {
    pagoError.value = err.message || 'Error en la pasarela de pagos. Compra cancelada.'
  } finally {
    cargandoPago.value = false
  }
}

const cancelarReservaDesdePago = async () => {
  if (!reservaAPagar.value) return
  isLoading.value = true
  try {
    const res = await fetch(`http://localhost:8000/api/reservas/${reservaAPagar.value.id}/estado`, {
      method: 'PATCH',
      headers: getAuthHeaders(),
      body: JSON.stringify({ estado: 'cancelada' })
    })

    if (!res.ok) throw new Error((await res.json()).message || 'Error al cancelar')

    snackbar.value = { show: true, text: 'Turno cancelado correctamente.', color: 'error' }
    dialogPagarReserva.value = false
    cargarRegistros()
  } catch (err) {
    snackbar.value = { show: true, text: err.message, color: 'error' }
  } finally {
    isLoading.value = false
  }
}

// === UTILS ===
const formatDateObj = (ds) => new Date(ds).toLocaleString('es-ES', { weekday: 'long', year: 'numeric', month: 'short', day: 'numeric', hour: '2-digit', minute:'2-digit' })
const formatDateShort = (ds) => new Date(ds).toLocaleString('es-ES', { day: '2-digit', month: '2-digit', year:'numeric', hour: '2-digit', minute:'2-digit' })
const getColorEstado = (estado) => ({ pendiente: 'warning', confirmada: 'success', cancelada: 'error', pagada: 'primary', finalizada: 'grey' }[estado] || 'grey')

</script>

<style scoped>
.brand-header { background: linear-gradient(135deg, #8C6D46 0%, #A6987A 100%); }
.border-card { border: 1px solid rgba(140, 109, 70, 0.1); }
.border-panel { border: 1px solid rgba(140, 109, 70, 0.2); transition: all 0.3s ease; }
.border-panel:hover { border-color: rgba(140, 109, 70, 0.5); box-shadow: 0 4px 12px rgba(140, 109, 70, 0.05); }
.gap-2 { gap: 8px; }
</style>
