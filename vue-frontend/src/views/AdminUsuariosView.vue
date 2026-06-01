<template>
  <DashboardLayout title="Gestionar Usuarios">
    <!-- Header visual -->
    <v-row class="mb-6">
      <v-col cols="12">
        <v-card class="pa-8 rounded-xl elevation-2 bg-gradient text-white">
          <div class="d-flex align-center flex-wrap">
            <v-avatar color="white" size="64" class="mr-6 elevation-2 text-primary font-weight-black">
              <v-icon size="36" color="primary">mdi-shield-account</v-icon>
            </v-avatar>
            <div>
              <h1 class="text-h4 font-weight-bold mb-2">Administración de Usuarios</h1>
              <p class="text-body-1 opacity-80 mb-0">
                Supervisa y gestiona las cuentas de todos los clientes, profesionales y administradores del sistema. Activa, desactiva o elimina perfiles de forma centralizada.
              </p>
            </div>
          </div>
        </v-card>
      </v-col>
    </v-row>

    <!-- Search & Filters -->
    <v-row class="mb-4">
      <v-col cols="12" md="6">
        <v-text-field
          v-model="search"
          variant="outlined"
          density="comfortable"
          prepend-inner-icon="mdi-magnify"
          label="Buscar por nombre o correo..."
          color="primary"
          bg-color="white"
          hide-details
          clearable
          class="rounded-lg"
        ></v-text-field>
      </v-col>
      <v-col cols="12" md="3">
        <v-select
          v-model="filterRole"
          variant="outlined"
          density="comfortable"
          label="Filtrar por rol"
          :items="roleOptions"
          item-title="text"
          item-value="value"
          color="primary"
          bg-color="white"
          hide-details
          clearable
          class="rounded-lg"
        ></v-select>
      </v-col>
      <v-col cols="12" md="3">
        <v-select
          v-model="filterStatus"
          variant="outlined"
          density="comfortable"
          label="Estado"
          :items="statusOptions"
          item-title="text"
          item-value="value"
          color="primary"
          bg-color="white"
          hide-details
          clearable
          class="rounded-lg"
        ></v-select>
      </v-col>
    </v-row>

    <!-- Stats Summary -->
    <v-row class="mb-4">
      <v-col cols="12" sm="4">
        <v-card class="pa-4 rounded-xl elevation-1 text-center">
          <div class="text-h4 font-weight-bold text-primary">{{ allUsers.length }}</div>
          <div class="text-body-2 text-medium-emphasis">Total registrados</div>
        </v-card>
      </v-col>
      <v-col cols="12" sm="4">
        <v-card class="pa-4 rounded-xl elevation-1 text-center">
          <div class="text-h4 font-weight-bold text-success">{{ allUsers.filter(u => u.activo).length }}</div>
          <div class="text-body-2 text-medium-emphasis">Activos</div>
        </v-card>
      </v-col>
      <v-col cols="12" sm="4">
        <v-card class="pa-4 rounded-xl elevation-1 text-center">
          <div class="text-h4 font-weight-bold text-error">{{ allUsers.filter(u => !u.activo).length }}</div>
          <div class="text-body-2 text-medium-emphasis">Inactivos</div>
        </v-card>
      </v-col>
    </v-row>

    <!-- Users Table -->
    <v-card class="rounded-xl elevation-1">
      <v-data-table
        :headers="headers"
        :items="filteredUsers"
        :search="search"
        :loading="loading"
        loading-text="Cargando usuarios..."
        no-data-text="No se encontraron usuarios"
        items-per-page="10"
        class="rounded-xl"
        hover
      >
        <!-- Role chip -->
        <template v-slot:item.role="{ item }">
          <v-chip
            :color="getRoleColor(item.role)"
            size="small"
            variant="flat"
            class="font-weight-bold text-uppercase"
          >
            {{ getRoleLabel(item.role) }}
          </v-chip>
        </template>

        <!-- Status toggle -->
        <template v-slot:item.activo="{ item }">
          <v-switch
            :model-value="item.activo"
            color="success"
            hide-details
            density="compact"
            :disabled="item.role === 'admin'"
            @update:model-value="toggleActivo(item)"
          ></v-switch>
        </template>

        <!-- Date formatted -->
        <template v-slot:item.fecha_registro="{ item }">
          <span class="text-body-2">{{ formatDate(item.fecha_registro) }}</span>
        </template>

        <!-- Actions -->
        <template v-slot:item.actions="{ item }">
          <v-btn
            icon
            variant="text"
            color="error"
            size="small"
            :disabled="item.role === 'admin'"
            @click="confirmDelete(item)"
          >
            <v-icon>mdi-delete-outline</v-icon>
            <v-tooltip activator="parent" location="top">Eliminar usuario</v-tooltip>
          </v-btn>
        </template>
      </v-data-table>
    </v-card>

    <!-- Delete confirmation dialog -->
    <v-dialog v-model="deleteDialog" max-width="450" persistent>
      <v-card class="rounded-xl pa-2">
        <v-card-title class="text-h6 font-weight-bold d-flex align-center">
          <v-icon color="error" class="mr-2">mdi-alert-circle</v-icon>
          Confirmar eliminación
        </v-card-title>
        <v-card-text>
          ¿Estás seguro de que deseas eliminar al usuario <strong>{{ userToDelete?.nombre }}</strong> ({{ userToDelete?.email }})? Esta acción no se puede deshacer fácilmente.
        </v-card-text>
        <v-card-actions>
          <v-spacer></v-spacer>
          <v-btn variant="text" @click="deleteDialog = false">Cancelar</v-btn>
          <v-btn color="error" variant="flat" @click="deleteUser" :loading="deleting">Eliminar</v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>

    <!-- Snackbar for feedback -->
    <v-snackbar v-model="snackbar.show" :color="snackbar.color" :timeout="3000" location="bottom right">
      {{ snackbar.text }}
      <template v-slot:actions>
        <v-btn variant="text" @click="snackbar.show = false">Cerrar</v-btn>
      </template>
    </v-snackbar>
  </DashboardLayout>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import DashboardLayout from '../components/DashboardLayout.vue'

const loading = ref(true)
const allUsers = ref([])
const search = ref('')
const filterRole = ref(null)
const filterStatus = ref(null)
const deleteDialog = ref(false)
const userToDelete = ref(null)
const deleting = ref(false)

const snackbar = ref({
  show: false,
  text: '',
  color: 'success'
})

const roleOptions = [
  { text: 'Cliente', value: 'cliente' },
  { text: 'Profesional', value: 'profesional' },
  { text: 'Admin', value: 'admin' },
]

const statusOptions = [
  { text: 'Activo', value: true },
  { text: 'Inactivo', value: false },
]

const headers = [
  { title: 'ID', key: 'id', width: '70px' },
  { title: 'Nombre', key: 'nombre' },
  { title: 'Email', key: 'email' },
  { title: 'Rol', key: 'role', width: '130px' },
  { title: 'Fecha Registro', key: 'fecha_registro', width: '160px' },
  { title: 'Estado', key: 'activo', width: '100px', sortable: false },
  { title: 'Acciones', key: 'actions', width: '100px', sortable: false },
]

const getAuthHeaders = () => ({
  'Content-Type': 'application/json',
  'Accept': 'application/json',
  'Authorization': `Bearer ${localStorage.getItem('auth_token')}`
})

const filteredUsers = computed(() => {
  let result = allUsers.value
  if (filterRole.value) {
    result = result.filter(u => u.role === filterRole.value)
  }
  if (filterStatus.value !== null && filterStatus.value !== undefined) {
    result = result.filter(u => u.activo === filterStatus.value)
  }
  return result
})

const getRoleColor = (role) => {
  const map = { 'cliente': 'blue', 'profesional': 'orange', 'admin': 'deep-purple' }
  return map[role] || 'grey'
}

const getRoleLabel = (role) => {
  const map = { 'cliente': 'Cliente', 'profesional': 'Profesional', 'admin': 'Admin' }
  return map[role] || role
}

const formatDate = (dateStr) => {
  if (!dateStr) return 'N/A'
  const d = new Date(dateStr)
  return d.toLocaleDateString('es-PE', { year: 'numeric', month: 'short', day: 'numeric' })
}

const fetchUsers = async () => {
  loading.value = true
  try {
    const res = await fetch('http://localhost:8000/api/usuarios', { headers: getAuthHeaders() })
    if (res.ok) {
      const data = await res.json()
      allUsers.value = data.data || []
    } else {
      showSnackbar('Error al cargar usuarios', 'error')
    }
  } catch (err) {
    console.error('Error fetching users:', err)
    showSnackbar('Error de conexión al servidor', 'error')
  } finally {
    loading.value = false
  }
}

const toggleActivo = async (user) => {
  const newStatus = !user.activo
  try {
    const res = await fetch(`http://localhost:8000/api/usuarios/${user.id}`, {
      method: 'PUT',
      headers: getAuthHeaders(),
      body: JSON.stringify({ activo: newStatus })
    })
    if (res.ok) {
      user.activo = newStatus
      showSnackbar(
        newStatus ? `${user.nombre} ha sido activado` : `${user.nombre} ha sido desactivado`,
        newStatus ? 'success' : 'warning'
      )
    } else {
      showSnackbar('Error al actualizar estado', 'error')
    }
  } catch (err) {
    console.error('Error toggling user:', err)
    showSnackbar('Error de conexión', 'error')
  }
}

const confirmDelete = (user) => {
  userToDelete.value = user
  deleteDialog.value = true
}

const deleteUser = async () => {
  if (!userToDelete.value) return
  deleting.value = true
  try {
    const res = await fetch(`http://localhost:8000/api/usuarios/${userToDelete.value.id}`, {
      method: 'DELETE',
      headers: getAuthHeaders()
    })
    if (res.ok || res.status === 204) {
      allUsers.value = allUsers.value.filter(u => u.id !== userToDelete.value.id)
      showSnackbar(`Usuario ${userToDelete.value.nombre} eliminado correctamente`, 'success')
    } else {
      showSnackbar('Error al eliminar usuario', 'error')
    }
  } catch (err) {
    console.error('Error deleting user:', err)
    showSnackbar('Error de conexión', 'error')
  } finally {
    deleting.value = false
    deleteDialog.value = false
    userToDelete.value = null
  }
}

const showSnackbar = (text, color = 'success') => {
  snackbar.value = { show: true, text, color }
}

onMounted(() => {
  fetchUsers()
})
</script>

<style scoped>
.bg-gradient {
  background: linear-gradient(135deg, #8C6D46 0%, #A6987A 100%);
}
</style>
