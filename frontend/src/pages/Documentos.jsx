import { useCallback, useEffect, useState } from 'react'
import api, { leerError } from '../api'
import { useAuth } from '../context/AuthContext'
import { descargar, mensajeErrorBlob } from '../descargar'

export default function Documentos() {
  const { esAdmin } = useAuth()
  const [lista, setLista] = useState([])
  const [pagina, setPagina] = useState(1)
  const [ultimaPagina, setUltimaPagina] = useState(1)
  const [buscar, setBuscar] = useState('')
  const [cargando, setCargando] = useState(false)
  const [aviso, setAviso] = useState(null)

  const [nombre, setNombre] = useState('')
  const [archivo, setArchivo] = useState(null)
  const [errores, setErrores] = useState({})
  const [subiendo, setSubiendo] = useState(false)

  const mostrar = (tipo, texto) => {
    setAviso({ tipo, texto })
    setTimeout(() => setAviso(null), 4000)
  }

  const cargar = useCallback(async () => {
    setCargando(true)
    try {
      const { data } = await api.get('/documentos', { params: { page: pagina, buscar } })
      setLista(data.data)
      setUltimaPagina(data.last_page)
    } catch (err) {
      mostrar('error', leerError(err).mensaje)
    } finally {
      setCargando(false)
    }
  }, [pagina, buscar])

  useEffect(() => {
    const t = setTimeout(cargar, 300)
    return () => clearTimeout(t)
  }, [cargar])

  const subir = async (e) => {
    e.preventDefault()
    setErrores({})
    setSubiendo(true)
    try {
      const datos = new FormData()
      datos.append('nombre', nombre)
      if (archivo) datos.append('archivo', archivo)
      const { data } = await api.post('/documentos', datos)
      mostrar('exito', data.message)
      setNombre('')
      setArchivo(null)
      e.target.reset()
      setPagina(1)
      cargar()
    } catch (err) {
      const { mensaje, errores } = leerError(err)
      setErrores(errores)
      if (!Object.keys(errores).length) mostrar('error', mensaje)
    } finally {
      setSubiendo(false)
    }
  }

  const bajar = async (d) => {
    try {
      await descargar(`/documentos/${d.id}/descargar`, {}, `${d.nombre}.${d.tipo}`)
    } catch (err) {
      mostrar('error', await mensajeErrorBlob(err))
    }
  }

  const eliminar = async (d) => {
    if (!window.confirm(`¿Eliminar el documento "${d.nombre}"?`)) return
    try {
      const { data } = await api.delete(`/documentos/${d.id}`)
      mostrar('exito', data.message)
      cargar()
    } catch (err) {
      mostrar('error', leerError(err).mensaje)
    }
  }

  return (
    <>
      <h2>Documentos</h2>
      <p className="subtitulo">Facturas, órdenes de compra y otros archivos del inventario (máx. 5 MB).</p>

      {aviso && <div className={`alerta alerta-${aviso.tipo}`}>{aviso.texto}</div>}

      <form className="tarjeta formulario-linea" onSubmit={subir} noValidate>
        <label>
          Nombre del documento
          <input value={nombre} onChange={(e) => setNombre(e.target.value)} placeholder="Ej. Factura proveedor noviembre" />
          {errores.nombre && <span className="error-campo">{errores.nombre[0]}</span>}
        </label>
        <label>
          Archivo
          <input type="file" onChange={(e) => setArchivo(e.target.files[0] ?? null)} />
          {errores.archivo && <span className="error-campo">{errores.archivo[0]}</span>}
        </label>
        <button className="btn btn-primario" disabled={subiendo}>
          {subiendo ? 'Subiendo…' : 'Subir documento'}
        </button>
      </form>

      <div className="filtros">
        <input
          placeholder="Buscar documento…"
          value={buscar}
          onChange={(e) => { setBuscar(e.target.value); setPagina(1) }}
        />
      </div>

      <div className="tarjeta tabla-contenedor">
        <table>
          <thead>
            <tr><th>Nombre</th><th>Tipo</th><th>Subido por</th><th>Fecha</th><th className="acciones">Acciones</th></tr>
          </thead>
          <tbody>
            {cargando && <tr><td colSpan="5" className="vacio">Cargando…</td></tr>}
            {!cargando && lista.length === 0 && (
              <tr><td colSpan="5" className="vacio">No hay documentos cargados.</td></tr>
            )}
            {!cargando && lista.map((d) => (
              <tr key={d.id}>
                <td>{d.nombre}</td>
                <td><span className="badge">{d.tipo}</span></td>
                <td>{d.usuario?.name}</td>
                <td>{new Date(d.created_at).toLocaleString('es-SV')}</td>
                <td className="acciones">
                  <button className="btn btn-chico" onClick={() => bajar(d)}>Descargar</button>
                  {esAdmin && (
                    <button className="btn btn-chico btn-peligro" onClick={() => eliminar(d)}>Eliminar</button>
                  )}
                </td>
              </tr>
            ))}
          </tbody>
        </table>
      </div>

      <div className="paginacion">
        <button className="btn btn-chico" disabled={pagina <= 1} onClick={() => setPagina(pagina - 1)}>← Anterior</button>
        <span>Página {pagina} de {ultimaPagina}</span>
        <button className="btn btn-chico" disabled={pagina >= ultimaPagina} onClick={() => setPagina(pagina + 1)}>Siguiente →</button>
      </div>
    </>
  )
}
