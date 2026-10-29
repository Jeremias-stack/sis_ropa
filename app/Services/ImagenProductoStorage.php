<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

/** Una sola interfaz para imágenes locales y Cloudinary (sin SDK adicional). */
class ImagenProductoStorage
{
    public static function url(?string $ruta): ?string
    {
        if (!$ruta) return null;
        if (str_starts_with($ruta, 'https://') || str_starts_with($ruta, 'http://')) {
            return $ruta;
        }
        return asset('storage/'.ltrim($ruta, '/'));
    }

    private function configurado(): bool
    {
        $valores = [config('imagenes.cloud_name'), config('imagenes.api_key'), config('imagenes.api_secret')];
        $presentes = count(array_filter($valores, fn ($v) => is_string($v) && trim($v) !== ''));
        if ($presentes !== 0 && $presentes !== 3) {
            throw ValidationException::withMessages(['imagen' => 'Configuración de Cloudinary incompleta. Revisa las tres variables CLOUDINARY.']);
        }
        return $presentes === 3;
    }

    public function subir(UploadedFile $archivo, string $subcarpeta = 'productos'): string
    {
        if (!$this->configurado()) {
            return $archivo->store($subcarpeta, 'public');
        }

        $nombre = (string) config('imagenes.cloud_name');
        $clave = (string) config('imagenes.api_key');
        $secreto = (string) config('imagenes.api_secret');
        $folder = (string) config('imagenes.folder', 'sis_ropa').'/'.trim($subcarpeta, '/');
        $timestamp = time();
        // Cloudinary firma parámetros ordenados alfabéticamente (excepto file, api_key y signature).
        $firma = sha1('folder='.$folder.'&timestamp='.$timestamp.$secreto);
        $recurso = fopen($archivo->getRealPath(), 'rb');
        if (!$recurso) throw ValidationException::withMessages(['imagen' => 'No se pudo leer la fotografía.']);
        try {
            $respuesta = Http::timeout(35)->attach('file', $recurso, $archivo->getClientOriginalName())
                ->post('https://api.cloudinary.com/v1_1/'.rawurlencode($nombre).'/image/upload', [
                    'api_key' => $clave, 'timestamp' => $timestamp,
                    'folder' => $folder, 'signature' => $firma,
                ]);
        } catch (\Throwable $e) {
            Log::warning('No fue posible cargar imagen a Cloudinary: '.$e->getMessage());
            throw ValidationException::withMessages(['imagen' => 'No se pudo conectar con Cloudinary. Intenta de nuevo.']);
        } finally {
            fclose($recurso);
        }
        $url = $respuesta->json('secure_url');
        $host = is_string($url) ? parse_url($url, PHP_URL_HOST) : null;
        if (!$respuesta->successful() || !is_string($url) || $host !== 'res.cloudinary.com') {
            Log::warning('Cloudinary rechazó una imagen. HTTP '.$respuesta->status());
            throw ValidationException::withMessages(['imagen' => 'Cloudinary no pudo guardar la fotografía. Verifica las credenciales.']);
        }
        return $url;
    }

    /** La eliminación remota es mejor esfuerzo: nunca interrumpe una venta o actualización. */
    public function eliminar(?string $ruta): void
    {
        if (!$ruta) return;
        if (!str_starts_with($ruta, 'https://')) {
            Storage::disk('public')->delete($ruta);
            return;
        }
        if (!$this->configurado()) return;
        $cloud = (string) config('imagenes.cloud_name');
        $path = parse_url($ruta, PHP_URL_PATH);
        $host = parse_url($ruta, PHP_URL_HOST);
        // Solo eliminar imágenes de la carpeta SIS_ROPA de nuestra propia cuenta Cloudinary.
        if ($host !== 'res.cloudinary.com' || !is_string($path)) return;
        $prefix = '/'.$cloud.'/image/upload/';
        if (!str_starts_with($path, $prefix)) return;
        $resto = substr($path, strlen($prefix));
        if (!preg_match('#^v\d+/(sis_ropa/[A-Za-z0-9_./-]+)\.(?:png|jpg|jpeg|webp)$#i', $resto, $m)) return;
        $publicId = $m[1];
        if (str_contains($publicId, '..')) return;
        $timestamp = time();
        $signature = sha1('public_id='.$publicId.'&timestamp='.$timestamp.(string) config('imagenes.api_secret'));
        try {
            $resultado = Http::timeout(12)->post('https://api.cloudinary.com/v1_1/'.rawurlencode($cloud).'/image/destroy', [
                'public_id' => $publicId, 'timestamp' => $timestamp,
                'api_key' => config('imagenes.api_key'), 'signature' => $signature,
            ]);
            if (!$resultado->successful()) Log::warning('Cloudinary: no se pudo eliminar una imagen anterior. HTTP '.$resultado->status());
        } catch (\Throwable $e) {
            Log::warning('Cloudinary: eliminación remota pendiente: '.$e->getMessage());
        }
    }
}
