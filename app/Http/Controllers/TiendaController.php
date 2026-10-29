<?php
namespace App\Http\Controllers;

use App\Models\Pedido;
use App\Models\Producto;
use App\Models\ProductoVariante;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class TiendaController extends Controller
{
    public function index(Request $request)
    {
        $productos = Producto::with(['categoria','marca','variantes.talla','variantes.color','imagenesColores.color'])
            ->where('activo', true)->when($request->filled('buscar'), fn($q)=>$q->buscar($request->input('buscar')))
            ->orderBy('nombre')->paginate(12)->withQueryString();
        return view('tienda.index', compact('productos'));
    }

    public function carrito(Request $request)
    {
        $carrito = $this->items($request);
        return view('tienda.carrito', compact('carrito'));
    }

    private function items(Request $request): array
    {
        $cantidades = $request->session()->get('carrito', []);
        if (!$cantidades) return [];
        $variantes = ProductoVariante::with(['producto','talla','color'])->whereIn('id', array_keys($cantidades))->get()->keyBy('id');
        $items = [];
        foreach ($cantidades as $id => $cantidad) {
            $variante = $variantes->get((int)$id);
            if (!$variante || !$variante->producto->activo || $cantidad < 1) continue;
            $items[] = ['variante'=>$variante,'cantidad'=>(int)$cantidad,'subtotal'=>round($variante->producto->precio * $cantidad,2)];
        }
        return $items;
    }

    public function agregar(Request $request)
    {
        $datos = $request->validate([
            'variante_id' => ['required','integer','exists:producto_variantes,id'],
            'cantidad' => ['required','integer','min:1','max:100'],
        ]);
        $variante = ProductoVariante::with('producto')->findOrFail($datos['variante_id']);
        if (!$variante->producto->activo) throw ValidationException::withMessages(['cantidad'=>'Producto no disponible.']);
        $carrito = $request->session()->get('carrito', []);
        $nuevo = (int)($carrito[$variante->id] ?? 0) + (int)$datos['cantidad'];
        if ($nuevo > $variante->stock) throw ValidationException::withMessages(['cantidad'=>'No hay suficientes unidades de esa talla y color.']);
        $carrito[$variante->id] = $nuevo;
        $request->session()->put('carrito', $carrito);
        return redirect()->route('tienda.carrito')->with('success','Producto agregado al carrito.');
    }

    public function actualizar(Request $request)
    {
        $datos = $request->validate(['cantidades'=>['required','array'],'cantidades.*'=>['required','integer','min:0','max:100']]);
        $carrito = $request->session()->get('carrito', []);
        foreach ($datos['cantidades'] as $id => $cantidad) {
            if (!array_key_exists($id,$carrito)) continue;
            if ((int)$cantidad === 0) { unset($carrito[$id]); continue; }
            $variante = ProductoVariante::find($id);
            if (!$variante || $cantidad > $variante->stock) {
                throw ValidationException::withMessages(['cantidades'=>'Una variante está agotada o tiene menos unidades disponibles.']);
            }
            $carrito[$id] = (int)$cantidad;
        }
        $request->session()->put('carrito',$carrito);
        return redirect()->route('tienda.carrito')->with('success','Carrito actualizado.');
    }

    public function checkout(Request $request)
    {
        $carrito = $this->items($request);
        if (!$carrito) return redirect()->route('tienda.carrito')->with('error','Tu carrito está vacío.');
        return view('tienda.checkout',compact('carrito'));
    }

    public function confirmar(Request $request)
    {
        $datos = $request->validate([
            'nombre_cliente'=>['required','string','max:150'],
            'telefono'=>['required','string','max:30'],
            'direccion_entrega'=>['required','string','max:255'],
            'indicaciones'=>['nullable','string','max:2000'],
            'metodo_pago'=>['required','in:contra_entrega,stripe_prueba'],
        ]);
        if ($datos['metodo_pago'] === 'stripe_prueba' && !app(\App\Services\StripeCheckoutService::class)->disponible()) {
            throw ValidationException::withMessages(['metodo_pago'=>'Stripe de prueba no está configurado. Usa pago contra entrega.']);
        }
        $carrito = $request->session()->get('carrito', []);
        if (!$carrito) return redirect()->route('tienda.carrito')->with('error','Carrito vacío.');
        $pedido = DB::transaction(function () use ($carrito,$datos) {
            $lineas=[]; $subtotal=0;
            // Bloqueo en orden estable para evitar sobreventa en compras simultáneas.
            $ids=array_keys($carrito);
            $varianteInfo=ProductoVariante::whereIn('id',$ids)->get(['id','producto_id'])->keyBy('id');
            if ($varianteInfo->count()!==count($ids)) throw ValidationException::withMessages(['carrito'=>'Uno de los artículos ya no existe.']);
            $productosPorId=$varianteInfo->pluck('producto_id')->unique()->sort()->values();
            $productos=Producto::whereIn('id',$productosPorId)->orderBy('id')->lockForUpdate()->get()->keyBy('id');
            $variantes=ProductoVariante::with(['talla','color'])->whereIn('id',$ids)->orderBy('id')->lockForUpdate()->get()->keyBy('id');
            foreach ($variantes as $variante) {
                $producto=$productos->get($variante->producto_id); $cantidad=(int)$carrito[$variante->id];
                if (!$producto || !$producto->activo || $cantidad<1 || $cantidad>$variante->stock) {
                    throw ValidationException::withMessages(['carrito'=>'No hay suficientes existencias de uno de los productos. Actualiza el carrito.']);
                }
                $importe=round((float)$producto->precio*$cantidad,2);
                $subtotal+=$importe;
                $lineas[]=['variante'=>$variante,'producto'=>$producto,'cantidad'=>$cantidad,'importe'=>$importe];
            }
            $subtotal=round($subtotal,2);
            $pedido=Pedido::create($datos+[
                'user_id'=>auth()->id(),'subtotal'=>$subtotal,'total'=>$subtotal,
                'metodo_pago'=>$datos['metodo_pago'],'estado'=>'pendiente',
                'pago_estado'=>$datos['metodo_pago']==='stripe_prueba'?'pendiente':'contra_entrega',
            ]);
            foreach ($lineas as $linea) {
                $variante=$linea['variante'];$producto=$linea['producto'];
                $pedido->detalles()->create([
                    'producto_variante_id'=>$variante->id,'nombre_producto'=>$producto->nombre,
                    'talla'=>$variante->talla?->nombre,'color'=>$variante->color?->nombre,
                    'cantidad'=>$linea['cantidad'],'precio_unitario'=>$producto->precio,
                    'subtotal'=>$linea['importe'],
                ]);
                $variante->decrement('stock',$linea['cantidad']);
                $producto->decrement('stock',$linea['cantidad']);
            }
            return $pedido;
        });
        $request->session()->forget('carrito');
        return redirect()->route('pedidos.show',$pedido)->with('success',
            $datos['metodo_pago']==='stripe_prueba'
                ? 'Pedido creado. Usa el botón Pagar con Stripe (prueba) para completar el pago.'
                : 'Pedido recibido. Puedes consultar su estado aquí.');
    }
}
