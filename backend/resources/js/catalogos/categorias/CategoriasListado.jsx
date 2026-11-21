
import { useEffect, useState } from 'react';
import { api } from '../api';
import DetalleCategoria from './DetalleCategoria';

const resultadoInicial = {
    data: [],
    current_page: 1,
    last_page: 1,
    total: 0,
    from: null,
    to: null,
};

export default function CategoriasListado() {
    // Valores de los filtros
    const [busqueda, setBusqueda] = useState('');
    const [textoAplicado, setTextoAplicado] = useState('');
    const [estado, setEstado] = useState('');
    const [pagina, setPagina] = useState(1);

    // Resultados y estado de la consulta
    const [resultado, setResultado] = useState(resultadoInicial);
    const [cargando, setCargando] = useState(true);
    const [error, setError] = useState('');
    const [reintento, setReintento] = useState(0);

    // Categoría seleccionada para consultar su detalle
    const [idDetalle, setIdDetalle] = useState(null);

    // Esperar 350 ms antes de aplicar la búsqueda
    useEffect(() => {
        const temporizador = setTimeout(() => {
            setTextoAplicado(busqueda.trim());
        }, 350);

        return () => clearTimeout(temporizador);
    }, [busqueda]);

    // Consultar las categorías cuando cambian los filtros
    useEffect(() => {
        const controller = new AbortController();

        async function cargarCategorias() {
            setCargando(true);
            setError('');

            try {
                const respuesta = await api.get('/api/categorias', {
                    params: {
                        q: textoAplicado || undefined,
                        estado: estado || undefined,
                        page: pagina,
                    },
                    signal: controller.signal,
                });

                if (!controller.signal.aborted) {
                    setResultado(respuesta.data);
                }
            } catch (err) {
                if (
                    controller.signal.aborted ||
                    err.code === 'ERR_CANCELED'
                ) {
                    return;
                }

                if (err.response?.status === 401) {
                    setError('Tu sesión ha expirado. Inicia sesión nuevamente.');
                } else if (err.response?.status === 403) {
                    setError('No tienes permisos para consultar categorías.');
                } else {
                    setError('No se pudieron cargar las categorías.');
                }
            } finally {
                if (!controller.signal.aborted) {
                    setCargando(false);
                }
            }
        }

        cargarCategorias();

        return () => controller.abort();
    }, [textoAplicado, estado, pagina, reintento]);

    function cambiarBusqueda(evento) {
        setBusqueda(evento.target.value);
        setPagina(1);
        setIdDetalle(null);
    }

    function cambiarEstado(evento) {
        setEstado(evento.target.value);
        setPagina(1);
        setIdDetalle(null);
    }

    function limpiarFiltros() {
        setBusqueda('');
        setTextoAplicado('');
        setEstado('');
        setPagina(1);
        setIdDetalle(null);
    }

    function cambiarPagina(nuevaPagina) {
        setPagina(nuevaPagina);
        setIdDetalle(null);
    }

    const tieneFiltros = busqueda.trim() !== '' || estado !== '';

    return (
        <section aria-labelledby="titulo-categorias">
            <div className="cabecera-pagina">
                <div>
                    <h1 id="titulo-categorias">Categorías</h1>
                    <p className="subtitulo">
                        Consulta y organiza las categorías de productos.
                    </p>
                </div>
            </div>

            {/* Búsqueda y filtros */}
            <form
                className="filtros"
                role="search"
                onSubmit={(evento) => evento.preventDefault()}
            >
                <div className="campo-filtro campo-busqueda">
                    <label htmlFor="buscar-categoria">
                        Buscar categoría
                    </label>

                    <input
                        id="buscar-categoria"
                        type="search"
                        placeholder="Buscar por nombre..."
                        value={busqueda}
                        onChange={cambiarBusqueda}
                        maxLength={100}
                    />
                </div>

                <div className="campo-filtro">
                    <label htmlFor="filtrar-estado">
                        Estado
                    </label>

                    <select
                        id="filtrar-estado"
                        value={estado}
                        onChange={cambiarEstado}
                    >
                        <option value="">Todos los estados</option>
                        <option value="activo">Activos</option>
                        <option value="inactivo">Inactivos</option>
                    </select>
                </div>

                <div className="filtros-acciones">
                    <button
                        type="button"
                        className="boton boton-secundario"
                        onClick={limpiarFiltros}
                        disabled={!tieneFiltros}
                    >
                        Limpiar filtros
                    </button>
                </div>
            </form>

            {/* Estado de carga */}
            {cargando && (
                <p role="status">
                    Cargando categorías...
                </p>
            )}

            {/* Mensaje de error */}
            {!cargando && error && (
                <div className="alerta alerta-error" role="alert">
                    <p>{error}</p>

                    <button
                        type="button"
                        className="boton boton-secundario boton-mini"
                        onClick={() => setReintento((valor) => valor + 1)}
                    >
                        Volver a intentar
                    </button>
                </div>
            )}

            {/* Sin resultados */}
            {!cargando && !error && resultado.data.length === 0 && (
                <div className="estado-vacio">
                    <h3>No se encontraron categorías</h3>

                    <p>
                        {tieneFiltros
                            ? 'Prueba con otra búsqueda o cambia los filtros.'
                            : 'Todavía no hay categorías registradas.'}
                    </p>
                </div>
            )}

            {/* Tabla de categorías */}
            {!cargando && !error && resultado.data.length > 0 && (
                <>
                    <div className="tabla-contenedor">
                        <table className="tabla">
                            <thead>
                                <tr>
                                    <th scope="col">Categoría</th>
                                    <th scope="col">Descripción</th>
                                    <th scope="col">Estado</th>
                                    <th scope="col" className="num">
                                        Libros
                                    </th>
                                    <th scope="col" className="num">
                                        Acciones
                                    </th>
                                </tr>
                            </thead>

                            <tbody>
                                {resultado.data.map((categoria) => (
                                    <tr key={categoria.id_categoria}>
                                        <td>
                                            <span className="libro-titulo">
                                                {categoria.nombre}
                                            </span>
                                        </td>

                                        <td className="texto-secundario">
                                            {categoria.descripcion || '—'}
                                        </td>

                                        <td>
                                            <span
                                                className={
                                                    categoria.estado === 'activo'
                                                        ? 'insignia insignia-disponible'
                                                        : 'insignia insignia-inactivo'
                                                }
                                            >
                                                {categoria.estado === 'activo'
                                                    ? 'Activo'
                                                    : 'Inactivo'}
                                            </span>
                                        </td>

                                        <td className="num">
                                            {categoria.productos_count} unid.
                                        </td>

                                        <td className="acciones">
                                            <button
                                                type="button"
                                                className="boton boton-secundario boton-mini"
                                                onClick={() =>
                                                    setIdDetalle(
                                                        categoria.id_categoria
                                                    )
                                                }
                                            >
                                                Consultar
                                            </button>
                                        </td>
                                    </tr>
                                ))}
                            </tbody>
                        </table>
                    </div>

                    {/* Navegación entre páginas */}
                    <nav
                        className="paginacion"
                        aria-label="Paginación de categorías"
                    >
                        <span>
                            Mostrando {resultado.from ?? 0}–
                            {resultado.to ?? 0} de {resultado.total}
                        </span>

                        <div className="paginacion-enlaces">
                            <button
                                type="button"
                                className="boton boton-secundario boton-mini"
                                disabled={resultado.current_page <= 1}
                                onClick={() =>
                                    cambiarPagina(
                                        resultado.current_page - 1
                                    )
                                }
                            >
                                Anterior
                            </button>

                            <span
                                className="pagina-actual"
                                aria-current="page"
                            >
                                {resultado.current_page} de {resultado.last_page}
                            </span>

                            <button
                                type="button"
                                className="boton boton-secundario boton-mini"
                                disabled={
                                    resultado.current_page >= resultado.last_page
                                }
                                onClick={() =>
                                    cambiarPagina(
                                        resultado.current_page + 1
                                    )
                                }
                            >
                                Siguiente
                            </button>
                        </div>
                    </nav>
                </>
            )}

            {/* Detalle de una categoría seleccionada */}
            {idDetalle !== null && (
                <DetalleCategoria
                    key={idDetalle}
                    idCategoria={idDetalle}
                    onCerrar={() => setIdDetalle(null)}
                />
            )}
        </section>
    );
}
