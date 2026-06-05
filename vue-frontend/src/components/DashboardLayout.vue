<template>
  <v-layout class="bg-grey-lighten-4">
    <!-- Barra superior de estado Offline -->
    <v-system-bar v-if="isOffline" color="error" class="justify-center font-weight-bold text-white py-4" style="z-index: 1009; height: auto;">
      <v-icon start class="mr-2">mdi-wifi-off</v-icon>
      Sin conexión a Internet. Mostrando datos locales (Modo Offline).
    </v-system-bar>

    <v-navigation-drawer
      v-model="drawer"
      :rail="rail && $vuetify.display.mdAndUp"
      :permanent="$vuetify.display.mdAndUp"
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
            v-if="$vuetify.display.mdAndUp"
            icon="mdi-chevron-left"
            variant="text"
            @click.stop="rail = !rail"
          ></v-btn>
        </template>
      </v-list-item>

      <v-divider></v-divider>

      <v-list density="compact" nav>
        <v-list-item prepend-icon="mdi-view-dashboard" title="Panel Principal" value="dashboard" to="/dashboard"></v-list-item>
        <!-- Admin Menu -->
        <template v-if="isAdmin">
          <v-list-item prepend-icon="mdi-account-group" title="Gestionar Usuarios" value="admin-users" to="/admin/users"></v-list-item>
          <v-list-item prepend-icon="mdi-monitor-dashboard" title="Monitorear Sistema" value="admin-system" to="/admin/system"></v-list-item>
        </template>

        <!-- Cliente / Profesional Menu -->
        <template v-else>
          <v-list-item v-if="isProfesional" prepend-icon="mdi-account-details" title="Mi Perfil" value="profile" to="/profile"></v-list-item>
          <v-list-item v-if="!isProfesional" prepend-icon="mdi-magnify" title="Buscar Servicios" value="search" to="/buscar"></v-list-item>
          <v-list-item v-if="!isProfesional" prepend-icon="mdi-account-group" title="Profesionales" value="profesionales" to="/profesionales"></v-list-item>
          <v-list-item v-if="!isProfesional" prepend-icon="mdi-package-variant" title="Comprar Paquetes" value="comprar-paquetes" to="/comprar-paquetes"></v-list-item>
          <v-list-item v-if="!isProfesional" prepend-icon="mdi-briefcase-account" title="Mis Paquetes" value="mis-paquetes" to="/mis-paquetes"></v-list-item>
          <v-list-item v-if="isProfesional" prepend-icon="mdi-briefcase-edit" title="Mis Servicios" value="services" to="/services"></v-list-item>
          <v-list-item v-if="isProfesional" prepend-icon="mdi-package-variant-closed" title="Mis Paquetes" value="packages" to="/packages"></v-list-item>
          <v-list-item v-if="isProfesional" prepend-icon="mdi-calendar-clock" title="Mis Horarios" value="schedule" to="/mis-horarios"></v-list-item>
          <v-list-item prepend-icon="mdi-calendar-check" title="Mis Reservas" value="reservas" to="/mis-reservas"></v-list-item>
          <v-list-item prepend-icon="mdi-calendar-multiselect" title="Mi Agenda" value="agenda" to="/mi-agenda"></v-list-item>
          <v-list-item prepend-icon="mdi-video" title="Videollamadas" value="videollamadas" to="/videollamadas"></v-list-item>
          <!-- Opción para instalar la PWA -->
          <v-list-item
            v-if="canInstall"
            prepend-icon="mdi-download"
            title="Instalar App"
            value="install-pwa"
            @click="instalarPwa"
            class="bg-amber-darken-3 font-weight-bold text-white mt-4"
          ></v-list-item>
        </template>

      </v-list>

      <template v-slot:append>
        <div class="pa-2">
          <v-btn v-if="!rail || !$vuetify.display.mdAndUp" block color="white" variant="outlined" prepend-icon="mdi-logout" @click="confirmarLogout" class="logout-btn font-weight-bold">
            Cerrar Sesión
          </v-btn>
          <v-btn v-else icon color="white" variant="outlined" @click="confirmarLogout" class="mx-auto d-flex logout-btn">
            <v-icon>mdi-logout</v-icon>
          </v-btn>
        </div>
      </template>
    </v-navigation-drawer>

    <v-main>
      <v-app-bar elevation="0" class="bg-transparent px-4 mt-2">
        <v-app-bar-nav-icon color="grey-darken-3" class="d-md-none mr-2" @click="drawer = !drawer"></v-app-bar-nav-icon>
        <v-app-bar-title class="text-h5 font-weight-bold text-grey-darken-3">{{ title }}</v-app-bar-title>
        <v-spacer></v-spacer>
        <!-- Botón de permiso de notificaciones PWA -->
        <v-btn icon @click="solicitarPermisoNotificacion" class="mr-2" :title="tituloPermisoNotificacion">
          <v-icon color="grey-darken-2">{{ iconoPermisoNotificacion }}</v-icon>
        </v-btn>
        <v-menu
          v-model="menuNotificaciones"
          :close-on-content-click="false"
          location="bottom end"
          width="350"
        >
          <template v-slot:activator="{ props }">
            <v-btn icon v-bind="props" class="mr-2">
              <v-badge :content="notificaciones.length" :color="notificaciones.length > 0 ? 'error' : 'transparent'" :model-value="notificaciones.length > 0 && !bubbleHidden">
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
            
            <v-expansion-panels
              variant="accordion"
              class="pa-0 elevation-0"
              v-if="notificaciones.length > 0"
              max-height="400"
              style="overflow-y: auto;"
            >
              <v-expansion-panel
                v-for="notif in notificaciones"
                :key="notif.id"
                class="border-b border-card elevation-0 bg-transparent rounded-0"
                :value="notif.id"
              >
                <v-expansion-panel-title class="py-3 px-4">
                  <template v-slot:default="{ expanded }">
                    <div class="d-flex align-center w-100 pr-2 min-width-0">
                      <v-avatar :color="notif.data?.color || 'primary'" variant="tonal" size="36" class="mr-3 flex-shrink-0">
                        <v-icon size="18">{{ getIconoNotificacion(notif.data?.tipo) }}</v-icon>
                      </v-avatar>
                      <div class="text-left flex-grow-1 min-width-0">
                        <div class="font-weight-bold text-body-2 mb-0 text-truncate text-grey-darken-3">
                          {{ notif.data?.titulo }}
                        </div>
                        <div class="text-caption text-grey opacity-60">
                          {{ formatTimeAgo(notif.created_at) }}
                        </div>
                      </div>
                    </div>
                  </template>
                </v-expansion-panel-title>
                
                <v-expansion-panel-text class="bg-grey-lighten-5 text-body-2 text-grey-darken-2 pa-0">
                  <div class="pa-4">
                    <div class="mb-3 text-wrap text-left text-body-2" style="line-height: 1.4; color: #555;">
                      {{ notif.data?.mensaje }}
                    </div>
                    <div class="d-flex justify-end">
                      <v-btn
                        size="x-small"
                        color="secondary"
                        variant="flat"
                        class="text-none font-weight-bold rounded-pill text-white px-3"
                        prepend-icon="mdi-check"
                        @click.stop="marcarComoLeida(notif.id)"
                      >
                        Entendido
                      </v-btn>
                    </div>
                  </div>
                </v-expansion-panel-text>
              </v-expansion-panel>
            </v-expansion-panels>
            
            <div v-else class="text-center pa-8 opacity-60">
              <v-icon size="48" color="grey-lighten-1" class="mb-2">mdi-bell-sleep</v-icon>
              <p class="mb-0 text-body-2">No tienes notificaciones nuevas</p>
            </div>
          </v-card>
        </v-menu>
        <v-avatar
          color="primary"
          class="ml-4"
          :class="{ 'cursor-pointer': isProfesional }"
          size="40"
          @click="isProfesional && router.push('/profile')"
        >
          <v-img v-if="userAvatar" :src="userAvatar" alt="Avatar"></v-img>
          <span v-else class="text-white font-weight-bold">{{ userInitials }}</span>
        </v-avatar>
      </v-app-bar>

      <v-container fluid class="px-4 px-md-8 py-4">
        <slot></slot>
      </v-container>
    </v-main>

    <!-- Diálogo de Confirmación de Cierre de Sesión -->
    <ConfirmationDialog
      v-model="dialogLogout"
      title="Cerrar Sesión"
      message="¿Seguro que quieres cerrar sesión?"
      confirm-text="Cerrar Sesión"
      confirm-color="secondary"
      icon="mdi-alert-circle-outline"
      @confirm="logout"
    />

    <!-- Registro de Service Worker PWA -->
    <RegisterSW />
  </v-layout>
</template>

<script setup>
import { ref, watch, computed, onMounted, onUnmounted } from 'vue'
import { useRouter } from 'vue-router'
import { useAuth } from '../composables/useAuth'
import ConfirmationDialog from './ConfirmationDialog.vue'
import RegisterSW from './RegisterSW.vue'

const props = defineProps({
  title: {
    type: String,
    default: 'Panel Principal'
  }
})

const router = useRouter()
const drawer = ref(true)
const rail = ref(false)

const { 
  user, 
  isProfesional, 
  isAdmin, 
  userInitials, 
  getAuthHeaders, 
  logoutServer 
} = useAuth()

const userAvatar = computed(() => user.value?.profesional?.foto_perfil_url || null)

const dialogLogout = ref(false)
const confirmarLogout = () => {
  dialogLogout.value = true
}

const bubbleHidden = ref(false)

const menuNotificaciones = ref(false)
const notificaciones = ref([])
let pollingInterval = null

// PWA: Estado de conexión
const isOffline = ref(!navigator.onLine)
const actualizarEstadoConexion = () => {
  isOffline.value = !navigator.onLine
}

// PWA: Instalar Aplicación
const installPromptEvent = ref(null)
const canInstall = computed(() => !!installPromptEvent.value)

const capturarPromptInstalacion = (e) => {
  e.preventDefault()
  installPromptEvent.value = e
}

const instalarPwa = async () => {
  if (!installPromptEvent.value) return
  installPromptEvent.value.prompt()
  const { outcome } = await installPromptEvent.value.userChoice
  console.log(`PWA install prompt result: ${outcome}`)
  installPromptEvent.value = null
}

// PWA: Permiso y envío de notificaciones locales
const permisoNotificacion = ref(typeof Notification !== 'undefined' ? Notification.permission : 'denied')

const iconoPermisoNotificacion = computed(() => {
  if (permisoNotificacion.value === 'granted') return 'mdi-bell-ring-outline'
  if (permisoNotificacion.value === 'denied') return 'mdi-bell-off-outline'
  return 'mdi-bell-plus-outline'
})

const tituloPermisoNotificacion = computed(() => {
  if (permisoNotificacion.value === 'granted') return 'Notificaciones activadas (clic para enviar prueba)'
  if (permisoNotificacion.value === 'denied') return 'Notificaciones bloqueadas por el navegador'
  return 'Activar notificaciones de la plataforma'
})

const solicitarPermisoNotificacion = async () => {
  if (typeof Notification === 'undefined') {
    alert('Este navegador no soporta notificaciones.')
    return
  }
  
  if (Notification.permission === 'default') {
    const permission = await Notification.requestPermission()
    permisoNotificacion.value = permission
    if (permission === 'granted') {
      lanzarNotificacionPrueba()
    }
  } else if (Notification.permission === 'granted') {
    lanzarNotificacionPrueba()
  } else {
    alert('Has bloqueado las notificaciones. Para habilitarlas, por favor cambia los permisos en la barra de direcciones de tu navegador.')
  }
}

const lanzarNotificacionPrueba = () => {
  if (typeof Notification !== 'undefined' && Notification.permission === 'granted') {
    new Notification('Plataforma Profesional', {
      body: '¡Notificaciones activadas con éxito! Recibirás recordatorios de turnos y confirmaciones aquí.',
      icon: '/pwa-192x192.png',
      badge: '/pwa-192x192.png',
      vibrate: [200, 100, 200]
    })
  }
}

const cargarNotificaciones = async () => {
  try {
    const res = await fetch('/api/auth/notificaciones', { headers: getAuthHeaders() })
    if (res.status === 401) {
      logout()
      return
    }
    if (res.ok) {
      const data = await res.json()
      const nuevasNotif = data.data || []
      // Si llegan notificaciones nuevas (mayor cantidad que antes), volvemos a mostrar la burbuja
      if (nuevasNotif.length > notificaciones.value.length) {
        bubbleHidden.value = false
      }
      notificaciones.value = nuevasNotif
    }
  } catch (err) {
    console.error('Error cargando notificaciones', err)
  }
}

watch(menuNotificaciones, (val) => {
  if (val) {
    bubbleHidden.value = true
  }
})

const marcarComoLeida = async (id) => {
  try {
    await fetch(`/api/auth/notificaciones/${id}/marcar-leida`, {
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
  cargarNotificaciones()
  pollingInterval = setInterval(cargarNotificaciones, 30000) // Poll cada 30 segundos
  
  // PWA listeners
  window.addEventListener('online', actualizarEstadoConexion)
  window.addEventListener('offline', actualizarEstadoConexion)
  window.addEventListener('beforeinstallprompt', capturarPromptInstalacion)
})

onUnmounted(() => {
  if (pollingInterval) clearInterval(pollingInterval)
  window.removeEventListener('online', actualizarEstadoConexion)
  window.removeEventListener('offline', actualizarEstadoConexion)
  window.removeEventListener('beforeinstallprompt', capturarPromptInstalacion)
})

const logout = async () => {
  dialogLogout.value = false
  await logoutServer((path) => router.push(path))
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
.logout-btn {
  border-color: rgba(255, 255, 255, 0.3) !important;
  color: rgba(255, 255, 255, 0.85) !important;
  transition: all 0.2s ease-in-out !important;
}
.logout-btn:hover {
  background-color: rgba(255, 255, 255, 0.15) !important;
  border-color: rgba(255, 255, 255, 0.9) !important;
  color: #ffffff !important;
}
</style>
