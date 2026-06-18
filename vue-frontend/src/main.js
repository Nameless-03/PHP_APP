import { createApp } from 'vue'
import App from './App.vue'
import router from './router'
import vuetify from './plugins/vuetify'

// Parche global para redirigir búsquedas de auth_token y user en localStorage a sessionStorage si no están presentes
if (typeof window !== 'undefined') {
  const originalGet = localStorage.getItem;
  localStorage.getItem = function (key) {
    let val = originalGet.call(localStorage, key);
    if (val === null && (key === 'auth_token' || key === 'user')) {
      val = sessionStorage.getItem(key);
    }
    return val;
  };
}

const app = createApp(App)

app.use(router)
app.use(vuetify)

app.mount('#app')
