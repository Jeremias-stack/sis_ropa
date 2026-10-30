@extends('layouts.app')
@section('title','Nueva compra')
@section('page-title','Nueva compra')
@section('content')
<div class="container py-4 py-lg-5" style="max-width:1050px">
 <div class="d-flex justify-content-between align-items-center mb-4"><div><h1 class="h3 fw-bold">Registrar compra</h1><p class="text-secondary">Selecciona proveedor y variantes. Puedes recibir la mercancía después.</p></div><a href="{{ route('compras.index') }}" class="btn btn-outline-secondary">Volver</a></div>
 @if($errors->any())<div class="alert alert-danger"><strong>Revisa estos datos:</strong><ul class="mb-0">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
 @if($proveedores->isEmpty())<div class="alert alert-warning">Debes <a href="{{ route('proveedores.index') }}">registrar un proveedor</a> antes de crear una compra.</div>@endif
 <form action="{{ route('compras.store') }}" method="POST" id="compraForm">@csrf
  <div class="card border-0 rounded-4 shadow-sm mb-3"><div class="card-body p-4"><div class="row g-3">
   <div class="col-md-6"><label class="form-label">Proveedor *</label><select name="proveedor_id" class="form-select" required><option value="">Selecciona...</option>@foreach($proveedores as $p)<option value="{{ $p->id }}" @selected(old('proveedor_id')==$p->id)>{{ $p->nombre }}</option>@endforeach</select></div>
   <div class="col-md-6"><label class="form-label">Fecha *</label><input type="date" name="fecha" class="form-control" required value="{{ old('fecha', now()->toDateString()) }}"></div>
   <div class="col-md-6"><label class="form-label">Número de factura / referencia</label><input name="referencia" class="form-control" value="{{ old('referencia') }}"></div>
   <div class="col-md-6"><label class="form-label">Observaciones</label><input name="observaciones" class="form-control" value="{{ old('observaciones') }}"></div>
  </div></div></div>
  <div class="card border-0 rounded-4 shadow-sm"><div class="card-body p-4">
   <h2 class="h5 fw-bold">Productos comprados</h2><p class="text-secondary small">Una línea por talla y color. Cada combinación puede tener distinto costo.</p>
   <div class="table-responsive"><table class="table align-middle" id="lineas"><thead><tr><th style="min-width:240px">Producto / Talla / Color</th><th>Cantidad</th><th>Costo unitario (Q)</th><th>Subtotal</th><th></th></tr></thead><tbody></tbody></table></div>
   <div class="d-flex justify-content-between align-items-center flex-wrap gap-2"><button type="button" id="agregarLinea" class="btn btn-outline-dark"><i class="bi bi-plus-lg"></i> Agregar producto</button><div><strong>Total estimado: <span id="totalCompra">Q0.00</span></strong></div></div>
  </div></div>
  <div class="d-flex justify-content-end mt-3"><button class="btn btn-dark px-4" @disabled($proveedores->isEmpty())>Guardar compra pendiente</button></div>
 </form>
</div>
<template id="lineaTemplate"><tr><td><select class="form-select variante" required>
<option value="">Selecciona una variante...</option>
@foreach($productos as $producto)
@foreach($producto->variantes as $variante)
<option value="{{ $variante->id }}">{{ $producto->nombre }} / {{ $variante->talla?->nombre ?? 'Única' }} / {{ $variante->color?->nombre ?? 'Sin color' }}</option>
@endforeach
@endforeach
</select></td><td><input type="number" class="form-control cantidad" min="1" max="100000" value="1" required></td><td><input type="number" class="form-control costo" min="0" max="999999" step="0.01" value="0" required></td><td class="subtotal text-nowrap">Q0.00</td><td><button class="btn btn-outline-danger btn-sm quitar" type="button" aria-label="Quitar línea"><i class="bi bi-x-lg"></i></button></td></tr></template>
<script>
(() => {
const tbody=document.querySelector('#lineas tbody'),tpl=document.querySelector('#lineaTemplate'),total=document.querySelector('#totalCompra');
function reordenar(){let t=0;[...tbody.children].forEach((tr,i)=>{tr.querySelector('.variante').name=`items[${i}][variante_id]`;tr.querySelector('.cantidad').name=`items[${i}][cantidad]`;tr.querySelector('.costo').name=`items[${i}][costo_unitario]`;let n=Number(tr.querySelector('.cantidad').value)||0,p=Number(tr.querySelector('.costo').value)||0,s=n*p;t+=s;tr.querySelector('.subtotal').textContent=`Q${s.toFixed(2)}`;});total.textContent=`Q${t.toFixed(2)}`;}
function agregar(){tbody.appendChild(tpl.content.firstElementChild.cloneNode(true));reordenar();}
document.querySelector('#agregarLinea').addEventListener('click',agregar);
tbody.addEventListener('input',reordenar);tbody.addEventListener('change',reordenar);
tbody.addEventListener('click',e=>{if(e.target.closest('.quitar')&&tbody.children.length>1){e.target.closest('tr').remove();reordenar();}});
document.querySelector('#compraForm').addEventListener('submit',e=>{const ids=[...tbody.querySelectorAll('.variante')].map(s=>s.value);if(new Set(ids).size!==ids.length){e.preventDefault();alert('Una variante no puede repetirse en la misma compra.');}});
agregar();
})();
</script>
@endsection
