# Integrante 4 – Dashboard, reportes y documentos

## 1. Archivos nuevos (copiar tal cual al repositorio)

```
backend/
├── app/Services/ReporteService.php
├── app/Exports/InventarioExport.php
├── app/Exports/MovimientosExport.php
├── app/Http/Controllers/DashboardController.php
├── app/Http/Controllers/ReporteController.php
├── app/Http/Controllers/DocumentoController.php
├── resources/views/reportes/inventario.blade.php
├── resources/views/reportes/movimientos.blade.php
└── routes/integrante4.php

frontend/src/
├── descargar.js
├── estilos-integrante4.css
└── pages/Dashboard.jsx, Reportes.jsx, Documentos.jsx
```

## 2. Instalar dependencias

```bash
# backend/  (PDF y Excel)
composer require barryvdh/laravel-dompdf maatwebsite/excel

# frontend/  (gráficos)
npm install recharts
```

En `C:\xampp\php\php.ini` deben estar activas (sin `;`): `extension=gd`, `extension=zip`, `extension=fileinfo`, `extension=mbstring`. Reinicia Apache.

## 3. Cuatro cambios en archivos compartidos

**backend/routes/api.php** – al final del grupo `auth:sanctum` (antes del grupo `rol:administrador`), agrega:
```php
require __DIR__.'/integrante4.php';
```

**frontend/src/main.jsx**
```jsx
import './estilos-integrante4.css'
```

**frontend/src/App.jsx**
```jsx
import Dashboard from './pages/Dashboard'
import Reportes from './pages/Reportes'
import Documentos from './pages/Documentos'

// dentro del <Route element={<Layout/>}>, reemplazando la ruta "/" que apuntaba a Inicio:
<Route path="/" element={<Dashboard />} />
<Route path="/documentos" element={<Documentos />} />
<Route path="/reportes" element={<ProtectedRoute roles={['administrador']}><Reportes /></ProtectedRoute>} />
```
(`pages/Inicio.jsx` ya no se usa; se puede borrar.)

**frontend/src/components/Layout.jsx** – en el arreglo `MENU`:
```js
{ to: '/reportes', texto: 'Reportes', roles: ['administrador'] },
{ to: '/documentos', texto: 'Documentos', roles: [] },
```

## 4. Permisos

| Función | Administrador | Empleado |
|---|---|---|
| Dashboard | sí | sí |
| Documentos: ver, subir, descargar | sí | sí |
| Documentos: eliminar | sí | no |
| Reportes y exportaciones | sí | no |

## 5. Dependencias con los demás integrantes

- Los reportes leen las tablas `productos`, `categorias`, `proveedores` y `movimientos` (ya creadas por las migraciones). Sin datos, todo aparece en cero.
- Los filtros de producto y categoría usan `GET /api/productos` y `GET /api/categorias` (Integrantes 2 y 3). Si responden paginado (`data.data`) o como arreglo, funciona igual; si aún no existen, los selects quedan vacíos sin romper la página.
- Los movimientos deben guardar `fecha_movimiento` (obligatoria en la migración).

## 6. Endpoints

| Método | Ruta | Parámetros |
|---|---|---|
| GET | `/api/dashboard` | – |
| GET | `/api/reportes/movimientos` (+ `/pdf`, `/excel`) | `desde`, `hasta`, `producto_id`, `tipo` |
| GET | `/api/reportes/inventario` (+ `/pdf`, `/excel`) | `buscar`, `categoria_id`, `proveedor_id`, `estado` |
| GET / POST | `/api/documentos` | `buscar`, `page` / `nombre`, `archivo` (máx. 5 MB) |
| GET | `/api/documentos/{id}/descargar` | – |
| DELETE | `/api/documentos/{id}` | solo administrador |

## 7. Verificar que el dashboard coincide con MySQL (punto 10)

Ejecuta en phpMyAdmin y compara con los indicadores del dashboard:

```sql
SELECT COUNT(*) FROM productos;      -- Productos
SELECT COUNT(*) FROM categorias;     -- Categorías
SELECT COUNT(*) FROM proveedores;    -- Proveedores
SELECT COUNT(*) FROM movimientos;    -- Movimientos
SELECT COUNT(*) FROM productos WHERE stock = 0;                          -- Agotados
SELECT COUNT(*) FROM productos WHERE stock > 0 AND stock <= stock_minimo; -- Stock bajo

-- Entradas y salidas del mes
SELECT tipo, SUM(cantidad) FROM movimientos
WHERE fecha_movimiento >= DATE_FORMAT(NOW(), '%Y-%m-01') GROUP BY tipo;

-- Top salidas
SELECT p.nombre, SUM(m.cantidad) total FROM movimientos m
JOIN productos p ON p.id = m.producto_id
WHERE m.tipo = 'salida' GROUP BY p.id, p.nombre ORDER BY total DESC LIMIT 5;

-- El stock de cada producto debe ser entradas - salidas (si el stock inicial fue 0 y todo se registró como movimiento)
SELECT p.nombre, p.stock,
  COALESCE(SUM(CASE WHEN m.tipo='entrada' THEN m.cantidad ELSE -m.cantidad END),0) AS calculado
FROM productos p LEFT JOIN movimientos m ON m.producto_id = p.id GROUP BY p.id, p.nombre, p.stock;
```

## 8. Prueba rápida

1. Inicia sesión como administrador, crea categorías, un proveedor, productos y movimientos (módulos de los Integrantes 2 y 3).
2. Dashboard: indicadores, gráficos y alertas deben coincidir con las consultas SQL.
3. Reportes: filtra por fechas, producto y tipo; exporta PDF y Excel; abre ambos y compara con la tabla en pantalla.
4. Documentos: sube un PDF, descárgalo, intenta subir uno de más de 5 MB (debe dar error).
5. Como empleado: no ves "Reportes" y `GET /api/reportes/inventario` responde 403.
