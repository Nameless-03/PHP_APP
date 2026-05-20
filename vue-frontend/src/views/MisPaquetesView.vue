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
          </div>
        </v-card>
      </v-col>
    </v-row>
  </DashboardLayout>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import DashboardLayout from '../components/DashboardLayout.vue'

const isLoading = ref(true)
const purchases = ref([])

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
.line-clamp-1 {
  display: -webkit-box;
  -webkit-line-clamp: 1;
  -webkit-box-orient: vertical;
  overflow: hidden;
}
</style>
