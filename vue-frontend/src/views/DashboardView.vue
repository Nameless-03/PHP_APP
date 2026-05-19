<template>
  <DashboardLayout title="Resumen de Actividad">
    <!-- Dashboard Content -->
    <v-row>
      <!-- Welcome Card -->
      <v-col cols="12">
        <v-card class="pa-8 rounded-xl elevation-2 bg-gradient text-white">
          <v-row align="center">
            <v-col cols="12" md="8">
              <h1 class="text-h4 font-weight-bold mb-2">¡Hola, Profesional! 👋</h1>
              <p class="text-body-1 opacity-80 mb-6">
                Bienvenido a tu panel de control. Desde aquí puedes gestionar tu perfil, añadir nuevos servicios y ver tus estadísticas recientes.
              </p>
              <v-btn color="white" class="text-primary font-weight-bold rounded-lg px-6" elevation="2" to="/profile">
                Completar Perfil
              </v-btn>
            </v-col>
            <v-col cols="12" md="4" class="d-none d-md-flex justify-center">
              <v-icon size="120" color="white" class="opacity-50">mdi-chart-bar</v-icon>
            </v-col>
          </v-row>
        </v-card>
      </v-col>
    </v-row>

    <!-- Stats Cards -->
    <v-row class="mt-4">
      <v-col cols="12" sm="4">
        <v-card class="pa-6 rounded-xl elevation-1 card-hover">
          <div class="d-flex align-center justify-space-between mb-4">
            <div class="text-subtitle-1 text-medium-emphasis font-weight-medium">Servicios Activos</div>
            <v-avatar color="primary-lighten-4" size="48">
              <v-icon color="primary">mdi-briefcase-check</v-icon>
            </v-avatar>
          </div>
          <div class="text-h3 font-weight-bold text-grey-darken-4">3</div>
        </v-card>
      </v-col>
      <v-col cols="12" sm="4">
        <v-card class="pa-6 rounded-xl elevation-1 card-hover">
          <div class="d-flex align-center justify-space-between mb-4">
            <div class="text-subtitle-1 text-medium-emphasis font-weight-medium">Vistas del Perfil</div>
            <v-avatar color="info-lighten-4" size="48">
              <v-icon color="info">mdi-eye</v-icon>
            </v-avatar>
          </div>
          <div class="text-h3 font-weight-bold text-grey-darken-4">124</div>
        </v-card>
      </v-col>
      <v-col cols="12" sm="4">
        <v-card class="pa-6 rounded-xl elevation-1 card-hover">
          <div class="d-flex align-center justify-space-between mb-4">
            <div class="text-subtitle-1 text-medium-emphasis font-weight-medium">Calificación Promedio</div>
            <v-avatar color="warning-lighten-4" size="48">
              <v-icon color="warning">mdi-star</v-icon>
            </v-avatar>
          </div>
          <div class="text-h3 font-weight-bold text-grey-darken-4">4.8</div>
        </v-card>
      </v-col>
    </v-row>
  </DashboardLayout>
</template>

<script setup>
import DashboardLayout from '../components/DashboardLayout.vue'
</script>

<style scoped>
.bg-gradient {
  background: linear-gradient(135deg, #4F46E5 0%, #4338CA 100%);
}

.card-hover {
  transition: transform 0.2s, box-shadow 0.2s;
}
.card-hover:hover {
  transform: translateY(-4px);
  box-shadow: 0 10px 25px rgba(0,0,0,0.08) !important;
}
</style>
