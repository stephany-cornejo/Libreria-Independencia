@php($esEdicion = isset($usuario) && $usuario !== null)

{{--
    Formulario compartido de cuenta de usuario. Con `novalidate` se
    desactivan los mensajes nativos del navegador para mostrar
    siempre nuestros mensajes de validación en español. La contraseña
    es obligatoria al crear y opcional al editar.
--}}
<form
    method="POST"
    class="tarjeta tarjeta-ancho formulario form-con-carga"
    novalidate
    action="{{ $esEdicion ? route('usuarios.actualizar', $usuario) : route('usuarios.guardar') }}"
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
            <label for="nombre">Nombre completo</label>
            <input
                id="nombre"
                name="nombre"
                type="text"
                maxlength="100"
                required
                autofocus
                placeholder="Ej.: Ana Martínez"
                class="{{ $errors->has('nombre') ? 'con-error' : '' }}"
                value="{{ old('nombre', $usuario->nombre ?? '') }}"
            >
            @error('nombre')
                <p class="error-campo">{{ $message }}</p>
            @enderror
        </div>

        <div class="campo">
            <label for="usuario">Usuario de acceso</label>
            <input
                id="usuario"
                name="usuario"
                type="text"
                maxlength="50"
                required
                placeholder="Ej.: ana.martinez"
                class="{{ $errors->has('usuario') ? 'con-error' : '' }}"
                value="{{ old('usuario', $usuario->usuario ?? '') }}"
            >
            @error('usuario')
                <p class="error-campo">{{ $message }}</p>
            @enderror
        </div>

        <div class="campo">
            <label for="id_rol">Rol</label>
            <select
                id="id_rol"
                name="id_rol"
                required
                class="{{ $errors->has('id_rol') ? 'con-error' : '' }}"
            >
                <option value="">Selecciona un rol</option>
                @foreach ($roles as $rol)
                    <option
                        value="{{ $rol->id_rol }}"
                        {{ (string) old('id_rol', $usuario->id_rol ?? '') === (string) $rol->id_rol ? 'selected' : '' }}
                    >{{ $rol->nombre }}</option>
                @endforeach
            </select>
            <span class="pista">El rol define qué módulos puede consultar la cuenta.</span>
            @error('id_rol')
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
                <option value="activo" {{ old('estado', $usuario->estado ?? 'activo') === 'activo' ? 'selected' : '' }}>Activo</option>
                <option value="inactivo" {{ old('estado', $usuario->estado ?? '') === 'inactivo' ? 'selected' : '' }}>Inactivo</option>
            </select>
            <span class="pista">Las cuentas inactivas no pueden iniciar sesión.</span>
            @error('estado')
                <p class="error-campo">{{ $message }}</p>
            @enderror
        </div>

        <div class="campo campo-ancho">
            <label for="contrasena">
                Contraseña
                @if ($esEdicion)
                    <span class="etiqueta-ayuda">(opcional al editar)</span>
                @endif
            </label>
            <input
                id="contrasena"
                name="contrasena"
                type="password"
                maxlength="255"
                {{ $esEdicion ? '' : 'required' }}
                autocomplete="new-password"
                placeholder="{{ $esEdicion ? 'Déjala en blanco para conservar la actual' : 'Mínimo 12 caracteres' }}"
                class="{{ $errors->has('contrasena') ? 'con-error' : '' }}"
            >
            <span class="pista">Se almacena como hash; nunca se guarda ni se muestra en texto plano.</span>
            @error('contrasena')
                <p class="error-campo">{{ $message }}</p>
            @enderror
        </div>
    </div>

    <div class="formulario-acciones">
        <a href="{{ route('usuarios.index') }}" class="boton boton-secundario">Cancelar</a>

        <button type="submit" class="boton">
            {{ $esEdicion ? 'Guardar cambios' : 'Registrar cuenta' }}
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
