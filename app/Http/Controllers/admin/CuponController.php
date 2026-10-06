<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Cupon;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Carbon\Carbon;
use Illuminate\Pagination\LengthAwarePaginator;

class CuponController extends Controller
{
    // ==================== MÉTODOS PRINCIPALES ====================
    
    public function index(Request $request)
    {
        // Obtener todos los cupones con relaciones
        $query = Cupon::with(['usuarioGenero', 'usuarioUso']);
        
        // Filtro por búsqueda
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where('codigo', 'LIKE', '%' . $search . '%');
        }
        
        // Filtro por tipo de descuento
        if ($request->filled('tipo_descuento')) {
            $query->where('tipo_descuento', $request->tipo_descuento);
        }
        
        // Filtro por estado
        if ($request->filled('estatus')) {
            $query->where('estatus', $request->estatus);
        }
        
        // Filtro por expiración
        $expira = $request->get('expira', $request->get('expiracion_filter'));
        if ($expira) {
            switch ($expira) {
                case 'expirados':
                    $query->where('fecha_expiracion', '<', now());
                    break;
                case 'vigentes':
                case 'no_expirados':
                    $query->where(function($q) {
                        $q->where('fecha_expiracion', '>=', now())
                          ->orWhereNull('fecha_expiracion');
                    });
                    break;
                case 'sin_fecha':
                case 'sin_expiracion':
                    $query->whereNull('fecha_expiracion');
                    break;
                case 'con_fecha':
                    $query->whereNotNull('fecha_expiracion');
                    break;
                case 'proximos_7_dias':
                    $query->whereBetween('fecha_expiracion', [now(), now()->addDays(7)]);
                    break;
            }
        }
        
        // Obtener todos los cupones (sin paginar aún)
        $cuponesCollection = $query->get();
        
        // Agregar campos calculados a cada cupón
        foreach ($cuponesCollection as $cupon) {
            $cupon->expirado = $cupon->fecha_expiracion && now()->greaterThan($cupon->fecha_expiracion);
            $cupon->proximo_expiracion = $cupon->fecha_expiracion && now()->diffInDays($cupon->fecha_expiracion, false) <= 7 && now()->diffInDays($cupon->fecha_expiracion, false) >= 0;
            $cupon->dias_restantes = $cupon->fecha_expiracion ? now()->diffInDays($cupon->fecha_expiracion, false) : null;
            
            // Obtener nombre del generador para ordenamiento
            $nombreGenerador = 'Sistema';
            if ($cupon->usuarioGenero) {
                if ($cupon->usuarioGenero->estudiante) {
                    $nombreGenerador = $cupon->usuarioGenero->estudiante->nombre_completo ?? $cupon->usuarioGenero->correo;
                } elseif ($cupon->usuarioGenero->administrador) {
                    $nombreGenerador = $cupon->usuarioGenero->administrador->nombre_completo ?? $cupon->usuarioGenero->correo;
                } else {
                    $nombreGenerador = $cupon->usuarioGenero->correo;
                }
            }
            $cupon->nombre_generador = $nombreGenerador;
        }

        // Filtro por generador (sobre el nombre calculado)
        if ($request->filled('generador')) {
            $g = mb_strtolower($request->generador);
            $cuponesCollection = $cuponesCollection->filter(
                fn ($c) => str_contains(mb_strtolower($c->nombre_generador ?? ''), $g)
            )->values();
        }

        // ORDENAMIENTO
        $ordenCampo = $request->get('orden_campo', 'id');
        $ordenDireccion = $request->get('orden_direccion', 'desc');
        
        // Ordenar la colección según el campo seleccionado
        if ($ordenCampo == 'id') {
            $cuponesCollection = $ordenDireccion == 'asc' 
                ? $cuponesCollection->sortBy('id') 
                : $cuponesCollection->sortByDesc('id');
        } 
        elseif ($ordenCampo == 'codigo') {
            $cuponesCollection = $ordenDireccion == 'asc' 
                ? $cuponesCollection->sortBy('codigo') 
                : $cuponesCollection->sortByDesc('codigo');
        }
        elseif ($ordenCampo == 'descuento') {
            $cuponesCollection = $ordenDireccion == 'asc' 
                ? $cuponesCollection->sortBy('valor_descuento') 
                : $cuponesCollection->sortByDesc('valor_descuento');
        }
        elseif ($ordenCampo == 'generador') {
            $cuponesCollection = $ordenDireccion == 'asc' 
                ? $cuponesCollection->sortBy('nombre_generador') 
                : $cuponesCollection->sortByDesc('nombre_generador');
        }
        elseif ($ordenCampo == 'fecha_creacion') {
            $cuponesCollection = $ordenDireccion == 'asc' 
                ? $cuponesCollection->sortBy('fecha_genero') 
                : $cuponesCollection->sortByDesc('fecha_genero');
        }
        elseif ($ordenCampo == 'fecha_expiracion') {
            $cuponesCollection = $ordenDireccion == 'asc' 
                ? $cuponesCollection->sortBy(function($item) {
                    return $item->fecha_expiracion ? $item->fecha_expiracion->timestamp : PHP_INT_MAX;
                }) 
                : $cuponesCollection->sortByDesc(function($item) {
                    return $item->fecha_expiracion ? $item->fecha_expiracion->timestamp : 0;
                });
        }
        elseif ($ordenCampo == 'estado') {
            $cuponesCollection = $ordenDireccion == 'asc' 
                ? $cuponesCollection->sortBy(function($item) {
                    if ($item->usado) return 4;
                    if ($item->expirado) return 3;
                    if ($item->estatus == 'inactivo') return 2;
                    return 1;
                }) 
                : $cuponesCollection->sortByDesc(function($item) {
                    if ($item->usado) return 4;
                    if ($item->expirado) return 3;
                    if ($item->estatus == 'inactivo') return 2;
                    return 1;
                });
        }
        
        // Paginar la colección manualmente
        $perPage = 15;
        $currentPage = $request->get('page', 1);
        $currentItems = $cuponesCollection->slice(($currentPage - 1) * $perPage, $perPage)->values();
        
        $cupones = new LengthAwarePaginator(
            $currentItems,
            $cuponesCollection->count(),
            $perPage,
            $currentPage,
            ['path' => $request->url(), 'query' => $request->query()]
        );
        
        // Estadísticas para las tarjetas
        $totalCupones = Cupon::count();
        $cuponesUsados = Cupon::where('usado', 1)->count();
        $cuponesActivos = Cupon::where('estatus', 'activo')
            ->where('usado', 0)
            ->where(function($q) {
                $q->whereNull('fecha_expiracion')->orWhere('fecha_expiracion', '>=', now());
            })->count();
        $cuponesExpirados = Cupon::where(function($q) {
            $q->where('estatus', 'expirado')->orWhere('fecha_expiracion', '<', now());
        })->count();
        $cuponesVencidos = Cupon::where('fecha_expiracion', '<', now())->count();
        
        $cupones->getCollection()->transform(fn ($c) => [
            'id' => $c->id,
            'codigo' => $c->codigo,
            'usado' => (bool) $c->usado,
            'estatus' => $c->estatus,
            'tipo_descuento' => $c->tipo_descuento,
            'valor_descuento' => $c->valor_descuento !== null ? (float) $c->valor_descuento : null,
            'fecha_genero' => optional($c->fecha_genero)->format('Y-m-d'),
            'fecha_expiracion' => optional($c->fecha_expiracion)->format('Y-m-d'),
            'expirado' => (bool) $c->expirado,
            'generador' => $c->nombre_generador,
        ]);

        return \Inertia\Inertia::render('Admin/Cupones/Index', [
            'cupones' => $cupones,
            'codigoSugerido' => $this->generarCodigoUnico(),
            'stats' => [
                'total' => $totalCupones,
                'usados' => $cuponesUsados,
                'activos' => $cuponesActivos,
                'expirados' => $cuponesExpirados,
            ],
            'filters' => [
                'search' => $request->search,
                'estatus' => $request->estatus,
                'tipo_descuento' => $request->tipo_descuento,
                'expira' => $expira,
                'generador' => $request->generador,
            ],
        ]);
    }

    public function create()
    {
        return redirect()->route('admin.cupones.index');
    }

    // Guardar nuevo cupón
    public function store(Request $request)
    {
        $request->validate([
            'codigo' => 'required|string|max:50|unique:cupones,codigo',
            'usuario_genero' => 'nullable|exists:usuario,id',
            'estatus' => 'required|in:activo,inactivo,expirado',
            'tipo_descuento' => 'required|in:porcentaje,cantidad_fija',
            'valor_descuento' => 'required|numeric|min:0',
            'fecha_expiracion' => 'nullable|date|after_or_equal:today',
        ]);

        try {
            DB::beginTransaction();
            
            if ($request->tipo_descuento == 'porcentaje' && $request->valor_descuento > 100) {
                throw new \Exception('El porcentaje de descuento no puede ser mayor a 100%');
            }
            
            // ✅ Validación extra por si acaso (evita race conditions)
            if (Cupon::where('codigo', $request->codigo)->exists()) {
                throw new \Exception('El código ya existe, por favor regenera el código');
            }
            
            $cupon = Cupon::create([
                'codigo' => $request->codigo,
                'usado' => 0,
                'usuario_uso' => null,
                'usuario_genero' => $request->usuario_genero ?? auth()->id(),
                'estatus' => $request->estatus,
                'fecha_genero' => now(),
                'fecha_uso' => null,
                'fecha_expiracion' => $request->fecha_expiracion,
                'tipo_descuento' => $request->tipo_descuento,
                'valor_descuento' => $request->valor_descuento,
            ]);
            
            DB::commit();
            
            return redirect()->route('admin.cupones.index')
                ->with('success', 'Cupón creado correctamente. Código: ' . $request->codigo);
                
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al crear cupón: ' . $e->getMessage());
            return redirect()->back()
                ->withInput()
                ->with('error', 'Error al crear el cupón: ' . $e->getMessage());
        }
    }

    // Mostrar un cupón específico (API para modal)
    public function show($id)
    {
        try {
            $cupon = Cupon::with(['usuarioGenero', 'usuarioUso'])->findOrFail($id);
            
            $data = [
                'id' => $cupon->id,
                'codigo' => $cupon->codigo,
                'usado' => $cupon->usado,
                'estatus' => $cupon->estatus,
                'tipo_descuento' => $cupon->tipo_descuento,
                'valor_descuento' => $cupon->valor_descuento,
                'fecha_genero' => $cupon->fecha_genero ? $cupon->fecha_genero->format('d/m/Y') : null,
                'fecha_uso' => $cupon->fecha_uso ? $cupon->fecha_uso->format('d/m/Y') : null,
                'fecha_expiracion' => $cupon->fecha_expiracion ? $cupon->fecha_expiracion->format('d/m/Y') : null,
                'esta_expirado' => $cupon->isExpired(),
                'usuario_genero' => $cupon->usuarioGenero ? [
                    'id' => $cupon->usuarioGenero->id,
                    'name' => $this->getUserName($cupon->usuarioGenero),
                    'correo' => $cupon->usuarioGenero->correo
                ] : null,
                'usuario_uso' => $cupon->usuarioUso ? [
                    'id' => $cupon->usuarioUso->id,
                    'name' => $this->getUserName($cupon->usuarioUso),
                    'correo' => $cupon->usuarioUso->correo
                ] : null,
            ];
            
            return response()->json([
                'success' => true,
                'data' => $data
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error al mostrar cupón: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al cargar los datos del cupón'
            ], 500);
        }
    }

    public function edit($id)
    {
        return redirect()->route('admin.cupones.index');
    }

    // Actualizar cupón
    public function update(Request $request, $id)
    {
        $request->validate([
            'codigo' => 'required|string|max:50|unique:cupones,codigo,' . $id,
            'usuario_genero' => 'nullable|exists:usuario,id',
            'estatus' => 'required|in:activo,inactivo,expirado',
            'usado' => 'required|boolean',
            'tipo_descuento' => 'required|in:porcentaje,cantidad_fija',
            'valor_descuento' => 'required|numeric|min:0',
            'fecha_expiracion' => 'nullable|date|after_or_equal:today',
        ]);

        try {
            $cupon = Cupon::findOrFail($id);
            
            if ($request->tipo_descuento == 'porcentaje' && $request->valor_descuento > 100) {
                throw new \Exception('El porcentaje de descuento no puede ser mayor a 100%');
            }
            
            if (Cupon::where('codigo', $request->codigo)->where('id', '!=', $id)->exists()) {
                throw new \Exception('El código ya existe en otro cupón');
            }
            
            $data = [
                'codigo' => $request->codigo,
                'usuario_genero' => $request->usuario_genero,
                'estatus' => $request->estatus,
                'usado' => $request->usado,
                'tipo_descuento' => $request->tipo_descuento,
                'valor_descuento' => $request->valor_descuento,
                'fecha_expiracion' => $request->fecha_expiracion,
            ];
            
            if ($request->usado == 1 && !$cupon->fecha_uso) {
                $data['fecha_uso'] = now();
                $data['usuario_uso'] = auth()->id();
            }
            
            if ($request->usado == 0) {
                $data['fecha_uso'] = null;
                $data['usuario_uso'] = null;
            }
            
            $cupon->update($data);
            
            return redirect()->route('admin.cupones.index')
                ->with('success', 'Cupón actualizado correctamente');
                
        } catch (\Exception $e) {
            Log::error('Error al actualizar cupón: ' . $e->getMessage());
            return redirect()->back()
                ->withInput()
                ->with('error', 'Error al actualizar el cupón: ' . $e->getMessage());
        }
    }

    // Eliminar cupón
    public function destroy($id)
    {
        try {
            $cupon = Cupon::findOrFail($id);
            
            if ($cupon->usado == 1) {
                return redirect()->route('admin.cupones.index')
                    ->with('error', 'No se puede eliminar un cupón que ya ha sido usado');
            }
            
            $cupon->delete();
            
            return redirect()->route('admin.cupones.index')
                ->with('success', 'Cupón eliminado correctamente');
                
        } catch (\Exception $e) {
            Log::error('Error al eliminar cupón: ' . $e->getMessage());
            return redirect()->route('admin.cupones.index')
                ->with('error', 'Error al eliminar el cupón: ' . $e->getMessage());
        }
    }

    // Regenerar código de cupón (AJAX)
    public function regenerarCodigo(Request $request, $id = null)
    {
        try {
            if ($id === null) {
                $nuevoCodigo = $this->generarCodigoUnico();
                return response()->json([
                    'success' => true,
                    'codigo' => $nuevoCodigo,
                    'message' => 'Código generado exitosamente'
                ]);
            }
            
            $cupon = Cupon::findOrFail($id);
            $nuevoCodigo = $this->generarCodigoUnico();
            $cupon->update(['codigo' => $nuevoCodigo]);
            
            return response()->json([
                'success' => true,
                'codigo' => $nuevoCodigo,
                'message' => 'Código regenerado exitosamente'
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error en regenerarCodigo: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al regenerar el código: ' . $e->getMessage()
            ], 500);
        }
    }

    // ==================== GENERACIÓN MASIVA ====================
    
    // Generar cupones masivos
    public function generarMasivo(Request $request)
    {
        $request->validate([
            'cantidad' => 'required|integer|min:1|max:100',
            'estatus' => 'required|in:activo,inactivo,expirado',
            'tipo_descuento' => 'required|in:porcentaje,cantidad_fija',
            'valor_descuento' => 'required|numeric|min:0',
            'fecha_expiracion' => 'nullable|date|after_or_equal:today',
        ]);

        try {
            DB::beginTransaction();
            
            if ($request->tipo_descuento == 'porcentaje' && $request->valor_descuento > 100) {
                throw new \Exception('El porcentaje de descuento no puede ser mayor a 100%');
            }
            
            $generados = 0;
            $codigosGenerados = [];
            
            for ($i = 0; $i < $request->cantidad; $i++) {
                do {
                    $codigo = strtoupper(Str::random(15));
                } while (Cupon::where('codigo', $codigo)->exists() || in_array($codigo, $codigosGenerados));
                
                $codigosGenerados[] = $codigo;
                
                Cupon::create([
                    'codigo' => $codigo,
                    'usado' => 0,
                    'usuario_uso' => null,
                    'usuario_genero' => auth()->id(),
                    'estatus' => $request->estatus,
                    'fecha_genero' => now(),
                    'fecha_uso' => null,
                    'fecha_expiracion' => $request->fecha_expiracion,
                    'tipo_descuento' => $request->tipo_descuento,
                    'valor_descuento' => $request->valor_descuento,
                ]);
                
                $generados++;
            }
            
            DB::commit();
            
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'generados' => $generados,
                    'message' => "Se generaron {$generados} cupones correctamente"
                ]);
            }
            
            return redirect()->route('admin.cupones.index')
                ->with('success', "Se generaron {$generados} cupones correctamente");
                
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al generar cupones masivos: ' . $e->getMessage());
            
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage()
                ], 500);
            }
            
            return redirect()->back()
                ->with('error', 'Error al generar cupones: ' . $e->getMessage());
        }
    }

    // ==================== MÉTODOS ADICIONALES ====================
    
    private function getUserName($user)
    {
        if (!$user) return 'N/A';
        
        if ($user->administrador) {
            return $user->administrador->nombre_completo ?? $user->correo;
        }
        
        if ($user->estudiante) {
            return $user->estudiante->nombre_completo ?? $user->correo;
        }
        
        return $user->correo;
    }
    
    private function generarCodigoUnico($intentos = 0)
    {
        if ($intentos >= 10) {
            throw new \Exception('No se pudo generar un código único después de varios intentos');
        }
        
        $codigo = strtoupper(Str::random(15));
        
        if (Cupon::where('codigo', $codigo)->exists()) {
            return $this->generarCodigoUnico($intentos + 1);
        }
        
        return $codigo;
    }
    
    // Marcar cupón como usado manualmente
    public function marcarUsado($id)
    {
        try {
            $cupon = Cupon::findOrFail($id);
            
            if ($cupon->usado == 1) {
                return redirect()->route('admin.cupones.index')
                    ->with('error', 'El cupón ya estaba marcado como usado');
            }
            
            $cupon->update([
                'usado' => 1,
                'fecha_uso' => now(),
                'usuario_uso' => auth()->id(),
            ]);
            
            return redirect()->route('admin.cupones.index')
                ->with('success', 'Cupón marcado como usado correctamente');
                
        } catch (\Exception $e) {
            Log::error('Error al marcar cupón como usado: ' . $e->getMessage());
            return redirect()->route('admin.cupones.index')
                ->with('error', 'Error al marcar el cupón como usado');
        }
    }

    /**
     * Muestra el formulario de generación masiva de cupones
     */
    public function generarMasivoForm()
    {
        return redirect()->route('admin.cupones.index');
    }
    
    // Exportar cupones a CSV
    public function exportar(Request $request)
    {
        $query = Cupon::with(['usuarioGenero', 'usuarioUso']);
        
        if ($request->filled('estatus')) {
            $query->where('estatus', $request->estatus);
        }
        
        if ($request->filled('tipo_descuento')) {
            $query->where('tipo_descuento', $request->tipo_descuento);
        }
        
        $cupones = $query->get();
        
        $filename = 'cupones_' . date('Y-m-d_His') . '.csv';
        $handle = fopen('php://temp', 'w+');
        
        fputcsv($handle, ['ID', 'Código', 'Estado', 'Usado', 'Tipo Descuento', 'Valor Descuento', 'Generado por', 'Usado por', 'Fecha Generación', 'Fecha Uso', 'Fecha Expiración', '¿Expirado?']);
        
        foreach ($cupones as $cupon) {
            fputcsv($handle, [
                $cupon->id,
                $cupon->codigo,
                $cupon->estatus,
                $cupon->usado ? 'Sí' : 'No',
                $cupon->tipo_descuento == 'porcentaje' ? 'Porcentaje' : 'Cantidad fija',
                $cupon->tipo_descuento == 'porcentaje' ? $cupon->valor_descuento . '%' : '$' . number_format($cupon->valor_descuento, 2),
                $cupon->usuarioGenero?->correo ?? 'N/A',
                $cupon->usuarioUso?->correo ?? 'N/A',
                $cupon->fecha_genero?->format('d/m/Y') ?? 'N/A',
                $cupon->fecha_uso?->format('d/m/Y') ?? 'N/A',
                $cupon->fecha_expiracion?->format('d/m/Y') ?? 'Sin expiración',
                $cupon->isExpired() ? 'Sí' : 'No',
            ]);
        }
        
        rewind($handle);
        $content = stream_get_contents($handle);
        fclose($handle);
        
        return response($content, 200)
            ->header('Content-Type', 'text/csv')
            ->header('Content-Disposition', "attachment; filename={$filename}");
    }
    
    // Actualizar automáticamente el estatus basado en expiración
    public function actualizarEstatusPorExpiracion()
    {
        try {
            $actualizados = Cupon::where('usado', 0)
                ->where('fecha_expiracion', '<', now())
                ->where('estatus', 'activo')
                ->update(['estatus' => 'expirado']);
            
            return response()->json([
                'success' => true,
                'message' => "Se actualizaron {$actualizados} cupones a expirados"
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error al actualizar estatus por expiración: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar los estatus'
            ], 500);
        }
    }
}