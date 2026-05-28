<template>
  <DashboardLayout title="Resumen de Actividad">
    <!-- Dashboard Content -->
    <v-row>
      <!-- Welcome Card -->
      <v-col cols="12">
        <v-card class="pa-8 rounded-xl elevation-2 bg-gradient text-white">
          <v-row align="center">
            <v-col cols="12" md="8">
              <h1 class="text-h4 font-weight-bold mb-2">¡Hola, {{ userName }}! 👋</h1>
              <p v-if="isAdmin" class="text-body-1 opacity-80 mb-6">
                Bienvenido al panel de administración. Supervisa usuarios, reservas y la actividad general del sistema.
              </p>
              <p v-else-if="isProfesional" class="text-body-1 opacity-80 mb-6">
                Bienvenido a tu panel de control. Desde aquí puedes gestionar tu perfil, añadir nuevos servicios y ver tus estadísticas recientes.
              </p>
              <p v-else class="text-body-1 opacity-80 mb-6">
                Bienvenido a tu panel de control. Desde aquí puedes buscar servicios, reservar turnos y gestionar tus citas con profesionales.
              </p>
              <v-btn v-if="isAdmin" color="white" class="text-primary font-weight-bold rounded-lg px-6" elevation="2" to="/admin/users">
                Gestionar Usuarios
              </v-btn>
              <v-btn v-else-if="isProfesional" color="white" class="text-primary font-weight-bold rounded-lg px-6" elevation="2" to="/profile">
                Completar Perfil
              </v-btn>
              <v-btn v-else color="white" class="text-primary font-weight-bold rounded-lg px-6" elevation="2" @click="router.push('/buscar')">
                Explorar Servicios
              </v-btn>
            </v-col>
            <v-col cols="12" md="4" class="d-none d-md-flex justify-center">
              <v-icon size="120" color="white" class="opacity-50">
                {{ isAdmin ? 'mdi-shield-crown' : (isProfesional ? 'mdi-chart-bar' : 'mdi-magnify-expand') }}
              </v-icon>
            </v-col>
          </v-row>
        </v-card>
      </v-col>
    </v-row>

    <!-- Admin Stats Cards -->
    <v-row class="mt-4" v-if="isAdmin">
      <v-col cols="12" sm="6" md="3">
        <v-card class="pa-5 rounded-xl elevation-1 card-hover">
          <div class="d-flex align-center justify-space-between mb-3">
            <div class="text-subtitle-2 text-medium-emphasis font-weight-medium">Total Usuarios</div>
            <v-avatar color="blue-lighten-4" size="44">
              <v-icon color="blue">mdi-account-group</v-icon>
            </v-avatar>
          </div>
          <div class="text-h3 font-weight-bold text-grey-darken-4">{{ adminStats.total_usuarios }}</div>
          <div class="text-caption text-medium-emphasis mt-1">
            <v-icon size="14" color="success" class="mr-1">mdi-circle</v-icon>{{ adminStats.usuarios_activos }} activos
            <span class="ml-2"><v-icon size="14" color="error" class="mr-1">mdi-circle</v-icon>{{ adminStats.usuarios_inactivos }} inactivos</span>
          </div>
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
          <div class="text-h3 font-weight-bold text-grey-darken-4">{{ adminStats.total_reservas }}</div>
        </v-card>
      </v-col>
      <v-col cols="12" sm="6" md="3">
        <v-card class="pa-5 rounded-xl elevation-1 card-hover">
          <div class="d-flex align-center justify-space-between mb-3">
            <div class="text-subtitle-2 text-medium-emphasis font-weight-medium">Clientes</div>
            <v-avatar color="purple-lighten-4" size="44">
              <v-icon color="purple">mdi-account</v-icon>
            </v-avatar>
          </div>
          <div class="text-h3 font-weight-bold text-grey-darken-4">{{ adminStats.usuarios_por_rol?.cliente || 0 }}</div>
        </v-card>
      </v-col>
      <v-col cols="12" sm="6" md="3">
        <v-card class="pa-5 rounded-xl elevation-1 card-hover">
          <div class="d-flex align-center justify-space-between mb-3">
            <div class="text-subtitle-2 text-medium-emphasis font-weight-medium">Profesionales</div>
            <v-avatar color="orange-lighten-4" size="44">
              <v-icon color="orange">mdi-account-tie</v-icon>
            </v-avatar>
          </div>
          <div class="text-h3 font-weight-bold text-grey-darken-4">{{ adminStats.usuarios_por_rol?.profesional || 0 }}</div>
        </v-card>
      </v-col>
    </v-row>

    <!-- Admin Quick Links -->
    <v-row class="mt-2" v-if="isAdmin">
      <v-col cols="12" md="6">
        <v-card class="pa-6 rounded-xl elevation-1 card-hover" to="/admin/users">
          <div class="d-flex align-center">
            <v-avatar color="primary" size="56" class="mr-4">
              <v-icon color="white" size="28">mdi-account-cog</v-icon>
            </v-avatar>
            <div>
              <h3 class="text-h6 font-weight-bold text-grey-darken-4">Gestionar Usuarios</h3>
              <p class="text-body-2 text-medium-emphasis mb-0">Activar, desactivar o eliminar cuentas de usuario</p>
            </div>
            <v-spacer></v-spacer>
            <v-icon color="grey">mdi-chevron-right</v-icon>
          </div>
        </v-card>
      </v-col>
      <v-col cols="12" md="6">
        <v-card class="pa-6 rounded-xl elevation-1 card-hover" to="/admin/system">
          <div class="d-flex align-center">
            <v-avatar color="teal" size="56" class="mr-4">
              <v-icon color="white" size="28">mdi-chart-timeline-variant</v-icon>
            </v-avatar>
            <div>
              <h3 class="text-h6 font-weight-bold text-grey-darken-4">Monitorear Sistema</h3>
              <p class="text-body-2 text-medium-emphasis mb-0">Ver estadísticas de reservas y actividad reciente</p>
            </div>
            <v-spacer></v-spacer>
            <v-icon color="grey">mdi-chevron-right</v-icon>
          </div>
        </v-card>
      </v-col>
    </v-row>

    <!-- Stats Cards (Solo Profesionales) -->
    <v-row class="mt-4" v-if="isProfesional && !isAdmin">
      <v-col cols="12" sm="6">
        <v-card class="pa-6 rounded-xl elevation-1 card-hover">
          <div class="d-flex align-center justify-space-between mb-4">
            <div class="text-subtitle-1 text-medium-emphasis font-weight-medium">Servicios Activos</div>
            <v-avatar color="primary-lighten-4" size="48">
              <v-icon color="primary">mdi-briefcase-check</v-icon>
            </v-avatar>
          </div>
          <div class="text-h3 font-weight-bold text-grey-darken-4">{{ activeServicesCount }}</div>
        </v-card>
      </v-col>
      <v-col cols="12" sm="6">
        <v-card class="pa-6 rounded-xl elevation-1 card-hover">
          <div class="d-flex align-center justify-space-between mb-4">
            <div class="text-subtitle-1 text-medium-emphasis font-weight-medium">Calificación Promedio</div>
            <v-avatar color="warning-lighten-4" size="48">
              <v-icon color="warning">mdi-star</v-icon>
            </v-avatar>
          </div>
          <div class="text-h3 font-weight-bold text-grey-darken-4">{{ avgRating }}</div>
        </v-card>
      </v-col>
    </v-row>

    <!-- Buscador (Solo Clientes) -->
    <v-row class="mt-4" v-if="!isProfesional && !isAdmin">
      <v-col cols="12">
        <v-card class="pa-8 rounded-xl elevation-1 border">
          <h3 class="text-h5 font-weight-bold mb-4 text-grey-darken-4">Encuentra al profesional ideal</h3>
          <p class="text-body-1 text-medium-emphasis mb-6">
            Usa el buscador para filtrar por especialidad, nombre o tipo de servicio que necesitas.
          </p>
          <v-text-field
            v-model="searchQuery"
            variant="outlined"
            prepend-inner-icon="mdi-magnify"
            append-inner-icon="mdi-arrow-right"
            label="Buscar servicios..."
            color="primary"
            bg-color="grey-lighten-4"
            class="mb-2"
            hide-details
            @keyup.enter="handleSearch"
            @click:append-inner="handleSearch"
          ></v-text-field>
        </v-card>
      </v-col>
    </v-row>
  </DashboardLayout>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import DashboardLayout from '../components/DashboardLayout.vue'

const router = useRouter()

const getInitialName = () => {
  const userStr = localStorage.getItem('user')
  if (userStr) {
    try {
      const user = JSON.parse(userStr)
      if (user.nombre) return user.nombre.split(' ')[0]
    } catch(e) {}
  }
  return ''
}

const getUserRole = () => {
  const userStr = localStorage.getItem('user')
  if (userStr) {
    try {
      const user = JSON.parse(userStr)
      return user.role || ''
    } catch(e) {}
  }
  return ''
}

const userName = ref(getInitialName())
const isProfesional = ref(getUserRole() === 'profesional')
const isAdmin = ref(getUserRole() === 'admin')
const activeServicesCount = ref(0)
const avgRating = ref('0.0')
const searchQuery = ref('')

// Admin stats
const adminStats = ref({
  total_usuarios: 0,
  usuarios_activos: 0,
  usuarios_inactivos: 0,
  usuarios_por_rol: {},
  total_reservas: 0,
  reservas_por_estado: {},
  ultimas_reservas: [],
  ultimos_usuarios: [],
})

const handleSearch = () => {
  if (searchQuery.value.trim()) {
    router.push({ name: 'search', query: { q: searchQuery.value.trim() } })
  } else {
    router.push({ name: 'search' })
  }
}

onMounted(async () => {
  const token = localStorage.getItem('auth_token')
  if (!token) return

  const headers = {
    'Authorization': `Bearer ${token}`,
    'Accept': 'application/json'
  }

  try {
    // Fetch user info
    const authResponse = await fetch('http://localhost:8000/api/auth/me', { headers })

    if (authResponse.ok) {
      const data = await authResponse.json()
      if (data.user && data.user.nombre) {
        userName.value = data.user.nombre.split(' ')[0]
        isProfesional.value = data.user.role === 'profesional'
        isAdmin.value = data.user.role === 'admin'
        
        if (isProfesional.value && data.user.profesional) {
          avgRating.value = Number(data.user.profesional.reputacion).toFixed(1)
        }
      }
    }

    // Admin: fetch stats
    if (isAdmin.value) {
      try {
        const statsRes = await fetch('http://localhost:8000/api/admin/stats', { headers })
        if (statsRes.ok) {
          adminStats.value = await statsRes.json()
        }
      } catch (err) {
        console.error('Error fetching admin stats:', err)
      }
    }

    // Profesional: fetch services count
    if (isProfesional.value && !isAdmin.value) {
      const userStr = localStorage.getItem('user')
      if (userStr) {
        try {
          const user = JSON.parse(userStr)
          if (user.id) {
            const servResponse = await fetch(`http://localhost:8000/api/servicios?id_profesional=${user.id}`, { headers })
            if (servResponse.ok) {
              const servData = await servResponse.json()
              activeServicesCount.value = servData.data ? servData.data.length : 0
            }
          }
        } catch (e) {}
      }
    }
  } catch (error) {
    console.error('Error fetching dashboard data:', error)
  }
})
</script>

<style scoped>
.bg-gradient {
  background: linear-gradient(135deg, #8C6D46 0%, #A6987A 100%);
}

.card-hover {
  transition: transform 0.2s, box-shadow 0.2s;
}
.card-hover:hover {
  transform: translateY(-4px);
  box-shadow: 0 10px 25px rgba(0,0,0,0.08) !important;
}
</style>
