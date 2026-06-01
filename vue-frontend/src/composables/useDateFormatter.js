export function useDateFormatter() {
  const formatDate = (dateString) => {
    if (!dateString) return 'N/A'
    const options = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' };
    const d = new Date(dateString + 'T00:00:00'); // Evita problemas de timezone
    return d.toLocaleDateString('es-ES', options).replace(/^\w/, c => c.toUpperCase());
  }

  const formatDateObj = (ds) => {
    if (!ds) return 'N/A'
    return new Date(ds).toLocaleString('es-ES', { 
      weekday: 'long', 
      year: 'numeric', 
      month: 'short', 
      day: 'numeric', 
      hour: '2-digit', 
      minute: '2-digit' 
    })
  }

  const formatDateShort = (ds) => {
    if (!ds) return 'N/A'
    return new Date(ds).toLocaleString('es-ES', { 
      day: '2-digit', 
      month: '2-digit', 
      year: 'numeric', 
      hour: '2-digit', 
      minute: '2-digit' 
    })
  }

  const formatDateTime = (dateStr) => {
    if (!dateStr) return 'N/A'
    return new Date(dateStr).toLocaleDateString('es-PE', {
      year: 'numeric', month: 'short', day: 'numeric',
      hour: '2-digit', minute: '2-digit',
    })
  }

  const formatDateLong = (dateObj) => {
    if (!dateObj) return ''
    const options = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' }
    return new Date(dateObj).toLocaleDateString('es-ES', options)
  }

  const formatTime = (dateStr) => {
    if (!dateStr) return ''
    return new Date(dateStr).toLocaleTimeString('es-ES', { hour: '2-digit', minute: '2-digit' })
  }

  return {
    formatDate,
    formatDateObj,
    formatDateShort,
    formatDateTime,
    formatDateLong,
    formatTime
  }
}
