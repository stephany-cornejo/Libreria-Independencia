
import { useEffect, useState } from 'react';
import { api } from '../api';

export default function DetalleCategoria({
    idCategoria,
    onCerrar,
}) {
    const [categoria, setCategoria] = useState(null);
    const [cargando, setCargando] = useState(true);
    const [error, setError] = useState('');

    useEffect(() => {
        const controller = new AbortController();

        async function cargarDetalle() {
            setCargando(true);
            setError('');

            try {
                const respuesta = await api.get(
                    `/api/categorias/${idCategoria}`,
                    { signal: controller.signal }
                );

                if (!controller.signal.aborted) {
                    setCategoria(respuesta.data);
                }
            } catch (err) {
                if (
                    controller.signal.aborted ||
                    err.code === 'ERR_CANCELED'
                ) {
                    return;
                }

                setError(
                    err.response?.status === 404
                        ? 'La categoría ya no existe.'
                        : 'No se pudo consultar la categoría.'
                );
            } finally {
                if (!controller.signal.aborted) {
                    setCargando(false);
                }
            }
        }

        cargarDetalle();

        return () => controller.abort();
    }, [idCategoria]);

    return (
        <section
            className="tabla-contenedor"
            style={{ marginTop: '1.25rem', padding: '1.25rem' }}
            aria-labelledby="titulo-detalle-categoria"
        >
            <div className="cabecera-pagina">
                <h2 id="titulo-detalle-categoria">
                    Detalle de categoría
                </h2>

                <button
                    type="button"
                    className="boton boton-secundario boton-mini"
                    onClick={onCerrar}
                >
                    Cerrar
                </button>
            </div>

            {cargando && (
                <p role="status">Consultando categoría...</p>
            )}

            {error && (
                <div className="alerta alerta-error" role="alert">
                    {error}
                </div>
            )}

            {!cargando && !error && categoria && (
                <div>
                    <p>
                        <strong>Nombre:</strong> {categoria.nombre}
                    </p>

                    <p>
                        <strong>Descripción:</strong>{' '}
                        {categoria.descripcion || 'Sin descripción'}
                    </p>

                    <p>
                        <strong>Estado:</strong>{' '}
                        {categoria.estado === 'activo'
                            ? 'Activo'
                            : 'Inactivo'}
                    </p>

                    <p>
                        <strong>Productos asociados:</strong>{' '}
                        {categoria.productos_count}
                    </p>
                </div>
            )}
        </section>
    );
}
