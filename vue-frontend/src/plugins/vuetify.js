import 'vuetify/styles'
import { createVuetify } from 'vuetify'
import * as components from 'vuetify/components'
import * as directives from 'vuetify/directives'
import '@mdi/font/css/materialdesignicons.css'

const myCustomTheme = {
  dark: false,
  colors: {
    background: '#FDFBF5',
    surface: '#FFFFFF',
    primary: '#D4C5A3', // Un beige principal
    secondary: '#8C6D46', // Un marrón cálido como secundario
    accent: '#A6987A', // Un tono intermedio para acentos
    error: '#B71C1C',
    info: '#2196F3',
    success: '#4CAF50',
    warning: '#FB8C00',
  },
}

export default createVuetify({
  components,
  directives,
  theme: {
    defaultTheme: 'myCustomTheme',
    themes: {
      myCustomTheme,
    },
  },
})
