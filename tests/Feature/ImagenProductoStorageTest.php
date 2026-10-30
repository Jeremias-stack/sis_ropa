<?php

namespace Tests\Feature;

use App\Services\ImagenProductoStorage;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ImagenProductoStorageTest extends TestCase
{
    public function test_imagenes_locales_se_siguen_almacenando_y_mostrando(): void
    {
        config(['imagenes.cloud_name'=>'', 'imagenes.api_key'=>'', 'imagenes.api_secret'=>'']);
        Storage::fake('public');
        $ruta = app(ImagenProductoStorage::class)->subir(UploadedFile::fake()->create('foto.jpg', 30, 'image/jpeg'));
        Storage::disk('public')->assertExists($ruta);
        $this->assertStringContainsString('/storage/', ImagenProductoStorage::url($ruta));
        app(ImagenProductoStorage::class)->eliminar($ruta);
        Storage::disk('public')->assertMissing($ruta);
    }

    public function test_subida_cloudinary_firmada_retorna_url_https(): void
    {
        config(['imagenes.cloud_name'=>'cuenta-demo', 'imagenes.api_key'=>'1234567', 'imagenes.api_secret'=>'secreto-demo']);
        Http::fake([
            'api.cloudinary.com/*' => Http::response([
                'secure_url' => 'https://res.cloudinary.com/cuenta-demo/image/upload/v123/sis_ropa/productos/demo.jpg',
            ], 200),
        ]);
        $ruta = app(ImagenProductoStorage::class)->subir(UploadedFile::fake()->create('foto.jpg', 30, 'image/jpeg'));
        $this->assertStringStartsWith('https://res.cloudinary.com/', $ruta);
        $this->assertSame($ruta, ImagenProductoStorage::url($ruta));
        Http::assertSent(fn ($r) => str_contains($r->url(), '/image/upload') && $r->hasHeader('Content-Type'));
    }
}
