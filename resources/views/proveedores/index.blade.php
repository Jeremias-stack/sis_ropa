@extends('layouts.app')
@section('title','Proveedores')
@section('page-title','Proveedores')
@section('content')
<div class="container py-4 py-lg-5">
  <div class="d-flex justify-content-between flex-wrap gap-2 align-items-center mb-4">
    <div><h1 class="h3 fw-bold mb-1">Proveedores</h1><p class="text-secondary mb-0">Datos de los negocios donde compras mercancía.</p></div>
    <button class="btn btn-dark" data-bs-toggle="collapse" data-bs-target="#nuevoProveedor"><i class="bi bi-plus-lg"></i> Nuevo proveedor</button>
  </div>
  @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
  @if($errors->any())<div class="alert alert-danger"><ul class="mb-0">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
  <div class="collapse {{ $errors->any() ? 'show' : '' }} mb-4" id="nuevoProveedor"><div class="card border-0 shadow-sm rounded-4"><div class="card-body p-4">
    <h2 class="h5">Registrar proveedor</h2>
    <form method="POST" action="{{ route('proveedores.store') }}" class="row g-3">@csrf
      <div class="col-md-6"><label class="form-label">Nombre del proveedor *</label><input name="nombre" required maxlength="150" class="form-control" value="{{ old('nombre') }}"></div>
      <div class="col-md-6"><label class="form-label">Persona de contacto</label><input name="contacto" class="form-control" maxlength="120" value="{{ old('contacto') }}"></div>
      <div class="col-md-6"><label class="form-label">Teléfono</label><input name="telefono" class="form-control" maxlength="30" value="{{ old('telefono') }}"></div>
      <div class="col-md-6"><label class="form-label">Correo</label><input type="email" name="correo" class="form-control" value="{{ old('correo') }}"></div>
      <div class="col-12"><label class="form-label">Dirección</label><input name="direccion" class="form-control" value="{{ old('direccion') }}"></div>
      <div class="col-12"><button class="btn btn-dark">Guardar proveedor</button></div>
    </form>
  </div></div></div>
  <div class="card border-0 rounded-4 shadow-sm"><div class="card-body p-4">
    <form class="d-flex gap-2 mb-3"><input name="buscar" class="form-control" placeholder="Buscar por nombre o contacto" value="{{ request('buscar') }}"><button class="btn btn-outline-dark">Buscar</button></form>
    <div class="table-responsive"><table class="table table-hover align-middle"><thead><tr><th>Proveedor</th><th>Contacto</th><th>Teléfono</th><th class="text-end">Acción</th></tr></thead><tbody>
    @forelse($proveedores as $proveedor)
      <tr><td><strong>{{ $proveedor->nombre }}</strong><div class="small text-secondary">{{ $proveedor->correo }}</div></td><td>{{ $proveedor->contacto ?: '—' }}</td><td>{{ $proveedor->telefono ?: '—' }}</td><td class="text-end"><button class="btn btn-sm btn-outline-dark" data-bs-toggle="collapse" data-bs-target="#editar-{{ $proveedor->id }}">Editar</button></td></tr>
      <tr class="collapse" id="editar-{{ $proveedor->id }}"><td colspan="4"><form class="row g-2 py-2" action="{{ route('proveedores.update',$proveedor) }}" method="POST">@csrf @method('PUT')
        <div class="col-md-6"><label class="small">Nombre</label><input class="form-control" name="nombre" required value="{{ $proveedor->nombre }}"></div>
        <div class="col-md-6"><label class="small">Contacto</label><input class="form-control" name="contacto" value="{{ $proveedor->contacto }}"></div>
        <div class="col-md-4"><label class="small">Teléfono</label><input class="form-control" name="telefono" value="{{ $proveedor->telefono }}"></div>
        <div class="col-md-4"><label class="small">Correo</label><input class="form-control" type="email" name="correo" value="{{ $proveedor->correo }}"></div>
        <div class="col-md-4"><label class="small">Dirección</label><input class="form-control" name="direccion" value="{{ $proveedor->direccion }}"></div>
        <div class="col-12"><button class="btn btn-sm btn-dark">Actualizar datos</button></div>
      </form></td></tr>
    @empty<tr><td colspan="4" class="text-center text-secondary py-4">Todavía no hay proveedores.</td></tr>@endforelse
    </tbody></table></div>{{ $proveedores->links() }}
  </div></div>
</div>
@endsection
