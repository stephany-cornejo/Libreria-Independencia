
import { useEffect, useState } from 'react';
import { api } from '../api';

export default function DetalleProveedor({ idProveedor, onCerrar }) {
    const [proveedor, setProveedor] = useState(null);
    const [cargando, setCargando] = useState(true);
    const [error, setError] = useState('');

    useEffect(() => {
        const controller = new AbortController();

        api.get(`/api/proveedores/${idProveedor}`, {
            signal: controller.signal,
        })
            .then((respuesta) => setProveedor(respuesta.data))
            .catch((err) => {
                if (err.code !== 'ERR_CANCELED') {
                    setError(
                        err.response?.status === 404
                            ? 'El proveedor ya no existe.'
                            : 'No se pudo consultar el proveedor.'
                    );
                }
            })
            .finally(() => {
                if (!controller.signal.aborted) {
                    setCargando(false);
                }
            });

        return () => controller.abort();
    }, [idProveedor]);

    return (
        <section
            className="tarjeta"
            style={{ marginTop: '1.25rem' }}
            aria-labelledby="titulo-detalle-proveedor"
        >
            <div className="cabecera-pagina">
                <h2 id="titulo-detalle-proveedor">Detalle de proveedor</h2>
                <button
                    className="boton boton-secundario boton-mini"
                    type="button"
                    onClick={onCerrar}
                >
                    Cerrar
                </button>
            </div>

            {cargando && (
                <p role="status">Consultando proveedor...</p>
            )}

            {error && (
                <div className="alerta alerta-error" role="alert">
                    {error}
                </div>
            )}

            {!cargando && !error && proveedor && (
                <div>
                    <p><strong>Nombre:</strong> {proveedor.nombre}</p>
                    <p><strong>Teléfono:</strong> {proveedor.telefono || '—'}</p>
                    <p><strong>Correo:</strong> {proveedor.email || '—'}</p>
                    <p><strong>Dirección:</strong> {proveedor.direccion || '—'}</p>
                    <p><strong>Estado:</strong> {proveedor.estado}</p>
                    <p><strong>Productos asociados:</strong> {proveedor.productos_count}</p>
                </div>
            )}
        </section>
    );
}
