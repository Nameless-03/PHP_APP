<template>
  <v-dialog :model-value="modelValue" @update:model-value="$emit('update:modelValue', $event)" max-width="450" persistent>
    <v-card class="rounded-xl pa-4">
      <v-card-title class="text-h6 font-weight-bold d-flex align-center">
        <v-icon :color="confirmColor" class="mr-2">{{ icon }}</v-icon>
        {{ title }}
      </v-card-title>
      <v-card-text class="text-body-1 py-2">
        <slot>{{ message }}</slot>
      </v-card-text>
      <v-card-actions class="justify-end pt-4">
        <v-btn variant="outlined" color="grey-darken-1" class="text-none font-weight-bold px-4" @click="$emit('update:modelValue', false)">
          {{ cancelText }}
        </v-btn>
        <v-btn :color="confirmColor" class="text-none font-weight-bold px-6 elevation-1 text-white" :loading="loading" @click="$emit('confirm')">
          {{ confirmText }}
        </v-btn>
      </v-card-actions>
    </v-card>
  </v-dialog>
</template>

<script setup>
defineProps({
  modelValue: {
    type: Boolean,
    required: true
  },
  title: {
    type: String,
    required: true
  },
  message: {
    type: String,
    default: ''
  },
  confirmText: {
    type: String,
    default: 'Confirmar'
  },
  cancelText: {
    type: String,
    default: 'Cancelar'
  },
  confirmColor: {
    type: String,
    default: 'error'
  },
  icon: {
    type: String,
    default: 'mdi-alert-circle'
  },
  loading: {
    type: Boolean,
    default: false
  }
})

defineEmits(['update:modelValue', 'confirm'])
</script>
