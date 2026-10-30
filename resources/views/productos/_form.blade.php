@php
    $filas = old('variantes', isset($producto)
        ? $producto->variantes->map(fn ($v) => ['id'=>$v->id, 'talla_id'=>$v->talla_id, 'color_id'=>$v->color_id, 'stock'=>$v->stock])->toArray()
        : [['talla_id'=>null,'color_id'=>null,'stock'=>0]]);
@endphp
<div class="p-4 p-lg-5">
    @if($errors->any())
        <div class="alert alert-danger rounded-3"><strong>Revisa estos datos:</strong><ul class="mb-0 mt-1">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
    @endif
    <div class="mb-4">
        <h2 class="h5 fw-bold">1. Información del producto</h2>
        <p class="text-muted small">Registra el producto una sola vez. Sus tallas y colores se agregan debajo.</p>
        <div class="row g-3">
            <div class="col-md-4"><label for="codigo" class="form-label fw-semibold">Código principal *</label><input id="codigo" name="codigo" class="form-control" maxlength="50" required value="{{ old('codigo',$producto->codigo ?? '') }}" placeholder="Ej. PLY-NIKE-001"></div>
            <div class="col-md-8"><label for="nombre" class="form-label fw-semibold">Nombre *</label><input id="nombre" name="nombre" class="form-control" maxlength="150" required value="{{ old('nombre',$producto->nombre ?? '') }}" placeholder="Ej. Playera Deportiva Fly"></div>
            <div class="col-md-4"><label for="categoria_id" class="form-label fw-semibold">Categoría *</label><select id="categoria_id" name="categoria_id" class="form-select" required><option value="">Selecciona</option>@foreach($categorias as $categoria)<option value="{{ $categoria->id }}" @selected(old('categoria_id', $producto->categoria_id ?? '') == $categoria->id)>{{ $categoria->nombre }}</option>@endforeach</select></div>
            <div class="col-md-4"><label for="marca_id" class="form-label fw-semibold">Marca</label><select id="marca_id" name="marca_id" class="form-select"><option value="">Genérica / sin marca</option>@foreach($marcas as $marca)<option value="{{ $marca->id }}" @selected(old('marca_id', $producto->marca_id ?? '') == $marca->id)>{{ $marca->nombre }}</option>@endforeach</select></div>
            <div class="col-md-4"><label for="precio" class="form-label fw-semibold">Precio de venta (Q) *</label><input type="number" id="precio" name="precio" class="form-control" min="0" step="0.01" required value="{{ old('precio',$producto->precio ?? '') }}" placeholder="175.00"></div>
            <div class="col-12"><a class="small text-decoration-none" href="{{ route('catalogos.index') }}"><i class="bi bi-plus-circle me-1"></i>Agregar categoría, marca, talla o color al catálogo</a></div>
            <div class="col-md-4"><label for="genero" class="form-label fw-semibold">Sección</label><select id="genero" name="genero" class="form-select"><option value="">No aplica</option>@foreach(['hombre'=>'Hombre','mujer'=>'Mujer','unisex'=>'Unisex','nino'=>'Niño','nina'=>'Niña'] as $valor=>$etiqueta)<option value="{{ $valor }}" @selected(old('genero',$producto->genero ?? '')===$valor)>{{ $etiqueta }}</option>@endforeach</select></div>
            <div class="col-md-8"><label for="descripcion" class="form-label fw-semibold">Descripción</label><textarea id="descripcion" name="descripcion" rows="2" class="form-control" placeholder="Material, estilo, detalles...">{{ old('descripcion',$producto->descripcion ?? '') }}</textarea></div>
        </div>
    </div>
    <hr class="my-4">
    <div class="mb-4">
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-2">
            <div><h2 class="h5 fw-bold mb-1">2. Tallas, colores y existencias</h2><p class="small text-muted mb-0">Cada fila es una combinación. Puedes dejar talla y color vacíos si no aplican.</p></div>
            <button type="button" id="agregarVariante" class="btn btn-outline-dark btn-sm"><i class="bi bi-plus-lg"></i> Agregar combinación</button>
        </div>
        <div class="table-responsive border rounded-3"><table class="table table-hover align-middle mb-0"><thead class="table-light"><tr><th>Talla</th><th>Color</th><th style="min-width:130px">Cantidad</th><th></th></tr></thead><tbody id="filasVariantes">
            @foreach($filas as $i => $fila)
            <tr data-existing="{{ !empty($fila['id']) ? '1' : '0' }}">
                <td>@if(!empty($fila['id']))<input type="hidden" name="variantes[{{ $i }}][id]" value="{{ $fila['id'] }}">@endif<select class="form-select form-select-sm" name="variantes[{{ $i }}][talla_id]" @if(!empty($fila['id'])) style="pointer-events:none;background-color:#f5f6f7" tabindex="-1" @endif><option value="">Sin talla</option>@foreach($tallas as $talla)<option value="{{ $talla->id }}" @selected(($fila['talla_id'] ?? '') == $talla->id)>{{ $talla->nombre }}</option>@endforeach</select></td>
                <td><select class="form-select form-select-sm" name="variantes[{{ $i }}][color_id]" @if(!empty($fila['id'])) style="pointer-events:none;background-color:#f5f6f7" tabindex="-1" @endif><option value="">Sin color</option>@foreach($colores as $color)<option value="{{ $color->id }}" @selected(($fila['color_id'] ?? '') == $color->id)>{{ $color->nombre }}</option>@endforeach</select></td>
                <td><input class="form-control form-control-sm variante-stock" type="number" min="0" max="1000000" name="variantes[{{ $i }}][stock]" value="{{ $fila['stock'] ?? 0 }}" required></td>
                <td class="text-end">@if(empty($fila['id']))<button type="button" class="btn btn-sm btn-outline-danger eliminarVariante" title="Quitar fila"><i class="bi bi-x-lg"></i></button>@else<span class="small text-muted" title="Mantén el historial de esta variante">Guardada</span>@endif</td>
            </tr>
            @endforeach
        </tbody></table></div>
        <div class="d-flex justify-content-end pt-2"><span class="small text-muted">Unidades totales: <strong id="totalExistencias">0</strong></span></div>
        <p class="small text-muted">Para una combinación que ya existe, modifica solo la cantidad. Si necesitas otra talla o color, agrega una nueva fila. Las variantes guardadas pueden dejarse en cero para conservar el historial.</p>
    </div>
    <hr class="my-4">
    <div class="row g-4 align-items-center">
        <div class="col-md-7"><label for="imagen" class="form-label fw-semibold">3. Fotografía del producto</label><input type="file" id="imagen" name="imagen" class="form-control" accept="image/png,image/jpeg,image/webp"><div class="form-text">JPG, PNG o WebP, máximo 2 MB.</div></div>
        <div class="col-md-5"><input type="hidden" name="activo" value="0"><div class="form-check form-switch"><input type="checkbox" name="activo" class="form-check-input" id="activo" value="1" @checked((int)old('activo',isset($producto)?(int)$producto->activo:1)===1)><label for="activo" class="form-check-label fw-semibold">Producto activo y disponible para la venta</label></div></div>
    </div>
    <div class="mt-4">
        <h3 class="h6 fw-bold">4. Fotografías por color (opcional)</h3>
        <p class="small text-muted">Cada color puede tener una fotografía distinta. La foto se comparte entre todas las tallas de ese color. Primero agrega el color en la tabla de variantes.</p>
        <div class="row g-3">
        @foreach($colores as $color)
            @php $fotoExistente = isset($producto) ? $producto->imagenesColores->firstWhere('color_id', $color->id) : null; @endphp
            <div class="col-md-6 foto-por-color d-none" data-color-id="{{ $color->id }}">
                <label class="form-label fw-semibold">{{ $color->nombre }}</label>
                <div class="d-flex align-items-center gap-2">
                    @if($fotoExistente)<img src="{{ \App\Services\ImagenProductoStorage::url($fotoExistente->ruta) }}" alt="{{ $color->nombre }}" width="58" height="58" class="rounded border" style="object-fit:contain">@endif
                    <input type="file" class="form-control" name="imagenes_colores[{{ $color->id }}]" accept="image/png,image/jpeg,image/webp">
                </div>
            </div>
        @endforeach
        </div>
    </div>
</div>
<template id="nuevaFilaVariante"><tr data-existing="0"><td><select class="form-select form-select-sm" name="variantes[__INDEX__][talla_id]"><option value="">Sin talla</option>@foreach($tallas as $talla)<option value="{{ $talla->id }}">{{ $talla->nombre }}</option>@endforeach</select></td><td><select class="form-select form-select-sm" name="variantes[__INDEX__][color_id]"><option value="">Sin color</option>@foreach($colores as $color)<option value="{{ $color->id }}">{{ $color->nombre }}</option>@endforeach</select></td><td><input class="form-control form-control-sm variante-stock" type="number" min="0" max="1000000" name="variantes[__INDEX__][stock]" value="0" required></td><td class="text-end"><button type="button" class="btn btn-sm btn-outline-danger eliminarVariante" title="Quitar fila"><i class="bi bi-x-lg"></i></button></td></tr></template>
<script>
(() => {
    const filas = document.getElementById('filasVariantes');
    const boton = document.getElementById('agregarVariante');
    const template = document.getElementById('nuevaFilaVariante');
    const total = document.getElementById('totalExistencias');
    let siguiente = {{ count($filas) }};
    function recalcular() {
        total.textContent = [...filas.querySelectorAll('.variante-stock')]
            .reduce((sum, input) => sum + (Math.max(0, Number(input.value)) || 0), 0);
        const coloresElegidos = new Set([...filas.querySelectorAll('select[name$="[color_id]"]')]
            .map(select => select.value).filter(Boolean));
        document.querySelectorAll('.foto-por-color').forEach(contenedor => {
            const activo = coloresElegidos.has(contenedor.dataset.colorId);
            contenedor.classList.toggle('d-none', !activo);
            if (!activo) {
                const campo = contenedor.querySelector('input[type="file"]');
                if (campo) campo.value = '';
            }
        });
    }
    boton.addEventListener('click', () => {
        filas.insertAdjacentHTML('beforeend', template.innerHTML.replaceAll('__INDEX__', siguiente++));
        recalcular();
    });
    filas.addEventListener('click', event => {
        const quitar = event.target.closest('.eliminarVariante');
        if (!quitar) return;
        if (filas.children.length === 1) { alert('Deja al menos una combinación.'); return; }
        quitar.closest('tr').remove();
        recalcular();
    });
    filas.addEventListener('input', recalcular);
    filas.addEventListener('change', recalcular);
    recalcular();
})();
</script>
