@extends('layouts.app')
@section('title','Compras a proveedores')
@section('page-title','Compras')
@section('content')
<div class="container py-4 py-lg-5">
<div class="d-flex flex-wrap justify-content-between gap-2 align-items-center mb-4"><div><h1 class="h3 fw-bold">Compras a proveedores</h1><p class="text-secondary mb-0">El stock aumenta únicamente al recibir los productos.</p></div><a class="btn btn-dark" href="{{ route('compras.create') }}"><i class="bi bi-plus-lg"></i> Nueva compra</a></div>
@if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
<div class="card border-0 rounded-4 shadow-sm"><div class="card-body p-4"><div class="table-responsive"><table class="table align-middle table-hover"><thead><tr><th>Compra</th><th>Proveedor</th><th>Fecha</th><th>Estado</th><th class="text-end">Total</th><th></th></tr></thead><tbody>
@forelse($compras as $compra)<tr><td>#{{ $compra->id }} @if($compra->referencia)<div class="small text-secondary">{{ $compra->referencia }}</div>@endif</td><td>{{ $compra->proveedor->nombre }}</td><td>{{ $compra->fecha->format('d/m/Y') }}</td><td><span class="badge text-bg-light border">{{ ucfirst($compra->estado) }}</span></td><td class="text-end">Q{{ number_format($compra->total,2) }}</td><td class="text-end"><a class="btn btn-sm btn-outline-dark" href="{{ route('compras.show', $compra) }}">Ver</a></td></tr>
@empty<tr><td colspan="6" class="text-center py-4 text-secondary">Sin compras registradas.</td></tr>@endforelse
</tbody></table></div>{{ $compras->links() }}</div></div>
</div>
@endsection
