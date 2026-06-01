import { ref } from 'vue'

export function useSnackbar() {
  const show = ref(false)
  const text = ref('')
  const color = ref('success')

  const showSnackbar = (msg, col = 'success') => {
    text.value = msg
    color.value = col
    show.value = true
  }

  return {
    show,
    text,
    color,
    showSnackbar
  }
}
