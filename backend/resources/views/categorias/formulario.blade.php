@php($esEdicion = isset($categoria) && $categoria !== null)

{{--
    Formulario compartido de categoría. Con `novalidate` se
    desactivan los mensajes nativos del navegador para mostrar
    siempre nuestros mensajes de validación en español.
--}}
<form
    method="POST"
    class="tarjeta tarjeta-ancho formulario form-con-carga"
    novalidate
    action="{{ $esEdicion ? route('categorias.actualizar', $categoria) : route('categorias.guardar') }}"
>
    @csrf

    @if ($esEdicion)
        @method('PUT')
    @endif

    @if ($errors->any())
        <div class="alerta alerta-error" role="alert">
            <strong>Revisa el formulario:</strong>
            <ul class="lista-errores">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="formulario-campos">
        <div class="campo">
            <label for="nombre">Nombre</label>
            <input
                id="nombre"
                name="nombre"
                type="text"
                maxlength="50"
                required
                autofocus
                placeholder="Ej.: Narrativa"
                class="{{ $errors->has('nombre') ? 'con-error' : '' }}"
                value="{{ old('nombre', $categoria->nombre ?? '') }}"
            >
            @error('nombre')
                <p class="error-campo">{{ $message }}</p>
            @enderror
        </div>

        <div class="campo">
            <label for="estado">Estado</label>
            <select
                id="estado"
                name="estado"
                required
                class="{{ $errors->has('estado') ? 'con-error' : '' }}"
            >
                <option value="activo" {{ old('estado', $categoria->estado ?? 'activo') === 'activo' ? 'selected' : '' }}>Activo</option>
                <option value="inactivo" {{ old('estado', $categoria->estado ?? '') === 'inactivo' ? 'selected' : '' }}>Inactivo</option>
            </select>
            <span class="pista">Las categorías inactivas no aparecen al registrar libros.</span>
            @error('estado')
                <p class="error-campo">{{ $message }}</p>
            @enderror
        </div>

        <div class="campo campo-ancho">
            <label for="descripcion">Descripción <span class="etiqueta-ayuda">(opcional)</span></label>
            <input
                id="descripcion"
                name="descripcion"
                type="text"
                maxlength="150"
                class="{{ $errors->has('descripcion') ? 'con-error' : '' }}"
                value="{{ old('descripcion', $categoria->descripcion ?? '') }}"
                placeholder="Breve descripción de la categoría…"
            >
            @error('descripcion')
                <p class="error-campo">{{ $message }}</p>
            @enderror
        </div>
    </div>

    <div class="formulario-acciones">
        <a href="{{ route('categorias.index') }}" class="boton boton-secundario">Cancelar</a>

        <button type="submit" class="boton">
            {{ $esEdicion ? 'Guardar cambios' : 'Registrar categoría' }}
        </button>
    </div>
</form>

{{-- Estado de carga: evita envíos dobles mientras se guarda --}}
<script>
    document.querySelectorAll('form.form-con-carga').forEach((formulario) => {
        formulario.addEventListener('submit', () => {
            const boton = formulario.querySelector('[type="submit"]');
            boton.disabled = true;
            boton.textContent = 'Guardando…';
        });
    });
</script>
