<template>
  <DashboardLayout title="Monitorear Sistema">
    <!-- Loading Overlay -->
    <v-overlay :model-value="loading" class="align-center justify-center" contained>
      <v-progress-circular indeterminate size="64" color="primary"></v-progress-circular>
    </v-overlay>

    <!-- Stats Summary Cards -->
    <v-row class="mb-4">
      <v-col cols="12" sm="6" md="3">
        <v-card class="pa-5 rounded-xl elevation-1 card-hover">
          <div class="d-flex align-center justify-space-between mb-3">
            <div class="text-subtitle-2 text-medium-emphasis font-weight-medium">Total Usuarios</div>
            <v-avatar color="blue-lighten-4" size="44">
              <v-icon color="blue">mdi-account-group</v-icon>
            </v-avatar>
          </div>
          <div class="text-h3 font-weight-bold text-grey-darken-4">{{ stats.total_usuarios }}</div>
        </v-card>
      </v-col>
      <v-col cols="12" sm="6" md="3">
        <v-card class="pa-5 rounded-xl elevation-1 card-hover">
          <div class="d-flex align-center justify-space-between mb-3">
            <div class="text-subtitle-2 text-medium-emphasis font-weight-medium">Total Reservas</div>
            <v-avatar color="green-lighten-4" size="44">
              <v-icon color="green">mdi-calendar-check</v-icon>
            </v-avatar>
          </div>
          <div class="text-h3 font-weight-bold text-grey-darken-4">{{ stats.total_reservas }}</div>
        </v-card>
      </v-col>
      <v-col cols="12" sm="6" md="3">
        <v-card class="pa-5 rounded-xl elevation-1 card-hover">
          <div class="d-flex align-center justify-space-between mb-3">
            <div class="text-subtitle-2 text-medium-emphasis font-weight-medium">Usuarios Activos</div>
            <v-avatar color="teal-lighten-4" size="44">
              <v-icon color="teal">mdi-account-check</v-icon>
            </v-avatar>
          </div>
          <div class="text-h3 font-weight-bold text-grey-darken-4">{{ stats.usuarios_activos }}</div>
        </v-card>
      </v-col>
      <v-col cols="12" sm="6" md="3">
        <v-card class="pa-5 rounded-xl elevation-1 card-hover">
          <div class="d-flex align-center justify-space-between mb-3">
            <div class="text-subtitle-2 text-medium-emphasis font-weight-medium">Usuarios Inactivos</div>
            <v-avatar color="red-lighten-4" size="44">
              <v-icon color="red">mdi-account-off</v-icon>
            </v-avatar>
          </div>
          <div class="text-h3 font-weight-bold text-grey-darken-4">{{ stats.usuarios_inactivos }}</div>
        </v-card>
      </v-col>
    </v-row>

    <!-- Reservations by Status + Users by Role -->
    <v-row class="mb-4">
      <v-col cols="12" md="6">
        <v-card class="pa-6 rounded-xl elevation-1 h-100">
          <h3 class="text-h6 font-weight-bold mb-4 text-grey-darken-4">
            <v-icon class="mr-2" color="primary">mdi-chart-pie</v-icon>
            Reservas por Estado
          </h3>
          <div v-if="Object.keys(stats.reservas_por_estado || {}).length === 0" class="text-center pa-8 text-medium-emphasis">
            <v-icon size="48" color="grey-lighten-1" class="mb-2">mdi-calendar-blank</v-icon>
            <p class="mb-0">Aún no hay reservas registradas</p>
          </div>
          <v-list v-else density="compact">
            <v-list-item v-for="(count, estado) in stats.reservas_por_estado" :key="estado" class="px-0">
              <template v-slot:prepend>
                <v-avatar :color="getEstadoColor(estado)" size="36" variant="tonal" class="mr-3">
                  <v-icon size="18">{{ getEstadoIcon(estado) }}</v-icon>
                </v-avatar>
              </template>
              <v-list-item-title class="font-weight-medium text-capitalize">{{ estado }}</v-list-item-title>
              <template v-slot:append>
                <v-chip :color="getEstadoColor(estado)" variant="flat" size="small" class="font-weight-bold">
                  {{ count }}
                </v-chip>
              </template>
            </v-list-item>
          </v-list>
        </v-card>
      </v-col>

      <v-col cols="12" md="6">
        <v-card class="pa-6 rounded-xl elevation-1 h-100">
          <h3 class="text-h6 font-weight-bold mb-4 text-grey-darken-4">
            <v-icon class="mr-2" color="primary">mdi-account-multiple</v-icon>
            Usuarios por Rol
          </h3>
          <div v-if="Object.keys(stats.usuarios_por_rol || {}).length === 0" class="text-center pa-8 text-medium-emphasis">
            <v-icon size="48" color="grey-lighten-1" class="mb-2">mdi-account-question</v-icon>
            <p class="mb-0">Aún no hay usuarios registrados</p>
          </div>
          <div v-else>
            <div v-for="(count, role) in stats.usuarios_por_rol" :key="role" class="mb-4">
              <div class="d-flex justify-space-between align-center mb-1">
                <span class="text-body-2 font-weight-medium text-capitalize">{{ getRoleLabel(role) }}</span>
                <span class="text-body-2 font-weight-bold">{{ count }}</span>
              </div>
              <v-progress-linear
                :model-value="stats.total_usuarios > 0 ? (count / stats.total_usuarios) * 100 : 0"
                :color="getRoleColor(role)"
                height="10"
                rounded
              ></v-progress-linear>
            </div>
          </div>
        </v-card>
      </v-col>
    </v-row>

    <!-- Latest Reservations -->
    <v-row class="mb-4">
      <v-col cols="12">
        <v-card class="rounded-xl elevation-1">
          <v-card-title class="pa-6 pb-2">
            <div class="d-flex align-center">
              <v-icon class="mr-2" color="primary">mdi-history</v-icon>
              <span class="text-h6 font-weight-bold">Últimas Reservas</span>
            </div>
          </v-card-title>
          <v-card-text class="pa-0" v-if="(stats.ultimas_reservas || []).length === 0">
            <div class="text-center pa-8 text-medium-emphasis">
              <v-icon size="48" color="grey-lighten-1" class="mb-2">mdi-calendar-blank-outline</v-icon>
              <p class="mb-0">No hay reservas recientes</p>
            </div>
          </v-card-text>
          <v-data-table
            v-else
            :headers="reservaHeaders"
            :items="stats.ultimas_reservas || []"
            density="comfortable"
            class="rounded-xl"
            :items-per-page="10"
            hover
          >
            <template v-slot:item.estado="{ item }">
              <v-chip :color="getEstadoColor(item.estado)" size="small" variant="flat" class="font-weight-bold text-capitalize">
                {{ item.estado }}
              </v-chip>
            </template>
            <template v-slot:item.fecha_hora_inicio="{ item }">
              <span class="text-body-2">{{ formatDateTime(item.fecha_hora_inicio) }}</span>
            </template>
            <template v-slot:item.created_at="{ item }">
              <span class="text-body-2">{{ formatDateTime(item.created_at) }}</span>
            </template>
          </v-data-table>
        </v-card>
      </v-col>
    </v-row>

    <!-- Latest Registered Users -->
    <v-row>
      <v-col cols="12">
        <v-card class="rounded-xl elevation-1">
          <v-card-title class="pa-6 pb-2">
            <div class="d-flex align-center">
              <v-icon class="mr-2" color="primary">mdi-account-plus</v-icon>
              <span class="text-h6 font-weight-bold">Últimos Usuarios Registrados</span>
            </div>
          </v-card-title>
          <v-card-text class="pa-0" v-if="(stats.ultimos_usuarios || []).length === 0">
            <div class="text-center pa-8 text-medium-emphasis">
              <v-icon size="48" color="grey-lighten-1" class="mb-2">mdi-account-question-outline</v-icon>
              <p class="mb-0">No hay usuarios registrados recientemente</p>
            </div>
          </v-card-text>
          <v-data-table
            v-else
            :headers="userHeaders"
            :items="stats.ultimos_usuarios || []"
            density="comfortable"
            class="rounded-xl"
            :items-per-page="10"
            hover
          >
            <template v-slot:item.role="{ item }">
              <v-chip :color="getRoleColor(item.role)" size="small" variant="flat" class="font-weight-bold text-capitalize">
                {{ getRoleLabel(item.role) }}
              </v-chip>
            </template>
            <template v-slot:item.activo="{ item }">
              <v-chip :color="item.activo ? 'success' : 'error'" size="small" variant="tonal">
                {{ item.activo ? 'Activo' : 'Inactivo' }}
              </v-chip>
            </template>
            <template v-slot:item.fecha_registro="{ item }">
              <span class="text-body-2">{{ formatDateTime(item.fecha_registro) }}</span>
            </template>
          </v-data-table>
        </v-card>
      </v-col>
    </v-row>
  </DashboardLayout>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import DashboardLayout from '../components/DashboardLayout.vue'

const loading = ref(true)
const stats = ref({
  total_usuarios: 0,
  usuarios_activos: 0,
  usuarios_inactivos: 0,
  usuarios_por_rol: {},
  total_reservas: 0,
  reservas_por_estado: {},
  ultimas_reservas: [],
  ultimos_usuarios: [],
})

const reservaHeaders = [
  { title: 'ID', key: 'id', width: '70px' },
  { title: 'Cliente', key: 'cliente_nombre' },
  { title: 'Servicio', key: 'servicio_nombre' },
  { title: 'Estado', key: 'estado', width: '130px' },
  { title: 'Fecha Inicio', key: 'fecha_hora_inicio', width: '180px' },
  { title: 'Creada', key: 'created_at', width: '180px' },
]

const userHeaders = [
  { title: 'ID', key: 'id', width: '70px' },
  { title: 'Nombre', key: 'nombre' },
  { title: 'Email', key: 'email' },
  { title: 'Rol', key: 'role', width: '130px' },
  { title: 'Estado', key: 'activo', width: '100px' },
  { title: 'Fecha Registro', key: 'fecha_registro', width: '180px' },
]

const getAuthHeaders = () => ({
  'Content-Type': 'application/json',
  'Accept': 'application/json',
  'Authorization': `Bearer ${localStorage.getItem('auth_token')}`
})

const getEstadoColor = (estado) => {
  const map = {
    'pendiente': 'amber',
    'confirmada': 'blue',
    'pagada': 'teal',
    'en_curso': 'indigo',
    'finalizada': 'green',
    'cancelada': 'red',
    'no_asistida': 'grey',
  }
  return map[estado] || 'grey'
}

const getEstadoIcon = (estado) => {
  const map = {
    'pendiente': 'mdi-clock-outline',
    'confirmada': 'mdi-check-circle-outline',
    'pagada': 'mdi-cash-check',
    'en_curso': 'mdi-play-circle-outline',
    'finalizada': 'mdi-flag-checkered',
    'cancelada': 'mdi-cancel',
    'no_asistida': 'mdi-account-off-outline',
  }
  return map[estado] || 'mdi-help-circle-outline'
}

const getRoleColor = (role) => {
  const map = { 'cliente': 'blue', 'profesional': 'orange', 'admin': 'deep-purple' }
  return map[role] || 'grey'
}

const getRoleLabel = (role) => {
  const map = { 'cliente': 'Cliente', 'profesional': 'Profesional', 'admin': 'Admin' }
  return map[role] || role
}

const formatDateTime = (dateStr) => {
  if (!dateStr) return 'N/A'
  const d = new Date(dateStr)
  return d.toLocaleDateString('es-PE', {
    year: 'numeric', month: 'short', day: 'numeric',
    hour: '2-digit', minute: '2-digit'
  })
}

const fetchStats = async () => {
  loading.value = true
  try {
    const res = await fetch('http://localhost:8000/api/admin/stats', { headers: getAuthHeaders() })
    if (res.ok) {
      stats.value = await res.json()
    }
  } catch (err) {
    console.error('Error fetching stats:', err)
  } finally {
    loading.value = false
  }
}

onMounted(() => {
  fetchStats()
})
</script>

<style scoped>
.card-hover {
  transition: transform 0.2s, box-shadow 0.2s;
}
.card-hover:hover {
  transform: translateY(-4px);
  box-shadow: 0 10px 25px rgba(0,0,0,0.08) !important;
}
.h-100 {
  height: 100%;
}
</style>
