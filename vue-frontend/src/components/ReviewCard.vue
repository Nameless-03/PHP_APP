<template>
  <v-card class="rounded-xl border pa-5 h-100 d-flex flex-column" elevation="1">
    <div class="d-flex justify-space-between align-center mb-3">
      <div>
        <span class="font-weight-bold text-subtitle-1 text-grey-darken-3 d-block">{{ clienteNombre }}</span>
        <span class="text-caption text-medium-emphasis d-block">
          {{ formattedDate }}
        </span>
      </div>
      <v-rating
        :model-value="puntuacion"
        color="warning"
        active-color="warning"
        density="compact"
        readonly
      ></v-rating>
    </div>
    <p class="text-body-2 text-medium-emphasis italic font-weight-medium flex-grow-1 mb-0">
      "{{ comentario }}"
    </p>
  </v-card>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
  clienteNombre: {
    type: String,
    required: true
  },
  fecha: {
    type: [String, Date],
    required: true
  },
  puntuacion: {
    type: Number,
    required: true
  },
  comentario: {
    type: String,
    required: true
  }
})

const formattedDate = computed(() => {
  if (!props.fecha) return ''
  return new Date(props.fecha).toLocaleDateString('es-ES', { 
    day: '2-digit', 
    month: '2-digit', 
    year: 'numeric' 
  })
})
</script>

<style scoped>
.italic {
  font-style: italic;
}
.border {
  border: 1px solid rgba(0, 0, 0, 0.08) !important;
}
</style>
