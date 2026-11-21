
import { useEffect, useState } from 'react';
import { api } from '../api';
import DetalleProveedor from './DetalleProveedor';
import FormularioProveedor from './FormularioProveedor';

const resultadoInicial = {
    data: [],
    current_page: 1,
    last_page: 1,
    total: 0,
    from: null,
    to: null,
};

export default function ProveedoresListado() {
    const [busqueda, setBusqueda] = useState('');
    const [textoAplicado, setTextoAplicado] = useState('');
    const [estado, setEstado] = useState('');
    const [pagina, setPagina] = useState(1);

    const [resultado, setResultado] = useState(resultadoInicial);
    const [cargando, setCargando] = useState(true);
    const [error, setError] = useState('');
    const [revision, setRevision] = useState(0);

    const [idDetalle, setIdDetalle] = useState(null);
    const [formularioVisible, setFormularioVisible] = useState(false);
    const [proveedorEnEdicion, setProveedorEnEdicion] = useState(null);

    const [mensaje, setMensaje] = useState('');
    const [errorAccion, setErrorAccion] = useState('');
    const [idProcesando, setIdProcesando] = useState(null);

    // Evitar consultar MySQL por cada letra escrita.
    useEffect(() => {
        const temporizador = setTimeout(() => {
            setTextoAplicado(busqueda.trim());
        }, 350);

        return () => clearTimeout(temporizador);
    }, [busqueda]);

    // Consultar proveedores cuando cambien los filtros o la página.
    useEffect(() => {
        const controller = new AbortController();

        async function cargar() {
            setCargando(true);
            setError('');

            try {
                const respuesta = await api.get('/api/proveedores', {
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

                setError(
                    err.response?.status === 401
                        ? 'Inicia sesión nuevamente.'
                        : err.response?.status === 403
                            ? 'No tienes permisos para consultar proveedores.'
                            : 'No se pudieron cargar los proveedores.'
                );
            } finally {
                if (!controller.signal.aborted) {
                    setCargando(false);
                }
            }
        }

        cargar();
        return () => controller.abort();
    }, [textoAplicado, estado, pagina, revision]);

    function nuevo() {
        setProveedorEnEdicion(null);
        setFormularioVisible(true);
        setIdDetalle(null);
        setErrorAccion('');
    }

    function editar(proveedor) {
        setProveedorEnEdicion(proveedor);
        setFormularioVisible(true);
        setIdDetalle(null);
        setErrorAccion('');
    }

    function guardado(texto) {
        setMensaje(texto);
        setErrorAccion('');
        setFormularioVisible(false);
        setProveedorEnEdicion(null);

        setBusqueda('');
        setTextoAplicado('');
        setEstado('');
        setPagina(1);
        setRevision((n) => n + 1);
    }

    async function cambiarEstado(proveedor) {
        setIdProcesando(proveedor.id_proveedor);
        setErrorAccion('');
        setMensaje('');

        try {
            const nuevoEstado = proveedor.estado === 'activo'
                ? 'inactivo'
                : 'activo';

            const respuesta = await api.patch(
                `/api/proveedores/${proveedor.id_proveedor}/estado`,
                { estado: nuevoEstado }
            );

            setMensaje(respuesta.data.mensaje);
            setIdDetalle(null);
            setRevision((n) => n + 1);
        } catch (err) {
            setErrorAccion(
                err.response?.data?.mensaje ||
                'No se pudo cambiar el estado.'
            );
        } finally {
            setIdProcesando(null);
        }
    }

    async function eliminar(proveedor) {
        if (proveedor.productos_count > 0) {
            setErrorAccion(
                'No se puede eliminar: el proveedor tiene productos asociados.'
            );
            return;
        }

        const confirmar = window.confirm(
            `¿Eliminar el proveedor "${proveedor.nombre}"? Esta acción no se puede deshacer.`
        );

        if (!confirmar) return;

        setIdProcesando(proveedor.id_proveedor);
        setErrorAccion('');
        setMensaje('');

        try {
            const respuesta = await api.delete(
                `/api/proveedores/${proveedor.id_proveedor}`
            );

            setMensaje(respuesta.data.mensaje);
            setIdDetalle(null);
            setPagina(1);
            setRevision((n) => n + 1);
        } catch (err) {
            setErrorAccion(
                err.response?.data?.mensaje ||
                'No se pudo eliminar el proveedor.'
            );
        } finally {
            setIdProcesando(null);
        }
    }

    const tieneFiltros = busqueda.trim() !== '' || estado !== '';

    return (
        <section aria-labelledby="titulo-proveedores">
            <div className="cabecera-pagina">
                <div>
                    <h1 id="titulo-proveedores">Proveedores</h1>
                    <p className="subtitulo">
                        Consulta y administra el directorio de proveedores.
                    </p>
                </div>

                <button
                    className="boton"
                    type="button"
                    onClick={nuevo}
                >
                    Registrar proveedor
                </button>
            </div>

            {mensaje && (
                <div className="alerta alerta-exito" role="status">
                    {mensaje}
                </div>
            )}

            {errorAccion && (
                <div className="alerta alerta-error" role="alert">
                    {errorAccion}
                </div>
            )}

            {formularioVisible && (
                <FormularioProveedor
                    key={proveedorEnEdicion?.id_proveedor ?? 'nuevo'}
                    proveedor={proveedorEnEdicion}
                    onGuardado={guardado}
                    onCancelar={() => setFormularioVisible(false)}
                />
            )}

            <form
                className="filtros"
                role="search"
                onSubmit={(e) => e.preventDefault()}
            >
                <div className="campo-filtro campo-busqueda">
                    <label htmlFor="buscar-proveedor">
                        Buscar proveedor
                    </label>

                    <input
                        id="buscar-proveedor"
                        type="search"
                        value={busqueda}
                        maxLength={100}
                        placeholder="Nombre, teléfono, correo o dirección"
                        onChange={(e) => {
                            setBusqueda(e.target.value);
                            setPagina(1);
                            setIdDetalle(null);
                        }}
                    />
                </div>

                <div className="campo-filtro">
                    <label htmlFor="estado-proveedor">Estado</label>

                    <select
                        id="estado-proveedor"
                        value={estado}
                        onChange={(e) => {
                            setEstado(e.target.value);
                            setPagina(1);
                            setIdDetalle(null);
                        }}
                    >
                        <option value="">Todos</option>
                        <option value="activo">Activos</option>
                        <option value="inactivo">Inactivos</option>
                    </select>
                </div>

                <div className="filtros-acciones">
                    <button
                        type="button"
                        className="boton boton-secundario"
                        disabled={!tieneFiltros}
                        onClick={() => {
                            setBusqueda('');
                            setTextoAplicado('');
                            setEstado('');
                            setPagina(1);
                            setIdDetalle(null);
                        }}
                    >
                        Limpiar filtros
                    </button>
                </div>
            </form>

            {cargando && (
                <p role="status">Cargando proveedores...</p>
            )}

            {!cargando && error && (
                <div className="alerta alerta-error" role="alert">
                    {error}
                </div>
            )}

            {!cargando && !error && resultado.data.length === 0 && (
                <div className="estado-vacio">
                    <h3>No se encontraron proveedores</h3>
                    <p>
                        {tieneFiltros
                            ? 'Prueba otra búsqueda o filtro.'
                            : 'Aún no hay proveedores registrados.'}
                    </p>
                </div>
            )}

            {!cargando && !error && resultado.data.length > 0 && (
                <>
                    <div className="tabla-contenedor">
                        <table className="tabla">
                            <thead>
                                <tr>
                                    <th>Proveedor</th>
                                    <th>Teléfono</th>
                                    <th>Correo</th>
                                    <th>Dirección</th>
                                    <th>Estado</th>
                                    <th className="num">Productos</th>
                                    <th className="num">Acciones</th>
                                </tr>
                            </thead>

                            <tbody>
                                {resultado.data.map((proveedor) => (
                                    <tr key={proveedor.id_proveedor}>
                                        <td>
                                            <span className="libro-titulo">
                                                {proveedor.nombre}
                                            </span>
                                        </td>
                                        <td>{proveedor.telefono || '—'}</td>
                                        <td>{proveedor.email || '—'}</td>
                                        <td>{proveedor.direccion || '—'}</td>

                                        <td>
                                            <span
                                                className={
                                                    proveedor.estado === 'activo'
                                                        ? 'insignia insignia-disponible'
                                                        : 'insignia insignia-inactivo'
                                                }
                                            >
                                                {proveedor.estado === 'activo'
                                                    ? 'Activo'
                                                    : 'Inactivo'}
                                            </span>
                                        </td>

                                        <td className="num">
                                            {proveedor.productos_count}
                                        </td>

                                        <td className="acciones">
                                            <button
                                                type="button"
                                                className="boton boton-secundario boton-mini"
                                                onClick={() =>
                                                    setIdDetalle(proveedor.id_proveedor)
                                                }
                                            >
                                                Consultar
                                            </button>

                                            <button
                                                type="button"
                                                className="boton boton-secundario boton-mini"
                                                disabled={idProcesando !== null}
                                                onClick={() => editar(proveedor)}
                                            >
                                                Editar
                                            </button>

                                            <button
                                                type="button"
                                                className="boton boton-secundario boton-mini"
                                                disabled={idProcesando !== null}
                                                onClick={() => cambiarEstado(proveedor)}
                                            >
                                                {proveedor.estado === 'activo'
                                                    ? 'Desactivar'
                                                    : 'Activar'}
                                            </button>

                                            <button
                                                type="button"
                                                className="boton boton-peligro-tenue boton-mini"
                                                disabled={
                                                    idProcesando !== null ||
                                                    proveedor.productos_count > 0
                                                }
                                                title={
                                                    proveedor.productos_count > 0
                                                        ? 'Tiene productos asociados'
                                                        : 'Eliminar proveedor'
                                                }
                                                onClick={() => eliminar(proveedor)}
                                            >
                                                Eliminar
                                            </button>
                                        </td>
                                    </tr>
                                ))}
                            </tbody>
                        </table>
                    </div>

                    <nav
                        className="paginacion"
                        aria-label="Paginación de proveedores"
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
                                onClick={() => {
                                    setPagina(resultado.current_page - 1);
                                    setIdDetalle(null);
                                }}
                            >
                                Anterior
                            </button>

                            <span className="pagina-actual" aria-current="page">
                                {resultado.current_page} de {resultado.last_page}
                            </span>

                            <button
                                type="button"
                                className="boton boton-secundario boton-mini"
                                disabled={
                                    resultado.current_page >= resultado.last_page
                                }
                                onClick={() => {
                                    setPagina(resultado.current_page + 1);
                                    setIdDetalle(null);
                                }}
                            >
                                Siguiente
                            </button>
                        </div>
                    </nav>
                </>
            )}

            {idDetalle !== null && (
                <DetalleProveedor
                    key={idDetalle}
                    idProveedor={idDetalle}
                    onCerrar={() => setIdDetalle(null)}
                />
            )}
        </section>
    );
}
