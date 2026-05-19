<template>
  <v-app-bar color="primary" elevation="2">
    <v-app-bar-title class="font-weight-bold">Business Dashboard</v-app-bar-title>
    <v-spacer></v-spacer>
    <v-btn icon @click="logout">
      <v-icon>mdi-logout</v-icon>
    </v-btn>
  </v-app-bar>

  <v-main class="bg-background">
    <v-container class="mt-8">
      <v-row>
        <v-col cols="12">
          <v-card class="pa-8 rounded-xl elevation-2 text-center">
            <v-icon size="64" color="success" class="mb-4">mdi-check-decagram</v-icon>
            <h1 class="text-h4 font-weight-bold mb-2">¡Inicio de sesión exitoso!</h1>
            <p class="text-body-1 text-medium-emphasis">
              Bienvenido al sistema. Esta es la vista protegida conectada a la API de Laravel.
            </p>
          </v-card>
        </v-col>
      </v-row>
    </v-container>
  </v-main>
</template>

<script setup>
import { useRouter } from 'vue-router'

const router = useRouter()

const logout = () => {
  // Clear token, call API /logout, etc.
  router.push('/login')
}
</script>
