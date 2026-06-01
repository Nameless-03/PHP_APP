<template>
  <v-hover v-slot="{ isHovering, props: hoverProps }">
    <v-card
      v-bind="hoverProps"
      :elevation="isHovering ? 8 : 2"
      class="h-100 rounded-xl cursor-pointer transition-swing border-panel d-flex flex-column align-center text-center"
      :class="dense ? 'pa-4' : 'pa-6'"
      @click="$emit('click')"
    >
      <v-avatar :color="color" :size="dense ? 64 : 80" variant="tonal" class="mb-4">
        <v-icon :size="dense ? 32 : 40">{{ icon }}</v-icon>
      </v-avatar>
      <h2 class="text-h6 font-weight-bold mb-2 text-grey-darken-3">{{ title }}</h2>
      <p :class="dense ? 'text-caption text-medium-emphasis' : 'text-body-2 text-medium-emphasis'">
        {{ description }}
      </p>
    </v-card>
  </v-hover>
</template>

<script setup>
defineProps({
  title: {
    type: String,
    required: true
  },
  description: {
    type: String,
    required: true
  },
  icon: {
    type: String,
    required: true
  },
  color: {
    type: String,
    default: 'primary'
  },
  dense: {
    type: Boolean,
    default: false
  }
})

defineEmits(['click'])
</script>

<style scoped>
.border-panel {
  border: 1px solid rgba(140, 109, 70, 0.2) !important;
  transition: all 0.3s ease;
}

.border-panel:hover {
  border-color: rgba(140, 109, 70, 0.5) !important;
  box-shadow: 0 4px 12px rgba(140, 109, 70, 0.05) !important;
}
</style>
