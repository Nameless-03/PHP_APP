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
        <v-list-item v-if="isProfesional" prepend-icon="mdi-briefcase-edit" title="Mis Servicios" value="services" to="/services"></v-list-item>
        <v-list-item v-if="isProfesional" prepend-icon="mdi-calendar-clock" title="Mis Horarios" value="schedule" to="/mis-horarios"></v-list-item>
        <v-list-item prepend-icon="mdi-calendar-check" title="Mis Reservas" value="reservas" to="/mis-reservas"></v-list-item>
        <v-list-item prepend-icon="mdi-calendar-multiselect" title="Mi Agenda" value="agenda" to="/mi-agenda"></v-list-item>
      </v-list>

      <template v-slot:append>
        <div class="pa-2">
          <v-btn block color="error" variant="tonal" prepend-icon="mdi-logout" @click="logout" v-show="!rail">
            Cerrar Sesión
          </v-btn>
          <v-btn icon color="error" variant="tonal" @click="logout" v-show="rail" class="mx-auto d-flex">
            <v-icon>mdi-logout</v-icon>
          </v-btn>
        </div>
      </template>
    </v-navigation-drawer>

    <v-main>
      <v-app-bar elevation="0" class="bg-transparent px-4 mt-2">
        <v-app-bar-title class="text-h5 font-weight-bold text-grey-darken-3">{{ title }}</v-app-bar-title>
        <v-spacer></v-spacer>
        <v-btn icon>
          <v-badge dot color="error">
            <v-icon color="grey-darken-2">mdi-bell-outline</v-icon>
          </v-badge>
        </v-btn>
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

import { onMounted } from 'vue'

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
</style>
