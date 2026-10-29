<!doctype html><html lang="es"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Finalizar pedido · SIS_ROPA</title><link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"></head>
<body class="bg-light"><main class="container py-5" style="max-width:900px"><div class="d-flex justify-content-between align-items-center mb-4"><h1 class="h3 fw-bold">Finalizar pedido</h1><a class="btn btn-outline-dark" href="{{ route('tienda.carrito') }}">Volver al carrito</a></div>
@if($errors->any())<div class="alert alert-danger"><ul class="mb-0">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
<div class="row g-4"><div class="col-lg-7"><div class="card border-0 rounded-4 shadow-sm"><div class="card-body p-4"><form method="POST" action="{{ route('tienda.confirmar') }}">@csrf
<div class="mb-3"><label class="form-label">Nombre de quien recibe *</label><input name="nombre_cliente" class="form-control" required value="{{ old('nombre_cliente',auth()->user()->nombre) }}"></div>
<div class="mb-3"><label class="form-label">Teléfono *</label><input name="telefono" class="form-control" required value="{{ old('telefono') }}"></div>
<div class="mb-3"><label class="form-label">Dirección de entrega *</label><textarea name="direccion_entrega" class="form-control" rows="2" maxlength="255" required>{{ old('direccion_entrega') }}</textarea></div>
<div class="mb-3"><label class="form-label">Indicaciones (opcional)</label><textarea name="indicaciones" class="form-control" rows="2">{{ old('indicaciones') }}</textarea></div>
<div class="mb-3"><label class="form-label fw-semibold">Método de pago *</label>
<div class="form-check mb-2"><input class="form-check-input" type="radio" name="metodo_pago" value="contra_entrega" id="pagoEntrega" @checked(old('metodo_pago','contra_entrega') === 'contra_entrega')><label class="form-check-label" for="pagoEntrega">Contra entrega (cobro al recibir)</label></div>
@if(app(\App\Services\StripeCheckoutService::class)->disponible())
<div class="form-check"><input class="form-check-input" type="radio" name="metodo_pago" value="stripe_prueba" id="pagoStripe" @checked(old('metodo_pago') === 'stripe_prueba')><label class="form-check-label" for="pagoStripe">Tarjeta de prueba · Stripe Sandbox (sin dinero real)</label></div>
<div class="form-text">Si eliges Stripe, primero se creará el pedido y luego podrás abrir su pago de prueba.</div>
@else
<div class="form-text">Stripe de prueba aún no está configurado. Puedes seguir usando pago contra entrega.</div>
@endif
</div>
<button class="btn btn-dark w-100">Confirmar pedido</button>
</form></div></div></div>
<div class="col-lg-5"><div class="card border-0 rounded-4 shadow-sm"><div class="card-body p-4"><h2 class="h5">Resumen</h2>@foreach($carrito as $linea)<div class="border-bottom py-2 d-flex justify-content-between gap-2"><div><strong>{{ $linea['variante']->producto->nombre }}</strong><div class="small text-secondary">{{ $linea['variante']->talla?->nombre ?? 'Única' }} · {{ $linea['variante']->color?->nombre ?? 'Sin color' }} · {{ $linea['cantidad'] }} ud.</div></div><span>Q{{ number_format($linea['subtotal'],2) }}</span></div>@endforeach<div class="d-flex justify-content-between mt-3"><strong>Total</strong><strong>Q{{ number_format(collect($carrito)->sum('subtotal'),2) }}</strong></div></div></div></div></div>
</main></body></html>
