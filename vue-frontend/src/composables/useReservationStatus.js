export function useReservationStatus() {
  const getEstadoColor = (estado) => {
    const map = {
      pendiente:   'warning',
      confirmada:  'success',
      pagada:      'primary',
      en_curso:    'info',
      finalizada:  'grey',
      cancelada:   'error',
      no_asistida: 'grey'
    }
    return map[estado] || 'grey'
  }

  const getEstadoIcon = (estado) => {
    const map = {
      pendiente:   'mdi-clock-outline',
      confirmada:  'mdi-check-circle-outline',
      pagada:      'mdi-cash-check',
      en_curso:    'mdi-play-circle-outline',
      finalizada:  'mdi-flag-checkered',
      cancelada:   'mdi-cancel',
      no_asistida: 'mdi-account-off-outline',
    }
    return map[estado] || 'mdi-help-circle-outline'
  }

  const getEstadoLabel = (estado) => {
    const map = {
      pendiente:   'Pendiente',
      confirmada:  'Confirmada',
      pagada:      'Pagada',
      en_curso:    'En Curso',
      finalizada:  'Finalizada',
      cancelada:   'Cancelada',
      no_asistida: 'No Asistida'
    }
    return map[estado] || estado
  }

  return {
    getEstadoColor,
    getEstadoIcon,
    getEstadoLabel
  }
}
