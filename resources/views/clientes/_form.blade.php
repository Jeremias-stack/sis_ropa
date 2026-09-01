<div class="cliente-form-section">

    <div class="cliente-form-section-title">

        <span class="cliente-form-section-number">
            1
        </span>

        Información personal

    </div>


    <div class="row g-3">

        <div class="col-md-8">

            <label
                for="nombre"
                class="form-label cliente-form-label"
            >
                Nombre completo *
            </label>

            <input
                type="text"
                name="nombre"
                id="nombre"
                class="form-control cliente-form-control @error('nombre') is-invalid @enderror"
                value="{{ old('nombre', $cliente->nombre ?? '') }}"
                maxlength="150"
                required
            >

            @error('nombre')
                <div class="cliente-form-error">
                    {{ $message }}
                </div>
            @enderror

        </div>


        <div class="col-md-4">

            <label
                for="nit"
                class="form-label cliente-form-label"
            >
                NIT
            </label>

            <input
                type="text"
                name="nit"
                id="nit"
                class="form-control cliente-form-control @error('nit') is-invalid @enderror"
                value="{{ old('nit', $cliente->nit ?? 'CF') }}"
                maxlength="20"
            >

            <span class="cliente-form-help">
                Si no posee NIT, puedes utilizar CF.
            </span>

            @error('nit')
                <div class="cliente-form-error">
                    {{ $message }}
                </div>
            @enderror

        </div>

    </div>

</div>


<div class="cliente-form-section">

    <div class="cliente-form-section-title">

        <span class="cliente-form-section-number">
            2
        </span>

        Información de contacto

    </div>


    <div class="row g-3">

        <div class="col-md-6">

            <label
                for="correo"
                class="form-label cliente-form-label"
            >
                Correo electrónico
            </label>

            <input
                type="email"
                name="correo"
                id="correo"
                class="form-control cliente-form-control @error('correo') is-invalid @enderror"
                value="{{ old('correo', $cliente->correo ?? '') }}"
                maxlength="150"
                placeholder="cliente@correo.com"
            >

            @error('correo')
                <div class="cliente-form-error">
                    {{ $message }}
                </div>
            @enderror

        </div>


        <div class="col-md-6">

            <label
                for="telefono"
                class="form-label cliente-form-label"
            >
                Teléfono
            </label>

            <input
                type="text"
                name="telefono"
                id="telefono"
                class="form-control cliente-form-control @error('telefono') is-invalid @enderror"
                value="{{ old('telefono', $cliente->telefono ?? '') }}"
                maxlength="20"
                placeholder="Ej. 5555-5555"
            >

            @error('telefono')
                <div class="cliente-form-error">
                    {{ $message }}
                </div>
            @enderror

        </div>


        <div class="col-12">

            <label
                for="direccion"
                class="form-label cliente-form-label"
            >
                Dirección
            </label>

            <textarea
                name="direccion"
                id="direccion"
                class="form-control cliente-form-control @error('direccion') is-invalid @enderror"
                maxlength="255"
                placeholder="Dirección del cliente..."
            >{{ old('direccion', $cliente->direccion ?? '') }}</textarea>

            @error('direccion')
                <div class="cliente-form-error">
                    {{ $message }}
                </div>
            @enderror

        </div>

    </div>

</div>