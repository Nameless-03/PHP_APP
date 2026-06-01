<template>
  <v-card 
    class="rounded-xl elevation-2 bg-gradient text-white" 
    :class="cardClass"
  >
    <div class="d-flex align-center flex-wrap" :class="{ 'justify-center text-center': centered }">
      <v-avatar 
        v-if="icon" 
        color="white" 
        size="64" 
        :class="{ 'mr-6 mb-4 mb-sm-0': !centered, 'mb-4': centered }"
        class="elevation-2 text-primary font-weight-black"
      >
        <v-icon size="36" color="primary">{{ icon }}</v-icon>
      </v-avatar>
      <div :class="{ 'text-center': centered, 'flex-grow-1': !centered }">
        <h1 :class="titleClass">{{ title }}</h1>
        <p :class="subtitleClass">
          {{ subtitle }}
        </p>
      </div>
    </div>
  </v-card>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
  title: {
    type: String,
    required: true
  },
  subtitle: {
    type: String,
    default: ''
  },
  icon: {
    type: String,
    default: ''
  },
  centered: {
    type: Boolean,
    default: false
  },
  dense: {
    type: Boolean,
    default: false
  }
})

const cardClass = computed(() => {
  return props.dense ? 'pa-6' : 'pa-8'
})

const titleClass = computed(() => {
  return props.dense ? 'text-h5 font-weight-bold mb-0' : 'text-h4 font-weight-bold mb-2'
})

const subtitleClass = computed(() => {
  return props.dense ? 'text-subtitle-2 opacity-90 mb-0' : 'text-body-1 opacity-80 mb-0'
})
</script>

<style scoped>
.bg-gradient {
  background: linear-gradient(135deg, #8C6D46 0%, #A6987A 100%) !important;
}
</style>
