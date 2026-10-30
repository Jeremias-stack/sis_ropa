@extends('layouts.app')
@section('title', 'Usuarios y roles')
@section('page-title', 'Usuarios')
@section('content')
<div class="container py-4 py-lg-5">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
        <div><h1 class="h3 fw-bold mb-1">Usuarios y roles</h1><p class="text-muted mb-0">Solo el administrador puede crear empleados o cambiar sus permisos.</p></div>
        <button class="btn btn-dark" type="button" data-bs-toggle="collapse" data-bs-target="#nuevoUsuario" aria-expanded="{{ $errors->any() ? 'true' : 'false' }}"><i class="bi bi-person-plus me-1"></i> Nuevo usuario</button>
    </div>
    @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
    @if($errors->any())<div class="alert alert-danger"><strong>Revisa la información:</strong><ul class="mb-0">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
    <div class="collapse {{ $errors->any() ? 'show' : '' }} mb-4" id="nuevoUsuario">
        <div class="card border-0 shadow-sm rounded-4"><div class="card-body p-4">
            <h2 class="h5 fw-bold">Crear cuenta</h2>
            <form action="{{ route('usuarios.store') }}" method="POST" class="row g-3">@csrf
                <div class="col-md-6"><label class="form-label">Nombre</label><input name="nombre" class="form-control" maxlength="150" required value="{{ old('nombre') }}"></div>
                <div class="col-md-6"><label class="form-label">Correo electrónico</label><input type="email" name="correo" class="form-control" maxlength="150" required value="{{ old('correo') }}"></div>
                <div class="col-md-6"><label class="form-label">Contraseña</label><input type="password" name="password" class="form-control" minlength="8" required autocomplete="new-password"></div>
                <div class="col-md-6"><label class="form-label">Confirmar contraseña</label><input type="password" name="password_confirmation" class="form-control" minlength="8" required autocomplete="new-password"></div>
                <div class="col-md-6"><label class="form-label">Rol inicial</label><select name="rol" class="form-select" required>@foreach(['cliente'=>'Cliente','cajero'=>'Cajero','administrador'=>'Administrador'] as $clave=>$valor)<option value="{{ $clave }}" @selected(old('rol','cliente') === $clave)>{{ $valor }}</option>@endforeach</select></div>
                <div class="col-12"><button class="btn btn-dark"><i class="bi bi-check-lg me-1"></i> Guardar usuario</button></div>
            </form>
        </div></div>
    </div>
    <div class="card border-0 shadow-sm rounded-4"><div class="card-body p-4">
        <form method="GET" action="{{ route('usuarios.index') }}" class="row g-2 mb-3"><div class="col"><input type="search" class="form-control" name="buscar" placeholder="Buscar por nombre o correo" value="{{ request('buscar') }}"></div><div class="col-auto"><button class="btn btn-outline-dark">Buscar</button></div></form>
        <div class="table-responsive"><table class="table table-hover align-middle"><thead><tr><th>Usuario</th><th>Correo</th><th>Rol</th><th class="text-end">Acción</th></tr></thead><tbody>
        @forelse($usuarios as $usuario)
            <tr><td class="fw-semibold">{{ $usuario->nombre }} @if($usuario->id === auth()->id())<span class="text-muted small">(tú)</span>@endif</td><td>{{ $usuario->correo }}</td><td><span class="badge text-bg-light border text-dark">{{ ucfirst($usuario->rol) }}</span></td><td class="text-end"><a class="btn btn-outline-dark btn-sm" href="{{ route('usuarios.edit', $usuario) }}"><i class="bi bi-pencil me-1"></i> Editar</a></td></tr>
        @empty<tr><td colspan="4" class="text-muted text-center py-4">No se encontraron usuarios.</td></tr>@endforelse
        </tbody></table></div>
        {{ $usuarios->links() }}
    </div></div>
    <p class="small text-muted mt-3">Los clientes que se registran desde la tienda aparecen aquí con rol Cliente. El módulo «Clientes» del POS contiene datos de compradores para facturación y no necesariamente cuentas de acceso.</p>
</div>
@endsection
