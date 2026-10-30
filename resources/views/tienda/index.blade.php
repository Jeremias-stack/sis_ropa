<!doctype html>
<html lang="es"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>SIS_ROPA · Tienda</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"><link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet"></head>
<body class="bg-light">
<header class="bg-white border-bottom sticky-top"><div class="container py-3 d-flex flex-wrap justify-content-between gap-3 align-items-center">
<a href="{{ route('tienda.index') }}" class="text-decoration-none text-dark fs-4 fw-bold"><i class="bi bi-bag-heart me-2"></i>SIS_ROPA</a>
<div class="d-flex flex-wrap gap-2 align-items-center">
<a href="{{ route('tienda.carrito') }}" class="btn btn-outline-dark btn-sm"><i class="bi bi-cart3"></i> Carrito ({{ array_sum(session('carrito', [])) }})</a>
@if(auth()->check())
<span class="text-secondary small d-none d-sm-inline">{{ auth()->user()->nombre }}</span>
<a class="btn btn-outline-dark btn-sm" href="{{ route('pedidos.index') }}">Mis pedidos</a>
@if(in_array(auth()->user()->rol, ['administrador','cajero']))<a class="btn btn-outline-dark btn-sm" href="{{ route('dashboard') }}">Panel</a>@endif
<form method="post" action="{{ route('logout') }}">@csrf<button class="btn btn-outline-secondary btn-sm">Salir</button></form>
@else <a class="btn btn-outline-dark btn-sm" href="{{ route('login') }}">Ingresar</a><a class="btn btn-dark btn-sm" href="{{ route('register') }}">Registrarse</a>@endif
</div></div></header>
<main class="container py-5">
<div class="mb-4"><div class="text-uppercase text-secondary small fw-semibold">Colección de ropa</div><h1 class="display-6 fw-bold">Descubre nuestros productos</h1><p class="text-secondary">Selecciona la talla y el color disponibles de cada prenda.</p></div>
@if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
@if($errors->any())<div class="alert alert-danger"><ul class="mb-0">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
<form method="GET" class="mb-4 d-flex gap-2"><input name="buscar" class="form-control" value="{{ request('buscar') }}" placeholder="Buscar por nombre, marca, talla o color"><button class="btn btn-outline-dark">Buscar</button></form>
<div class="row g-4">@forelse($productos as $producto)
<div class="col-md-6 col-lg-4"><article class="card rounded-4 border-0 shadow-sm h-100 overflow-hidden">
@php $primeraFoto=$producto->imagen_url ?: $producto->imagenesColores->first()?->ruta; @endphp
@if($primeraFoto)<img src="{{ \App\Services\ImagenProductoStorage::url($primeraFoto) }}" alt="{{ $producto->nombre }}" class="card-img-top" data-producto-foto="{{ $producto->id }}" style="height:230px;object-fit:contain;background:white">
@else<div class="bg-white d-flex align-items-center justify-content-center" style="height:230px"><i class="bi bi-bag text-secondary" style="font-size:4rem"></i></div>@endif
<div class="card-body p-4 d-flex flex-column"><p class="text-secondary small mb-1">{{ $producto->marca?->nombre ?? 'Sin marca' }} · {{ $producto->categoria?->nombre }}</p><h2 class="h5 fw-bold">{{ $producto->nombre }}</h2><p class="fs-5 fw-semibold">Q{{ number_format($producto->precio,2) }}</p>
@if($producto->imagenesColores->isNotEmpty())<div class="d-flex flex-wrap gap-1 mb-3">@foreach($producto->imagenesColores as $fotoColor)<button type="button" class="btn btn-outline-secondary btn-sm ver-foto-color" data-target="{{ $producto->id }}" data-src="{{ \App\Services\ImagenProductoStorage::url($fotoColor->ruta) }}">{{ $fotoColor->color?->nombre }}</button>@endforeach</div>@endif
<form action="{{ route('tienda.agregar') }}" method="POST" class="mt-auto">@csrf
<label class="form-label small fw-semibold">Talla y color</label><select name="variante_id" class="form-select mb-2" required><option value="">Selecciona...</option>
@foreach($producto->variantes->filter(fn ($v)=>$v->stock>0) as $variante)
<option value="{{ $variante->id }}">{{ $variante->talla?->nombre ?? 'Única' }} · {{ $variante->color?->nombre ?? 'Sin color' }} — {{ $variante->stock }} disponibles</option>
@endforeach</select>
<div class="d-flex gap-2"><input type="number" name="cantidad" value="1" min="1" max="100" class="form-control" style="width:82px" aria-label="Cantidad" required><button class="btn btn-dark flex-grow-1" @disabled(!$producto->variantes->contains(fn($v)=>$v->stock>0))><i class="bi bi-cart-plus"></i> Agregar al carrito</button></div>
</form></div></article></div>
@empty<div class="col-12"><div class="alert alert-secondary">No encontramos productos con ese criterio.</div></div>@endforelse</div>
<div class="mt-4">{{ $productos->links() }}</div></main>
<script>document.querySelectorAll('.ver-foto-color').forEach(b=>b.addEventListener('click',()=>{let f=document.querySelector(`[data-producto-foto="${b.dataset.target}"]`);if(f)f.src=b.dataset.src;}));</script>
</body></html>
