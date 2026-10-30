@extends('layouts.app')
@section('title', 'Editar usuario')
@section('page-title', 'Usuarios')
@section('content')
<div class="container py-4 py-lg-5" style="max-width:850px">
    <div class="mb-4"><a href="{{ route('usuarios.index') }}" class="text-decoration-none text-secondary small"><i class="bi bi-arrow-left me-1"></i> Volver a usuarios</a><h1 class="h3 fw-bold mt-3">Editar usuario</h1><p class="text-muted">Modifica el perfil y asigna el rol correspondiente.</p></div>
    <div class="card border-0 shadow-sm rounded-4"><div class="card-body p-4 p-lg-5">
        @if($errors->any())<div class="alert alert-danger"><ul class="mb-0">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
        <form method="POST" action="{{ route('usuarios.update', $usuario) }}" class="row g-3">@csrf @method('PUT')
            <div class="col-md-6"><label class="form-label fw-semibold">Nombre</label><input class="form-control" name="nombre" maxlength="150" required value="{{ old('nombre', $usuario->nombre) }}"></div>
            <div class="col-md-6"><label class="form-label fw-semibold">Correo</label><input class="form-control" type="email" name="correo" maxlength="150" required value="{{ old('correo', $usuario->correo) }}"></div>
            <div class="col-12"><label class="form-label fw-semibold">Rol</label><select class="form-select" name="rol" required>@foreach(['cliente'=>'Cliente','cajero'=>'Cajero','administrador'=>'Administrador'] as $clave=>$etiqueta)<option value="{{ $clave }}" @selected(old('rol', $usuario->rol) === $clave)>{{ $etiqueta }}</option>@endforeach</select><div class="form-text">Solo los administradores pueden cambiar roles. No puedes quitarte el rol de administrador a ti mismo.</div></div>
            <div class="col-md-6"><label class="form-label fw-semibold">Nueva contraseña (opcional)</label><input class="form-control" type="password" name="password" minlength="8" autocomplete="new-password"><div class="form-text">Déjala vacía para conservar la contraseña actual.</div></div>
            <div class="col-md-6"><label class="form-label fw-semibold">Confirmar contraseña</label><input class="form-control" type="password" name="password_confirmation" minlength="8" autocomplete="new-password"></div>
            <div class="col-12 d-flex gap-2"><button class="btn btn-dark" type="submit">Guardar cambios</button><a href="{{ route('usuarios.index') }}" class="btn btn-outline-secondary">Cancelar</a></div>
        </form>
    </div></div>
</div>
@endsection
