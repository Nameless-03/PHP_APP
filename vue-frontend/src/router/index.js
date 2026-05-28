import { createRouter, createWebHistory } from 'vue-router'
import InicioSesionView from '../views/InicioSesionView.vue'
import RegistroView from '../views/RegistroView.vue'
import PanelPrincipalView from '../views/PanelPrincipalView.vue'
import PerfilProfesionalView from '../views/PerfilProfesionalView.vue'
import GestionServiciosView from '../views/GestionServiciosView.vue'
import GestionPaquetesView from '../views/GestionPaquetesView.vue'
import BusquedaServiciosView from '../views/BusquedaServiciosView.vue'
import ComprarPaquetesView from '../views/ComprarPaquetesView.vue'
import MisPaquetesView from '../views/MisPaquetesView.vue'
import VideollamadaView from '../views/VideollamadaView.vue'

import MisHorariosView from '../views/MisHorariosView.vue'
import MisReservasView from '../views/MisReservasView.vue'
import MiAgendaView from '../views/MiAgendaView.vue'

import AdminUsuariosView from '../views/AdminUsuariosView.vue'
import AdminMonitoreoView from '../views/AdminMonitoreoView.vue'

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
      path: '/packages',
      name: 'packages',
      component: GestionPaquetesView,
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
    },
    {
      path: '/comprar-paquetes',
      name: 'comprar-paquetes',
      component: ComprarPaquetesView,
      meta: { requiresAuth: true }
    },
    {
      path: '/videollamada/:id',
      name: 'videollamada',
      component: VideollamadaView,
      meta: { requiresAuth: true }
    },
    {
      path: '/mis-paquetes',
      name: 'mis-paquetes',
      component: MisPaquetesView,
      meta: { requiresAuth: true }
    },
    // --- Admin Routes ---
    {
      path: '/admin/users',
      name: 'admin-users',
      component: AdminUsuariosView,
      meta: { requiresAuth: true, requiresAdmin: true }
    },
    {
      path: '/admin/system',
      name: 'admin-system',
      component: AdminMonitoreoView,
      meta: { requiresAuth: true, requiresAdmin: true }
    }
  ]
})

// Guardia de navegación global (Navigation Guard) para proteger rutas
router.beforeEach((to, from, next) => {
  const isAuthenticated = !!localStorage.getItem('auth_token')

  // Obtener rol del usuario
  let userRole = null
  const userStr = localStorage.getItem('user')
  if (userStr) {
    try {
      const user = JSON.parse(userStr)
      userRole = user.role
    } catch (e) {}
  }

  if (to.matched.some(record => record.meta.requiresAuth)) {
    if (!isAuthenticated) {
      // Si la ruta requiere autenticación y no hay token, redirigir a login
      next({ name: 'login' })
    } else if (to.matched.some(record => record.meta.requiresAdmin) && userRole !== 'admin') {
      // Si la ruta requiere admin y el usuario no lo es, redirigir al dashboard
      next({ name: 'dashboard' })
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
