<template>
  <v-layout class="bg-grey-lighten-4">
    <v-navigation-drawer
      v-model="drawer"
      :rail="rail"
      permanent
      @click="rail = false"
      class="bg-primary-darken-1"
      theme="dark"
    >
      <v-list-item
        prepend-icon="mdi-rocket-launch"
        title="Plataforma Prof."
        nav
        class="mb-2 mt-2 font-weight-bold"
      >
        <template v-slot:append>
          <v-btn
            icon="mdi-chevron-left"
            variant="text"
            @click.stop="rail = !rail"
          ></v-btn>
        </template>
      </v-list-item>

      <v-divider></v-divider>

      <v-list density="compact" nav>
        <v-list-item prepend-icon="mdi-view-dashboard" title="Panel Principal" value="dashboard" to="/dashboard"></v-list-item>
        <v-list-item prepend-icon="mdi-account-details" title="Mi Perfil" value="profile" to="/profile"></v-list-item>
        <v-list-item v-if="!isProfesional" prepend-icon="mdi-magnify" title="Buscar Servicios" value="search" to="/buscar"></v-list-item>
        <v-list-item v-if="!isProfesional" prepend-icon="mdi-package-variant" title="Comprar Paquetes" value="comprar-paquetes" to="/comprar-paquetes"></v-list-item>
        <v-list-item v-if="!isProfesional" prepend-icon="mdi-briefcase-account" title="Mis Paquetes" value="mis-paquetes" to="/mis-paquetes"></v-list-item>
        <v-list-item v-if="isProfesional" prepend-icon="mdi-briefcase-edit" title="Mis Servicios" value="services" to="/services"></v-list-item>
        <v-list-item v-if="isProfesional" prepend-icon="mdi-package-variant-closed" title="Mis Paquetes" value="packages" to="/packages"></v-list-item>
        <v-list-item v-if="isProfesional" prepend-icon="mdi-calendar-clock" title="Mis Horarios" value="schedule" to="/mis-horarios"></v-list-item>
        <v-list-item prepend-icon="mdi-calendar-check" title="Mis Reservas" value="reservas" to="/mis-reservas"></v-list-item>
        <v-list-item prepend-icon="mdi-calendar-multiselect" title="Mi Agenda" value="agenda" to="/mi-agenda"></v-list-item>
      </v-list>

      <template v-slot:append>
        <div class="pa-2">
          <v-btn v-if="!rail" block color="error" variant="tonal" prepend-icon="mdi-logout" @click="logout">
            Cerrar Sesión
          </v-btn>
          <v-btn v-else icon color="error" variant="tonal" @click="logout" class="mx-auto d-flex">
            <v-icon>mdi-logout</v-icon>
          </v-btn>
        </div>
      </template>
    </v-navigation-drawer>

    <v-main>
      <v-app-bar elevation="0" class="bg-transparent px-4 mt-2">
        <v-app-bar-title class="text-h5 font-weight-bold text-grey-darken-3">{{ title }}</v-app-bar-title>
        <v-spacer></v-spacer>
        <v-menu
          v-model="menuNotificaciones"
          :close-on-content-click="false"
          location="bottom end"
          width="350"
        >
          <template v-slot:activator="{ props }">
            <v-btn icon v-bind="props" class="mr-2">
              <v-badge :content="notificaciones.length" :color="notificaciones.length > 0 ? 'error' : 'transparent'" :model-value="notificaciones.length > 0">
                <v-icon color="grey-darken-2">mdi-bell-outline</v-icon>
              </v-badge>
            </v-btn>
          </template>

          <v-card class="rounded-lg elevation-4 border-card">
            <v-card-title class="d-flex justify-space-between align-center pa-4 bg-grey-lighten-4">
              <span class="text-subtitle-1 font-weight-bold">Notificaciones</span>
              <v-chip size="small" color="primary" variant="flat" v-if="notificaciones.length > 0">{{ notificaciones.length }} nuevas</v-chip>
            </v-card-title>
            <v-divider></v-divider>
            
            <v-list lines="three" class="pa-0" v-if="notificaciones.length > 0" max-height="400" style="overflow-y: auto;">
              <template v-for="(notif, index) in notificaciones" :key="notif.id">
                <v-list-item
                  class="cursor-pointer notification-item"
                  @click="marcarComoLeida(notif.id)"
                  hover
                >
                  <template v-slot:prepend>
                    <v-avatar :color="notif.data?.color || 'primary'" variant="tonal" size="40" class="mr-3">
                      <v-icon size="20">{{ getIconoNotificacion(notif.data?.tipo) }}</v-icon>
                    </v-avatar>
                  </template>
                  
                  <v-list-item-title class="font-weight-bold text-body-2 mb-1">
                    {{ notif.data?.titulo }}
                  </v-list-item-title>
                  <v-list-item-subtitle class="text-caption text-wrap opacity-80" style="line-height: 1.2;">
                    {{ notif.data?.mensaje }}
                  </v-list-item-subtitle>
                  
                  <template v-slot:append>
                    <div class="d-flex flex-column align-end justify-center h-100 ml-2">
                      <span class="text-caption text-grey opacity-60">{{ formatTimeAgo(notif.created_at) }}</span>
                      <v-btn icon="mdi-circle-small" size="x-small" color="primary" variant="text" class="mt-1" @click.stop="marcarComoLeida(notif.id)"></v-btn>
                    </div>
                  </template>
                </v-list-item>
                <v-divider v-if="index < notificaciones.length - 1"></v-divider>
              </template>
            </v-list>
            
            <div v-else class="text-center pa-8 opacity-60">
              <v-icon size="48" color="grey-lighten-1" class="mb-2">mdi-bell-sleep</v-icon>
              <p class="mb-0 text-body-2">No tienes notificaciones nuevas</p>
            </div>
          </v-card>
        </v-menu>
        <v-avatar color="primary" class="ml-4" size="40">
          <span class="text-white font-weight-bold">{{ userInitials }}</span>
        </v-avatar>
      </v-app-bar>

      <v-container fluid class="px-8 py-4">
        <slot></slot>
      </v-container>
    </v-main>
  </v-layout>
</template>

<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'

const props = defineProps({
  title: {
    type: String,
    default: 'Panel Principal'
  }
})

const router = useRouter()
const drawer = ref(true)
const rail = ref(false)
const isProfesional = ref(false)
const userInitials = ref('US')

import { onMounted, onUnmounted } from 'vue'

const menuNotificaciones = ref(false)
const notificaciones = ref([])
let pollingInterval = null

const getAuthHeaders = () => ({
  'Content-Type': 'application/json',
  'Accept': 'application/json',
  'Authorization': `Bearer ${localStorage.getItem('auth_token')}`
})

const cargarNotificaciones = async () => {
  try {
    const res = await fetch('http://localhost:8000/api/auth/notificaciones', { headers: getAuthHeaders() })
    if (res.ok) {
      const data = await res.json()
      notificaciones.value = data.data || []
    }
  } catch (err) {
    console.error('Error cargando notificaciones', err)
  }
}

const marcarComoLeida = async (id) => {
  try {
    await fetch(`http://localhost:8000/api/auth/notificaciones/${id}/marcar-leida`, {
      method: 'PATCH',
      headers: getAuthHeaders()
    })
    // Remover visualmente de inmediato para mejorar la experiencia
    notificaciones.value = notificaciones.value.filter(n => n.id !== id)
    if (notificaciones.value.length === 0) {
      menuNotificaciones.value = false
    }
  } catch (err) {
    console.error('Error al marcar leída', err)
  }
}

const getIconoNotificacion = (tipo) => {
  const iconMap = {
    'confirmacion': 'mdi-check-circle',
    'cancelada': 'mdi-close-circle',
    'reprogramada': 'mdi-calendar-sync',
    'recordatorio': 'mdi-clock-alert'
  }
  return iconMap[tipo] || 'mdi-bell'
}

const formatTimeAgo = (dateStr) => {
  const diffMs = new Date() - new Date(dateStr)
  const diffMins = Math.round(diffMs / 60000)
  if (diffMins < 60) return `Hace ${diffMins} min`
  const diffHrs = Math.round(diffMins / 60)
  if (diffHrs < 24) return `Hace ${diffHrs} h`
  return `Hace ${Math.round(diffHrs / 24)} d`
}

onMounted(() => {
  const userStr = localStorage.getItem('user')
  if (userStr) {
    try {
      const user = JSON.parse(userStr)
      isProfesional.value = user.role === 'profesional'
      if (user.nombre) {
        userInitials.value = user.nombre.substring(0, 2).toUpperCase()
      }
    } catch (e) {
      console.error('Error parsing user data', e)
    }
  }
  
  cargarNotificaciones()
  pollingInterval = setInterval(cargarNotificaciones, 30000) // Poll cada 30 segundos
})

onUnmounted(() => {
  if (pollingInterval) clearInterval(pollingInterval)
})

const logout = async () => {
  const token = localStorage.getItem('auth_token')

  // Limpiar el estado de autenticación en el cliente de inmediato
  localStorage.removeItem('auth_token')
  localStorage.removeItem('user')

  // Redirigir a la pantalla de Inicio de Sesión
  router.push('/login')

  // Opcional y recomendado: invalidar el token en el servidor en segundo plano
  if (token) {
    try {
      await fetch('http://localhost:8000/api/auth/logout', {
        method: 'POST',
        headers: {
          'Authorization': `Bearer ${token}`,
          'Accept': 'application/json'
        }
      })
    } catch (e) {
      console.error('Error al revocar el token en el servidor:', e)
    }
  }
}
</script>

<style scoped>
.bg-primary-darken-1 {
  background-color: #8C6D46 !important;
}
.border-card {
  border: 1px solid rgba(140, 109, 70, 0.1);
}
.notification-item {
  transition: background-color 0.2s ease;
}
.notification-item:hover {
  background-color: rgba(140, 109, 70, 0.05);
}
</style>
