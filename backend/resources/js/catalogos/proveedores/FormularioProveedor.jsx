
import { useState } from 'react';
import { api } from '../api';

export default function FormularioProveedor({ proveedor = null, onGuardado, onCancelar }) {
    const [datos, setDatos] = useState({
        nombre: proveedor?.nombre ?? '',
        telefono: proveedor?.telefono ?? '',
        email: proveedor?.email ?? '',
        direccion: proveedor?.direccion ?? '',
        estado: proveedor?.estado ?? 'activo',
    });
    const [errores, setErrores] = useState({});
    const [errorGeneral, setErrorGeneral] = useState('');
    const [guardando, setGuardando] = useState(false);

    function cambiar(evento) {
        const { name, value } = evento.target;
        setDatos((actual) => ({ ...actual, [name]: value }));
        setErrores((actual) => ({ ...actual, [name]: undefined }));
    }

    async function guardar(evento) {
        evento.preventDefault();
        if (guardando) return;

        setGuardando(true);
        setErrores({});
        setErrorGeneral('');

        const payload = {
            nombre: datos.nombre.trim(),
            telefono: datos.telefono.trim() || null,
            email: datos.email.trim() || null,
            direccion: datos.direccion.trim() || null,
            estado: datos.estado,
        };

        try {
            const respuesta = proveedor
                ? await api.put(`/api/proveedores/${proveedor.id_proveedor}`, payload)
                : await api.post('/api/proveedores', payload);

            onGuardado(respuesta.data.mensaje);
        } catch (error) {
            if (error.response?.status === 422) {
                setErrores(error.response.data.errors ?? {});
                setErrorGeneral('Revisa los campos indicados.');
            } else {
                setErrorGeneral(
                    error.response?.data?.mensaje ||
                    error.response?.data?.message ||
                    'No se pudo guardar el proveedor.'
                );
            }
        } finally {
            setGuardando(false);
        }
    }

    return (
        <form
            className="tarjeta tarjeta-ancho formulario"
            noValidate
            onSubmit={guardar}
            style={{ marginBottom: '1.5rem' }}
        >
            <h2>{proveedor ? 'Editar proveedor' : 'Registrar proveedor'}</h2>

            {errorGeneral && (
                <div className="alerta alerta-error" role="alert">
                    {errorGeneral}
                </div>
            )}

            <div className="formulario-campos">
                <div className="campo">
                    <label htmlFor="prov-nombre">Nombre</label>
                    <input
                        id="prov-nombre"
                        name="nombre"
                        value={datos.nombre}
                        onChange={cambiar}
                        maxLength={100}
                        required
                        autoFocus
                        className={errores.nombre ? 'con-error' : ''}
                    />
                    {errores.nombre?.map((mensaje, i) => (
                        <p className="error-campo" key={i}>{mensaje}</p>
                    ))}
                </div>

                <div className="campo">
                    <label htmlFor="prov-estado">Estado</label>
                    <select
                        id="prov-estado"
                        name="estado"
                        value={datos.estado}
                        onChange={cambiar}
                    >
                        <option value="activo">Activo</option>
                        <option value="inactivo">Inactivo</option>
                    </select>
                    {errores.estado?.map((mensaje, i) => (
                        <p className="error-campo" key={i}>{mensaje}</p>
                    ))}
                </div>

                <div className="campo">
                    <label htmlFor="prov-telefono">Teléfono (opcional)</label>
                    <input
                        id="prov-telefono"
                        name="telefono"
                        value={datos.telefono}
                        onChange={cambiar}
                        maxLength={20}
                        className={errores.telefono ? 'con-error' : ''}
                    />
                    {errores.telefono?.map((mensaje, i) => (
                        <p className="error-campo" key={i}>{mensaje}</p>
                    ))}
                </div>

                <div className="campo">
                    <label htmlFor="prov-email">Correo (opcional)</label>
                    <input
                        id="prov-email"
                        name="email"
                        type="email"
                        value={datos.email}
                        onChange={cambiar}
                        maxLength={100}
                        className={errores.email ? 'con-error' : ''}
                    />
                    {errores.email?.map((mensaje, i) => (
                        <p className="error-campo" key={i}>{mensaje}</p>
                    ))}
                </div>

                <div className="campo campo-ancho">
                    <label htmlFor="prov-direccion">Dirección (opcional)</label>
                    <input
                        id="prov-direccion"
                        name="direccion"
                        value={datos.direccion}
                        onChange={cambiar}
                        maxLength={150}
                        className={errores.direccion ? 'con-error' : ''}
                    />
                    {errores.direccion?.map((mensaje, i) => (
                        <p className="error-campo" key={i}>{mensaje}</p>
                    ))}
                </div>
            </div>

            <div className="formulario-acciones">
                <button
                    type="button"
                    className="boton boton-secundario"
                    onClick={onCancelar}
                    disabled={guardando}
                >
                    Cancelar
                </button>
                <button type="submit" className="boton" disabled={guardando}>
                    {guardando
                        ? 'Guardando…'
                        : proveedor
                            ? 'Guardar cambios'
                            : 'Registrar proveedor'}
                </button>
            </div>
        </form>
    );
}
