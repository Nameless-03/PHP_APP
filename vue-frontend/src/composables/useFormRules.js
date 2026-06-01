export function useFormRules() {
  const required = (value) => !!value || 'Este campo es obligatorio.'

  const email = (value) => {
    if (!value) return true
    const pattern = /^(([^<>()[\]\\.,;:\s@"]+(\.[^<>()[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/
    return pattern.test(value) || 'Correo electrónico inválido.'
  }

  const isNumber = (value) => {
    if (!value) return true
    const pattern = /^\d+(\.\d{1,2})?$/
    return pattern.test(value) || 'Debe ser un número válido (ej: 150.00).'
  }

  const isInteger = (value) => {
    if (!value) return true
    const pattern = /^\d+$/
    return pattern.test(value) || 'Debe ser un número entero.'
  }

  const minOne = (value) => {
    if (!value) return true
    return parseInt(value) >= 1 || 'Debe ser al menos 1.'
  }

  const requiredArray = (value) => {
    return (Array.isArray(value) && value.length > 0) || 'Debes seleccionar al menos un elemento.'
  }

  return {
    required,
    email,
    isNumber,
    isInteger,
    minOne,
    requiredArray
  }
}
