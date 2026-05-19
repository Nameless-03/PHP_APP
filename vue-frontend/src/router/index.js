import { createRouter, createWebHistory } from 'vue-router'
import InicioSesionView from '../views/InicioSesionView.vue'
import RegistroView from '../views/RegistroView.vue'
import PanelPrincipalView from '../views/PanelPrincipalView.vue'
import PerfilProfesionalView from '../views/PerfilProfesionalView.vue'
import GestionServiciosView from '../views/GestionServiciosView.vue'
import BusquedaServiciosView from '../views/BusquedaServiciosView.vue'

import MisHorariosView from '../views/MisHorariosView.vue'
import MisReservasView from '../views/MisReservasView.vue'
import MiAgendaView from '../views/MiAgendaView.vue'

const router = createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),
  routes: [
    {
      path: '/',
      redirect: '/login'
    },
    {
      path: '/login',
      name: 'login',
      component: InicioSesionView
    },
    {
      path: '/register',
      name: 'register',
      component: RegistroView
    },
    {
      path: '/dashboard',
      name: 'dashboard',
      component: PanelPrincipalView,
      meta: { requiresAuth: true }
    },
    {
      path: '/profile',
      name: 'profile',
      component: PerfilProfesionalView,
      meta: { requiresAuth: true }
    },
    {
      path: '/services',
      name: 'services',
      component: GestionServiciosView,
      meta: { requiresAuth: true }
    },
    {
      path: '/buscar',
      name: 'search',
      component: BusquedaServiciosView,
      meta: { requiresAuth: true }
    },
    {
      path: '/mis-horarios',
      name: 'mis-horarios',
      component: MisHorariosView,
      meta: { requiresAuth: true }
    },
    {
      path: '/mis-reservas',
      name: 'mis-reservas',
      component: MisReservasView,
      meta: { requiresAuth: true }
    },
    {
      path: '/mi-agenda',
      name: 'mi-agenda',
      component: MiAgendaView,
      meta: { requiresAuth: true }
    }
  ]
})

// Guardia de navegación global (Navigation Guard) para proteger rutas
router.beforeEach((to, from, next) => {
  const isAuthenticated = !!localStorage.getItem('auth_token')

  if (to.matched.some(record => record.meta.requiresAuth)) {
    if (!isAuthenticated) {
      // Si la ruta requiere autenticación y no hay token, redirigir a login
      next({ name: 'login' })
    } else {
      next() // Permitir la navegación
    }
  } else if ((to.name === 'login' || to.name === 'register') && isAuthenticated) {
    // Si el usuario ya está autenticado e intenta ir a login o registro, redirigir a dashboard
    next({ name: 'dashboard' })
  } else {
    next() // En cualquier otro caso, continuar navegación
  }
})

export default router

