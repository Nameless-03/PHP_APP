import { createRouter, createWebHistory } from 'vue-router'
import InicioSesionView from '../views/InicioSesionView.vue'
import RegistroView from '../views/RegistroView.vue'
import PanelPrincipalView from '../views/PanelPrincipalView.vue'
import PerfilProfesionalView from '../views/PerfilProfesionalView.vue'
import GestionServiciosView from '../views/GestionServiciosView.vue'
import BusquedaServiciosView from '../views/BusquedaServiciosView.vue'

import MisHorariosView from '../views/MisHorariosView.vue'

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
      component: PanelPrincipalView
    },
    {
      path: '/profile',
      name: 'profile',
      component: PerfilProfesionalView
    },
    {
      path: '/services',
      name: 'services',
      component: GestionServiciosView
    },
    {
      path: '/buscar',
      name: 'search',
      component: BusquedaServiciosView
    },
    {
      path: '/mis-horarios',
      name: 'mis-horarios',
      component: MisHorariosView
    },
  ]
})

export default router
