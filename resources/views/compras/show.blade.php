@extends('layouts.app')
@section('title','Compra #'.$compra->id)
@section('page-title','Compra')
@section('content')
<div class="container py-4 py-lg-5" style="max-width:1040px">
 <div class="d-flex justify-content-between align-items-start mb-4 gap-3"><div><h1 class="h3 fw-bold">Compra #{{ $compra->id }}</h1><p class="text-secondary mb-0">{{ $compra->proveedor->nombre }} · {{ $compra->fecha->format('d/m/Y') }} · Estado: {{ ucfirst($compra->estado) }}</p></div><a href="{{ route('compras.index') }}" class="btn btn-outline-secondary">Volver</a></div>
 @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
 @if($errors->any())<div class="alert alert-danger"><ul class="mb-0">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
 <div class="card rounded-4 border-0 shadow-sm"><div class="card-body p-4">
  @if($compra->referencia)<p class="small text-secondary">Referencia: {{ $compra->referencia }}</p>@endif
  <form method="POST" action="{{ route('compras.recibir',$compra) }}">@csrf
   <div class="table-responsive"><table class="table align-middle"><thead><tr><th>Producto / Variante</th><th class="text-end">Pedido</th><th class="text-end">Recibido</th><th class="text-end">Pendiente</th><th class="text-end">Costo</th><th>Recibir ahora</th></tr></thead><tbody>
   @foreach($compra->detalles as $detalle)
    @php $pendiente=$detalle->cantidad-$detalle->cantidad_recibida; @endphp
    <tr><td><strong>{{ $detalle->variante->producto->nombre }}</strong><div class="small text-muted">{{ $detalle->variante->talla?->nombre ?? 'Única' }} / {{ $detalle->variante->color?->nombre ?? 'Sin color' }}</div></td><td class="text-end">{{ $detalle->cantidad }}</td><td class="text-end">{{ $detalle->cantidad_recibida }}</td><td class="text-end">{{ $pendiente }}</td><td class="text-end">Q{{ number_format($detalle->costo_unitario,2) }}</td><td><input type="number" name="recibir[{{ $detalle->id }}]" value="{{ $pendiente }}" min="0" max="{{ $pendiente }}" class="form-control" style="min-width:85px;width:110px" @disabled(!$pendiente)></td></tr>
   @endforeach
   </tbody></table></div>
   <div class="d-flex justify-content-between align-items-center gap-3"><strong>Total: Q{{ number_format($compra->total,2) }}</strong>@if($compra->estado!=='recibida')<button class="btn btn-dark" onclick="return confirm('¿Confirmas las cantidades que llegaron? Esto actualizará el inventario.')">Confirmar recepción</button>@else<span class="text-success"><i class="bi bi-check-circle"></i> Completamente recibida</span>@endif</div>
  </form>
  @if($compra->observaciones)<p class="text-secondary small mt-3">{{ $compra->observaciones }}</p>@endif
 </div></div>
</div>
@endsection
