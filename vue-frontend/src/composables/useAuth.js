import { ref, computed } from 'vue'

const user = ref(null)
const token = ref(null)

const loadSession = () => {
  const tokenStr = localStorage.getItem('auth_token')
  const userStr = localStorage.getItem('user')
  token.value = tokenStr
  if (userStr) {
    try {
      user.value = JSON.parse(userStr)
    } catch (e) {
      console.error('Error parsing user storage in useAuth', e)
      user.value = null
    }
  } else {
    user.value = null
  }
}

// Initial load
if (typeof window !== 'undefined') {
  loadSession()
  window.addEventListener('user-updated', loadSession)
  window.addEventListener('storage', (e) => {
    if (e.key === 'auth_token' || e.key === 'user') {
      loadSession()
    }
  })
}

export function useAuth() {
  const isCliente = computed(() => user.value ? user.value.role !== 'profesional' : true)
  const isProfesional = computed(() => user.value ? user.value.role === 'profesional' : false)
  const isAdmin = computed(() => user.value ? user.value.role === 'admin' : false)
  
  const getAuthHeaders = () => ({
    'Content-Type': 'application/json',
    'Accept': 'application/json',
    'Authorization': token.value ? `Bearer ${token.value}` : `Bearer ${localStorage.getItem('auth_token')}`
  })

  const logoutLocal = () => {
    localStorage.removeItem('auth_token')
    localStorage.removeItem('user')
    user.value = null
    token.value = null
    window.dispatchEvent(new Event('user-updated'))
  }

  const logoutServer = async (routerRedirect = null) => {
    const currentToken = token.value || localStorage.getItem('auth_token')
    logoutLocal()
    if (routerRedirect) {
      routerRedirect('/login')
    }
    if (currentToken) {
      try {
        await fetch('http://localhost:8000/api/auth/logout', {
          method: 'POST',
          headers: {
            'Authorization': `Bearer ${currentToken}`,
            'Accept': 'application/json'
          }
        })
      } catch (e) {
        console.error('Error al revocar el token en el servidor:', e)
      }
    }
  }

  const userInitials = computed(() => {
    if (!user.value || !user.value.nombre) return 'US'
    return user.value.nombre.substring(0, 2).toUpperCase()
  })

  return {
    user,
    token,
    isCliente,
    isProfesional,
    isAdmin,
    userInitials,
    getAuthHeaders,
    logoutLocal,
    logoutServer,
    refreshSession: loadSession
  }
}
