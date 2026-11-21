import api from './api'

/**
 * Descarga un archivo desde la API enviando el token (un simple <a href> no lo enviaría).
 * Si no se indica nombre, usa el que manda el servidor en Content-Disposition.
 */
export async function descargar(url, params = {}, nombreSugerido) {
  const res = await api.get(url, { params, responseType: 'blob' })

  let nombre = nombreSugerido
  const cabecera = res.headers['content-disposition']
  const match = cabecera && /filename\*?=(?:UTF-8'')?"?([^";]+)"?/i.exec(cabecera)
  if (match) nombre = decodeURIComponent(match[1])

  const enlace = document.createElement('a')
  enlace.href = URL.createObjectURL(res.data)
  enlace.download = nombre || 'archivo'
  document.body.appendChild(enlace)
  enlace.click()
  enlace.remove()
  URL.revokeObjectURL(enlace.href)
}

// Cuando la respuesta es un blob, el error JSON también llega como blob: lo convertimos a texto.
export async function mensajeErrorBlob(error) {
  const data = error.response?.data
  if (data instanceof Blob) {
    try {
      const json = JSON.parse(await data.text())
      return json.message || 'No se pudo descargar el archivo.'
    } catch {
      return 'No se pudo descargar el archivo.'
    }
  }
  return error.response?.data?.message || 'No se pudo conectar con el servidor.'
}
