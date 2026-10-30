@extends('layouts.app')
@section('title', 'Catálogos')
@section('content')
<div class="container py-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div><h1 class="fw-bold">Catálogos</h1><p class="text-muted mb-0">Agrega categorías, marcas, tallas y colores para tus productos.</p></div>
        <a href="{{ route('productos.create') }}" class="btn btn-outline-dark">Ir a productos</a>
    </div>
    @if(session('success')) <div class="alert alert-success">{{ session('success') }}</div> @endif
    @if($errors->any()) <div class="alert alert-danger">{{ $errors->first() }}</div> @endif
    <div class="row g-4">
        @foreach(['categorias'=>'Categorías','marcas'=>'Marcas','tallas'=>'Tallas','colores'=>'Colores'] as $clave => $titulo)
        <div class="col-md-6"><section class="card border-0 shadow-sm rounded-4 h-100"><div class="card-body p-4">
            <h2 class="h5 fw-bold">{{ $titulo }}</h2>
            <form action="{{ route('catalogos.store', $clave) }}" method="POST" class="d-flex gap-2 my-3">
                @csrf <input class="form-control" name="nombre" maxlength="100" placeholder="Nuevo {{ mb_strtolower(rtrim($titulo, 's')) }}" required aria-label="Nuevo valor">
                <button class="btn btn-dark" type="submit">Agregar</button>
            </form>
            <div class="d-flex flex-wrap gap-2">
                @forelse($$clave as $valor) <span class="badge rounded-pill bg-light text-dark border px-3 py-2">{{ $valor->nombre }}</span>
                @empty <p class="text-muted small">Aún no hay registros.</p> @endforelse
            </div>
        </div></section></div>
        @endforeach
    </div>
</div>
@endsection
