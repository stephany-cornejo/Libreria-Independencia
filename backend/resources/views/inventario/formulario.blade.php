@php($esEdicion = isset($producto) && $producto !== null)

{{--
    Formulario compartido de libro. Con `novalidate` se desactivan
    los mensajes nativos del navegador para mostrar siempre nuestros
    mensajes de validación en español, con el mismo aspecto.
--}}
<form
    method="POST"
    class="tarjeta tarjeta-ancho formulario form-con-carga"
    novalidate
    action="{{ $esEdicion ? route('inventario.actualizar', $producto) : route('inventario.guardar') }}"
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
        <div class="campo campo-ancho">
            <label for="nombre">Título</label>
            <input
                id="nombre"
                name="nombre"
                type="text"
                maxlength="100"
                required
                autofocus
                class="{{ $errors->has('nombre') ? 'con-error' : '' }}"
                value="{{ old('nombre', $producto->nombre ?? '') }}"
            >
            @error('nombre')
                <p class="error-campo">{{ $message }}</p>
            @enderror
        </div>

        <div class="campo">
            <label for="autor">Autor <span class="etiqueta-ayuda">(opcional)</span></label>
            <input
                id="autor"
                name="autor"
                type="text"
                maxlength="100"
                class="{{ $errors->has('autor') ? 'con-error' : '' }}"
                value="{{ old('autor', $producto->autor ?? '') }}"
            >
            @error('autor')
                <p class="error-campo">{{ $message }}</p>
            @enderror
        </div>

        <div class="campo">
            <label for="editorial">Editorial <span class="etiqueta-ayuda">(opcional)</span></label>
            <input
                id="editorial"
                name="editorial"
                type="text"
                maxlength="100"
                class="{{ $errors->has('editorial') ? 'con-error' : '' }}"
                value="{{ old('editorial', $producto->editorial ?? '') }}"
            >
            @error('editorial')
                <p class="error-campo">{{ $message }}</p>
            @enderror
        </div>

        <div class="campo">
            <label for="isbn">ISBN <span class="etiqueta-ayuda">(opcional)</span></label>
            <input
                id="isbn"
                name="isbn"
                type="text"
                maxlength="20"
                placeholder="978-84-376-0494-7"
                class="{{ $errors->has('isbn') ? 'con-error' : '' }}"
                value="{{ old('isbn', $producto->isbn ?? '') }}"
            >
            <span class="pista">ISBN-10 o ISBN-13; se comprueba su dígito verificador.</span>
            @error('isbn')
                <p class="error-campo">{{ $message }}</p>
            @enderror
        </div>

        <div class="campo">
            <label for="id_categoria">Categoría</label>
            <select
                id="id_categoria"
                name="id_categoria"
                required
                class="{{ $errors->has('id_categoria') ? 'con-error' : '' }}"
            >
                <option value="">Selecciona una categoría</option>
                @foreach ($categorias as $categoria)
                    <option
                        value="{{ $categoria->id_categoria }}"
                        {{ (string) old('id_categoria', $producto->id_categoria ?? '') === (string) $categoria->id_categoria ? 'selected' : '' }}
                    >{{ $categoria->nombre }}</option>
                @endforeach
            </select>
            @error('id_categoria')
                <p class="error-campo">{{ $message }}</p>
            @enderror
        </div>

        <div class="campo">
            <label for="precio">Precio <span class="etiqueta-ayuda">(USD)</span></label>
            <input
                id="precio"
                name="precio"
                type="number"
                step="0.01"
                min="0"
                inputmode="decimal"
                required
                class="{{ $errors->has('precio') ? 'con-error' : '' }}"
                value="{{ old('precio', $producto->precio_venta ?? '') }}"
            >
            <span class="pista">Precio de venta al público; debe ser mayor que cero.</span>
            @error('precio')
                <p class="error-campo">{{ $message }}</p>
            @enderror
        </div>

        <div class="campo">
            <label for="cantidad">Cantidad en stock</label>
            <input
                id="cantidad"
                name="cantidad"
                type="number"
                step="1"
                min="0"
                inputmode="numeric"
                required
                class="{{ $errors->has('cantidad') ? 'con-error' : '' }}"
                value="{{ old('cantidad', $producto->cantidad_disponible ?? '') }}"
            >
            @error('cantidad')
                <p class="error-campo">{{ $message }}</p>
            @enderror
        </div>
    </div>

    <div class="formulario-acciones">
        <a href="{{ route('inventario.index') }}" class="boton boton-secundario">Cancelar</a>

        <button type="submit" class="boton">
            {{ $esEdicion ? 'Guardar cambios' : 'Registrar libro' }}
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
