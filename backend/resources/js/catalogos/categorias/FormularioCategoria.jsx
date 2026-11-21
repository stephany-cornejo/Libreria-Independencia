
import { useState } from 'react';
import { api } from '../api';

export default function FormularioCategoria({ categoria = null, onGuardado, onCancelar }) {
    const [datos, setDatos] = useState({
        nombre: categoria?.nombre ?? '',
        descripcion: categoria?.descripcion ?? '',
        estado: categoria?.estado ?? 'activo',
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
            descripcion: datos.descripcion.trim() || null,
            estado: datos.estado,
        };

        try {
            const respuesta = categoria
                ? await api.put(`/api/categorias/${categoria.id_categoria}`, payload)
                : await api.post('/api/categorias', payload);

            onGuardado(respuesta.data.mensaje);
        } catch (error) {
            if (error.response?.status === 422) {
                setErrores(error.response.data.errors ?? {});
                setErrorGeneral('Revisa los campos indicados.');
            } else {
                setErrorGeneral(
                    error.response?.data?.mensaje ||
                    error.response?.data?.message ||
                    'No se pudo guardar la categoría.'
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
            <h2>{categoria ? 'Editar categoría' : 'Registrar categoría'}</h2>

            {errorGeneral && (
                <div className="alerta alerta-error" role="alert">
                    {errorGeneral}
                </div>
            )}

            <div className="formulario-campos">
                <div className="campo">
                    <label htmlFor="cat-nombre">Nombre</label>
                    <input
                        id="cat-nombre"
                        name="nombre"
                        value={datos.nombre}
                        onChange={cambiar}
                        maxLength={50}
                        required
                        autoFocus
                        className={errores.nombre ? 'con-error' : ''}
                    />
                    {errores.nombre?.map((mensaje, i) => (
                        <p className="error-campo" key={i}>{mensaje}</p>
                    ))}
                </div>

                <div className="campo">
                    <label htmlFor="cat-estado">Estado</label>
                    <select
                        id="cat-estado"
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

                <div className="campo campo-ancho">
                    <label htmlFor="cat-descripcion">Descripción (opcional)</label>
                    <input
                        id="cat-descripcion"
                        name="descripcion"
                        value={datos.descripcion}
                        onChange={cambiar}
                        maxLength={150}
                        className={errores.descripcion ? 'con-error' : ''}
                    />
                    {errores.descripcion?.map((mensaje, i) => (
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
                        : categoria
                            ? 'Guardar cambios'
                            : 'Registrar categoría'}
                </button>
            </div>
        </form>
    );
}
