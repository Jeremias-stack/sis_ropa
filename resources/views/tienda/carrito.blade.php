<!doctype html><html lang="es"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Carrito · SIS_ROPA</title><link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"></head>
<body class="bg-light"><main class="container py-5" style="max-width:1000px"><div class="d-flex justify-content-between align-items-center mb-4"><h1 class="h3 fw-bold">Tu carrito</h1><a href="{{ route('tienda.index') }}" class="btn btn-outline-dark">Seguir comprando</a></div>
@if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
@if(session('error'))<div class="alert alert-warning">{{ session('error') }}</div>@endif
@if($errors->any())<div class="alert alert-danger"><ul class="mb-0">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
@if(count($carrito))
<form method="POST" action="{{ route('tienda.actualizar') }}">@csrf @method('PUT')<div class="card border-0 shadow-sm rounded-4"><div class="card-body p-4"><div class="table-responsive"><table class="table align-middle"><thead><tr><th>Producto / Variante</th><th class="text-end">Precio</th><th>Cantidad</th><th class="text-end">Subtotal</th></tr></thead><tbody>
@foreach($carrito as $linea)<tr><td><strong>{{ $linea['variante']->producto->nombre }}</strong><div class="small text-secondary">{{ $linea['variante']->talla?->nombre ?? 'Única' }} · {{ $linea['variante']->color?->nombre ?? 'Sin color' }} · Disponible {{ $linea['variante']->stock }}</div></td><td class="text-end">Q{{ number_format($linea['variante']->producto->precio,2) }}</td><td><input type="number" name="cantidades[{{ $linea['variante']->id }}]" min="0" max="100" value="{{ $linea['cantidad'] }}" class="form-control" style="width:100px"><span class="small text-secondary">0 para quitar</span></td><td class="text-end">Q{{ number_format($linea['subtotal'],2) }}</td></tr>@endforeach
</tbody></table></div>
<div class="d-flex justify-content-between align-items-center flex-wrap gap-2"><button class="btn btn-outline-dark">Actualizar carrito</button><div class="fs-5 fw-bold">Total: Q{{ number_format(collect($carrito)->sum('subtotal'),2) }}</div></div>
</div></div></form>
<div class="text-end mt-3"><a href="{{ route('tienda.checkout') }}" class="btn btn-dark btn-lg">Continuar pedido</a></div>
@else<div class="alert alert-secondary">Todavía no agregaste productos.</div>@endif
</main></body></html>
