{{-- =========================================================
     INFORMACIÓN PRINCIPAL
     ========================================================= --}}

<div class="producto-form-section">

    <div class="producto-form-section-title">
        <span class="producto-form-section-number">1</span>
        Información principal
    </div>

    <div class="row g-4">

        {{-- Código --}}
        <div class="col-md-4">

            <label
                for="codigo"
                class="producto-form-label"
            >
                Código
                <span class="producto-form-required">*</span>
            </label>

            <input
                type="text"
                id="codigo"
                name="codigo"
                class="producto-form-input @error('codigo') is-invalid @enderror"
                value="{{ old('codigo', $producto->codigo ?? '') }}"
                placeholder="Ej. CAM-001"
                maxlength="50"
                required
            >

            <span class="producto-form-help">
                Identificador único del producto.
            </span>

            @error('codigo')
                <div class="producto-form-error">
                    {{ $message }}
                </div>
            @enderror

        </div>


        {{-- Nombre --}}
        <div class="col-md-8">

            <label
                for="nombre"
                class="producto-form-label"
            >
                Nombre del producto
                <span class="producto-form-required">*</span>
            </label>

            <input
                type="text"
                id="nombre"
                name="nombre"
                class="producto-form-input @error('nombre') is-invalid @enderror"
                value="{{ old('nombre', $producto->nombre ?? '') }}"
                placeholder="Ej. Camisa casual manga larga"
                maxlength="150"
                required
            >

            <span class="producto-form-help">
                Usa un nombre claro y fácil de identificar.
            </span>

            @error('nombre')
                <div class="producto-form-error">
                    {{ $message }}
                </div>
            @enderror

        </div>


        {{-- Categoría --}}
        <div class="col-md-6">

            <label
                for="categoria_id"
                class="producto-form-label"
            >
                Categoría
                <span class="producto-form-required">*</span>
            </label>

            <select
                id="categoria_id"
                name="categoria_id"
                class="producto-form-select @error('categoria_id') is-invalid @enderror"
                required
            >

                <option value="">
                    Selecciona una categoría
                </option>

                @foreach($categorias as $categoria)

                    <option
                        value="{{ $categoria->id }}"
                        @selected(
                            old(
                                'categoria_id',
                                $producto->categoria_id ?? ''
                            ) == $categoria->id
                        )
                    >
                        {{ $categoria->nombre }}
                    </option>

                @endforeach

            </select>

            @error('categoria_id')
                <div class="producto-form-error">
                    {{ $message }}
                </div>
            @enderror

        </div>


        {{-- Precio --}}
        <div class="col-sm-6 col-md-3">

            <label
                for="precio"
                class="producto-form-label"
            >
                Precio
                <span class="producto-form-required">*</span>
            </label>

            <div class="producto-precio-group">

                <span class="producto-precio-symbol">
                    Q
                </span>

                <input
                    type="number"
                    id="precio"
                    name="precio"
                    class="producto-form-input @error('precio') is-invalid @enderror"
                    value="{{ old('precio', $producto->precio ?? '') }}"
                    placeholder="0.00"
                    min="0"
                    step="0.01"
                    required
                >

            </div>

            @error('precio')
                <div class="producto-form-error">
                    {{ $message }}
                </div>
            @enderror

        </div>


        {{-- Stock --}}
        <div class="col-sm-6 col-md-3">

            <label
                for="stock"
                class="producto-form-label"
            >
                Stock
                <span class="producto-form-required">*</span>
            </label>

            <input
                type="number"
                id="stock"
                name="stock"
                class="producto-form-input @error('stock') is-invalid @enderror"
                value="{{ old('stock', $producto->stock ?? 0) }}"
                min="0"
                required
            >

            <span class="producto-form-help">
                Unidades disponibles.
            </span>

            @error('stock')
                <div class="producto-form-error">
                    {{ $message }}
                </div>
            @enderror

        </div>

    </div>

</div>


{{-- =========================================================
     CARACTERÍSTICAS
     ========================================================= --}}

<div class="producto-form-section">

    <div class="producto-form-section-title">
        <span class="producto-form-section-number">2</span>
        Características de la prenda
    </div>

    <div class="row g-4">

        {{-- Talla --}}
        <div class="col-md-4">

            <label
                for="talla"
                class="producto-form-label"
            >
                Talla
            </label>

            <input
                type="text"
                id="talla"
                name="talla"
                class="producto-form-input @error('talla') is-invalid @enderror"
                value="{{ old('talla', $producto->talla ?? '') }}"
                placeholder="Ej. S, M, L, XL, 32..."
                maxlength="10"
            >

            <span class="producto-form-help">
                Puede dejarse vacío para accesorios.
            </span>

            @error('talla')
                <div class="producto-form-error">
                    {{ $message }}
                </div>
            @enderror

        </div>


        {{-- Color --}}
        <div class="col-md-4">

            <label
                for="color"
                class="producto-form-label"
            >
                Color
            </label>

            <input
                type="text"
                id="color"
                name="color"
                class="producto-form-input @error('color') is-invalid @enderror"
                value="{{ old('color', $producto->color ?? '') }}"
                placeholder="Ej. Negro"
                maxlength="40"
            >

            @error('color')
                <div class="producto-form-error">
                    {{ $message }}
                </div>
            @enderror

        </div>


        {{-- Género --}}
        <div class="col-md-4">

            <label
                for="genero"
                class="producto-form-label"
            >
                Género
            </label>

            <select
                id="genero"
                name="genero"
                class="producto-form-select @error('genero') is-invalid @enderror"
            >

                <option value="">
                    No aplica
                </option>

                <option
                    value="hombre"
                    @selected(old('genero', $producto->genero ?? '') === 'hombre')
                >
                    Hombre
                </option>

                <option
                    value="mujer"
                    @selected(old('genero', $producto->genero ?? '') === 'mujer')
                >
                    Mujer
                </option>

                <option
                    value="unisex"
                    @selected(old('genero', $producto->genero ?? '') === 'unisex')
                >
                    Unisex
                </option>

                <option
                    value="nino"
                    @selected(old('genero', $producto->genero ?? '') === 'nino')
                >
                    Niño
                </option>

                <option
                    value="nina"
                    @selected(old('genero', $producto->genero ?? '') === 'nina')
                >
                    Niña
                </option>

            </select>

            @error('genero')
                <div class="producto-form-error">
                    {{ $message }}
                </div>
            @enderror

        </div>


        {{-- Descripción --}}
        <div class="col-12">

            <label
                for="descripcion"
                class="producto-form-label"
            >
                Descripción
            </label>

            <textarea
                id="descripcion"
                name="descripcion"
                class="producto-form-textarea @error('descripcion') is-invalid @enderror"
                placeholder="Describe materiales, estilo, características u otros detalles importantes..."
            >{{ old('descripcion', $producto->descripcion ?? '') }}</textarea>

            <span class="producto-form-help">
                Esta información puede utilizarse posteriormente en el POS y en los detalles del producto.
            </span>

            @error('descripcion')
                <div class="producto-form-error">
                    {{ $message }}
                </div>
            @enderror

        </div>

    </div>

</div>


{{-- =========================================================
     IMAGEN Y ESTADO
     ========================================================= --}}

<div class="producto-form-section">

    <div class="producto-form-section-title">
        <span class="producto-form-section-number">3</span>
        Imagen y disponibilidad
    </div>

    <div class="row g-4">

        {{-- Imagen --}}
        <div class="col-lg-7">

            <label
                for="imagen"
                class="producto-form-label"
            >
                Imagen del producto
            </label>

            <div class="producto-image-upload">

                <span class="producto-image-icon">
                    🖼️
                </span>

                <div class="producto-image-title">
                    Agrega una fotografía del producto
                </div>

                <div class="producto-image-description">
                    JPG, JPEG, PNG o WEBP · Máximo 2 MB
                </div>

                <input
                    type="file"
                    id="imagen"
                    name="imagen"
                    class="form-control @error('imagen') is-invalid @enderror"
                    accept="image/jpeg,image/png,image/webp"
                >

            </div>

            @error('imagen')
                <div class="producto-form-error">
                    {{ $message }}
                </div>
            @enderror

        </div>


        {{-- Estado --}}
        <div class="col-lg-5">

            <label class="producto-form-label">
                Disponibilidad
            </label>

            <div class="producto-status-box">

                <div>

                    <p class="producto-status-title">
                        Producto activo
                    </p>

                    <p class="producto-status-description">
                        Los productos activos podrán utilizarse posteriormente en el punto de venta.
                    </p>

                </div>

                {{-- Permite enviar 0 cuando el switch está desactivado --}}
                <input
                    type="hidden"
                    name="activo"
                    value="0"
                >

                <div class="form-check form-switch">

                    <input
                        class="form-check-input"
                        type="checkbox"
                        role="switch"
                        id="activo"
                        name="activo"
                        value="1"
                        @checked(
                            (int) old(
                                'activo',
                                isset($producto) ? (int) $producto->activo : 1
                            ) === 1
                        )
                    >

                </div>

            </div>

        </div>

    </div>

</div>