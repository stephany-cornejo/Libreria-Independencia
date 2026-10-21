@php($esEdicion = isset($proveedor) && $proveedor !== null)

{{--
    Formulario compartido de proveedor. Con `novalidate` se
    desactivan los mensajes nativos del navegador para mostrar
    siempre nuestros mensajes de validación en español.
--}}
<form
    method="POST"
    class="tarjeta tarjeta-ancho formulario form-con-carga"
    novalidate
    action="{{ $esEdicion ? route('proveedores.actualizar', $proveedor) : route('proveedores.guardar') }}"
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
                maxlength="100"
                required
                autofocus
                placeholder="Ej.: Distribuidora Nacional"
                class="{{ $errors->has('nombre') ? 'con-error' : '' }}"
                value="{{ old('nombre', $proveedor->nombre ?? '') }}"
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
                <option value="activo" {{ old('estado', $proveedor->estado ?? 'activo') === 'activo' ? 'selected' : '' }}>Activo</option>
                <option value="inactivo" {{ old('estado', $proveedor->estado ?? '') === 'inactivo' ? 'selected' : '' }}>Inactivo</option>
            </select>
            <span class="pista">Los proveedores inactivos no se ofrecen en futuras operaciones.</span>
            @error('estado')
                <p class="error-campo">{{ $message }}</p>
            @enderror
        </div>

        <div class="campo">
            <label for="telefono">Teléfono <span class="etiqueta-ayuda">(opcional)</span></label>
            <input
                id="telefono"
                name="telefono"
                type="text"
                maxlength="20"
                placeholder="02-1234567"
                class="{{ $errors->has('telefono') ? 'con-error' : '' }}"
                value="{{ old('telefono', $proveedor->telefono ?? '') }}"
            >
            @error('telefono')
                <p class="error-campo">{{ $message }}</p>
            @enderror
        </div>

        <div class="campo">
            <label for="email">Correo <span class="etiqueta-ayuda">(opcional)</span></label>
            <input
                id="email"
                name="email"
                type="email"
                maxlength="100"
                placeholder="contacto@proveedor.com"
                class="{{ $errors->has('email') ? 'con-error' : '' }}"
                value="{{ old('email', $proveedor->email ?? '') }}"
            >
            @error('email')
                <p class="error-campo">{{ $message }}</p>
            @enderror
        </div>

        <div class="campo campo-ancho">
            <label for="direccion">Dirección <span class="etiqueta-ayuda">(opcional)</span></label>
            <input
                id="direccion"
                name="direccion"
                type="text"
                maxlength="150"
                class="{{ $errors->has('direccion') ? 'con-error' : '' }}"
                value="{{ old('direccion', $proveedor->direccion ?? '') }}"
                placeholder="Calle, número, ciudad…"
            >
            @error('direccion')
                <p class="error-campo">{{ $message }}</p>
            @enderror
        </div>
    </div>

    <div class="formulario-acciones">
        <a href="{{ route('proveedores.index') }}" class="boton boton-secundario">Cancelar</a>

        <button type="submit" class="boton">
            {{ $esEdicion ? 'Guardar cambios' : 'Registrar proveedor' }}
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
