import { useEffect, useState } from 'react'
import {
  Bar,
  BarChart,
  CartesianGrid,
  Legend,
  Line,
  LineChart,
  ResponsiveContainer,
  Tooltip,
  XAxis,
  YAxis,
} from 'recharts'
import api, { leerError } from '../api'
import { useAuth } from '../context/AuthContext'

function Indicador({ titulo, valor, color }) {
  return (
    <div className="tarjeta indicador" style={{ borderTopColor: color }}>
      <span className="indicador-titulo">{titulo}</span>
      <strong className="indicador-valor">{valor}</strong>
    </div>
  )
}

export default function Dashboard() {
  const { user } = useAuth()
  const [datos, setDatos] = useState(null)
  const [error, setError] = useState('')

  useEffect(() => {
    api
      .get('/dashboard')
      .then(({ data }) => setDatos(data))
      .catch((err) => setError(leerError(err).mensaje))
  }, [])

  if (error) return <div className="alerta alerta-error">{error}</div>
  if (!datos) return <p className="estado-carga">Cargando dashboard…</p>

  const { indicadores: i, movimientos_por_dia, top_salidas, alertas, por_categoria } = datos

  return (
    <>
      <h2>Bienvenido, {user.name}</h2>
      <p className="subtitulo">Resumen general del inventario</p>

      <div className="rejilla-indicadores">
        <Indicador titulo="Productos" valor={i.productos} color="#1d4ed8" />
        <Indicador titulo="Categorías" valor={i.categorias} color="#7c3aed" />
        <Indicador titulo="Proveedores" valor={i.proveedores} color="#0891b2" />
        <Indicador titulo="Movimientos" valor={i.movimientos} color="#16a34a" />
        <Indicador titulo="Stock bajo" valor={i.stock_bajo} color="#d97706" />
        <Indicador titulo="Agotados" valor={i.agotados} color="#dc2626" />
      </div>

      <div className="rejilla-graficos">
        <div className="tarjeta grafico-ancho">
          <h3>Entradas y salidas – últimos 30 días</h3>
          <p className="subtitulo">
            Este mes: {i.entradas_mes} unidades entraron · {i.salidas_mes} salieron
          </p>
          <ResponsiveContainer width="100%" height={260}>
            <LineChart data={movimientos_por_dia}>
              <CartesianGrid strokeDasharray="3 3" stroke="#e5e7eb" />
              <XAxis dataKey="dia" fontSize={12} interval={4} />
              <YAxis allowDecimals={false} fontSize={12} />
              <Tooltip />
              <Legend />
              <Line type="monotone" dataKey="entradas" name="Entradas" stroke="#16a34a" strokeWidth={2} dot={false} />
              <Line type="monotone" dataKey="salidas" name="Salidas" stroke="#dc2626" strokeWidth={2} dot={false} />
            </LineChart>
          </ResponsiveContainer>
        </div>

        <div className="tarjeta">
          <h3>Top 5 productos con más salidas</h3>
          {top_salidas.length === 0 ? (
            <p className="subtitulo">Aún no hay salidas registradas.</p>
          ) : (
            <ResponsiveContainer width="100%" height={260}>
              <BarChart data={top_salidas} layout="vertical" margin={{ left: 10 }}>
                <CartesianGrid strokeDasharray="3 3" stroke="#e5e7eb" />
                <XAxis type="number" allowDecimals={false} fontSize={12} />
                <YAxis type="category" dataKey="nombre" width={110} fontSize={12} />
                <Tooltip />
                <Bar dataKey="total" name="Unidades" fill="#1d4ed8" radius={[0, 4, 4, 0]} />
              </BarChart>
            </ResponsiveContainer>
          )}
        </div>

        <div className="tarjeta">
          <h3>Productos por categoría</h3>
          {por_categoria.length === 0 ? (
            <p className="subtitulo">Aún no hay categorías.</p>
          ) : (
            <ResponsiveContainer width="100%" height={260}>
              <BarChart data={por_categoria}>
                <CartesianGrid strokeDasharray="3 3" stroke="#e5e7eb" />
                <XAxis dataKey="nombre" fontSize={12} />
                <YAxis allowDecimals={false} fontSize={12} />
                <Tooltip />
                <Bar dataKey="total" name="Productos" fill="#7c3aed" radius={[4, 4, 0, 0]} />
              </BarChart>
            </ResponsiveContainer>
          )}
        </div>

        <div className="tarjeta tabla-contenedor grafico-ancho">
          <h3 className="titulo-tabla">Alertas de existencias</h3>
          <table>
            <thead>
              <tr>
                <th>Código</th>
                <th>Producto</th>
                <th>Stock</th>
                <th>Mínimo</th>
                <th>Estado</th>
              </tr>
            </thead>
            <tbody>
              {alertas.length === 0 && (
                <tr><td colSpan="5" className="vacio">Sin alertas: todas las existencias están en orden.</td></tr>
              )}
              {alertas.map((a) => (
                <tr key={a.id}>
                  <td>{a.codigo}</td>
                  <td>{a.nombre}</td>
                  <td>{a.stock}</td>
                  <td>{a.stock_minimo}</td>
                  <td>
                    <span className={`badge ${a.estado === 'agotado' ? 'badge-off' : 'badge-warn'}`}>
                      {a.estado === 'agotado' ? 'Agotado' : 'Stock bajo'}
                    </span>
                  </td>
                </tr>
              ))}
            </tbody>
          </table>
        </div>
      </div>
    </>
  )
}
