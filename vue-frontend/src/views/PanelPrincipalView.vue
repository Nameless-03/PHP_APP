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
              <p v-if="isProfesional" class="text-body-1 opacity-80 mb-6">
                Bienvenido a tu panel de control. Desde aquí puedes gestionar tu perfil, añadir nuevos servicios y ver tus estadísticas recientes.
              </p>
              <p v-else class="text-body-1 opacity-80 mb-6">
                Bienvenido a tu panel de control. Desde aquí puedes buscar servicios, reservar turnos y gestionar tus citas con profesionales.
              </p>
              
              <v-btn v-if="isProfesional" color="white" class="text-primary font-weight-bold rounded-lg px-6" elevation="2" to="/profile">
                Completar Perfil
              </v-btn>
              <v-btn v-else color="white" class="text-primary font-weight-bold rounded-lg px-6" elevation="2">
                Explorar Servicios
              </v-btn>
            </v-col>
            <v-col cols="12" md="4" class="d-none d-md-flex justify-center">
              <v-icon size="120" color="white" class="opacity-50">
                {{ isProfesional ? 'mdi-chart-bar' : 'mdi-magnify-expand' }}
              </v-icon>
            </v-col>
          </v-row>
        </v-card>
      </v-col>
    </v-row>

    <!-- Stats Cards (Solo Profesionales) -->
    <v-row class="mt-4" v-if="isProfesional">
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
    <v-row class="mt-4" v-else>
      <v-col cols="12">
        <v-card class="pa-8 rounded-xl elevation-1 border">
          <h3 class="text-h5 font-weight-bold mb-4 text-grey-darken-4">Encuentra al profesional ideal</h3>
          <p class="text-body-1 text-medium-emphasis mb-6">
            Usa el buscador para filtrar por especialidad, nombre o tipo de servicio que necesitas.
          </p>
          <v-text-field
            variant="outlined"
            prepend-inner-icon="mdi-magnify"
            label="Buscar servicios..."
            color="primary"
            bg-color="grey-lighten-4"
            class="mb-2"
            rounded
            hide-details
          ></v-text-field>
        </v-card>
      </v-col>
    </v-row>
  </DashboardLayout>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import DashboardLayout from '../components/DashboardLayout.vue'

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

const getIsProfesional = () => {
  const userStr = localStorage.getItem('user')
  if (userStr) {
    try {
      const user = JSON.parse(userStr)
      return user.role === 'profesional'
    } catch(e) {}
  }
  return false
}

const userName = ref(getInitialName())
const isProfesional = ref(getIsProfesional())
const activeServicesCount = ref(0)
const avgRating = ref('0.0')

onMounted(async () => {
  const token = localStorage.getItem('auth_token')
  if (!token) return

  try {
    const response = await fetch('http://localhost:8000/api/auth/me', {
      headers: {
        'Authorization': `Bearer ${token}`,
        'Accept': 'application/json'
      }
    })
    
    if (response.ok) {
      const data = await response.json()
      if (data.user && data.user.nombre) {
        userName.value = data.user.nombre.split(' ')[0]
        isProfesional.value = data.user.role === 'profesional'
        
        // Actualizar calificación si el usuario es profesional y tiene el perfil cargado
        if (isProfesional.value && data.user.profesional) {
          avgRating.value = Number(data.user.profesional.reputacion).toFixed(1)
        }
      }
    }
    
    // Obtener cantidad de servicios activos
    if (isProfesional.value) {
      const servResponse = await fetch('http://localhost:8000/api/servicios', {
        headers: {
          'Authorization': `Bearer ${token}`,
          'Accept': 'application/json'
        }
      })
      
      if (servResponse.ok) {
        const servData = await servResponse.json()
        activeServicesCount.value = servData.data ? servData.data.length : 0
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
