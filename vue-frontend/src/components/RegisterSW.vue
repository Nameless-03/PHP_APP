<template>
  <div>
    <!-- Snackbar para indicar que la app está lista para usarse offline -->
    <v-snackbar
      v-model="showOfflineReady"
      color="success"
      location="bottom right"
      :timeout="4000"
    >
      <div class="d-flex align-center">
        <v-icon class="mr-2">mdi-cloud-check</v-icon>
        <span>Aplicación lista para funcionar sin conexión.</span>
      </div>
      <template v-slot:actions>
        <v-btn variant="text" @click="showOfflineReady = false">Cerrar</v-btn>
      </template>
    </v-snackbar>

    <!-- Snackbar para actualizar el Service Worker -->
    <v-snackbar
      v-model="showNeedRefresh"
      color="primary"
      location="bottom right"
      :timeout="-1"
    >
      <div class="d-flex align-center">
        <v-icon class="mr-2">mdi-cached</v-icon>
        <span>Nueva versión disponible. ¿Deseas actualizar ahora?</span>
      </div>
      <template v-slot:actions>
        <v-btn color="white" variant="flat" class="text-primary mr-2" @click="refreshApp">
          Actualizar
        </v-btn>
        <v-btn variant="text" @click="showNeedRefresh = false">Cerrar</v-btn>
      </template>
    </v-snackbar>
  </div>
</template>

<script setup>
import { ref, watch } from 'vue'
import { useRegisterSW } from 'virtual:pwa-register/vue'

const { offlineReady, needRefresh, updateServiceWorker } = useRegisterSW()

const showOfflineReady = ref(false)
const showNeedRefresh = ref(false)

watch(offlineReady, (val) => {
  if (val) {
    showOfflineReady.value = true
  }
})

watch(needRefresh, (val) => {
  if (val) {
    showNeedRefresh.value = true
  }
})

const refreshApp = () => {
  updateServiceWorker(true)
}
</script>
