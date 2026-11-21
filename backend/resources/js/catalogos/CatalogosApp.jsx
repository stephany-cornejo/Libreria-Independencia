
import { useEffect, useState } from 'react';
import { api } from './api';

export default function CatalogosApp() {
    const [cantidad, setCantidad] = useState(null);
    const [cargando, setCargando] = useState(true);
    const [error, setError] = useState('');

    useEffect(() => {
        const controller = new AbortController();

        async function cargarCategorias() {
            try {
                const respuesta = await api.get('/api/categorias', {
                    signal: controller.signal,
                });

                setCantidad(respuesta.data.total);
            } catch (err) {
                if (err.code === 'ERR_CANCELED') {
                    return;
                }

                if (err.response?.status === 401) {
                    setError('Tu sesión ha expirado. Inicia sesión nuevamente.');
                } else if (err.response?.status === 403) {
                    setError('No tienes permisos para consultar categorías.');
                } else {
                    setError('No se pudo conectar con la API de categorías.');
                }
            } finally {
                if (!controller.signal.aborted) {
                    setCargando(false);
                }
            }
        }

        cargarCategorias();

        return () => controller.abort();
    }, []);

    return (
        <section>
            <div className="cabecera-pagina">
                <div>
                    <h1>Catálogos - React</h1>
                    <p className="subtitulo">
                        Integración de React con Laravel.
                    </p>
                </div>
            </div>

            {cargando && (
                <p role="status">
                    Cargando categorías desde MySQL...
                </p>
            )}

            {error && (
                <div className="alerta alerta-error" role="alert">
                    {error}
                </div>
            )}

            {!cargando && !error && (
                <div className="alerta alerta-exito" role="status">
                    <strong>React conectado correctamente.</strong>
                    <p>
                        Categorías disponibles: {cantidad}
                    </p>
                </div>
            )}
        </section>
    );
}
