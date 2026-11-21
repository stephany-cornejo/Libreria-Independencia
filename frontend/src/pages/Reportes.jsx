import { useCallback, useEffect, useMemo, useState } from 'react'
import { Bar, BarChart, CartesianGrid, Legend, ResponsiveContainer, Tooltip, XAxis, YAxis } from 'recharts'
import api, { leerError } from '../api'
import { descargar, mensajeErrorBlob } from '../descargar'

const dinero = (n) => `$${Number(n).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`

export default function Reportes() {
  const [tab, setTab] = useState('movimientos') // 'movimientos' | 'inventario'
  const [productos, setProductos] = useState([])
  const [categorias, setCategorias] = useState([])

  // Filtros de movimientos
  const [desde, setDesde] = useState('')
  const [hasta, setHasta] = useState('')
  const [productoId, setProductoId] = useState('')
  const [tipo, setTipo] = useState('')

  // Filtros de inventario
  const [categoriaId, setCategoriaId] = useState('')
  const [estado, setEstado] = useState('')
  const [buscar, setBuscar] = useState('')

  const [reporte, setReporte] = useState({ resumen: null, filas: [] })
  const [cargando, setCargando] = useState(false)
  const [aviso, setAviso] = useState(null)

  // Listas para los selects (productos y categorías de los módulos de los otros integrantes)
  useEffect(() => {
    api.get('/productos', { params: { per_page: 500 } })
      .then(({ data }) => setProductos(data.data ?? data))
      .catch(() => setProductos([]))
    api.get('/categorias', { params: { per_page: 500 } })
      .then(({ data }) => setCategorias(data.data ?? data))
      .catch(() => setCategorias([]))
  }, [])

  const params = useMemo(
    () =>
      tab === 'movimientos'
        ? { desde, hasta, producto_id: productoId, tipo }
        : { categoria_id: categoriaId, estado, buscar },
    [tab, desde, hasta, productoId, tipo, categoriaId, estado, buscar]
  )

  const cargar = useCallback(async () => {
    setCargando(true)
    setAviso(null)
    try {
      const { data } = await api.get(`/reportes/${tab}`, { params })
      setReporte(data)
    } catch (err) {
      setReporte({ resumen: null, filas: [] })
      setAviso({ tipo: 'error', texto: leerError(err).mensaje })
    } finally {
      setCargando(false)
    }
  }, [tab, params])

  useEffect(() => {
    const t = setTimeout(cargar, 300)
    return () => clearTimeout(t)
  }, [cargar])

  const exportar = async (formato) => {
    try {
      await descargar(`/reportes/${tab}/${formato}`, params, `reporte-${tab}.${formato === 'pdf' ? 'pdf' : 'xlsx'}`)
    } catch (err) {
      setAviso({ tipo: 'error', texto: await mensajeErrorBlob(err) })
    }
  }

  const limpiar = () => {
    setDesde(''); setHasta(''); setProductoId(''); setTipo('')
    setCategoriaId(''); setEstado(''); setBuscar('')
  }

  // Datos del gráfico: entradas vs salidas por día, calculados con las mismas filas del reporte
  const datosGrafico = useMemo(() => {
    if (tab !== 'movimientos') return []
    const mapa = {}
    for (const f of reporte.filas) {
      const dia = f.fecha.slice(0, 10)
      mapa[dia] ??= { dia, entradas: 0, salidas: 0 }
      mapa[dia][f.tipo === 'entrada' ? 'entradas' : 'salidas'] += f.cantidad
    }
    return Object.values(mapa).sort((a, b) => a.dia.localeCompare(b.dia))
  }, [tab, reporte.filas])

  const r = reporte.resumen

  return (
    <>
      <div className="encabezado">
        <h2>Reportes</h2>
        <div className="grupo-botones">
          <button className="btn" onClick={() => exportar('pdf')}>Exportar PDF</button>
          <button className="btn btn-primario" onClick={() => exportar('excel')}>Exportar Excel</button>
        </div>
      </div>

      <div className="pestanas">
        <button className={tab === 'movimientos' ? 'activa' : ''} onClick={() => setTab('movimientos')}>
          Entradas y salidas
        </button>
        <button className={tab === 'inventario' ? 'activa' : ''} onClick={() => setTab('inventario')}>
          Inventario
        </button>
      </div>

      {aviso && <div className={`alerta alerta-${aviso.tipo}`}>{aviso.texto}</div>}

      <div className="filtros">
        {tab === 'movimientos' ? (
          <>
            <label className="filtro">Desde
              <input type="date" value={desde} onChange={(e) => setDesde(e.target.value)} />
            </label>
            <label className="filtro">Hasta
              <input type="date" value={hasta} onChange={(e) => setHasta(e.target.value)} />
            </label>
            <label className="filtro">Producto
              <select value={productoId} onChange={(e) => setProductoId(e.target.value)}>
                <option value="">Todos</option>
                {productos.map((p) => <option key={p.id} value={p.id}>{p.nombre}</option>)}
              </select>
            </label>
            <label className="filtro">Tipo
              <select value={tipo} onChange={(e) => setTipo(e.target.value)}>
                <option value="">Todos</option>
                <option value="entrada">Entradas</option>
                <option value="salida">Salidas</option>
              </select>
            </label>
          </>
        ) : (
          <>
            <label className="filtro filtro-ancho">Buscar
              <input placeholder="Nombre o código…" value={buscar} onChange={(e) => setBuscar(e.target.value)} />
            </label>
            <label className="filtro">Categoría
              <select value={categoriaId} onChange={(e) => setCategoriaId(e.target.value)}>
                <option value="">Todas</option>
                {categorias.map((c) => <option key={c.id} value={c.id}>{c.nombre}</option>)}
              </select>
            </label>
            <label className="filtro">Estado
              <select value={estado} onChange={(e) => setEstado(e.target.value)}>
                <option value="">Todos</option>
                <option value="normal">Normal</option>
                <option value="bajo">Stock bajo</option>
                <option value="agotado">Agotado</option>
              </select>
            </label>
          </>
        )}
        <button className="btn btn-chico limpiar" onClick={limpiar}>Limpiar filtros</button>
      </div>

      {r && tab === 'movimientos' && (
        <div className="resumen-fila">
          <span><strong>{r.total_movimientos}</strong> movimientos</span>
          <span><strong>{r.total_entradas}</strong> unidades entraron</span>
          <span><strong>{r.total_salidas}</strong> unidades salieron</span>
        </div>
      )}
      {r && tab === 'inventario' && (
        <div className="resumen-fila">
          <span><strong>{r.total_productos}</strong> productos</span>
          <span><strong>{r.total_unidades}</strong> unidades</span>
          <span><strong>{dinero(r.valor_total)}</strong> valor total</span>
          <span><strong>{r.bajos}</strong> con stock bajo</span>
          <span><strong>{r.agotados}</strong> agotados</span>
        </div>
      )}

      {tab === 'movimientos' && datosGrafico.length > 0 && (
        <div className="tarjeta" style={{ marginBottom: 16 }}>
          <h3>Entradas y salidas por día</h3>
          <ResponsiveContainer width="100%" height={240}>
            <BarChart data={datosGrafico}>
              <CartesianGrid strokeDasharray="3 3" stroke="#e5e7eb" />
              <XAxis dataKey="dia" fontSize={12} />
              <YAxis allowDecimals={false} fontSize={12} />
              <Tooltip />
              <Legend />
              <Bar dataKey="entradas" name="Entradas" fill="#16a34a" radius={[4, 4, 0, 0]} />
              <Bar dataKey="salidas" name="Salidas" fill="#dc2626" radius={[4, 4, 0, 0]} />
            </BarChart>
          </ResponsiveContainer>
        </div>
      )}

      <div className="tarjeta tabla-contenedor">
        {tab === 'movimientos' ? (
          <table>
            <thead>
              <tr><th>Fecha</th><th>Producto</th><th>Tipo</th><th>Cantidad</th><th>Motivo</th><th>Usuario</th></tr>
            </thead>
            <tbody>
              {cargando && <tr><td colSpan="6" className="vacio">Cargando…</td></tr>}
              {!cargando && reporte.filas.length === 0 && (
                <tr><td colSpan="6" className="vacio">No hay movimientos con esos filtros.</td></tr>
              )}
              {!cargando && reporte.filas.map((f, idx) => (
                <tr key={idx}>
                  <td>{f.fecha}</td>
                  <td>{f.producto}</td>
                  <td><span className={`badge ${f.tipo === 'entrada' ? 'badge-ok' : 'badge-off'}`}>{f.tipo}</span></td>
                  <td>{f.cantidad}</td>
                  <td>{f.motivo}</td>
                  <td>{f.usuario}</td>
                </tr>
              ))}
            </tbody>
          </table>
        ) : (
          <table>
            <thead>
              <tr>
                <th>Código</th><th>Producto</th><th>Categoría</th><th>Proveedor</th>
                <th>Stock</th><th>Mínimo</th><th>Valor</th><th>Estado</th>
              </tr>
            </thead>
            <tbody>
              {cargando && <tr><td colSpan="8" className="vacio">Cargando…</td></tr>}
              {!cargando && reporte.filas.length === 0 && (
                <tr><td colSpan="8" className="vacio">No hay productos con esos filtros.</td></tr>
              )}
              {!cargando && reporte.filas.map((f) => (
                <tr key={f.codigo}>
                  <td>{f.codigo}</td>
                  <td>{f.nombre}</td>
                  <td>{f.categoria}</td>
                  <td>{f.proveedor}</td>
                  <td>{f.stock}</td>
                  <td>{f.stock_minimo}</td>
                  <td>{dinero(f.valor_inventario)}</td>
                  <td>
                    <span className={`badge ${f.estado === 'agotado' ? 'badge-off' : f.estado === 'bajo' ? 'badge-warn' : 'badge-ok'}`}>
                      {f.estado}
                    </span>
                  </td>
                </tr>
              ))}
            </tbody>
          </table>
        )}
      </div>
    </>
  )
}
