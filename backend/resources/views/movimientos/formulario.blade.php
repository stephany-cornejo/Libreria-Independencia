{{--
    Formulario compartido de movimiento. Con `novalidate` se
    desactivan los mensajes nativos del navegador para mostrar
    siempre nuestros mensajes de validación en español.
--}}
<form
    method="POST"
    class="tarjeta tarjeta-ancho formulario form-con-carga"
    novalidate
    action="{{ route('movimientos.guardar') }}"
>
    @csrf

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
            <label for="tipo">Tipo de movimiento</label>
            <select
                id="tipo"
                name="tipo"
                required
                class="{{ $errors->has('tipo') ? 'con-error' : '' }}"
            >
                <option value="">Selecciona un tipo</option>
                <option value="entrada" {{ old('tipo') === 'entrada' ? 'selected' : '' }}>Entrada (suma existencias)</option>
                <option value="salida" {{ old('tipo') === 'salida' ? 'selected' : '' }}>Salida (resta existencias)</option>
            </select>
            @error('tipo')
                <p class="error-campo">{{ $message }}</p>
            @enderror
        </div>

        <div class="campo">
            <label for="motivo">Motivo</label>
            <select
                id="motivo"
                name="motivo"
                required
                class="{{ $errors->has('motivo') ? 'con-error' : '' }}"
            >
                <option value="">Selecciona primero el tipo</option>
                @foreach ($motivosPorTipo as $tipo => $motivos)
                    @foreach ($motivos as $clave => $etiqueta)
                        <option
                            value="{{ $clave }}"
                            data-tipo="{{ $tipo }}"
                            {{ old('motivo') === $clave ? 'selected' : '' }}
                        >{{ $etiqueta }}</option>
                    @endforeach
                @endforeach
            </select>
            <span class="pista">Los motivos disponibles dependen del tipo seleccionado.</span>
            @error('motivo')
                <p class="error-campo">{{ $message }}</p>
            @enderror
        </div>

        <div class="campo campo-ancho">
            <label for="id_producto">Libro</label>
            <select
                id="id_producto"
                name="id_producto"
                required
                class="{{ $errors->has('id_producto') ? 'con-error' : '' }}"
            >
                <option value="">Selecciona un libro</option>
                @foreach ($productos as $producto)
                    <option
                        value="{{ $producto->id_producto }}"
                        data-stock="{{ $producto->cantidad_disponible }}"
                        {{ (string) old('id_producto') === (string) $producto->id_producto ? 'selected' : '' }}
                    >{{ $producto->nombre }} ({{ $producto->codigo }}) — {{ $producto->cantidad_disponible }} unid.</option>
                @endforeach
            </select>
            <span class="pista" id="pista-stock"></span>
            @error('id_producto')
                <p class="error-campo">{{ $message }}</p>
            @enderror
        </div>

        <div class="campo">
            <label for="cantidad">Cantidad</label>
            <input
                id="cantidad"
                name="cantidad"
                type="number"
                step="1"
                min="1"
                inputmode="numeric"
                required
                class="{{ $errors->has('cantidad') ? 'con-error' : '' }}"
                value="{{ old('cantidad', '') }}"
            >
            <span class="pista">Debe ser al menos 1 unidad.</span>
            @error('cantidad')
                <p class="error-campo">{{ $message }}</p>
            @enderror
        </div>

        <div class="campo">
            <label for="observaciones">Observaciones <span class="etiqueta-ayuda">(opcional)</span></label>
            <input
                id="observaciones"
                name="observaciones"
                type="text"
                maxlength="255"
                class="{{ $errors->has('observaciones') ? 'con-error' : '' }}"
                value="{{ old('observaciones', '') }}"
                placeholder="Detalle del motivo, proveedor, folio…"
            >
            @error('observaciones')
                <p class="error-campo">{{ $message }}</p>
            @enderror
        </div>
    </div>

    <div class="formulario-acciones">
        <a href="{{ route('movimientos.index') }}" class="boton boton-secundario">Cancelar</a>

        <button type="submit" class="boton">Registrar movimiento</button>
    </div>
</form>

{{-- Filtro de motivos según tipo + pista de existencias del libro --}}
<script>
    const tipo = document.getElementById('tipo');
    const motivo = document.getElementById('motivo');
    const producto = document.getElementById('id_producto');
    const pistaStock = document.getElementById('pista-stock');

    function filtrarMotivos() {
        const opciones = motivo.querySelectorAll('option[data-tipo]');

        opciones.forEach((opcion) => {
            const visible = opcion.dataset.tipo === tipo.value;
            opcion.hidden = ! visible;
            opcion.disabled = ! visible;
        });

        const seleccionada = motivo.querySelector('option[selected]:not([hidden])');
        if (! motivo.value || (motivo.selectedOptions[0]?.disabled ?? true)) {
            motivo.value = seleccionada ? seleccionada.value : '';
        }

        if (! tipo.value) {
            motivo.querySelector('option[value=""]').selected = true;
        }
    }

    function mostrarStock() {
        const seleccionado = producto.selectedOptions[0];
        pistaStock.textContent = seleccionado?.dataset.stock !== undefined
            ? `Existencias actuales: ${seleccionado.dataset.stock} unidades.`
            : '';
    }

    tipo.addEventListener('change', filtrarMotivos);
    producto.addEventListener('change', mostrarStock);

    filtrarMotivos();
    mostrarStock();

    // Estado de carga: evita envíos dobles mientras se guarda
    document.querySelectorAll('form.form-con-carga').forEach((formulario) => {
        formulario.addEventListener('submit', () => {
            const boton = formulario.querySelector('[type="submit"]');
            boton.disabled = true;
            boton.textContent = 'Guardando…';
        });
    });
</script>
