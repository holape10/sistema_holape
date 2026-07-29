<?php

namespace MasterSoft\Http\Controllers;

use Illuminate\Http\Request;
use MasterSoft\productos;
use MasterSoft\movimientos;
use MasterSoft\presentaciones; // Aunque la tabla 'presentaciones' no se usa directamente para guardar, el modelo podría existir. Usaremos 'productos' con tipo '2'.
use MasterSoft\recetas;
use MasterSoft\combos;
use MasterSoft\marcas;
use MasterSoft\Proveedor;
use MasterSoft\EmpresaNegocios;
use MasterSoft\Http\Requests\ProductoCreateFormRequest;
use MasterSoft\Http\Requests\ProductoUpdateFormRequest;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Auth;
use MasterSoft\Empresa;
use DB;
use Excel;
use PDF;

use Carbon\Carbon;

class ProductosController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */

      public function __construct()
    {
        $this->middleware('auth')->except(['consultastockproductos']);
    }


         public function historialproducto($id){

        $detalle = DB::tABLE('cpe_cabecera')
        ->leftjoin('cpe_detalle','cpe_detalle.IdCpe_cabecera','cpe_cabecera.IdCpe_cabecera')
        ->leftjoin('cliente','cliente.clicod','cpe_cabecera.clicod')
        ->leftjoin('productos','productos.IdProducto','cpe_detalle.IdProducto')
        ->leftjoin('unidad_medida','unidad_medida.umecod','productos.umecod')
          ->leftjoin('marcas','marcas.mar_id','productos.marca')
        ->where('cpe_detalle.IdProducto',$id)->paginate(1000);

        $producto = DB::tABLE('productos')->where('IdProducto',$id)->first();

        return  view('empresas.productos.historial',compact('detalle','producto'));

     }

     public function buscarCatalogoSunat(Request $request)
    {
        $term = trim($request->get('q'));

        if (empty($term)) {
            return response()->json([]);
        }

        $resultados = DB::table('sunat_catalogo_25')
            ->where('codigo', 'LIKE', "%{$term}%")
            ->orWhere('descripcion', 'LIKE', "%{$term}%")
            ->select('codigo as id', DB::raw("CONCAT(codigo, ' - ', descripcion) as text"))
            ->limit(20)
            ->get();

        return response()->json($resultados);
    }

    // Método opcional para detectar automáticamente por coincidencia de nombre
    public function autoselectCatalogoSunat(Request $request)
    {
        $nombre = trim($request->get('nombre'));

        if (empty($nombre)) {
            return response()->json(null);
        }

        // Busca coincidencia por palabras clave
        $coincidencia = DB::table('sunat_catalogo_25')
            ->whereRaw("MATCH(descripcion) AGAINST(? IN BOOLEAN MODE)", [$nombre])
            ->orWhere('descripcion', 'LIKE', "%{$nombre}%")
            ->select('codigo as id', DB::raw("CONCAT(codigo, ' - ', descripcion) as text"))
            ->first();

        return response()->json($coincidencia);
    }


     public function subir_imagen(Request $request,$id){

        if(Input::hasFile('pro_img'.$id)){

            $file=Input::file('pro_img'.$id);
            $file->move(public_path().'/imagenes/productos/',$file->getClientOriginalName());
            
            $producto = productos::findOrFail($id);
            $producto->imagenproducto = $file->getClientOriginalName();
            $producto->update();
        }

        return Redirect::to('/productos');

     }

    public function generarcodigo(){

        $rucemp = trim(Auth::user()->IdEmpresa);
        $rutabarcode = '/opt/data/comprobantes/'.$rucemp.'/barras/';

        $d = new DNS1D();
        $d1 = new DNS2D();
        $d->setStorPath($rutabarcode);
        echo $d->getBarcodePNGPath("6971636851325", "EAN13");

        $archivo = fopen($rutabarcode.'6971636851325.png', "a");
        fputs($archivo,$d->getBarcodePNGPath("6971636851325", "EAN13"));
        fclose($archivo);

    }


    public function actualizarpro(Request $request, $tipo){
       
       if($tipo=='venta'){
         $vista = view('empresas.productos.actualizarpro')->render();
       }elseif($tipo=='compra'){
        $vista = view('empresas.productos.actualizarprocompra')->render();
       }elseif($tipo=='servicio'){
        $vista = view('empresas.productos.actualizarproservicio')->render();
       }
        

        if($request->ajax()){
         return response()->json(['vista'=>$vista]);

        }
    }


    public function getPreciosDinamicosModal(Request $request, $productId)
    {
        $empresaNegocioId = Auth::user()->id_empresa_negocio;

        $precios_dinamicos_existentes = DB::table('precios_dia_semana')
                                          ->where('IdProducto', $productId)
                                          ->where('id_empresa_negocio', $empresaNegocioId)
                                          ->get();

        $vista = view('empresas.productos.modal_precios_dinamicos', compact(
            'precios_dinamicos_existentes',
            'productId',
            'empresaNegocioId'
        ))->render();

        return response()->json(['html' => $vista]);
    }

    public function guardarPreciosDinamicos(Request $request, $productId)
    {
        // Validar que el producto existe y pertenece a la empresa del usuario actual
        $product = productos::where('IdProducto', $productId)
                            ->where('id_empresa_negocio', Auth::user()->id_empresa_negocio)
                            ->first();

        if (!$product) {
            return response()->json(['success' => false, 'message' => 'Producto no encontrado o no autorizado.'], 404);
        }

        $precios_dinamicos_data = $request->input('precios_dinamicos');
        $empresaNegocioId = $request->input('empresa_negocio_id');

        // Obtener IDs de reglas existentes para este producto
        $current_rule_ids = DB::table('precios_dia_semana')
                                ->where('IdProducto', $productId)
                                ->where('id_empresa_negocio', $empresaNegocioId)
                                ->pluck('id_precio_dia')
                                ->toArray();

        $ids_to_keep = [];

        DB::beginTransaction();
        try {
            if (!empty($precios_dinamicos_data)) {
                foreach ($precios_dinamicos_data as $rule_data) {
                    $id_precio_dia = $rule_data['id_precio_dia'];
                    $dia_semana = $rule_data['dia_semana'];
                    $hora_inicio_vigencia = $rule_data['hora_inicio_vigencia'];
                    $hora_fin_vigencia = $rule_data['hora_fin_vigencia'];
                    $precio_especial = $rule_data['precio_especial'];
                    $estado = $rule_data['estado'];

                    $data = [
                        'IdProducto' => $productId,
                        'dia_semana' => $dia_semana,
                        'precio_especial' => $precio_especial,
                        'id_empresa_negocio' => $empresaNegocioId,
                        'fecha_inicio_vigencia' => null, // Si no hay fecha de inicio/fin general por regla
                        'hora_inicio_vigencia' => $hora_inicio_vigencia,
                        'fecha_fin_vigencia' => null,   // Si no hay fecha de inicio/fin general por regla
                        'hora_fin_vigencia' => $hora_fin_vigencia,
                        'estado' => $estado,
                    ];

                    if ($id_precio_dia == 0) {
                        // Nueva regla
                        $newId = DB::table('precios_dia_semana')->insertGetId($data);
                        $ids_to_keep[] = $newId;
                    } else {
                        // Regla existente, actualizar
                        DB::table('precios_dia_semana')
                            ->where('id_precio_dia', $id_precio_dia)
                            ->update($data);
                        $ids_to_keep[] = $id_precio_dia;
                    }
                }
            }

            // Eliminar reglas que ya no están en el request
            $ids_to_delete = array_diff($current_rule_ids, $ids_to_keep);
            if (!empty($ids_to_delete)) {
                DB::table('precios_dia_semana')->whereIn('id_precio_dia', $ids_to_delete)->delete();
            }

            DB::commit();
            return response()->json(['success' => true, 'message' => 'Precios dinámicos guardados exitosamente.']);

        } catch (\Exception $e) {
            DB::rollback();
            \Log::error("Error al guardar precios dinámicos para producto {$productId}: " . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Error al guardar los precios dinámicos: ' . $e->getMessage()], 500);
        }
    }

    public function toggleDescuento(Request $request)
    {
        $id = $request->id;
        $estado = $request->estado; // 1 (activado) o 0 (desactivado)

        if ($id === 'todos') {
            // Actualiza todos los productos de la sucursal actual
            DB::table('productos')
                ->where('IdEmpresa', Auth::user()->IdEmpresa) 
                ->update(['mitad_precio' => $estado]);
        } else {
            // Actualiza solo un producto
            DB::table('productos')
                ->where('IdProducto', $id)
                ->update(['mitad_precio' => $estado]);
        }

        return response()->json(['success' => true]);
    }



    public function index(Request $request)
    {
        $rucemp = trim(Auth::user()->IdEmpresa);
        $buspro = trim($request->get('buspro'));
        $tipo = $request->get('promocion'); // <-- Aquí capturamos el tipo de producto
        $categoria = $request->get('cmbCatId');
        $subcategoria = $request->get('subcat_id');
        $tip_pro_id = $request->get('tip_pro_id');
        $categorias = DB::table('categorias')->get();
        $subcategorias = DB::table('subcategorias')->get();
        $tipos = DB::table('tipo_producto')->get();
        $sucursales = DB::table('empresa_negocios')->get();
        $sucursal = $request->get('sucursal');
        
        if(empty($sucursal)){
          $sucursal = $sucursales->first()->id_empresa_negocio;
        }
        
        $tipos_productos = DB::table('tipos_productos')->get();            
        
        if(is_null($categoria)){
            $productos = DB::table('productos as p')
            ->select('ps.lote','ps.vencimiento','ma.mar_nom','p.procod','p.pronom','m.monnom','u.umenom','p.propun','p.proest','p.IdProducto','p.propun','modelo','cat_nom','imagenproducto','promocion','subcat_nom','tip_pro_nom','precio','codigo_barra','ps.stock','p.mitad_precio','debe','haber','costo','costo_total')
            ->leftjoin('moneda as m','p.moncod','=','m.moncod') 
            ->leftjoin('marcas as ma','ma.mar_id','=','p.marca') 
            ->leftjoin('categorias as cat','cat.cat_id','=','p.cat_id')
            ->leftjoin('subcategorias as subcat','subcat.subcat_id','=','p.subcat_id')
            ->leftjoin('tipo_producto as tp','tp.tip_pro_id','=','p.tip_pro_id')
            ->leftjoin('unidad_medida as u','p.umecod','=','u.umecod')
            ->leftjoin('producto_codigo as pc','pc.IdProducto','p.IdProducto')
            ->leftjoin('producto_stock as ps','ps.IdProducto','p.IdProducto')
            ->leftjoin('producto_empresa','producto_empresa.IdProducto','p.IdProducto')
            ->where('producto_empresa.id_empresa_negocio',$sucursal)
            ->where('p.tipo','1')
            // -- INICIO CAMBIO FILTRO TIPO DE PRODUCTO --
            ->where(function ($query) use ($tipo){
                 if($tipo != '' && $tipo !== null){
                     $query->where('p.promocion', $tipo);
                     if($tipo == '1') {
                        $query->orWhere('p.promocion', '6'); // Incluir el otro tipo de combo
                     }
                 } else {
                     $query->Where('p.promocion','0')
                     ->orWhere('p.promocion','2')             
                     ->orWhere('p.promocion','5')
                     ->orWhere('p.promocion','6');
                 }
            })
            // -- FIN CAMBIO FILTRO TIPO DE PRODUCTO --
            ->groupby('p.IdProducto')
            ->orderby('cat_nom','asc')
            ->orderby('pronom','asc')
            ->paginate(150);

        } else {
            $productos = DB::table('productos as p')
            ->select('ps.lote','ps.vencimiento','ma.mar_nom','p.procod','p.pronom','m.monnom','u.umenom','p.propun','p.proest','p.IdProducto','p.propun','modelo','cat_nom','imagenproducto','promocion','subcat_nom','tip_pro_nom','precio','codigo_barra','ps.stock','p.mitad_precio' ,'debe','haber','costo','costo_total')
            ->leftjoin('moneda as m','p.moncod','=','m.moncod') 
            ->leftjoin('categorias as cat','cat.cat_id','=','p.cat_id')
            ->leftjoin('marcas as ma','ma.mar_id','=','p.marca') 
            ->leftjoin('subcategorias as subcat','subcat.subcat_id','=','p.subcat_id')
            ->leftjoin('tipo_producto as tp','tp.tip_pro_id','=','p.tip_pro_id')
            ->leftjoin('unidad_medida as u','p.umecod','=','u.umecod')
            ->leftjoin('producto_codigo as pc','pc.IdProducto','p.IdProducto')
            ->leftjoin('producto_stock as ps','ps.IdProducto','p.IdProducto')
            ->leftjoin('producto_empresa','producto_empresa.IdProducto','p.IdProducto')
            ->where(function ($query) use ($buspro) {
              if(!empty($buspro)){
                 $query->Where('pronom','like','%'.$buspro.'%')
                 ->orWhere('codigo_barra',$buspro)
             ->orWhere('procod',$buspro);
          }
        })
        ->where(function ($query1) use ($categoria) {
          if($categoria !='0'){
             $query1->Where('p.cat_id',$categoria);
          }
        })
        ->where(function ($query2) use ($subcategoria) {
          if($subcategoria !='0'){
             $query2->Where('p.subcat_id',$subcategoria);
          }
        })
        ->where(function ($query3) use ($tip_pro_id) {
          if($tip_pro_id !='0' && !is_null($tip_pro_id)){
             $query3->Where('p.tip_pro_id',$tip_pro_id);
          }
        })
        // -- INICIO CAMBIO FILTRO TIPO DE PRODUCTO --
        ->where(function ($query) use ($tipo){
             if($tipo != '' && $tipo !== null){
                 $query->where('p.promocion', $tipo);
                 if($tipo == '1') {
                    $query->orWhere('p.promocion', '6'); // Incluir el otro tipo de combo
                 }
             } else {
                 $query->Where('p.promocion','0')
                 ->orWhere('p.promocion','2')             
                 ->orWhere('p.promocion','6')
                 ->orWhere('p.promocion','5');
             }
        })
        // -- FIN CAMBIO FILTRO TIPO DE PRODUCTO --
        ->where('producto_empresa.id_empresa_negocio',$sucursal)
        ->where('p.tipo','1')
        ->groupby('p.IdProducto')
        ->orderby('cat_nom','asc')
        ->orderby('pronom','asc')
        ->paginate(150);
    }

    $data_suc = DB::table('empresa_negocios')->where('id_empresa_negocio',$sucursal)->first();
       
    // Agregué 'tipo' al compact por si necesitas usar la variable en la vista.
    return view('empresas.productos.index',compact('productos','buspro','categorias','tipos_productos','tipos','subcategorias','subcategoria','tip_pro_id','categoria','sucursales','sucursal','data_suc', 'tipo'));
}

    
    public function indexInsumos(Request $request)
    {
        $sucursal = $request->get('sucursal');
        $buspro = trim($request->get('buspro'));
        $categoria = $request->get('cmbCatId');
        $subcategoria = $request->get('subcat_id');
        $tip_pro_id = $request->get('tip_pro_id');
        
        $sucursales = DB::tABLE('empresa_negocios')->get();
        $categorias = DB::tABLE('categorias')->get();
        $subcategorias = DB::tABLE('subcategorias')->get();
        $tipos = DB::tABLE('tipo_producto')->get();
        
        if(empty($sucursal)){
            $sucursal = $sucursales->first()->id_empresa_negocio;
        }

        $productos = DB::tABLE('productos as p')
            ->select('ps.lote','ps.vencimiento','ma.mar_nom','p.procod','p.pronom','m.monnom','u.umenom','p.propun','p.proest','p.IdProducto','p.propun','modelo','cat_nom','imagenproducto','promocion','subcat_nom','tip_pro_nom','precio','codigo_barra','ps.stock','p.mitad_precio','debe','haber','costo','costo_total')
            ->leftjoin('moneda as m','p.moncod','=','m.moncod') 
            ->leftjoin('marcas as ma','ma.mar_id','=','p.marca') 
            ->leftjoin('categorias as cat','cat.cat_id','=','p.cat_id')
            ->leftjoin('subcategorias as subcat','subcat.subcat_id','=','p.subcat_id')
            ->leftjoin('tipo_producto as tp','tp.tip_pro_id','=','p.tip_pro_id')
            ->leftjoin('unidad_medida as u','p.umecod','=','u.umecod')
            ->leftjoin('producto_codigo as pc','pc.IdProducto','p.IdProducto')
            ->leftjoin('producto_stock as ps','ps.IdProducto','p.IdProducto')
            ->leftjoin('producto_empresa','producto_empresa.IdProducto','p.IdProducto')
            ->where('producto_empresa.id_empresa_negocio', $sucursal)
            ->where('p.tipo', '1')
            ->where('p.promocion', '4') // Filtro exclusivo de INSUMOS
            // Filtro de búsqueda por texto o código
            ->where(function ($query) use ($buspro) {
                if(!empty($buspro)){
                    $query->where('p.pronom', 'like', '%'.$buspro.'%')
                          ->orWhere('p.codigo_barra', $buspro)
                          ->orWhere('p.procod', $buspro);
                }
            })
            // Filtro por categoría
            ->when($categoria && $categoria != '0', function ($query) use ($categoria) {
                return $query->where('p.cat_id', $categoria);
            })
            // Filtro por subcategoría
            ->when($subcategoria && $subcategoria != '0', function ($query) use ($subcategoria) {
                return $query->where('p.subcat_id', $subcategoria);
            })
            // Filtro por tipo de producto (línea)
            ->when($tip_pro_id && $tip_pro_id != '0', function ($query) use ($tip_pro_id) {
                return $query->where('p.tip_pro_id', $tip_pro_id);
            })
            ->groupby('p.IdProducto')
            ->orderby('cat_nom', 'asc')
            ->orderby('pronom', 'asc')
            ->paginate(150);

        $data_suc = DB::tABLE('empresa_negocios')->where('id_empresa_negocio', $sucursal)->first();
           
        return view('empresas.productos.index_insumos', compact(
            'productos', 'sucursales', 'sucursal', 'data_suc', 'categorias', 
            'subcategorias', 'tipos', 'categoria', 'subcategoria', 'tip_pro_id', 'buspro'
        ));
    }

    public function buscarfamilias($id)
{
    $categorias = DB::table('categorias')->where('tip_pro_id', $id)->get();
    
    $vista = '<option value="">Seleccione una Familia...</option>';
    foreach ($categorias as $cat) {
        $vista .= '<option value="'.$cat->cat_id.'">'.$cat->cat_nom.'</option>';
    }
    
    return response()->json(['vista' => $vista]);
}

    public function asignarreceta($prod_id){

        $insumos = DB::table('productos')
            ->where(function ($query){
                $query->where('promocion','=','0')
                      ->orWhere('promocion','=','4');
            })
            ->where('tipo','1')
            ->leftjoin('unidad_medida as cum','cum.umecod','productos.umecod','umecod_cons','factor_cons','factor')
            ->get();

        $receta = DB::table('recetas as tbc')
            ->join('productos as tbp','tbp.IdProducto','tbc.prod_insu')
            ->leftjoin('unidad_medida as cum','cum.umecod','tbp.umecod')
            ->where('tbc.prod_id',$prod_id)
            ->get();

        $producto = DB::table('productos')->where('IdProducto',$prod_id)->first();
        
        // Obtenemos el precio actual. Priorizamos producto_empresa, si no hay, usamos propun de productos
        $precio_actual = DB::table('producto_empresa')->where('IdProducto', $prod_id)->value('precio');
        if(is_null($precio_actual)) {
            $precio_actual = $producto->propun;
        }
          
        return view('empresas.productos.recetas', compact('insumos','prod_id','producto','receta', 'precio_actual'));
    }

    public function registrarreceta(Request $request){

        $insumos_marcados = $request->get('prod_ins_id'); 
        $cantidad         = $request->get('cantidad');
        $factor           = $request->get('factor_ins');
        $unidadmedida     = $request->get('unidadmedida');
        $costo_insumo     = $request->get('costo_insumo'); 
        $prod_id          = $request->get('producto');
        
        // 1. Recibimos el precio de venta editable
        $precio_venta     = $request->get('precio_venta');

        // 2. Actualizamos el precio en ambas tablas
        if(is_numeric($precio_venta)){
            DB::table('productos')
                ->where('IdProducto', $prod_id)
                ->update(['propun' => $precio_venta]);
                
            DB::table('producto_empresa')
                ->where('IdProducto', $prod_id)
                ->update(['precio' => $precio_venta]);
        }

        // 3. Limpiamos la receta actual
        recetas::where('prod_id', $prod_id)->delete();

        // 4. Guardamos los nuevos valores de insumos
        if(!empty($insumos_marcados)){
            foreach ($insumos_marcados as $insu_id) {     
                $receta = new recetas;
                $receta->prod_id      = $prod_id;
                $receta->prod_insu    = $insu_id;
                $receta->rec_cant     = $cantidad[$insu_id];
                $receta->ins_costo    = $costo_insumo[$insu_id] ?? 0; 
                $receta->factor_      = $factor[$insu_id];
                $receta->unidadmedida = $unidadmedida[$insu_id];
                $receta->save();     
            }     
        }

        return Redirect::to('/productos')->with('success', 'Receta y precio actualizados correctamente.');
    }

    public function exportarRecetasExcel()
{
    // Obtenemos solo los productos preparados (promocion = 2)
    $preparados = DB::table('productos')
        ->where('promocion', '2')
        ->orderBy('pronom', 'asc')
        ->get();

    $data = [];
    $filas_negrita = []; // Aquí guardaremos los números de fila que deben ir en negrita
    $numero_fila = 1;

    foreach ($preparados as $p) {
        // Fila 1: Título principal
        $data[] = ['PRODUCTOS PREPARADOS', 'PRECIO DE VENTA', ''];
        $filas_negrita[] = $numero_fila;
        $numero_fila++;

        // Fila 2: Nombre del producto y su Precio exacto
        $data[] = [$p->pronom, $p->propun, ''];
        $filas_negrita[] = $numero_fila;
        $numero_fila++;

        // Fila 3: Cabeceras de insumos
        $data[] = ['INSUMO', 'CANTIDAD', 'UNIDAD MEDIDA'];
        $filas_negrita[] = $numero_fila;
        $numero_fila++;

        // CORRECCIÓN VITAL: Buscamos los insumos cruzando la unidad de medida registrada en la receta (r.unidadmedida)
        $recetas = DB::table('recetas as r')
            ->join('productos as i', 'r.prod_insu', '=', 'i.IdProducto')
            // Cruzamos con unidad_medida usando el código de consumo guardado en la receta
            ->leftJoin('unidad_medida as u', 'r.unidadmedida', '=', 'u.umecod')
            ->where('r.prod_id', $p->IdProducto)
            // Si u.umenom viene vacío por alguna razón, usamos r.unidadmedida como respaldo
            ->select('i.pronom as insumo_nom', 'r.rec_cant', DB::raw('COALESCE(u.umenom, r.unidadmedida) as umenom'))
            ->get();

        if ($recetas->isEmpty()) {
            // Si no hay receta, agregamos la fila indicándolo
            $data[] = ['-- SIN RECETA REGISTRADA --', '', ''];
            $numero_fila++;
        } else {
            // Si hay receta, agregamos una fila por cada insumo
            foreach ($recetas as $r) {
                $data[] = [$r->insumo_nom, $r->rec_cant, $r->umenom];
                $numero_fila++;
            }
        }

        // Fila vacía para crear un espacio antes del siguiente producto
        $data[] = ['', '', ''];
        $numero_fila++;
    }

    return Excel::create('Recetas_Preparados_'.date('d-m-Y'), function($excel) use ($data, $filas_negrita) {
        $excel->sheet('Recetas', function($sheet) use ($data, $filas_negrita) {
            
            // El 5to parámetro en 'false' le dice a la librería que NO convierta
            // la primera fila en cabeceras de columna automáticamente.
            $sheet->fromArray($data, null, 'A1', false, false);

            // Aplicamos negrita a las filas de los títulos y nombres de productos
            foreach ($filas_negrita as $fila) {
                $sheet->row($fila, function($row) {
                    $row->setFontWeight('bold');
                });
            }
            
        });
    })->export('xlsx');
}

    public function asignarcombo($prod_id){

      $insumos = DB::tABLE('productos')
       ->where(function ($query){
                $query->where('promocion','=','0')
                      ->orWhere('promocion','=','2');
        })
      ->where('productos.IdEmpresa',Auth::user()->IdEmpresa)
      ->join('unidad_medida as cum','cum.umecod','productos.umecod')
      ->get();

      $combos = DB::tABLE('combos as tbc')
      ->join('productos as tbp','tbp.IdProducto','tbc.prod_combo')
      ->join('unidad_medida as cum','cum.umecod','tbp.umecod')
      ->where('tbc.prod_id',$prod_id)
      ->get();

      $producto = DB::tABLE('productos')->where('IdProducto',$prod_id)->first();

      return view('empresas.productos.combos',compact('insumos','prod_id','producto','combos'));

    }

     public function registrarcombo(Request $request){

      $prodcombo = $request->get('prod_comb_id');
      $cantidad = $request->get('cantidad');
      $prod_id =$request->get('producto');

      combos::where('prod_id',$prod_id)->delete();

      $suma = 0;
      if(!empty($prodcombo)){
         foreach ($prodcombo as $index => $insu) {
     
             $combo = new combos;
             $combo->prod_combo = $insu;
             $combo->prod_id = $prod_id;
             $combo->comb_cant = $cantidad[$index];
             $combo->save();

             $buscarprecio = DB::tABLE('productos')->where('IdProducto',$insu)->first();

             $suma = $suma + $cantidad[$index]*$buscarprecio->costo;
     
      }

        $actualizarcosto = productos::findOrFail($prod_id);
        $actualizarcosto->costo = $request->get('totalcosto');
        if($request->get('preciofinal')=='0'){
           $actualizarcosto->propun =  $request->get('preciosugerido');
        }else{
           $actualizarcosto->propun = $request->get('preciofinal');
        }
       
        $actualizarcosto->update();

      }

       return Redirect::to('/productos');
     
    }

    public function actualizar(){

      $empresas = DB::tABLE('empresa_negocios')->get();

      $productos = DB::tABLE('productos')->get();

      foreach ($empresas as $emp) {
        
        foreach ($productos as $pro) {
            

            $buspro = DB::tABLE('producto_empresa')
            ->where('id_empresa_negocio',$emp->id_empresa_negocio)
            ->where('IdProducto',$pro->IdProducto)
            ->first();

       
            if(empty($bus_pro)){

               DB::tABLE('producto_empresa')
               ->insert(['IdProducto'=>$pro->IdProducto,
                'id_empresa_negocio'=>$emp->id_empresa_negocio,
                'precio'=>$pro->propun,
                'precio3'=>$pro->propun2,
                'precio2'=>$pro->propun1
              ]); 
            }

           
        }
      }

      return Redirect::to('/actualizarproductostock');

    }

    public function actualizar_producto_stock(){

      $empresas = DB::tABLE('empresa_negocios')->get();

    

      $productos = DB::tABLE('productos')->where('tipo','1')->get();

      foreach ($empresas as $emp) {
        $almacenes = DB::tABLE('almacenes')->where('id_empresa_negocio',$emp->id_empresa_negocio)->get();


        if(count($almacenes)>'0'){
          foreach ($almacenes as $alm){

              foreach ($productos as $pro) {
             

                   DB::tABLE('producto_stock')->insert([
                   'IdProducto'=>$pro->IdProducto,
                   'id_empresa_negocio'=>$emp->id_empresa_negocio,
                   'id_almacen'=>$alm->id_almacen,
                   'stock'=>$pro->stock_migrar,
                    'stock_inicial'=>$pro->stock_migrar                
                   ]); 
               
              }
            }
           

        }
       


      }

       return Redirect::to('/utilitarios');

    }

  public function actualizarproductostock(){

      $empresas = DB::tABLE('empresa_negocios')->where('id_empresa_negocio','3')->get();

      $productos = DB::tABLE('productos')->get();

      foreach ($empresas as $emp) {
        

        foreach ($productos as $pro) {
            

            $buspro = DB::tABLE('producto_empresa')
            ->where('id_empresa_negocio',$emp->id_empresa_negocio)
            ->where('IdProducto',$pro->IdProducto)
            ->first();

       

            if(empty($bus_pro)){

               DB::tABLE('producto_empresa')->insert(['IdProducto'=>$pro->IdProducto,'id_empresa_negocio'=>$emp->id_empresa_negocio]); 
            }

           
        }
      }

    }


    public function actualizarstock(Request $request){

      $sucursal = $request->get('suc_id');
      $almacen = $request->get('alm_id');
      $fecha = $request->get('inv_fec');
      $productos = $request->get('id');
      $stock = $request->get('cant');
      $costo = $request->get('costo');

      $cabecera = DB::tABLE('inventario_cabecera')
      ->insert(['inv_fec'=>$fecha,
                'IdUsuario'=>Auth::user()->IdUsuario,
                'id_empresa_negocio'=>$sucursal,
                'id_almacen'=>$almacen
              ]);

      $inv_cab_id = DB::getPdo()->lastInsertId();


      foreach ($productos as $key => $pro) {
        
        $bus_sto = DB::tABLE('producto_stock')
        ->where('id_empresa_negocio',$sucursal)
        ->where('id_almacen',$almacen)
        ->where('IdProducto',$pro)
        ->first();


        if(!empty($bus_sto)){

          DB::tABLE('producto_stock')
          ->where('id_empresa_negocio',$sucursal)
          ->where('id_almacen',$almacen)
          ->where('IdProducto',$pro)
          ->update(['stock'=>$stock[$key]]);

        }else{

          DB::tABLE('producto_stock')
          ->insert(['stock'=>$stock[$key],
                    'id_empresa_negocio'=>$sucursal,
                    'id_almacen'=>$almacen,
                    'IdProducto'=>$pro
          ]);

        }
        
      


        DB::tABLE('inventario_detalle')
        ->insert(['inv_cab_id'=>$inv_cab_id,
          'IdProducto'=>$pro,
          'inv_can'=>$stock[$key],
          'inv_costo'=>$costo[$key]
        ]);


      }

      return Redirect::to('/inventarios');

    }

    


    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {
        $origen = $request->get('origen');

        $rucemp = trim(Auth::user()->IdEmpresa);

        $marcas = DB::tABLE('marcas')->get();

        $modelos = DB::tABLE('modelos')->get();

        $laboratorios = DB::tABLE('laboratorio')->get();

        $unidades = DB::tABLE('unidad_medida')
        ->orderBy('umecod','asc')->get();

        $monedas = DB::tABLE('moneda')->where('monest','=','Activo')->orderby('moncod','asc')->get();

        $proveedores = DB::tABLE('proveedor')->WHERE('id_empresa_negocio',Auth::user()->id_empresa_negocio)->get();

        $tipoigv = DB::tABLE('tipo_igv')->get();

         $categorias = DB::tABLE('categorias')->get();
        $subcategorias = DB::tABLE('subcategorias')->get();
        $tipos = DB::tABLE('tipo_producto')->get();

        $programas = DB::tABLE('programas')->get();

        $servicios = DB::tABLE('servicios')->get();

        $sucursal = DB::tABLE('empresa_negocios')->WHERE('id_empresa_negocio',Auth::user()->id_empresa_negocio)->first();

        return view('empresas.productos.create',compact('origen','modelos','programas','unidades','categorias','monedas','proveedores','tipoigv','subcategorias','tipos','servicios','marcas','laboratorios','sucursal'));

    }


      public function RegistrarPresentacion(Request $request){
       $rucemp = trim(Auth::user()->IdEmpresa); 
        $presentacion = new presentaciones;
        $presentacion->Presentacion = $request->get('presentacion');
        $presentacion->Descripcion = $request->get('descripcion');
        $presentacion->IdEmpresa =  $rucemp;
        $presentacion->save();

        return Redirect::to('/productos');
    }



     public function busquedaproductocomanda(Request $request, $valor){
        $rucemp = trim(Auth::user()->IdEmpresa);
        $productos = DB::tABLE('productos')->select('procod','pronom','propun','provun','IdProducto','umecod','stock','promocion','color','stock')
        ->where(function ($query) use($valor){
                $query->where('procod','=',$valor)
                      ->orWhere('pronom', 'like','%'.$valor.'%');
        })
        ->where(function ($query){
                $query->where('promocion','1')
                ->orWhere('promocion','0')
                      ->orWhere('promocion','2');
        })
        ->where('id_empresa_negocio',Auth::user()->id_empresa_negocio)
        ->get();

        $unidades = DB::tABLE('unidad_medida')->where('umeest','=','Activo')
        ->orderBy('umecod','asc')->get();

        $vista = view('empresas.productos.menucomanda',compact('productos','unidades'))->render();

        if($request->ajax()){
         return response()->json(['vista'=>$vista]);

        }
    }

    public function generarean13($digits){
        //first change digits to a string so that we can access individual numbers
       
        $digits =(string)$digits;
        // 1. Add the values of the digits in the even-numbered positions: 2, 4, 6, etc.
        $even_sum = $digits{1} + $digits{3} + $digits{5} + $digits{7} + $digits{9} + $digits{11};
        // 2. Multiply this result by 3.
        $even_sum_three = $even_sum * 3;
        // 3. Add the values of the digits in the odd-numbered positions: 1, 3, 5, etc.
        $odd_sum = $digits{0} + $digits{2} + $digits{4} + $digits{6} + $digits{8} + $digits{10};
        // 4. Sum the results of steps 2 and 3.
        $total_sum = $even_sum_three + $odd_sum;
        // 5. The check character is the smallest number which, when added to the result in step 4,  produces a multiple of 10.
        $next_ten = (ceil($total_sum/10))*10;
        $check_digit = $next_ten - $total_sum;
        return $digits . $check_digit;
    }



    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
   public function store(Request $request)
    {
        $origen = $request->get('origen');

        $empresas = DB::tABLE('empresa_negocios')->get();
        $dat_emp = Empresa::findOrFail(Auth::user()->IdEmpresa);
        $tipo_codigo = $request->get('tipo_codigo');
        $codigo = $request->get('txt_procod');
        $codigo_bar = $request->get('codigo_barra');
        
        if($tipo_codigo=='1'){
            $codigoean13 = self::generarean13($codigo_bar);
        }else{
            $codigoean13 = $codigo_bar;
        }
      
        $nombreprod = trim($request->get('txt_pronom'));

        $validar = DB::tABLE('productos')
        ->where('procod',$codigo)
        ->where('tipo','1')
        ->get();

        if(empty($nombreprod)){
            if($request->ajax()){
                return response()->json(['estado'=>'error','mensaje'=>'INGRESAR LA DESCRIPCION DEL PRODUCTO']);
            }
        }

        if(count($validar)>0 && !empty($codigo)){ // Agregamos !empty($codigo) para que no valide si el código se autogenerará
            if($request->ajax()){
                return response()->json(['estado'=>'error','mensaje'=>'EL CODIGO DE PRODUCTO EXISTE']);
            }
        }

        $codigos = $request->get('codigobarra');
        $presentacion = $request->get('presentacion');
        $codigo_barra_pre = $request->get('codigo_barra_pre');
        $descripcion = $request->get('descripcion');
        $costo = $request->get('txt_costo');
        $costo_pre = $request->get('costo');
        $flete = $request->get('txt_flete');
        $costo_total = $request->get('costo_total');
        
        $precio = $request->get('precio');
        $precio2 = $request->get('precio2');
        $precio3 = $request->get('precio3');
        $factor = $request->get('factor');
        

        $stock = $request->get('stock_inicial');

        $productos = new productos;
        $productos->IdEmpresa= trim(Auth::user()->IdEmpresa);
        if(!empty($request->get('txt_procod'))){
            $productos->procod = $request->get('txt_procod');
        }
        $productos->pronom = $request->get('txt_pronom');
        $productos->cod_producto_sunat = $request->get('cod_producto_sunat');
        $productos->marca = $request->get('marca');
        $productos->modelo = $request->get('modelo');
        $productos->tipo_codigo = $request->get('tipo_codigo');

        $productos->requiere_lote_vencimiento = $request->has('requiere_lote_vencimiento');
        $productos->tiene_entrada = $request->has('tiene_entrada') ? 1 : 0;
        $productos->genera_puntos = $request->has('genera_puntos') ? 1 : 0;

        $productos->codigo_barra = $codigoean13;
        $productos->umecod = $request->get('txt_umecod');
        $productos->moncod = $request->get('txt_moncod');

        $productos->debe = $request->get('debe');
        $productos->haber = $request->get('haber');
        $productos->debe_nc = $request->get('haber');
        $productos->haber_nc = $request->get('debe');

        $productos->costofijo = $request->get('txt_costofijo');
        $productos->propun = $request->get('txt_propun');
        $productos->propun1 = $request->get('txt_propun2');
        $productos->propun2 = $request->get('txt_propun3');
        $productos->stock_min = $request->get('stock_min');
        $productos->factor = $request->get('factor_pro');

        

        if($request->get('promocion')=='4'){
            $productos->factor_cons = $request->get('factor_cons');
            $productos->umecod_cons = $request->get('umecod_cons');
        }

        
        if($request->get('icbper')=='1'){
             $productos->mon_icbper = $dat_emp->icbper;
        }
        $productos->icbper = $request->get('icbper');
        $productos->comision = $request->get('comision');
        $productos->ubicacion = $request->get('ubicacion');
        $productos->promocion = $request->get('promocion');
        $productos->costo = $costo;
        $productos->costo_total = $costo_total;
        
        $productos->flete = $flete;
        $productos->tipo ='1';
        if($request->get('promocion')=='2'){
            $productos->ser_cod = $request->get('tip_pre');
        }
        
        $productos->tigcod = $request->get('tigcod');
        $productos->id_empresa_negocio = Auth::user()->id_empresa_negocio;
        $productos->cat_id = $request->get('cat_id');
        $productos->subcat_id = $request->get('subcat_id');
        $productos->tip_pro_id = $request->get('tip_pro_id');
        $productos->proest = "Activo";
        

        // **Manejo de imagen principal**
        if ($request->hasFile('imagen')) {
            $file = $request->file('imagen');
            $filename = time() . '_' . $file->getClientOriginalName();
            $file->move(public_path() . '/imagenes/productos/', $filename);
            $productos->imagenproducto = $filename;
        }
        $productos->save();
        
        // Registrar en todas las empresas
        foreach($empresas as $emp) {
            $bus_pro = DB::table('producto_empresa')
                ->where('id_empresa_negocio',$emp->id_empresa_negocio)
                ->where('IdProducto',$productos->IdProducto)
                ->first();
            
            if(empty($bus_pro)){
                DB::table('producto_empresa')
                    ->insert([
                        'id_empresa_negocio'=>$emp->id_empresa_negocio,
                        'IdProducto'=>$productos->IdProducto,
                        'precio'=>$request->get('txt_propun'),
                        'precio3'=>$request->get('txt_propun3'),
                        'precio2'=>$request->get('txt_propun2')
                    ]);
            }
            
            $almacenes = DB::table('almacenes')
                ->where('id_empresa_negocio',$emp->id_empresa_negocio)
                ->get();
            
            if(count($almacenes)>'0'){
                foreach ($almacenes as $alm){
                    $bus_pro_stock = DB::table('producto_stock')
                        ->where('id_empresa_negocio',$emp->id_empresa_negocio)
                        ->where('id_almacen',$alm->id_almacen)
                        ->where('IdProducto',$productos->IdProducto)
                        ->first();
                    if(empty($bus_pro_stock)){
                        DB::table('producto_stock')
                            ->insert([
                                'IdProducto'=>$productos->IdProducto,
                                'id_empresa_negocio'=>$emp->id_empresa_negocio,
                                'stock'=>$productos->stockinicial,
                                'id_almacen'=>$alm->id_almacen
                            ]); 
                    } 
                }
            }
        }

        // Autogenerar código si no se proporcionó
        if(empty($request->get('txt_procod'))){
            $buscarproducto = productos::findOrFail($productos->IdProducto);
            if($request->get('promocion')=='0'){
                $correlativo = DB::table('empresa_negocios')->select('corr_prod')->where('id_empresa_negocio',Auth::user()->id_empresa_negocio)->first();
                $numero = $correlativo->corr_prod+1;
                DB::table('empresa_negocios')->where('id_empresa_negocio',Auth::user()->id_empresa_negocio)->update(['corr_prod'=>$numero]);
                $buscarproducto->procod = 'PROD'.str_pad($numero,4,"0", STR_PAD_LEFT);
            }elseif($request->get('promocion')=='2'){
                $correlativo = DB::table('empresa_negocios')->select('corr_prep')->where('id_empresa_negocio',Auth::user()->id_empresa_negocio)->first();
                $numero = $correlativo->corr_prep+1;
                DB::table('empresa_negocios')->where('id_empresa_negocio',Auth::user()->id_empresa_negocio)->update(['corr_prep'=>$numero]);
                $buscarproducto->procod = 'PREP'.str_pad($numero,4,"0", STR_PAD_LEFT);
            }elseif($request->get('promocion')=='5'){
                $correlativo = DB::table('empresa_negocios')->select('corr_ent')->where('id_empresa_negocio',Auth::user()->id_empresa_negocio)->first();
                $numero = $correlativo->corr_ent+1;
                DB::table('empresa_negocios')->where('id_empresa_negocio',Auth::user()->id_empresa_negocio)->update(['corr_ent'=>$numero]);
                $buscarproducto->procod = 'ENTR'.str_pad($numero,4,"0", STR_PAD_LEFT);
            }elseif($request->get('promocion')=='6'){
                $correlativo = DB::table('empresa_negocios')->select('corr_comb')->where('id_empresa_negocio',Auth::user()->id_empresa_negocio)->first();
                $numero = $correlativo->corr_comb+1;
                DB::table('empresa_negocios')->where('id_empresa_negocio',Auth::user()->id_empresa_negocio)->update(['corr_comb'=>$numero]);
                $buscarproducto->procod = 'ENTR'.str_pad($numero,4,"0", STR_PAD_LEFT);
            }elseif($request->get('promocion')=='4'){
                $correlativo = DB::table('empresa_negocios')->select('corr_insu')->where('id_empresa_negocio',Auth::user()->id_empresa_negocio)->first();
                $numero = $correlativo->corr_insu+1;
                DB::table('empresa_negocios')->where('id_empresa_negocio',Auth::user()->id_empresa_negocio)->update(['corr_insu'=>$numero]);
                $buscarproducto->procod = 'INSU'.str_pad($numero,4,"0", STR_PAD_LEFT);
            }
            $buscarproducto->update();
            $cod_pro = $buscarproducto->procod;
        }else{
            $cod_pro = $productos->procod;
        }
        
        // Guardar presentaciones
        if(!empty($presentacion)){
            foreach ($presentacion as $i => $pre) {
                if(!empty(trim($descripcion[$i]))){
                    $objpresentacion = new productos;
                    $objpresentacion->procod = $cod_pro;
                    $objpresentacion->codigo_barra = $codigo_barra_pre[$i] ?? null;
                    $objpresentacion->pronom = $descripcion[$i];
                    $objpresentacion->marca =  $productos->marca;
                    $objpresentacion->modelo = $productos->modelo;
                    $objpresentacion->color =  $productos->color;
                    $objpresentacion->umecod = $pre;
                    $objpresentacion->moncod = $productos->moncod;                    
                    $objpresentacion->propun = $precio[$i];
                    $objpresentacion->icbper = $productos->icbper;
                    $objpresentacion->promocion = $productos->promocion;
                    $objpresentacion->tipo ='2';
                    $objpresentacion->costo = $costo_pre[$i];
                    $objpresentacion->factor = $factor[$i];
                    $objpresentacion->tigcod = $productos->tigcod;
                    $objpresentacion->id_empresa_negocio = Auth::user()->id_empresa_negocio;
                    $objpresentacion->cat_id = $productos->cat_id;
                    $objpresentacion->proest = "Activo";
                    $objpresentacion->pro_rel = $productos->IdProducto;

                    // Manejo de imagen para nuevas presentaciones
                    if ($request->hasFile('imagen_pre.' . $i)) {
                        $file = $request->file('imagen_pre')[$i];
                        $filename = time() . '_' . $file->getClientOriginalName();
                        $file->move(public_path() . '/imagenes/productos/', $filename);
                        $objpresentacion->imagenproducto = $filename;
                    }
                    $objpresentacion->save();

                    foreach($empresas as $emp) {
                        $bus_pro_emp = DB::table('producto_empresa')
                            ->where('id_empresa_negocio',$emp->id_empresa_negocio)
                            ->where('IdProducto',$objpresentacion->IdProducto)
                            ->first();
                        if(empty($bus_pro_emp)){
                            DB::table('producto_empresa')->insert([
                                'id_empresa_negocio'=>$emp->id_empresa_negocio,
                                'IdProducto'=>$objpresentacion->IdProducto,
                                'precio'=>$precio[$i],
                                'precio2'=>$precio2[$i]
                            ]);
                        }
                    }
                }
            }
        }

        if (!empty($codigos)) {
            foreach ($codigos as $index => $codbar) {
                DB::table('producto_codigo')->insert([
                    'IdProducto'         => $productos->IdProducto,
                    'cod_bar'            => $codbar,
                    'id_empresa_negocio' => Auth::user()->id_empresa_negocio
                ]);
            }
        }

        if($request->ajax()){
            return response()->json(['mensaje'=>'PRODUCTO REGISTRADO']);
        }
    }

    public function buscarcategorias($producto,Request $request){

        if($producto=='4'){
              $categorias = DB::tABLE('categorias')->where('tipo',$producto)->get();
        }else{
              $categorias = DB::tABLE('categorias')
              ->where('tipo',NULL)
              ->orWhere('tipo','')
              ->get();  
        }
      

        $vista = view('empresas.productos.divcategorias',compact('categorias'))->render();

        if($request->ajax()){
          return response()->json(['vista'=>$vista]);

        }

    }

     public function buscarsubcategorias($producto,Request $request){
 
        $subcategorias = DB::tABLE('subcategorias')->where('cat_id',$producto)->get();
        
        $vista = view('empresas.productos.divsubcategorias',compact('subcategorias'))->render();

        if($request->ajax()){
          return response()->json(['vista'=>$vista]);

        }

    }

    public function buscartipos($producto,Request $request){
 
        $tipos = DB::tABLE('tipo_producto')->where('subcat_id',$producto)->get();
        
        $vista = view('empresas.productos.divtipos',compact('tipos'))->render();

        if($request->ajax()){
          return response()->json(['vista'=>$vista]);

        }

    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id, $sucursal)
{
    $rucemp = trim(Auth::user()->IdEmpresa);
    $marcas = DB::table('marcas')->get();
    $programas = DB::table('programas')->get();
    $modelos = DB::table('modelos')->get();

    // Traemos el producto con su sucursal
    $productos = productos::where('productos.IdProducto', $id)
        ->leftjoin('producto_empresa', 'producto_empresa.IdProducto', 'productos.IdProducto')
        ->where('producto_empresa.id_empresa_negocio', $sucursal)
        ->first();

    // --- FILTRADO LÓGICO PARA LA VISTA ---
    
    // 1. Traemos TODOS los Abuelos (Líneas)
    $tipos = DB::table('tipo_producto')->get();

    // 2. Traemos SOLO las Familias que pertenecen al Abuelo de este producto
    $categorias = DB::table('categorias')
        ->where('tip_pro_id', $productos->tip_pro_id)
        ->get();

    // 3. Traemos SOLO las Subfamilias que pertenecen a la Familia de este producto
    $subcategorias = DB::table('subcategorias')
        ->where('cat_id', $productos->cat_id)
        ->get();

    // --- FIN FILTRADO ---

    $laboratorios = DB::table('laboratorio')->get();
    $unidades = DB::table('unidad_medida')->orderBy('umecod', 'asc')->get();
    $tipoigv = DB::table('tipo_igv')->get();
    $codigos = DB::table('producto_codigo')->where('IdProducto', $id)->get();

    $presentaciones = DB::table('productos')
        ->leftjoin('producto_empresa', 'producto_empresa.IdProducto', 'productos.IdProducto')
        ->where('pro_rel', $id)
        ->where('producto_empresa.id_empresa_negocio', $sucursal)
        ->get();

    $precios_dinamicos_existentes = DB::table('precios_dia_semana')
        ->where('IdProducto', $id)
        ->where('id_empresa_negocio', $sucursal)
        ->get();

    $servicios = DB::table('servicios')->get();
    $monedas = DB::table('moneda')->where('monest', '=', 'Activo')->orderby('moncod', 'asc')->get();

    return view('empresas.productos.edit', compact(
        'modelos', 'productos', 'unidades', 'categorias', 'monedas', 'tipoigv', 'codigos', 
        'presentaciones', 'subcategorias', 'tipos', 'sucursal', 'servicios', 'marcas', 
        'programas', 'laboratorios', 'precios_dinamicos_existentes'
    ));
}

    public function presentaciones($id){

       $rucemp = trim(Auth::user()->IdEmpresa);

        $productos = productos::findOrFail($id);

        $unidades = DB::tABLE('unidad_medida')
        ->orderBy('umecod','asc')->get();

        $tipoigv = DB::tABLE('tipo_igv')->get();
         
        $categorias = DB::tABLE('categorias')->get();
        $monedas = DB::tABLE('moneda')->where('monest','=','Activo')->orderby('moncod','asc')->get();

        $codigos = DB::tABLE('producto_codigo')
        ->where('IdProducto',$id)
        ->get();

        $presentaciones = DB::tABLE('productos')->where('pro_rel',$id)->get();
        
        return view('empresas.productos.presentaciones',compact('productos','unidades','categorias','monedas','tipoigv','codigos','presentaciones','id'));

    }


    public function actualizarpresentaciones(Request $request, $id, $sucursal)
    {
        $empresas = DB::table('empresa_negocios')->get();
        $producto_principal = productos::findOrFail($id); // Obtenemos el producto principal

        // Obtener IDs de presentaciones existentes para este producto principal en esta sucursal
        $current_presentations_ids = DB::table('productos')
            ->where('pro_rel', $id)
            ->where('tipo', '2')
            ->where('id_empresa_negocio', $sucursal)
            ->pluck('IdProducto')
            ->toArray();

        $ids_from_request = $request->input('idprod', []); // IDs de presentaciones que vienen del formulario
        if (!is_array($ids_from_request)) { // Asegurarse de que sea un array
            $ids_from_request = [$ids_from_request];
        }

        // Eliminar presentaciones que ya no están en el request
        $ids_to_delete = array_diff($current_presentations_ids, $ids_from_request);
        if (!empty($ids_to_delete)) {
            DB::table('productos')->whereIn('IdProducto', $ids_to_delete)->delete();
        }

        // Iterar sobre los datos de las presentaciones enviadas por el formulario
        $presentaciones_data = $request->input('presentacion', []);
        $descripciones_data = $request->input('descripcion', []);
        $costos_data = $request->input('costo', []);
        $precios_data = $request->input('precio', []);
        $precios2_data = $request->input('precio2', []);
        $factores_data = $request->input('factor', []);
        $codigo_barra_pre_data = $request->input('codigo_barra_pre', []);

        foreach ($presentaciones_data as $i => $pre_umecod) {
            // Validación básica de datos
            if (!empty(trim($descripciones_data[$i])) && isset($costos_data[$i]) && isset($precios_data[$i])) {
                $objpresentacion = null;
                $isNew = false;
                $current_presentation_id = $ids_from_request[$i] ?? 0;

                // Determina si es una presentación existente o nueva
                if ($current_presentation_id != 0) {
                    $objpresentacion = productos::find($current_presentation_id);
                }

                if (!$objpresentacion) {
                    $objpresentacion = new productos;
                    $isNew = true;
                }

                // Asignación de propiedades
                $objpresentacion->procod = $producto_principal->procod; // Usa el código del producto principal
                $objpresentacion->pronom = $descripciones_data[$i];
                $objpresentacion->codigo_barra = $codigo_barra_pre_data[$i] ?? null;
                $objpresentacion->marca = $producto_principal->marca;
                $objpresentacion->modelo = $producto_principal->modelo;
                $objpresentacion->color = $producto_principal->color;
                $objpresentacion->umecod = $pre_umecod;
                $objpresentacion->moncod = $producto_principal->moncod;                
                $objpresentacion->propun = $precios_data[$i];
                $objpresentacion->propun1 = $precios2_data[$i] ?? null; // Si tienes precio2 para presentaciones
                $objpresentacion->icbper = $producto_principal->icbper;
                $objpresentacion->promocion = $producto_principal->promocion;
                $objpresentacion->tipo = '2'; // Tipo '2' para presentación
                $objpresentacion->costo = $costos_data[$i];
                $objpresentacion->factor = $factores_data[$i];
                $objpresentacion->tigcod = $producto_principal->tigcod;
                $objpresentacion->id_empresa_negocio = $sucursal;
                $objpresentacion->cat_id = $producto_principal->cat_id;
                $objpresentacion->proest = "Activo";
                $objpresentacion->pro_rel = $id; // Relaciona con el ID del producto principal

                // **MANEJO DE IMAGEN PARA PRESENTACIONES**
                // Para nuevas presentaciones, el input se llama imagen_pre[]
                if ($request->hasFile('imagen_pre.' . $i)) {
                    $file = $request->file('imagen_pre')[$i];
                    $filename = time() . '_' . $file->getClientOriginalName();
                    $file->move(public_path() . '/imagenes/productos/', $filename);
                    $objpresentacion->imagenproducto = $filename;
                } 
                // Para presentaciones existentes, el input se llama imagen_pre_existente[ID_PRODUCTO_PRESENTACION]
                elseif (!$isNew && $request->hasFile('imagen_pre_existente.' . $current_presentation_id)) {
                    $file = $request->file('imagen_pre_existente')[$current_presentation_id];
                    $filename = time() . '_' . $file->getClientOriginalName();
                    $file->move(public_path() . '/imagenes/productos/', $filename);
                    $objpresentacion->imagenproducto = $filename;
                }
                // Si no se sube una nueva imagen, la imagen existente se mantiene (no se sobrescribe).

                // Guardar/Actualizar la presentación
                if ($isNew) {
                    $objpresentacion->save();
                } else {
                    $objpresentacion->update();
                }

                // Actualizar en todas las empresas o insertar si no existe (lógica existente)
                foreach ($empresas as $emp) {
                    $bus_pro_emp = DB::table('producto_empresa')
                        ->where('id_empresa_negocio', $emp->id_empresa_negocio)
                        ->where('IdProducto', $objpresentacion->IdProducto)
                        ->first();

                    if (empty($bus_pro_emp)) {
                        DB::table('producto_empresa')
                            ->insert([
                                'id_empresa_negocio' => $emp->id_empresa_negocio,
                                'IdProducto' => $objpresentacion->IdProducto,
                                'precio' => $precios_data[$i],
                                'precio2' => $precios2_data[$i] ?? null
                            ]);
                    } else {
                        // Si ya existe, actualiza el precio para esa sucursal
                        DB::table('producto_empresa')
                            ->where('IdProducto', $objpresentacion->IdProducto)
                            ->where('id_empresa_negocio', $emp->id_empresa_negocio)
                            ->update([
                                'precio' => $precios_data[$i],
                                'precio2' => $precios2_data[$i] ?? null
                            ]);
                    }
                }
            }
        }
        return 'listo'; // Indicar éxito de la operación
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {

        try {

        $dat_emp = Empresa::findOrFail(Auth::user()->IdEmpresa);

        $empresas = DB::tABLE('empresa_negocios')->get();

        $codigos = $request->get('codigobarra');
        $codigosid = $request->get('id');
        $codigo_bar = $request->get('codigo_barra');

        $tipo_codigo = $request->get('tipo_codigo');



        if(strlen($codigo_bar)=='12' && $tipo_codigo=='1'){
            $codigoean13 = self::generarean13($codigo_bar);
        }else{
            $codigoean13 = trim($codigo_bar);
        }


        $sucursal = $request->get('sucursal');
        $prog_id = $request->get('prog_id');

        $costo = $request->get('txt_costo');
        $costo_pre = $request->get('costo');
        $flete = $request->get('txt_flete');
        $costo_total = $request->get('costo_total');
        

        $productos= productos::findOrFail($id);
        $productos->codigo_barra = $codigoean13;

       // $codigoean13 = self::generarean13($request->get('txt_procod'));
        $productos->procod = $request->get('txt_procod');
        $productos->pronom = $request->get('txt_pronom');
        $productos->cod_producto_sunat = $request->get('cod_producto_sunat');
        $productos->marca = $request->get('marca');
        $productos->costo = $costo;
        
        $productos->flete = $flete;
        $productos->costo_total = $costo_total;
        $productos->costofijo = $request->get('txt_costofijo');

        
         $productos->tipo_codigo = $request->get('tipo_codigo');
       
        $productos->cat_id = $request->get('cat_id');
        $productos->subcat_id = $request->get('subcat_id');
       
        $productos->tip_pro_id = $request->get('tip_pro_id');
        $productos->modelo = $request->get('modelo');       

        $productos->requiere_lote_vencimiento = $request->has('requiere_lote_vencimiento');
        $productos->tiene_entrada = $request->has('tiene_entrada') ? 1 : 0;
        $productos->genera_puntos = $request->has('genera_puntos') ? 1 : 0;


        $productos->umecod = $request->get('txt_umecod');
        $productos->moncod = $request->get('txt_moncod');

        $productos->debe = $request->get('debe');
        $productos->haber = $request->get('haber');
        $productos->debe_nc = $request->get('haber');
        $productos->haber_nc = $request->get('debe');
        
        
         $productos->factor = $request->get('factor_pro');
        
        $productos->stock_min = $request->get('stock_min');
        $productos->ubicacion = $request->get('ubicacion');
        $productos->tigcod = $request->get('tigcod');
        
        if($request->get('promocion')=='4'){
          $productos->factor_cons = $request->get('factor_cons');
          $productos->umecod_cons = $request->get('umecod_cons');
        }else{
          $productos->factor_cons = null;
          $productos->umecod_cons = null;
        }

        if($request->get('promocion')=='2'){
          $productos->ser_cod = $request->get('tip_pre');
        }
        $productos->propun = $request->get('txt_propun');
        $productos->propun1 = $request->get('txt_propun2');
        $productos->propun2 = $request->get('txt_propun3');

        //$productos->tiempo_maximo = $request->get('tiempo_maximo');


         if($request->get('icbper')=='1'){
             $productos->mon_icbper = $dat_emp->icbper;
        }
        $productos->icbper = $request->get('icbper');
        $productos->promocion = $request->get('promocion');
        //$productos->proest = $request->get('txt_proest');
        $productos->proest = 'Activo';
        $productos->id_empresa_negocio = Auth::user()->id_empresa_negocio;
         if(Input::hasFile('imagen')){
            $file=Input::file('imagen');
            $file->move(public_path().'/imagenes/productos/',$file->getClientOriginalName());
            $productos->imagenproducto=$file->getClientOriginalName();
        }

        $productos->update();
  

        $actpre = DB::tABLE('producto_empresa')->where('id_empresa_negocio',$sucursal)->where('IdProducto',$id)
        ->update(['precio'=>$request->get('txt_propun'),'precio2'=>$request->get('txt_propun2'),'precio3'=>$request->get('txt_propun3')]);
        
        $registros= DB::tABLE('producto_codigo')->where('IdProducto',$productos->IdProducto)->get();

        if(!is_null($codigosid)){
           foreach ($registros as $reg) {
            
                if(!in_array($reg->pro_cod_id,$codigosid)){
                    
                   DB::tABLE('producto_codigo')->where('pro_cod_id',$reg->pro_cod_id)->delete();

                }

            }
        }
       

        if(!is_null($codigos)){

        
          foreach ($codigos as $index=> $codbar){

            $buscar = DB::tABLE('producto_codigo')
            ->where('pro_cod_id',$codigosid[$index])
            ->first();

            if(empty($buscar)){
              DB::tABLE('producto_codigo')
              ->insert(['IdProducto'=>$id,'cod_bar'=>$codbar]);
            }else{
              DB::tABLE('producto_codigo')
              ->where('pro_cod_id',$codigosid[$index])
              ->update(['cod_bar'=>$codbar]);
            }



            
          }

        }else{

  
            DB::tABLE('producto_codigo')->where('IdProducto',$productos->IdProducto)->delete();

        }
      
        // Llamada a la función de actualizar presentaciones, pasándole el objeto Request completo
        self::actualizarpresentaciones($request, $id, $sucursal);
      
       

        return response()->json([
                'estado' => 'success',
                'mensaje' => '¡Producto "' . $productos->pronom . '" actualizado con éxito!'
            ]);
        } catch (\Exception $e) {
            // Por si algo falla en la base de datos
            return response()->json([
                'estado' => 'error',
                'mensaje' => 'Error al actualizar: ' . $e->getMessage()
            ]);
        }
          
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */

    public function destroy($id)
    {
        // 1. Encuentra el producto principal.
        $producto_principal = productos::findOrFail($id);

        // 2. Elimina todas las presentaciones asociadas a este producto principal.
        //    Asumimos que las presentaciones tienen el IdProducto del principal en su campo 'pro_rel'.
        //    Y que 'tipo' = '2' identifica a las presentaciones, mientras 'tipo' = '1' es el producto principal.
        productos::where('pro_rel', $id)
                 ->where('tipo', '2') // Opcional pero recomendado: asegúrate de eliminar solo las presentaciones.
                 ->delete();

        // 3. Elimina el producto principal.
        $producto_principal->delete();

        // 4. Redirige a la página de productos.
        return Redirect::to('/productos');
    }

 
    public function precios(){

      $productos = DB::tABLE('productos')->get();

      foreach ($productos as $pro) {
        DB::tABLE('producto_empresa')->where('IdProducto',$pro->IdProducto)->update(['precio'=>$pro->propun]);
      }
    }

  //----------------------------------------CONSULTAS PARA PUNTO DE VENTA-------------------------------------------->

   public function consultarmenu(Request $request, $cat_id){
         
        $rucemp = trim(Auth::user()->IdEmpresa);

        $sucursal = DB::tABLE('empresa_negocios')->where('id_empresa_negocio',Auth::user()->id_empresa_negocio)->first();

        $almacen = DB::tABLE('almacenes')
        ->where('predeterminado','1')
        ->where('id_empresa_negocio',Auth::user()->id_empresa_negocio)
        ->first();
  
            $productos = DB::tABLE('productos')
            ->select('pro_rel','costo','mar_nom','procod','pronom','propun','productos.IdProducto','umecod','promocion','color','imagenproducto','precio2','precio3',DB::raw("(SELECT stock FROM producto_stock as pro WHERE productos.IdProducto = pro.IdProducto AND id_almacen='".$almacen->id_almacen."' AND pro.id_empresa_negocio='".$almacen->id_empresa_negocio."') as stock"),'precio',DB::raw("(SELECT count(*) FROM productos as pro WHERE pro.pro_rel =  productos.IdProducto) as cont_pre"))
            ->join('producto_empresa','producto_empresa.IdProducto','productos.IdProducto')                      
          ->leftjoin('marcas','marcas.mar_id','productos.marca')
        ->where(function ($query) use($search){
                $query->where('pronom','like','%'.$search.'%')
          ->orwhere('procod',$search);
          })
            ->where('tipo','1')
            ->where('promocion','!=','2')           
            ->groupby('productos.IdProducto')
            ->orderby('productos.pronom')
            ->orderby('productos.umecod')
            ->get();



        $contar = count($productos);


        $unidades = DB::tABLE('unidad_medida')->where('umeest','=','Activo')
        ->orderBy('umecod','asc')->get();

        $vista = view('empresas.productos.menu',compact('productos','unidades','sucursal'))->render();

        if($request->ajax()){
         return response()->json(['vista'=>$vista]);

        }
    }


    public function consultarproductosservicio(Request $request, $tipo,$prog_id){
         
        $rucemp = trim(Auth::user()->IdEmpresa);

        $sucursal = DB::tABLE('empresa_negocios')->where('id_empresa_negocio',Auth::user()->id_empresa_negocio)->first();

        $almacen = DB::tABLE('almacenes')
        ->where('predeterminado','1')
        ->where('id_empresa_negocio',Auth::user()->id_empresa_negocio)
        ->first();

        $productos = DB::tABLE('productos')
        ->join('programas_preparados','productos.IdProducto','programas_preparados.IdProducto')
        //->leftjoin('pedido_servicio_cab',)
        ->select('productos.IdProducto','pronom')
        ->where('promocion','2')
      //  ->where('ser_cod',$tipo)
        ->where('programas_preparados.prog_id',$prog_id)
        ->get();


      //  dd($productos);
        $unidades = DB::tABLE('unidad_medida')->where('umeest','=','Activo')
        ->orderBy('umecod','asc')->get();

        $vista = view('empresas.puntosventas.divdetalleservicio',compact('productos'))->render();

        if($request->ajax()){
         return response()->json(['vista'=>$vista]);

        }
    }

 
    public function consultarproductoscompra(Request $request){


      $search = $request->search; 

      $id_almacen = $request->almacen;
       
      $rucemp = trim(Auth::user()->IdEmpresa);

       $bus_alm = DB::tABLE('almacenes')
        ->where('id_almacen',$id_almacen)

        ->first();


       $bus_suc = DB::tABLE('empresa_negocios')->where('id_empresa_negocio',$bus_alm->id_empresa_negocio)->first();

  
            $productos = DB::tABLE('productos')
            ->select('costo_total','pro_rel','costo','mar_nom','procod','pronom','propun','productos.IdProducto','umecod','promocion','color','imagenproducto','precio2','precio3',DB::raw("(SELECT stock FROM producto_stock as pro WHERE productos.IdProducto = pro.IdProducto AND id_almacen='".$bus_alm->id_almacen."' AND pro.id_empresa_negocio='".$bus_alm->id_empresa_negocio."') as stock"),'precio',DB::raw("(SELECT count(*) FROM productos as pro WHERE pro.pro_rel =  productos.IdProducto) as cont_pre"))
            ->join('producto_empresa','producto_empresa.IdProducto','productos.IdProducto')
              
          ->leftjoin('marcas','marcas.mar_id','productos.marca')
        ->where(function ($query) use($search){
                $query->where('pronom','like','%'.$search.'%')
          ->orwhere('procod',$search);
          })
            ->where('tipo','1')
            ->where('promocion','!=','2')
           
            ->groupby('productos.IdProducto')
            ->orderby('productos.pronom')
            ->orderby('productos.umecod')
            ->get();



      $contar = count($productos);

      $results = array();
    foreach($productos as $pro){


        $codnom = $pro->procod;
         
    

             $results[] = ['id'=>$pro->IdProducto,'producto'=>$pro->pronom.' '.$pro->mar_nom,'text'=>'COD:'.$pro->procod.' | '.$pro->pronom.' '.$pro->mar_nom.' | Precio: '.$pro->precio .' | STOCK: '.$pro->stock,'textcompra'=>'COD:'.$pro->procod.' | '.$pro->pronom.' '.$pro->mar_nom.' | Costo: '.$pro->costo_total .' | STOCK: '.$pro->stock,'propun'=>$pro->precio,'contar'=>$pro->cont_pre,'sub_costo'=>$pro->costo,'costo'=>$pro->costo_total,'codigo'=>$pro->procod,'unidad'=>$pro->umecod,'pro_rel'=>$pro->pro_rel];
                   
       
      }
     
       return response()->json($results);
    }




       public function busquedaproducto(Request $request, $valor){

        $rucemp = trim(Auth::user()->IdEmpresa);

          $sucursal = DB::tABLE('empresa_negocios')->where('id_empresa_negocio',Auth::user()->id_empresa_negocio)->first();

        $almacen = DB::tABLE('almacenes')
        ->where('predeterminado','1')
        ->where('id_empresa_negocio',Auth::user()->id_empresa_negocio)
        ->first();

        $productos = DB::tABLE('productos')
        ->select('productos.procod','productos.pronom','productos.propun','productos.IdProducto','productos.umecod','productos.promocion','productos.color','productos.imagenproducto','precio','precio2',DB::raw("(SELECT stock FROM producto_stock as pro WHERE productos.IdProducto = pro.IdProducto AND tipo='1' AND id_almacen='".$almacen->id_almacen."' AND id_empresa_negocio='".Auth::user()->id_empresa_negocio."') as stock"),DB::raw("(SELECT count(*) FROM productos as pro WHERE productos.IdProducto = pro.pro_rel AND tipo='2') as cont_pre"),'stock')
        ->leftjoin('producto_codigo','producto_codigo.IdProducto','productos.IdProducto')
        ->join('producto_empresa','producto_empresa.IdProducto','productos.IdProducto')
        ->join('producto_stock','producto_stock.IdProducto','producto_empresa.IdProducto')
        
       
        ->where(function ($query) use($valor){
                $query->where('cod_bar','=',$valor)
                      ->orWhere('productos.pronom', 'like','%'.$valor.'%')
                      ->orWhere('productos.procod',$valor);
        })
        ->where('tipo','1')
        ->where('producto_empresa.id_empresa_negocio',Auth::user()->id_empresa_negocio)
        ->where('id_almacen',$almacen->id_almacen)
        ->groupby('productos.IdProducto')
        ->orderby('productos.pronom')
        ->orderby('productos.umecod')
        ->get();

      //  dd(Auth::user()->id_empresa_negocio);
        
        $unidades = DB::tABLE('unidad_medida')->where('umeest','=','Activo')
        ->orderBy('umecod','asc')->get();

        $vista = view('empresas.productos.menu',compact('productos','unidades','sucursal'))->render();

    
        if($request->ajax()){
         return response()->json(['vista'=>$vista]);

        }
    }

     public function presentacionesproducto(Request $request, $id){
         
        $rucemp = trim(Auth::user()->IdEmpresa);
        
        $almacen = DB::tABLE('almacenes')
        ->where('predeterminado','1')
        ->where('id_empresa_negocio',Auth::user()->id_empresa_negocio)
        ->first();

      

        $productos = DB::tABLE('productos')
        ->select('acom','cat_sig','factor','precio3','productos.procod','productos.pronom','productos.propun','productos.IdProducto','productos.umecod','productos.promocion','productos.color','productos.imagenproducto','precio','precio2',DB::raw("(SELECT stock FROM producto_stock as pro WHERE pro.IdProducto ='".$id."' and pro.id_almacen='".$almacen->id_almacen."'  and pro.id_empresa_negocio='".Auth::user()->id_empresa_negocio."') as stock"),DB::raw("(SELECT umenom FROM unidad_medida as um WHERE um.umecod=productos.umecod) as umenom"))
        ->leftjoin('producto_empresa','producto_empresa.IdProducto','productos.IdProducto')          
        ->where(function ($query) use ($id) {
             
                    $query->where('productos.pro_rel',$id)
                    ->orwhere('productos.IdProducto',$id);


                })
        ->where('producto_empresa.id_empresa_negocio',Auth::user()->id_empresa_negocio)
        // ->where('tipo','2')
        ->groupby('productos.IdProducto')
        ->orderby('productos.pronom')
        ->orderby('productos.umecod')
        ->get();

      

        $unidades = DB::tABLE('unidad_medida')->where('umeest','=','Activo')
        ->orderBy('umecod','asc')->get();

        $vista = view('empresas.puntosventas.divproductos',compact('productos','unidades'))->render();

        if($request->ajax()){
         return response()->json(['vista'=>$vista]);

        }
    }

//-----------------------------------------------------------------------------------------------//
  

//------------------------------------------consultar productos compra --------------------------//


    

     public function presentacionesproductocompra(Request $request, $id, $sucursal,$almacen){
         
        $rucemp = trim(Auth::user()->IdEmpresa);

        $productos = DB::tABLE('productos')
         ->select('lab_nom','factor','productos.procod','productos.pronom','productos.propun','productos.IdProducto','productos.umecod','productos.promocion','productos.color','productos.imagenproducto','precio','precio2',DB::raw("(SELECT stock FROM producto_stock as pro WHERE pro.IdProducto ='".$id."' and pro.id_almacen='".$almacen."' and pro.id_empresa_negocio='".$sucursal."') as stock"),'costo')
        ->join('producto_empresa','producto_empresa.IdProducto','productos.IdProducto')
          
        ->where(function ($query) use ($id) {
             
                          $query->where('productos.pro_rel',$id)
          ->orwhere('productos.IdProducto',$id);


                })
        ->where('producto_empresa.id_empresa_negocio',$sucursal)
     
        ->groupby('productos.IdProducto')
        ->orderby('productos.pronom')
        ->orderby('productos.umecod')
        ->get();

   

        $unidades = DB::tABLE('unidad_medida')->where('umeest','=','Activo')
        ->orderBy('umecod','asc')->get();

        $vista = view('empresas.compras.divproductos',compact('productos','unidades'))->render();

        if($request->ajax()){
         return response()->json(['vista'=>$vista]);

        }
    }


     public function consultarmenucompra(Request $request, $cat_id,$sucursal,$almacen){
         
        $rucemp = trim(Auth::user()->IdEmpresa);

       

        $productos = DB::tABLE('productos')
        ->select('procod','pronom','propun','productos.IdProducto','umecod','promocion','color','imagenproducto','precio2',DB::raw("(SELECT stock FROM producto_stock as pro WHERE productos.IdProducto = pro.IdProducto AND tipo='1' AND id_almacen='".$almacen."' AND id_empresa_negocio='".$sucursal."') as stock"),'precio',DB::raw("(SELECT count(*) FROM productos as pro WHERE productos.IdProducto = pro.pro_rel AND tipo='2') as cont_pre"),'costo')
        ->join('producto_empresa','producto_empresa.IdProducto','productos.IdProducto')
        ->join('producto_stock','producto_stock.IdProducto','producto_empresa.IdProducto')
        ->where('cat_id',$cat_id)
        ->where('tipo','1')
        ->where('producto_empresa.id_empresa_negocio',$sucursal)
        ->where('id_almacen',$almacen)
        ->groupby('productos.IdProducto')
        ->orderby('productos.pronom')
        ->orderby('productos.umecod')
        ->get();


        $unidades = DB::tABLE('unidad_medida')->where('umeest','=','Activo')
        ->orderBy('umecod','asc')->get();

        $vista = view('empresas.productos.menucompra',compact('productos','unidades'))->render();

        if($request->ajax()){
         return response()->json(['vista'=>$vista]);

        }
    }

    public function busquedaproductocompra(Request $request, $valor,$sucursal,$almacen){

        $rucemp = trim(Auth::user()->IdEmpresa);

         $productos = DB::tABLE('productos')
        ->select('procod','pronom','propun','productos.IdProducto','umecod','promocion','color','imagenproducto','precio2',DB::raw("(SELECT stock FROM producto_stock as pro WHERE productos.IdProducto = pro.IdProducto AND tipo='1' AND id_almacen='".$almacen."' AND id_empresa_negocio='".$sucursal."') as stock"),'precio',DB::raw("(SELECT count(*) FROM productos as pro WHERE productos.IdProducto = pro.pro_rel AND tipo='2') as cont_pre"),'costo')
        ->leftjoin('producto_codigo','producto_codigo.IdProducto','productos.IdProducto')
        ->leftjoin('producto_stock','producto_stock.IdProducto','productos.IdProducto')
        ->leftjoin('producto_empresa','producto_empresa.IdProducto','productos.IdProducto')
        ->where(function ($query) use($valor){
                $query->where('cod_bar','=',$valor)
                      ->orWhere('productos.pronom', 'like','%'.$valor.'%')
                      ->orWhere('productos.procod',$valor);
        })
        ->where('producto_empresa.id_empresa_negocio',$sucursal)
        ->where('id_almacen',$almacen)
        ->groupby('productos.IdProducto')
        ->orderby('productos.pronom')
        ->orderby('productos.umecod')
        ->get();

        $unidades = DB::tABLE('unidad_medida')->where('umeest','=','Activo')
        ->orderBy('umecod','asc')->get();

        $vista = view('empresas.productos.menucompra',compact('productos','unidades'))->render();

        if($request->ajax()){
         return response()->json(['vista'=>$vista]);

        }
    }

 

        public function consultarproductos(Request $request)
{
    $search = trim($request->search);
    $id_almacen = $request->almacen;
    $user = Auth::user();

    // 1. Blindaje contra nulos en PHP 7.2
    $bus_alm = DB::table('almacenes')->where('id_almacen', $id_almacen)->first();
    
    if (!$bus_alm) {
        return response()->json([]); // Retorna vacío si no existe el almacén para evitar fatal error
    }

    $bus_suc = DB::table('empresa_negocios')->where('id_empresa_negocio', $bus_alm->id_empresa_negocio)->first();

    // 2. Selección con los nombres REALES de tu base de datos (propun, propun1, propun2)
    $productos = DB::table('productos')
        ->select(
            'productos.IdProducto',
            'productos.procod',
            'productos.pronom',
            'productos.umecod',
            'productos.costo',
            'productos.costo_total',
            'productos.propun as precio',      // Mapeamos propun como precio
            'productos.propun1 as precio2',    // Mapeamos propun1 como precio2
            'productos.propun2 as precio3',    // Mapeamos propun2 como precio3
            'productos.pro_rel',
            'productos.promocion',
            'productos.color',
            'productos.imagenproducto',
            'productos.icbper',
            'productos.mon_icbper',
            'marcas.mar_nom',
            'laboratorio.lab_nom',
            DB::raw("(SELECT stock FROM producto_stock WHERE IdProducto = productos.IdProducto AND id_almacen = '".$bus_alm->id_almacen."' AND id_empresa_negocio = '".$bus_alm->id_empresa_negocio."' LIMIT 1) as stock"),
            DB::raw("(SELECT count(*) FROM productos as pro WHERE pro.pro_rel = productos.IdProducto) as cont_pre")
        )
        ->join('producto_empresa', 'producto_empresa.IdProducto', '=', 'productos.IdProducto')
        
        ->leftJoin('marcas', 'marcas.mar_id', '=', 'productos.marca')
        ->where(function ($query) use ($search) {
            $query->where('productos.pronom', 'like', '%' . $search . '%')
                  ->orWhere('productos.procod', 'like', '%' . $search . '%')
                  ->orWhere('productos.codigo_barra', '=', $search); // ¡Vital para lector de código de barras en POS!
        })
        ->where('productos.tipo', '1')
        ->where('productos.promocion', '!=', '2')
        ->groupBy('productos.IdProducto')
        ->orderBy('productos.pronom', 'asc')
        ->orderBy('productos.umecod', 'asc')
        ->get();

    $results = [];

    // 3. Procesamiento limpio de resultados
    foreach ($productos as $pro) {
        $nombre_completo = trim($pro->pronom . ' ' . $pro->mar_nom);
        
        // Estructura base para no repetir código
        $base_data = [
            'id' => $pro->IdProducto,
            'producto' => $nombre_completo,
            'contar' => $pro->cont_pre,
            'costo' => $pro->costo_total,
            'codigo' => $pro->procod,
            'unidad' => $pro->umecod,
            'pro_rel' => $pro->pro_rel,
            'id_almacen' => $id_almacen,
            'icbper' => $pro->icbper,
            'mon_icbper' => $pro->mon_icbper
        ];

        // LÓGICA PARA ADMINISTRADORES
        if ($user->hasRole('admin') || $user->hasRole('superadmin')) {
            
            // Precio Normal
            $results[] = array_merge($base_data, [
                'text' => 'COD:' . $pro->procod . ' | ' . $nombre_completo . ' | Precio: ' . $pro->precio . ' | STOCK: ' . $pro->stock,
                'textcompra' => 'COD:' . $pro->procod . ' | ' . $nombre_completo . ' | Costo: ' . $pro->costo_total . ' | STOCK: ' . $pro->stock,
                'propun' => $pro->precio
            ]);

            // Precio Mayorista (Si existe y es mayor a 0)
            if ($pro->precio2 > 0) {
                $results[] = array_merge($base_data, [
                    'text' => 'COD:' . $pro->procod . ' | ' . $nombre_completo . ' | Precio Mayor: ' . $pro->precio2 . ' | STOCK: ' . $pro->stock,
                    'textcompra' => 'COD:' . $pro->procod . ' | ' . $nombre_completo . ' | Costo: ' . $pro->costo_total . ' | STOCK: ' . $pro->stock,
                    'propun' => $pro->precio2
                ]);
            }

            // Precio Especial (Si existe y es mayor a 0)
            if ($pro->precio3 > 0) {
                $results[] = array_merge($base_data, [
                    'text' => 'COD:' . $pro->procod . ' | ' . $nombre_completo . ' | Precio Especial: ' . $pro->precio3 . ' | STOCK: ' . $pro->stock,
                    'textcompra' => 'COD:' . $pro->procod . ' | ' . $nombre_completo . ' | Costo: ' . $pro->costo_total . ' | STOCK: ' . $pro->stock,
                    'propun' => $pro->precio3
                ]);
            }

        // LÓGICA PARA VENDEDORES, CAJA Y TÉCNICOS
        } elseif ($user->hasRole('vendedor') || $user->hasRole('caja') || $user->hasRole('tecnico')) {
            
            // Si ven_sin_sto es '0', evaluamos que el stock sea mayor a 0
            $tiene_stock_valido = ($bus_suc && $bus_suc->ven_sin_sto == '0') ? ($pro->stock > 0) : true;

            if ($tiene_stock_valido) {
                // Precio Normal
                $results[] = array_merge($base_data, [
                    'text' => 'COD:' . $pro->procod . ' | ' . $nombre_completo . ' | Precio: ' . $pro->precio . ' | STOCK: ' . $pro->stock,
                    'textcompra' => 'COD:' . $pro->procod . ' | ' . $nombre_completo . ' | Costo: ' . $pro->costo_total . ' | STOCK: ' . $pro->stock,
                    'propun' => $pro->precio
                ]);

                // Precio Mayorista
                if ($pro->precio2 > 0) {
                    $results[] = array_merge($base_data, [
                        'text' => 'COD:' . $pro->procod . ' | ' . $nombre_completo . ' | Precio Mayor: ' . $pro->precio2 . ' | STOCK: ' . $pro->stock,
                        'textcompra' => 'COD:' . $pro->procod . ' | ' . $nombre_completo . ' | Costo: ' . $pro->costo_total . ' | STOCK: ' . $pro->stock,
                        'propun' => $pro->precio2
                    ]);
                }
            }
        }
    }

    return response()->json($results);
}



            public function consultarservicios(Request $request){


      $search = $request->search; 

      $id_almacen = $request->almacen;
       
      $rucemp = trim(Auth::user()->IdEmpresa);

       $bus_alm = DB::tABLE('almacenes')
        ->where('id_almacen',$id_almacen)

        ->first();


       $bus_suc = DB::tABLE('empresa_negocios')
       ->where('id_empresa_negocio',$bus_alm->id_empresa_negocio)
       ->first();

  
            $productos = DB::tABLE('productos')
            ->select('costo_total','pro_rel','costo','mar_nom','procod','pronom','propun','productos.IdProducto','umecod','promocion','color','imagenproducto','precio2','precio3',DB::raw("(SELECT stock FROM producto_stock as pro WHERE productos.IdProducto = pro.IdProducto AND id_almacen='".$bus_alm->id_almacen."' AND pro.id_empresa_negocio='".$bus_alm->id_empresa_negocio."') as stock"),'precio',DB::raw("(SELECT count(*) FROM productos as pro WHERE pro.pro_rel =  productos.IdProducto) as cont_pre"))
            ->join('producto_empresa','producto_empresa.IdProducto','productos.IdProducto')
            
          ->leftjoin('marcas','marcas.mar_id','productos.marca')
        ->where(function ($query) use($search){
                $query->where('pronom','like','%'.$search.'%')
          ->orwhere('procod',$search);
          })
            ->where('tipo','1')
            ->where('promocion','=','6')
           // ->where('producto_empresa.id_empresa_negocio',Auth::user()->id_empresa_negocio)
           // ->where('id_almacen',$almacen->id_almacen)
            ->groupby('productos.IdProducto')
            ->orderby('productos.pronom')
            ->orderby('productos.umecod')
            ->get();



      $contar = count($productos);

      $results = array();
    foreach($productos as $pro){


        $codnom = $pro->procod;
     
    if(Auth::User()->hasRole('admin') || Auth::user()->hasRole('superadmin')){


       $results[] = ['id'=>$pro->IdProducto,'producto'=>$pro->pronom.' '.$pro->mar_nom,'text'=>'COD:'.$pro->procod.' | '.$pro->pronom.' '.$pro->mar_nom.' | Precio: '.$pro->precio .' | STOCK: '.$pro->stock,'textcompra'=>'COD:'.$pro->procod.' | '.$pro->pronom.' '.$pro->mar_nom.' | Costo: '.$pro->costo_total .' | STOCK: '.$pro->stock,'propun'=>$pro->precio,'contar'=>$pro->cont_pre,'costo'=>$pro->costo_total,'codigo'=>$pro->procod,'unidad'=>$pro->umecod,'pro_rel'=>$pro->pro_rel,"id_almacen"=>$id_almacen];
    
      if($pro->precio2 > 0){
         $results[] = ['id'=>$pro->IdProducto,'producto'=>$pro->pronom.' '.$pro->mar_nom,'text'=>'COD:'.$pro->procod.' | '.$pro->pronom.' '.$pro->mar_nom.' | Precio Mayor: '.$pro->precio2 .' | STOCK: '.$pro->stock,'textcompra'=>'COD:'.$pro->procod.' | '.$pro->pronom.' '.$pro->mar_nom.' | Costo: '.$pro->costo_total .' | STOCK: '.$pro->stock,'propun'=>$pro->precio2,'contar'=>$pro->cont_pre,'costo'=>$pro->costo_total,'codigo'=>$pro->procod,'unidad'=>$pro->umecod,'pro_rel'=>$pro->pro_rel,"id_almacen"=>$id_almacen];
      
      }
      
      if($pro->precio3 > 0){
         $results[] = ['id'=>$pro->IdProducto,'producto'=>$pro->pronom.' '.$pro->mar_nom,'text'=>'COD:'.$pro->procod.' | '.$pro->pronom.' '.$pro->mar_nom.' | Precio Especial: '.$pro->precio3 .' | STOCK: '.$pro->stock,'textcompra'=>'COD:'.$pro->procod.' | '.$pro->pronom.' '.$pro->mar_nom.' | Costo: '.$pro->costo_total .' | STOCK: '.$pro->stock,'propun'=>$pro->precio3,'contar'=>$pro->cont_pre,'costo'=>$pro->costo_total,'codigo'=>$pro->procod,'unidad'=>$pro->umecod,'pro_rel'=>$pro->pro_rel,"id_almacen"=>$id_almacen];
      
      }


    }
    

    

      if(Auth::User()->hasRole('vendedor') || Auth::User()->hasRole('caja') || Auth::User()->hasRole('tecnico')){
        
        if($bus_suc->ven_sin_sto =='0'){  

          if($pro->stock > 0){

             $results[] = ['id'=>$pro->IdProducto,'producto'=>$pro->pronom.' '.$pro->mar_nom,'text'=>'COD:'.$pro->procod.' | '.$pro->pronom.' '.$pro->mar_nom.' | Precio: '.$pro->precio .' | STOCK: '.$pro->stock,'textcompra'=>'COD:'.$pro->procod.' | '.$pro->pronom.' '.$pro->mar_nom.' | Costo: '.$pro->costo_total .' | STOCK: '.$pro->stock,'propun'=>$pro->precio,'contar'=>$pro->cont_pre,'costo'=>$pro->costo_total,'codigo'=>$pro->procod,'unidad'=>$pro->umecod,'pro_rel'=>$pro->pro_rel,"id_almacen"=>$id_almacen];
          
          if($pro->precio2 > 0){
             $results[] = ['id'=>$pro->IdProducto,'producto'=>$pro->pronom.' '.$pro->mar_nom,'text'=>'COD:'.$pro->procod.' | '.$pro->pronom.' '.$pro->mar_nom.' | Precio Mayor: '.$pro->precio2 .' | STOCK: '.$pro->stock,'textcompra'=>'COD:'.$pro->procod.' | '.$pro->pronom.' '.$pro->mar_nom.' | Costo: '.$pro->costo_total .' | STOCK: '.$pro->stock,'propun'=>$pro->precio2,'contar'=>$pro->cont_pre,'costo'=>$pro->costo_total,'codigo'=>$pro->procod,'unidad'=>$pro->umecod,'pro_rel'=>$pro->pro_rel,"id_almacen"=>$id_almacen];
          
          }

          }
          
         
          
         }else{
             $results[] = ['id'=>$pro->IdProducto,'producto'=>$pro->pronom.' '.$pro->mar_nom,'text'=>'COD:'.$pro->procod.' | '.$pro->pronom.' '.$pro->mar_nom.' | Precio: '.$pro->precio .' | STOCK: '.$pro->stock,'textcompra'=>'COD:'.$pro->procod.' | '.$pro->pronom.' '.$pro->mar_nom.' | Costo: '.$pro->costo_total .' | STOCK: '.$pro->stock,'propun'=>$pro->precio,'contar'=>$pro->cont_pre,'costo'=>$pro->costo_total,'codigo'=>$pro->procod,'unidad'=>$pro->umecod,'pro_rel'=>$pro->pro_rel,"id_almacen"=>$id_almacen];
          
          if($pro->precio2 > 0){
             $results[] = ['id'=>$pro->IdProducto,'producto'=>$pro->pronom.' '.$pro->mar_nom,'text'=>'COD:'.$pro->procod.' | '.$pro->pronom.' '.$pro->mar_nom.' | Precio Mayor: '.$pro->precio2 .' | STOCK: '.$pro->stock,'textcompra'=>'COD:'.$pro->procod.' | '.$pro->pronom.' '.$pro->mar_nom.' | Costo: '.$pro->costo_total .' | STOCK: '.$pro->stock,'propun'=>$pro->precio2,'contar'=>$pro->cont_pre,'costo'=>$pro->costo_total,'codigo'=>$pro->procod,'unidad'=>$pro->umecod,'pro_rel'=>$pro->pro_rel,"id_almacen"=>$id_almacen];
          
          }
         }


        } 


   

    
       
       
    }
     
       return response()->json($results);



    }



       public function consultarrepuestos(Request $request){


      $search = $request->search; 

      $id_almacen = $request->almacen;
       
      $rucemp = trim(Auth::user()->IdEmpresa);

       $bus_alm = DB::tABLE('almacenes')
        ->where('id_almacen',$id_almacen)

        ->first();


       $bus_suc = DB::tABLE('empresa_negocios')
       ->where('id_empresa_negocio',$bus_alm->id_empresa_negocio)
       ->first();

  
            $productos = DB::tABLE('productos')
            ->select('costo_total','pro_rel','costo','mar_nom','procod','pronom','propun','productos.IdProducto','umecod','promocion','color','imagenproducto','precio2','precio3',DB::raw("(SELECT stock FROM producto_stock as pro WHERE productos.IdProducto = pro.IdProducto AND id_almacen='".$bus_alm->id_almacen."' AND pro.id_empresa_negocio='".$bus_alm->id_empresa_negocio."') as stock"),'precio',DB::raw("(SELECT count(*) FROM productos as pro WHERE pro.pro_rel =  productos.IdProducto) as cont_pre"))
            ->join('producto_empresa','producto_empresa.IdProducto','productos.IdProducto')
            
          ->leftjoin('marcas','marcas.mar_id','productos.marca')
        ->where(function ($query) use($search){
                $query->where('pronom','like','%'.$search.'%')
          ->orwhere('procod',$search);
          })
            ->where('tipo','1')
            ->where('promocion','=','7')
           // ->where('producto_empresa.id_empresa_negocio',Auth::user()->id_empresa_negocio)
           // ->where('id_almacen',$almacen->id_almacen)
            ->groupby('productos.IdProducto')
            ->orderby('productos.pronom')
            ->orderby('productos.umecod')
            ->get();



      $contar = count($productos);

      $results = array();
    foreach($productos as $pro){


        $codnom = $pro->procod;
     
    if(Auth::User()->hasRole('admin') || Auth::user()->hasRole('superadmin') || Auth::user()->hasRole('supervisor') || Auth::user()->hasRole('recepcion')){


       $results[] = ['id'=>$pro->IdProducto,'producto'=>$pro->pronom.' '.$pro->mar_nom,'text'=>'COD:'.$pro->procod.' | '.$pro->pronom.' '.$pro->mar_nom.' | Precio: '.$pro->precio .' | STOCK: '.$pro->stock,'textcompra'=>'COD:'.$pro->procod.' | '.$pro->pronom.' '.$pro->mar_nom.' | Costo: '.$pro->costo_total .' | STOCK: '.$pro->stock,'propun'=>$pro->precio,'contar'=>$pro->cont_pre,'costo'=>$pro->costo_total,'codigo'=>$pro->procod,'unidad'=>$pro->umecod,'pro_rel'=>$pro->pro_rel,"id_almacen"=>$id_almacen];
    
      if($pro->precio2 > 0){
         $results[] = ['id'=>$pro->IdProducto,'producto'=>$pro->pronom.' '.$pro->mar_nom,'text'=>'COD:'.$pro->procod.' | '.$pro->pronom.' '.$pro->mar_nom.' | Precio Mayor: '.$pro->precio2 .' | STOCK: '.$pro->stock,'textcompra'=>'COD:'.$pro->procod.' | '.$pro->pronom.' '.$pro->mar_nom.' | Costo: '.$pro->costo_total .' | STOCK: '.$pro->stock,'propun'=>$pro->precio2,'contar'=>$pro->cont_pre,'costo'=>$pro->costo_total,'codigo'=>$pro->procod,'unidad'=>$pro->umecod,'pro_rel'=>$pro->pro_rel,"id_almacen"=>$id_almacen];
      
      }
      
      if($pro->precio3 > 0){
         $results[] = ['id'=>$pro->IdProducto,'producto'=>$pro->pronom.' '.$pro->mar_nom,'text'=>'COD:'.$pro->procod.' | '.$pro->pronom.' '.$pro->mar_nom.' | Precio Especial: '.$pro->precio3 .' | STOCK: '.$pro->stock,'textcompra'=>'COD:'.$pro->procod.' | '.$pro->pronom.' '.$pro->mar_nom.' | Costo: '.$pro->costo_total .' | STOCK: '.$pro->stock,'propun'=>$pro->precio3,'contar'=>$pro->cont_pre,'costo'=>$pro->costo_total,'codigo'=>$pro->procod,'unidad'=>$pro->umecod,'pro_rel'=>$pro->pro_rel,"id_almacen"=>$id_almacen];
      
      }


    }
    

    

      if(Auth::User()->hasRole('vendedor') || Auth::User()->hasRole('caja') || Auth::User()->hasRole('tecnico')){
        
        if($bus_suc->ven_sin_sto =='0'){  

          if($pro->stock > 0){

             $results[] = ['id'=>$pro->IdProducto,'producto'=>$pro->pronom.' '.$pro->mar_nom,'text'=>'COD:'.$pro->procod.' | '.$pro->pronom.' '.$pro->mar_nom.' | Precio: '.$pro->precio .' | STOCK: '.$pro->stock,'textcompra'=>'COD:'.$pro->procod.' | '.$pro->pronom.' '.$pro->mar_nom.' | Costo: '.$pro->costo_total .' | STOCK: '.$pro->stock,'propun'=>$pro->precio,'contar'=>$pro->cont_pre,'costo'=>$pro->costo_total,'codigo'=>$pro->procod,'unidad'=>$pro->umecod,'pro_rel'=>$pro->pro_rel,"id_almacen"=>$id_almacen];
          
          if($pro->precio2 > 0){
             $results[] = ['id'=>$pro->IdProducto,'producto'=>$pro->pronom.' '.$pro->mar_nom,'text'=>'COD:'.$pro->procod.' | '.$pro->pronom.' '.$pro->mar_nom.' | Precio Mayor: '.$pro->precio2 .' | STOCK: '.$pro->stock,'textcompra'=>'COD:'.$pro->procod.' | '.$pro->pronom.' '.$pro->mar_nom.' | Costo: '.$pro->costo_total .' | STOCK: '.$pro->stock,'propun'=>$pro->precio2,'contar'=>$pro->cont_pre,'costo'=>$pro->costo_total,'codigo'=>$pro->procod,'unidad'=>$pro->umecod,'pro_rel'=>$pro->pro_rel,"id_almacen"=>$id_almacen];
          
          }

          }
          
         
          
         }else{
             $results[] = ['id'=>$pro->IdProducto,'producto'=>$pro->pronom.' '.$pro->mar_nom,'text'=>'COD:'.$pro->procod.' | '.$pro->pronom.' '.$pro->mar_nom.' | Precio: '.$pro->precio .' | STOCK: '.$pro->stock,'textcompra'=>'COD:'.$pro->procod.' | '.$pro->pronom.' '.$pro->mar_nom.' | Costo: '.$pro->costo_total .' | STOCK: '.$pro->stock,'propun'=>$pro->precio,'contar'=>$pro->cont_pre,'costo'=>$pro->costo_total,'codigo'=>$pro->procod,'unidad'=>$pro->umecod,'pro_rel'=>$pro->pro_rel,"id_almacen"=>$id_almacen];
          
          if($pro->precio2 > 0){
             $results[] = ['id'=>$pro->IdProducto,'producto'=>$pro->pronom.' '.$pro->mar_nom,'text'=>'COD:'.$pro->procod.' | '.$pro->pronom.' '.$pro->mar_nom.' | Precio Mayor: '.$pro->precio2 .' | STOCK: '.$pro->stock,'textcompra'=>'COD:'.$pro->procod.' | '.$pro->pronom.' '.$pro->mar_nom.' | Costo: '.$pro->costo_total .' | STOCK: '.$pro->stock,'propun'=>$pro->precio2,'contar'=>$pro->cont_pre,'costo'=>$pro->costo_total,'codigo'=>$pro->procod,'unidad'=>$pro->umecod,'pro_rel'=>$pro->pro_rel,"id_almacen"=>$id_almacen];
          
          }
         }


        } 


   

    
       
       
    }
     
       return response()->json($results);


        

    }



        public function consultarproductosbarra(Request $request){


      $search = $request->value; 

         $bus_suc = DB::tABLE('empresa_negocios')
       ->where('id_empresa_negocio',Auth::user()->id_empresa_negocio)
       ->first();

        $bus_alm = DB::tABLE('almacenes')
         ->where('id_empresa_negocio',Auth::user()->id_empresa_negocio)
        ->where('predeterminado','1')

        ->first();


      $id_almacen =  $bus_alm->id_almacen;
       
      $rucemp = trim(Auth::user()->IdEmpresa);

  
            $productos = DB::tABLE('productos')
            ->select('costo_total','pro_rel','costo','mar_nom','procod','pronom','propun','productos.IdProducto','umecod','promocion','color','imagenproducto','precio2','precio3',DB::raw("(SELECT stock FROM producto_stock as pro WHERE productos.IdProducto = pro.IdProducto AND id_almacen='".$bus_alm->id_almacen."' AND pro.id_empresa_negocio='".$bus_alm->id_empresa_negocio."') as stock"),'precio',DB::raw("(SELECT count(*) FROM productos as pro WHERE pro.pro_rel =  productos.IdProducto) as cont_pre"),'icbper','mon_icbper')
            ->join('producto_empresa','producto_empresa.IdProducto','productos.IdProducto')
            
          ->leftjoin('marcas','marcas.mar_id','productos.marca')
        ->where(function ($query) use($search){
                $query->where('codigo_barra',$search);
          })
            //->where('tipo','1')
            ->where('promocion','!=','2')
           // ->where('producto_empresa.id_empresa_negocio',Auth::user()->id_empresa_negocio)
           // ->where('id_almacen',$almacen->id_almacen)
            ->groupby('productos.IdProducto')
            ->orderby('productos.pronom')
            ->orderby('productos.umecod')
            ->get();


      $contar = count($productos);

      $results = array();


    foreach($productos as $pro){


        $codnom = $pro->procod;
     
    if(Auth::User()->hasRole('admin') || Auth::user()->hasRole('superadmin')){


       $results[] = ['id'=>$pro->IdProducto,'producto'=>$pro->pronom.' '.$pro->mar_nom,'text'=>'COD:'.$pro->procod.' | '.$pro->pronom.' '.$pro->mar_nom.' | Precio: '.$pro->precio .' | STOCK: '.$pro->stock,'textcompra'=>'COD:'.$pro->procod.' | '.$pro->pronom.' '.$pro->mar_nom.' | Costo: '.$pro->costo_total .' | STOCK: '.$pro->stock,'propun'=>$pro->precio,'contar'=>$pro->cont_pre,'costo'=>$pro->costo_total,'codigo'=>$pro->procod,'unidad'=>$pro->umecod,'pro_rel'=>$pro->pro_rel,"id_almacen"=>$id_almacen,"icbper"=>$pro->icbper,"mon_icbper"=>$pro->mon_icbper];
    
      if($pro->precio2 > 0){
         $results[] = ['id'=>$pro->IdProducto,'producto'=>$pro->pronom.' '.$pro->mar_nom,'text'=>'COD:'.$pro->procod.' | '.$pro->pronom.' '.$pro->mar_nom.' | Precio Mayor: '.$pro->precio2 .' | STOCK: '.$pro->stock,'textcompra'=>'COD:'.$pro->procod.' | '.$pro->pronom.' '.$pro->mar_nom.' | Costo: '.$pro->costo_total .' | STOCK: '.$pro->stock,'propun'=>$pro->precio2,'contar'=>$pro->cont_pre,'costo'=>$pro->costo_total,'codigo'=>$pro->procod,'unidad'=>$pro->umecod,'pro_rel'=>$pro->pro_rel,"id_almacen"=>$id_almacen,"icbper"=>$pro->icbper,"mon_icbper"=>$pro->mon_icbper];
      
      }
      
      if($pro->precio3 > 0){
         $results[] = ['id'=>$pro->IdProducto,'producto'=>$pro->pronom.' '.$pro->mar_nom,'text'=>'COD:'.$pro->procod.' | '.$pro->pronom.' '.$pro->mar_nom.' | Precio Especial: '.$pro->precio3 .' | STOCK: '.$pro->stock,'textcompra'=>'COD:'.$pro->procod.' | '.$pro->pronom.' '.$pro->mar_nom.' | Costo: '.$pro->costo_total .' | STOCK: '.$pro->stock,'propun'=>$pro->precio3,'contar'=>$pro->cont_pre,'costo'=>$pro->costo_total,'codigo'=>$pro->procod,'unidad'=>$pro->umecod,'pro_rel'=>$pro->pro_rel,"id_almacen"=>$id_almacen,"icbper"=>$pro->icbper,"mon_icbper"=>$pro->mon_icbper];
      
      }


    }
    



      if(Auth::User()->hasRole('vendedor') || Auth::User()->hasRole('caja')){
        
        if($bus_suc->ven_sin_sto =='0'){  

          if($pro->stock > 0){

             $results[] = ['id'=>$pro->IdProducto,'producto'=>$pro->pronom.' '.$pro->mar_nom,'text'=>'COD:'.$pro->procod.' | '.$pro->pronom.' '.$pro->mar_nom.' | Precio: '.$pro->precio .' | STOCK: '.$pro->stock,'textcompra'=>'COD:'.$pro->procod.' | '.$pro->pronom.' '.$pro->mar_nom.' | Costo: '.$pro->costo_total .' | STOCK: '.$pro->stock,'propun'=>$pro->precio,'contar'=>$pro->cont_pre,'costo'=>$pro->costo_total,'codigo'=>$pro->procod,'unidad'=>$pro->umecod,'pro_rel'=>$pro->pro_rel,"id_almacen"=>$id_almacen,"icbper"=>$pro->icbper,"mon_icbper"=>$pro->mon_icbper];
          
          if($pro->precio2 > 0){
             $results[] = ['id'=>$pro->IdProducto,'producto'=>$pro->pronom.' '.$pro->mar_nom,'text'=>'COD:'.$pro->procod.' | '.$pro->pronom.' '.$pro->mar_nom.' | Precio Mayor: '.$pro->precio2 .' | STOCK: '.$pro->stock,'textcompra'=>'COD:'.$pro->procod.' | '.$pro->pronom.' '.$pro->mar_nom.' | Costo: '.$pro->costo_total .' | STOCK: '.$pro->stock,'propun'=>$pro->precio2,'contar'=>$pro->cont_pre,'costo'=>$pro->costo_total,'codigo'=>$pro->procod,'unidad'=>$pro->umecod,'pro_rel'=>$pro->pro_rel,"id_almacen"=>$id_almacen,"icbper"=>$pro->icbper,"mon_icbper"=>$pro->mon_icbper];
          
          }

          }
          
         
          
         }else{

            
             $results[] = ['id'=>$pro->IdProducto,'producto'=>$pro->pronom.' '.$pro->mar_nom,'text'=>'COD:'.$pro->procod.' | '.$pro->pronom.' '.$pro->mar_nom.' | Precio: '.$pro->precio .' | STOCK: '.$pro->stock,'textcompra'=>'COD:'.$pro->procod.' | '.$pro->pronom.' '.$pro->mar_nom.' | Costo: '.$pro->costo_total .' | STOCK: '.$pro->stock,'propun'=>$pro->precio,'contar'=>$pro->cont_pre,'costo'=>$pro->costo_total,'codigo'=>$pro->procod,'unidad'=>$pro->umecod,'pro_rel'=>$pro->pro_rel,"id_almacen"=>$id_almacen,"icbper"=>$pro->icbper,"mon_icbper"=>$pro->mon_icbper];
          
          if($pro->precio2 > 0){
             $results[] = ['id'=>$pro->IdProducto,'producto'=>$pro->pronom.' '.$pro->mar_nom,'text'=>'COD:'.$pro->procod.' | '.$pro->pronom.' '.$pro->mar_nom.' | Precio Mayor: '.$pro->precio2 .' | STOCK: '.$pro->stock,'textcompra'=>'COD:'.$pro->procod.' | '.$pro->pronom.' '.$pro->mar_nom.' | Costo: '.$pro->costo_total .' | STOCK: '.$pro->stock,'propun'=>$pro->precio2,'contar'=>$pro->cont_pre,'costo'=>$pro->costo_total,'codigo'=>$pro->procod,'unidad'=>$pro->umecod,'pro_rel'=>$pro->pro_rel,"id_almacen"=>$id_almacen,"icbper"=>$pro->icbper,"mon_icbper"=>$pro->mon_icbper];
          
          }
         }


        } 

       
       
    }
     
       return response()->json($results);



    }




//---------------------------------------fin consultar productos compra-----------------------------//


//------------------------------------------consultar productos inventarios --------------------------//


    public function presentacionesproductoinventario(Request $request, $id, $sucursal,$almacen){
         
        $rucemp = trim(Auth::user()->IdEmpresa);

        $productos = DB::tABLE('productos')
         ->select('umecod','productos.procod','productos.pronom','productos.propun','productos.IdProducto','productos.umecod','productos.promocion','productos.color','productos.imagenproducto','precio','precio2',DB::raw("(SELECT stock FROM producto_stock as pro WHERE pro.IdProducto ='".$id."' and pro.id_almacen='".$almacen."' and pro.id_empresa_negocio='".$sucursal."') as stock"),'costo')
        ->join('producto_empresa','producto_empresa.IdProducto','productos.IdProducto')
        ->where(function ($query) use ($id) {
             
                    $query->where('productos.pro_rel',$id);


                })
        ->where('producto_empresa.id_empresa_negocio',$sucursal)
          ->where('tipo','2')
        ->groupby('productos.IdProducto')
        ->orderby('productos.pronom')
        ->orderby('productos.umecod')
        ->get();

   

        $unidades = DB::tABLE('unidad_medida')->where('umeest','=','Activo')
        ->orderBy('umecod','asc')->get();

        $vista = view('empresas.compras.divproductos',compact('productos','unidades'))->render();

        if($request->ajax()){
         return response()->json(['vista'=>$vista]);

        }
    }


     public function consultarmenuinventario(Request $request, $cat_id,$sucursal,$almacen){
         
        $rucemp = trim(Auth::user()->IdEmpresa);

       

        $productos = DB::tABLE('productos')
        ->select('procod','pronom','propun','productos.IdProducto','umecod','promocion','color','imagenproducto','precio2',DB::raw("(SELECT stock FROM producto_stock as pro WHERE productos.IdProducto = pro.IdProducto AND tipo='1' AND id_almacen='".$almacen."' AND id_empresa_negocio='".$sucursal."') as stock"),'precio',DB::raw("(SELECT count(*) FROM productos as pro WHERE productos.IdProducto = pro.pro_rel AND tipo='2') as cont_pre"),'costo')
        ->join('producto_empresa','producto_empresa.IdProducto','productos.IdProducto')
        ->join('producto_stock','producto_stock.IdProducto','producto_empresa.IdProducto')
        ->where('cat_id',$cat_id)
        ->where('tipo','1')
        ->where('producto_empresa.id_empresa_negocio',$sucursal)
        ->where('id_almacen',$almacen)
        ->groupby('productos.IdProducto')
        ->orderby('productos.pronom')
        ->orderby('productos.umecod')
        ->get();


        $unidades = DB::tABLE('unidad_medida')->where('umeest','=','Activo')
        ->orderBy('umecod','asc')->get();

        $vista = view('empresas.productos.menuinventario',compact('productos','unidades'))->render();

        if($request->ajax()){
         return response()->json(['vista'=>$vista]);

        }
    }

    public function busquedaproductoinventario(Request $request, $valor,$sucursal,$almacen){

        $rucemp = trim(Auth::user()->IdEmpresa);

         $productos = DB::tABLE('productos')
        ->select('procod','pronom','propun','productos.IdProducto','umecod','promocion','color','imagenproducto','precio2',DB::raw("(SELECT stock FROM producto_stock as pro WHERE productos.IdProducto = pro.IdProducto AND tipo='1' AND id_almacen='".$almacen."' AND id_empresa_negocio='".$sucursal."') as stock"),'precio',DB::raw("(SELECT count(*) FROM productos as pro WHERE productos.IdProducto = pro.pro_rel AND tipo='2') as cont_pre"),'costo')
        ->leftjoin('producto_codigo','producto_codigo.IdProducto','productos.IdProducto')
        ->leftjoin('producto_stock','producto_stock.IdProducto','productos.IdProducto')
        ->leftjoin('producto_empresa','producto_empresa.IdProducto','productos.IdProducto')
        ->where(function ($query) use($valor){
                $query->where('cod_bar','=',$valor)
                      ->orWhere('productos.pronom', 'like','%'.$valor.'%')
                      ->orWhere('productos.procod',$valor);
        })
        ->where('producto_empresa.id_empresa_negocio',$sucursal)
        ->where('id_almacen',$almacen)
        ->groupby('productos.IdProducto')
        ->orderby('productos.pronom')
        ->orderby('productos.umecod')
        ->get();

        $unidades = DB::tABLE('unidad_medida')->where('umeest','=','Activo')
        ->orderBy('umecod','asc')->get();

        $vista = view('empresas.productos.menuinventario',compact('productos','unidades'))->render();

        if($request->ajax()){
         return response()->json(['vista'=>$vista]);

        }
    }

     public function consultarproductosinventario(Request $request,$sucursal,$almacen){

      $search = $request->get('value');       
      $rucemp = trim(Auth::user()->IdEmpresa);


        $productos = DB::tABLE('productos')
        ->select('procod','pronom','propun','productos.IdProducto','umecod','promocion','color','imagenproducto','precio2',DB::raw("(SELECT stock FROM producto_stock as pro WHERE productos.IdProducto = pro.IdProducto AND tipo='1' AND id_almacen='".$almacen."' AND id_empresa_negocio='".$sucursal."') as stock"),'precio',DB::raw("(SELECT count(*) FROM productos as pro WHERE productos.IdProducto = pro.pro_rel AND tipo='2') as cont_pre"),'costo')
        ->join('producto_codigo','producto_codigo.IdProducto','productos.IdProducto')
        ->join('producto_empresa','producto_empresa.IdProducto','productos.IdProducto')
        ->join('producto_stock','producto_stock.IdProducto','producto_empresa.IdProducto')
        ->where(function ($query) use($search){
            $query->where('cod_bar','=',$search);
         })
        ->where('tipo','1')
        ->where('producto_empresa.id_empresa_negocio',$sucursal)
        ->where('id_almacen',$almacen)
        ->groupby('productos.IdProducto')
        ->orderby('productos.pronom')
        ->orderby('productos.umecod')
        ->get();

 
      $contar = count($productos);

      $results = array();
      foreach($productos as $pro){
        $codnom = $pro->procod;
        $results[] = ['success'=>'true','value'=>$codnom,'pronom'=>$pro->pronom,'propun'=>$pro->propun,'umecod'=>$pro->umecod,'stock'=>$pro->stock,'imagenproducto'=>$pro->imagenproducto,'proid'=>$pro->IdProducto,'contar'=>$contar,'costo'=>$pro->costo];
      }
     
       return response()->json($results);

    }



//---------------------------------------fin consultar inventarios-----------------------------//
    
    public function consultarmenualm(Request $request, $cat_id, $almacen,$sucursal){
         
       $rucemp = trim(Auth::user()->IdEmpresa);

      

        $productos = DB::tABLE('productos')
        ->select('procod','pronom','propun','productos.IdProducto','umecod','promocion','color','imagenproducto','precio2',DB::raw("(SELECT stock FROM producto_stock as pro WHERE productos.IdProducto = pro.IdProducto AND tipo='1' AND id_almacen='".$almacen."' AND id_empresa_negocio='".$sucursal."') as stock"),'precio',DB::raw("(SELECT count(*) FROM productos as pro WHERE productos.IdProducto = pro.pro_rel AND tipo='2') as cont_pre"),'costo')
        ->join('producto_empresa','producto_empresa.IdProducto','productos.IdProducto')
        ->join('producto_stock','producto_stock.IdProducto','producto_empresa.IdProducto')
        ->where('cat_id',$cat_id)
        ->where('tipo','1')
        ->where('producto_empresa.id_empresa_negocio',$sucursal)
        ->where('id_almacen',$almacen)
        ->groupby('productos.IdProducto')
        ->orderby('productos.pronom')
        ->orderby('productos.umecod')
        ->get();



        $unidades = DB::tABLE('unidad_medida')->where('umeest','=','Activo')
        ->orderBy('umecod','asc')->get();

        $vista = view('empresas.productos.menualm',compact('productos','unidades'))->render();

        if($request->ajax()){
         return response()->json(['vista'=>$vista]);

        }
    }


     public function consultarproductosalm(Request $request,$sucursal,$almacen){

      $search = $request->get('value');  
    

      $rucemp = trim(Auth::user()->IdEmpresa);

       $productos = DB::tABLE('productos')
        ->select('procod','pronom','propun','productos.IdProducto','umecod','promocion','color','imagenproducto','precio2',DB::raw("(SELECT stock FROM producto_stock as pro WHERE productos.IdProducto = pro.IdProducto AND tipo='1' AND id_almacen='".$almacen."' AND id_empresa_negocio='".$sucursal."') as stock"),'precio',DB::raw("(SELECT count(*) FROM productos as pro WHERE productos.IdProducto = pro.pro_rel AND tipo='2') as cont_pre"),'costo')
        ->join('producto_codigo','producto_codigo.IdProducto','productos.IdProducto')
        ->join('producto_empresa','producto_empresa.IdProducto','productos.IdProducto')
        ->join('producto_stock','producto_stock.IdProducto','producto_empresa.IdProducto')
        ->where(function ($query) use($search){
            $query->where('cod_bar','=',$search);
         })
        ->where('tipo','1')
        ->where('producto_empresa.id_empresa_negocio',$sucursal)
        ->where('id_almacen',$almacen)
        ->groupby('productos.IdProducto')
        ->orderby('productos.pronom')
        ->orderby('productos.umecod')
        ->get();


      $contar= count($productos);

      $results = array();
      foreach($productos as $pro){
        $codnom = $pro->procod;
        $results[] = ['success'=>'true','value'=>$codnom,'pronom'=>$pro->pronom,'propun'=>$pro->propun,'umecod'=>$pro->umecod,'stock'=>$pro->stock,'imagenproducto'=>$pro->imagenproducto,'proid'=>$pro->IdProducto,'contar'=>$contar,'costo'=>$pro->costo];
      }
     
       return response()->json($results);

    }

    

   

     public function busquedaproductoalm(Request $request, $valor, $almacen,$sucursal){

       $rucemp = trim(Auth::user()->IdEmpresa);

          $productos = DB::tABLE('productos')
        ->select('procod','pronom','propun','productos.IdProducto','umecod','promocion','color','imagenproducto','precio2',DB::raw("(SELECT stock FROM producto_stock as pro WHERE productos.IdProducto = pro.IdProducto AND tipo='1' AND id_almacen='".$almacen."' AND id_empresa_negocio='".$sucursal."') as stock"),'precio',DB::raw("(SELECT count(*) FROM productos as pro WHERE productos.IdProducto = pro.pro_rel AND tipo='2') as cont_pre"),'costo')
        ->leftjoin('producto_codigo','producto_codigo.IdProducto','productos.IdProducto')
        ->leftjoin('producto_stock','producto_stock.IdProducto','productos.IdProducto')
        ->leftjoin('producto_empresa','producto_empresa.IdProducto','productos.IdProducto')
        ->where(function ($query) use($valor){
                $query->where('cod_bar','=',$valor)
                      ->orWhere('productos.pronom', 'like','%'.$valor.'%')
                      ->orWhere('productos.procod',$valor);
        })
        ->where('producto_empresa.id_empresa_negocio',$sucursal)
        ->where('id_almacen',$almacen)
        ->groupby('productos.IdProducto')
        ->orderby('productos.pronom')
        ->orderby('productos.umecod')
        ->get();



        $unidades = DB::tABLE('unidad_medida')->where('umeest','=','Activo')
        ->orderBy('umecod','asc')->get();

        $vista = view('empresas.productos.menualm',compact('productos','unidades'))->render();

        if($request->ajax()){
         return response()->json(['vista'=>$vista]);

        }
    }


     public function consultarmenucomanda(Request $request, $cat_id){
        $rucemp = trim(Auth::user()->IdEmpresa);

        $sucursal = DB::tABLE('empresa_negocios')->where('id_empresa_negocio',Auth::user()->id_empresa_negocio)->first();

        $almacen = DB::tABLE('almacenes')
        ->where('predeterminado','1')
        ->where('id_empresa_negocio',Auth::user()->id_empresa_negocio)
        ->first();



  
            $productos = DB::tABLE('productos')
            ->select('pro_rel','costo','mar_nom','procod','pronom','propun','productos.IdProducto','umecod','promocion','color','imagenproducto','precio2','precio3',DB::raw("(SELECT stock FROM producto_stock as pro WHERE productos.IdProducto = pro.IdProducto AND id_almacen='".$almacen->id_almacen."' AND pro.id_empresa_negocio='".$almacen->id_empresa_negocio."') as stock"),'precio',DB::raw("(SELECT count(*) FROM productos as pro WHERE pro.pro_rel =  productos.IdProducto) as cont_pre"))
            ->join('producto_empresa','producto_empresa.IdProducto','productos.IdProducto')
            
          ->leftjoin('marcas','marcas.mar_id','productos.marca')
        ->where(function ($query) use($cat_id){
                $query->where('cat_id',$cat_id);
          })
            ->where('tipo','1')
            ->where('promocion','!=','2')
           // ->where('producto_empresa.id_empresa_negocio',Auth::user()->id_empresa_negocio)
           // ->where('id_almacen',$almacen->id_almacen)
            ->groupby('productos.IdProducto')
            ->orderby('productos.pronom')
            ->orderby('productos.umecod')
            ->get();



      $contar = count($productos);


        $unidades = DB::tABLE('unidad_medida')->where('umeest','=','Activo')
        ->orderBy('umecod','asc')->get();

        $vista = view('empresas.productos.menucomanda',compact('productos','unidades','sucursal'))->render();

        if($request->ajax()){
         return response()->json(['vista'=>$vista]);

        }
    }

    
     public function consultarmenucobrar(Request $request, $cat_id){
        $rucemp = trim(Auth::user()->IdEmpresa);
        $productos = DB::tABLE('productos')
        ->select('procod','pronom','propun','IdProducto','umecod','promocion','color','imagenproducto')
        ->where('cat_id',$cat_id)->where('id_empresa_negocio',Auth::user()->id_empresa_negocio)
        ->where(function ($query){
                $query->where('promocion','1')
                ->orWhere('promocion','0')
                      ->orWhere('promocion','2');
        })
        ->get();

        $unidades = DB::tABLE('unidad_medida')->where('umeest','=','Activo')
        ->orderBy('umecod','asc')->get();

        $vista = view('empresas.productos.menucobrar',compact('productos','unidades'))->render();

        if($request->ajax()){
         return response()->json(['vista'=>$vista]);

        }
    }


   
      public function stockproductos(Request $request)
{   
    $tipo = $request->get('promocion');
    $categoria = $request->get('cmbCatId');
    $sucursal = $request->get('sucursal');
    $negocios = DB::table('empresa_negocios')->get();
    $almacen = $request->get('almacen');
    $datosalm = "";

    $rucemp = trim(Auth::user()->IdEmpresa);
    $buspro = trim($request->get('buspro'));
    
    if(empty($sucursal)){

        $productos = DB::table('productos as p')
            ->select('producto_stock.lote','producto_stock.vencimiento','producto_stock.stock_inicial','producto_stock.stock','p.procod','p.pronom','m.monnom','u.umenom','p.propun','p.proest','producto_stock.IdProducto','p.costo','p.propun','mar.mar_nom as marca','modelo')
            ->leftjoin('moneda as m','p.moncod','=','m.moncod')
            ->leftjoin('marcas as mar','mar.mar_id','=','p.marca')
            ->leftjoin('unidad_medida as u','p.umecod','=','u.umecod')
            ->leftjoin('producto_empresa','producto_empresa.IdProducto','p.IdProducto')
            ->leftjoin('producto_stock','producto_stock.IdProducto','p.IdProducto')
            ->where('producto_stock.id_empresa_negocio', $negocios->first()->id_empresa_negocio)
            ->where('producto_stock.id_almacen', '0') // <-- CORREGIDO AQUÍ
            ->where('p.tipo', '1')                   // <-- ESPECIFICADO (o producto_stock.tipo si pertenece a stock)
            ->groupby('p.IdProducto')
            ->orderby('stock', 'asc')
            ->paginate(100);

    } else {

        $productos = DB::table('productos as p')
            ->select('producto_stock.lote','producto_stock.vencimiento','producto_stock.stock_inicial','producto_stock.stock','p.procod','p.pronom','m.monnom','u.umenom','p.propun','p.proest','producto_stock.IdProducto','mar.mar_nom as marca','p.costo','p.propun','modelo')
            ->leftjoin('moneda as m','p.moncod','=','m.moncod')
            ->leftjoin('marcas as mar','mar.mar_id','=','p.marca')
            ->leftjoin('unidad_medida as u','p.umecod','=','u.umecod')
            ->leftjoin('producto_empresa','producto_empresa.IdProducto','p.IdProducto')
            ->leftjoin('producto_stock','producto_stock.IdProducto','p.IdProducto')
            ->where(function ($query) use($categoria) {
                if($categoria != 'Todos'){
                    $query->where('p.cat_id', $categoria);
                }
            })
            ->where(function ($query1) use($buspro) {
                if(!empty($buspro)){
                    $query1->where('p.pronom', 'like', '%'.$buspro.'%')
                           ->orWhere('p.procod', $buspro);
                }
            })
            ->where('producto_stock.id_empresa_negocio', $sucursal)
            ->where('p.tipo', '1')                      // <-- ESPECIFICADO
            ->where('producto_stock.id_almacen', $almacen) // <-- CORREGIDO AQUÍ
            ->groupby('p.IdProducto')
            ->orderby('stock', 'asc')
            ->paginate(100);

        $datosalm = DB::table('almacenes')->where('id_almacen', $almacen)->first();
    }
        
    $categorias = DB::table('categorias')->get();
    $buspro = "";
    $tipos_productos = DB::table('tipos_productos')->get();
    $datos = DB::table('empresa_negocios')->where('id_empresa_negocio', $sucursal)->first();

    if(empty($sucursal)){
        $almacenes = DB::table('almacenes')->where('id_empresa_negocio', $negocios->first()->id_empresa_negocio)->get();
    } else {
        $almacenes = DB::table('almacenes')->where('id_empresa_negocio', $sucursal)->get();
    }

    return view('empresas.productos.stockproducto', compact('productos','buspro','negocios','categorias','tipos_productos','categoria','tipo','sucursal','datos','almacenes','almacen','datosalm'));
}

    public function consultastockproductos(Request $request)
    {   
   
          $tipo = $request->get('promocion');
          $categoria = $request->get('cmbCatId');

          $buspro = trim($request->get('buspro'));
            
            if(empty($sucursal)){

              $productos = DB::tABLE('productos as p')
                ->select('producto_stock.lote','producto_stock.vencimiento','producto_stock.stock_inicial','producto_stock.stock','p.procod','p.pronom','m.monnom','u.umenom','p.propun','p.proest','producto_stock.IdProducto','p.costo','p.propun','mar.mar_nom as marca','modelo')
                ->leftjoin('moneda as m','p.moncod','=','m.moncod')
         ->leftjoin('marcas as mar','mar.mar_id','=','p.marca')
                ->leftjoin('unidad_medida as u','p.umecod','=','u.umecod')
                ->leftjoin('producto_empresa','producto_empresa.IdProducto','p.IdProducto')
                ->leftjoin('producto_stock','producto_stock.IdProducto','p.IdProducto')
               
                ->where('id_almacen','0')
                ->where('tipo','1')
                ->groupby('p.IdProducto')
                ->orderby('stock','asc')
                ->paginate(100);
         

            }else{

           

                $productos = DB::tABLE('productos as p')
                ->select('producto_stock.lote','producto_stock.vencimiento','producto_stock.stock_inicial','producto_stock.stock','p.procod','p.pronom','m.monnom','u.umenom','p.propun','p.proest','producto_stock.IdProducto','mar.mar_nom as marca','p.costo','p.propun','modelo')
                ->leftjoin('moneda as m','p.moncod','=','m.moncod')
         ->leftjoin('marcas as mar','mar.mar_id','=','p.marca')
                ->leftjoin('unidad_medida as u','p.umecod','=','u.umecod')
                ->leftjoin('producto_empresa','producto_empresa.IdProducto','p.IdProducto')
                ->leftjoin('producto_stock','producto_stock.IdProducto','p.IdProducto')
                ->where(function ($query) use($categoria) {

                  if($categoria!='Todos'){
                    $query->where('cat_id',$categoria);
                  }
                  
                })
                ->where(function ($query1) use($buspro) {

                  if(!empty($buspro)){
                    $query1->where('pronom','like','%'.$buspro.'%')
                            ->orwhere('procod',$buspro);
                  }
                  
                })
                
                ->where('tipo','1')
              
                ->groupby('p.IdProducto')
                ->orderby('stock','asc')
                ->paginate(100);

              
            }
                

            $categorias = DB::tABLE('categorias')->get();

            $buspro="";

            $tipos_productos = DB::tABLE('tipos_productos')->get();

           
          
           

            return view('empresas.productos.consultastockproducto',compact('productos','buspro','categorias','tipos_productos','categoria','tipo'));
      

    }


    public function exportarstockproductos(Request $request)
    {
        // 1. OBTENCIÓN DE FILTROS NECESARIOS
        $tipo = $request->get('promocion');
        $estado = $request->get('estado'); 
        $categoria = $request->get('cmbCatId');
        $sucursal = $request->get('sucursal');
        $almacen = $request->get('almacen');
        $buspro = trim($request->get('buspro'));

        // 2. OBTENCIÓN DE DATOS COMPLEMENTARIOS
        $rucemp = trim(Auth::user()->IdEmpresa);
        $empresa = Empresa::findOrFail($rucemp);

        $categorias = DB::table('categorias')->get();
        $tipos_productos = DB::table('tipos_productos')->get();
        $negocios = DB::table('empresa_negocios')->get();
        $datos = DB::table('empresa_negocios')->where('id_empresa_negocio',$negocios->first()->id_empresa_negocio)->first();

        if(empty($sucursal)){
            $almacenes = DB::table('almacenes')->where('id_empresa_negocio',$negocios->first()->id_empresa_negocio)->get();
        }else{
            $almacenes = DB::table('almacenes')->where('id_empresa_negocio',$sucursal)->get();
        }
        
        // 3. LÓGICA DE OBTENCIÓN DE DATOS DEL STOCK
        $productos = DB::table('productos as p')
        ->select(
            'producto_stock.lote','producto_stock.vencimiento','producto_stock.stock_inicial','producto_stock.stock','producto_stock.stock_equivalencia','p.procod','p.pronom','m.monnom','u.umenom','p.propun','p.proest','producto_stock.IdProducto','mar.mar_nom as marca','p.costo','p.propun',
            'cat.cat_nom as categoria', // <-- Añadido cat_nom
            DB::raw("(SELECT precio FROM producto_empresa  WHERE producto_empresa.IdProducto=p.IdProducto and id_empresa_negocio='".$sucursal."') as precio"),
            DB::raw("(SELECT precio2 FROM producto_empresa  WHERE producto_empresa.IdProducto=p.IdProducto and id_empresa_negocio='".$sucursal."') as precio2"),
            DB::raw("(SELECT precio3 FROM producto_empresa  WHERE producto_empresa.IdProducto=p.IdProducto and id_empresa_negocio='".$sucursal."') as precio3")
         )
        ->leftjoin('moneda as m','p.moncod','=','m.moncod')
        ->leftjoin('marcas as mar','mar.mar_id','=','p.marca')
        ->leftjoin('unidad_medida as u','p.umecod','=','u.umecod')
        ->leftjoin('producto_stock','producto_stock.IdProducto','p.IdProducto')
        ->leftjoin('categorias as cat', 'p.cat_id', '=', 'cat.cat_id') // <-- Añadido leftjoin
        ->where(function ($query1) use($buspro) {
            if(!empty($buspro)){
                $query1->where('p.pronom','like','%'.$buspro.'%')
                       ->orwhere('p.procod',$buspro);
            }
        })
        ->where(function ($query1) use($estado) {
            if($estado=='cs'){
                $query1->where('producto_stock.stock','>','0');
            }elseif($estado=='se'){
                $query1->where('producto_stock.stock','=','0');
            }elseif($estado=='ne'){
                $query1->where('producto_stock.stock','<','0');
            }
        })
        ->where(function ($queryCat) use ($categoria) {
            if ($categoria != 'Todos' && !empty($categoria)) {
                $queryCat->where('p.cat_id', $categoria); // <-- Agregado filtro categoria
            }
        })
        ->where('producto_stock.id_empresa_negocio',$sucursal)
        ->where('p.tipo','1') // <-- Especificado p.tipo
        ->where(function ($query) { 
            $query->where('p.promocion','0') // <-- Especificado p.promocion
                  ->orWhere('p.promocion','4');
        })
        ->where('producto_stock.id_almacen',$almacen) // <-- Especificado producto_stock.id_almacen
        ->groupby('p.IdProducto')
        ->orderby('p.pronom','asc') // <-- Especificado p.pronom
        ->get();


        $datosalm = DB::table('almacenes')->where('id_almacen',$almacen)->first();
        
        // 4. GENERACIÓN DEL PDF
        $nompdffile='STOCK_PRODUCTOS_'.$datos->IdEmpresa.'.pdf';  
        $rutapdf = public_path().'/reporte_cuentas_cobrar/';

        if(file_exists($rutapdf.$nompdffile)){
            unlink($rutapdf.$nompdffile);
        }
        ini_set("pcre.backtrack_limit", "5000000");

        $pdf = \PDF::loadView('formatos_reportes.reporte_pdf_stock_productos',compact('productos','buspro','negocios','categorias','tipos_productos','categoria','tipo','sucursal','datos','almacenes','almacen','datosalm','empresa'));
        
        return $pdf->stream('document.pdf');
    }


    public function actualizarinventario(Request $request){

      $sucursal = $request->get('suc_id');
      $almacen = $request->get('alm_id');
      $fecha = $request->get('inv_fec');
      $productos = $request->get('id');
      $stock = $request->get('cant');
      $costo = $request->get('costo');
      $inv_cab_id = $request->get('inv_cab_id');

      DB::tABLE('inventario_detalle')->where('inv_cab_id',$inv_cab_id)->delete();
  


      foreach ($productos as $key => $pro) {
        
        $buscar_producto = DB::tABLE('productos')
        ->where('IdProducto',$pro)
        ->first();

        if(empty($buscar_producto->pro_rel)){

          $id_pro = $buscar_producto->IdProducto;

        }else{

           $id_pro = $buscar_producto->pro_rel;

        }

        $bus_sto = DB::tABLE('producto_stock')
        ->where('id_empresa_negocio',$sucursal)
        ->where('id_almacen',$almacen)
        ->where('IdProducto',$id_pro)
        ->first();


        if(!empty($bus_sto)){

          DB::tABLE('producto_stock')
          ->where('id_empresa_negocio',$sucursal)
          ->where('id_almacen',$almacen)
          ->where('IdProducto',$id_pro)
          ->update([
            'stock'=>$stock[$key],
            'stock_inicial'=>$stock[$key]

          ]);

        }else{

          DB::tABLE('producto_stock')
          ->insert(['stock'=>$stock[$key],
                    'stock_inicial'=>$stock[$key],
                    'id_empresa_negocio'=>$sucursal,
                    'id_almacen'=>$almacen,
                    'IdProducto'=>$id_pro
          ]);

        }
        
      


        DB::tABLE('inventario_detalle')
        ->insert(['inv_cab_id'=>$inv_cab_id,
          'IdProducto'=>$id_pro,
          'inv_can'=>$stock[$key],
          'inv_costo'=>$costo[$key]
        ]);


      }

      return Redirect::to('/inventarios');

    }
    
     public function editarinventario($id){

        $cabecera = DB::tABLE('inventario_cabecera')->where('inv_cab_id',$id)->first();

         $negocios = DB::tABLE('empresa_negocios')->where('IdEmpresa',Auth::user()->IdEmpresa)->get();

         $sucursal = $cabecera->id_empresa_negocio;

          $almacenes = DB::tABLE('almacenes')->where('id_empresa_negocio',$sucursal)->get();

          $almacen = $cabecera->id_almacen;

          $fecha = $cabecera->inv_fec;

          $tipo ='2';

            $productoslista = DB::tABLE('inventario_detalle')
            ->join('productos','productos.IdProducto','inventario_detalle.IdProducto')
            ->where('inv_cab_id',$id)
            ->get();

          return view('empresas.productos.inventarioseditar',compact('buspro','negocios','categorias','tipos_productos','categoria','tipo','sucursal','datos','almacenes','almacen','datosalm','fecha','unidades','productoslista','id'));


    }


     public function inventarios(Request $request,$sucursal=0,$almacen=0,$tipo=0)
    {   
   

          $categoria = $request->get('cmbCatId');
         // $sucursal = $request->get('sucursal');
          $negocios = DB::tABLE('empresa_negocios')->get();
         // $almacen = $request->get('almacen');
          $datosalm ="";


          $fecha = $request->get('fecha');

          if(empty($fecha)){
            $fecha = now()->format('Y-m-d');
          }

            $rucemp = trim(Auth::user()->IdEmpresa);
            $buspro = trim($request->get('buspro'));
            
            if($sucursal!='0'){

            
              if($tipo=='1'){

                $productos = DB::tABLE('inventario_cabecera')
                ->leftjoin('users','users.IdUsuario','inventario_cabecera.IdUsuario')
                ->leftjoin('almacenes','almacenes.id_almacen','inventario_cabecera.id_almacen')
                ->where('inventario_cabecera.id_empresa_negocio',$sucursal)
                ->where('inv_fec',$fecha)
                ->where('inventario_cabecera.id_almacen',$almacen)
                ->get();

                $datosalm = DB::tABLE('almacenes')->where('id_almacen',$almacen)->first();

              }elseif($tipo=='2'){

                $productos = DB::tABLE('productos as p')
                ->select('producto_stock.lote','producto_stock.vencimiento','p.procod','p.pronom','m.monnom','u.umenom','p.propun','p.proest','producto_empresa.IdProducto','p.costo','p.propun','marca','modelo')
                ->leftjoin('moneda as m','p.moncod','=','m.moncod')
                ->leftjoin('unidad_medida as u','p.umecod','=','u.umecod')
                ->leftjoin('producto_empresa','producto_empresa.IdProducto','p.IdProducto')
                ->leftjoin('producto_stock','producto_stock.IdProducto','p.IdProducto')
                ->where('producto_empresa.id_empresa_negocio',$sucursal)
            
              //  ->where('id_almacen',$almacen)
                ->groupby('p.IdProducto')
                ->orderby('pronom','asc')
                ->get();

                $datosalm = DB::tABLE('almacenes')->where('id_almacen',$almacen)->first();

              }
                

            }

           

            $categorias = DB::tABLE('categorias')->get();

            $buspro="";

            $tipos_productos = DB::tABLE('tipos_productos')->get();

            $datos = DB::tABLE('empresa_negocios')->where('id_empresa_negocio',$sucursal)->first();

            if(empty($sucursal)){

               $almacenes = DB::tABLE('almacenes')->where('id_empresa_negocio',$negocios->first()->id_empresa_negocio)->get();

            }else{

               $almacenes = DB::tABLE('almacenes')->where('id_empresa_negocio',$sucursal)->get();
            }

        

            return view('empresas.productos.inventarios',compact('buspro','negocios','categorias','tipos_productos','categoria','tipo','sucursal','datos','almacenes','almacen','datosalm','fecha','productos'));
      

    }

      public function BuscarProducto(Request $request){

        $buspro = $request->get('value');       
        $emp_id = trim(Auth::user()->IdEmpresa);

        $productos= DB::tABLE('productos')
        ->join('unidad_medida as cum','cum.umecod','productos.umecod')
        ->where('IdProducto',$buspro)
        ->where('productos.IdEmpresa',$emp_id)
        ->get();


        $results = array();
        
        foreach($productos as $pro){
          $results[] = ['success'=>'true','value'=>$pro->pronom,'prod_id'=>$pro->IdProducto,'prod_cost'=>$pro->costo,'prod_pun'=>$pro->propun,'prod_stock'=>$pro->stock,'unidad'=>$pro->umenom,'costo'=>$pro->costo_total,'precio'=>$pro->propun];
        }
     
       return response()->json($results);
    }



    public function ajustar_stock($id_almacen,$IdProducto){

        $productos = productos::findOrFail($IdProducto);
        $almacen = DB::tABLE('almacenes')->where('id_almacen',$id_almacen)->first();
        $sucursal = DB::tABLE('empresa_negocios')->where('id_empresa_negocio',$almacen->id_empresa_negocio)->first();

        return view('empresas.productos.ajustar_stock',compact('productos','almacen','sucursal'));

    }
  
     public function registrar_ajustar_stock(Request $request)
    {   

            $sucursal = $request->get('suc_id');
            $almacen = $request->get('alm_id');
            $cant = $request->get('cantidad');
      
            $IdProducto = $request->get('IdProducto');

             
            $bus_stock = DB::tABLE('producto_stock')->where('IdProducto',$IdProducto)->where('id_almacen',$almacen)->first();

            $cantidad = $cant - $bus_stock->stock;

            

              if($cantidad<0){

                 $cantidad = $cantidad*(-1);
                 $mov_tipo = 'E';
                 $tipo = '2';

                $tdocod = '84';

                $senudoc = DB::tABLE('empresa_negocios')->select('AESer','AENum')->where('id_empresa_negocio',$sucursal)->first();
                $numdoc =  $senudoc->AENum+1;
                $sercomp =  $senudoc->AESer;


                $emp_nego = EmpresaNegocios::findOrFail($sucursal);
                $emp_nego->AENum = $numdoc;
                $emp_nego->update();


    
              }else{

                $cantidad = $cantidad;
                $mov_tipo = 'I';
                $tipo = '3';

                $tdocod = '83';
                $senudoc = DB::tABLE('empresa_negocios')->select('AISer','AINum')->where('id_empresa_negocio',$sucursal)->first();
                $numdoc =  $senudoc->AINum+1;
                $sercomp =  $senudoc->AISer;


                $emp_nego = EmpresaNegocios::findOrFail($sucursal);
                $emp_nego->AINum = $numdoc;
                $emp_nego->update();




              }


             DB::tABLE('producto_stock')
             ->where('IdProducto',$IdProducto)
             ->update(['stock'=>$cant,
              'stock_inicial'=>$cant]);



               DB::tABLE('movimientos_productos')->insert([
                    'IdProducto'=>$IdProducto,
                    'precio'=>'',
                    'cantidad'=>$cantidad,
                    'costo'=>'',
                    'mov_cab_id'=>'',
                    'stock'=>'',
                    'IdProducto_rel'=>$IdProducto,
                    'IdCpe_cabecera'=>'',
                    'com_cab_id'=>'',
                    'stock_inicial'=>'',
                    'serie'=>$sercomp,
                    'numero'=>$numdoc,
                    'tdocod'=>$tdocod,
                    'tipo'=>$tipo,
                    'mov_tip'=>$mov_tipo,
                    'descripcion'=>'AJUSTE',
                    'id_empresa_negocio'=>$sucursal,
                    'id_almacen'=>$almacen,
                    'fecha_mov'=>now()->format('Y-m-d'),
            ]);




        return Redirect::to('/stockproductos');


    }


    public function consultarcategorias(Request $request){
        $rucemp = trim(Auth::user()->IdEmpresa);
        $categorias = DB::tABLE('categorias')
        ->select('cat_id','cat_nom','color')
        ->orderby('cat_nom','asc')
        ->get();

        $vista = view('empresas.productos.listacategorias',compact('categorias'))->render();

        if($request->ajax()){
         return response()->json(['vista'=>$vista]);

        }
    }
    

    

   
    public function costeo(request $request,$id,$suc){

        $rucemp = trim(Auth::user()->IdEmpresa);

        
        $bus_pro = DB::tABLE('productos')->where('IdProducto',$id)->first();

        if(empty($bus_pro->pro_rel)){
           $id = $bus_pro->IdProducto;
        }else{
           $id = $bus_pro->pro_rel;
        }

        $productos = DB::tABLE('productos')
        ->select('procod','pronom','productos.IdProducto','umecod','promocion','color','imagenproducto','precio','precio2','costo')
        ->leftjoin('producto_empresa','producto_empresa.IdProducto','productos.IdProducto')
        ->where('producto_empresa.id_empresa_negocio',$suc)
        ->where(function ($query) use ($id) {
             
                    $query->where('productos.IdProducto',$id)
                          ->orwhere('productos.pro_rel',$id);


                })
         ->groupby('IdProducto')
         ->orderby('productos.pronom')
         ->orderby('productos.umecod')
          ->get();


     

        $vista = view('empresas.compras.divcosteo',compact('productos','suc'))->render();

        if($request->ajax()){
         return response()->json(['vista'=>$vista]);

        }
    }

    
    public function actualizarprecios(Request $request){

       $productos = $request->get('idpro');
       $sucursal = $request->get('sucursal');
       $precios = $request->get('precio');
       $precios2 = $request->get('precio2');
       $costnue = $request->get('costnue');
       
       foreach ($productos as $i => $pro) {
          
          if(!empty($precios[$i]) || $precios[$i]=='0'){

             DB::tABLE('producto_empresa')
            ->where('IdProducto',$pro)
            ->where('id_empresa_negocio',$sucursal)
            ->update(['precio'=>$precios[$i]]);

          }

          if(!empty($precios2[$i]) || $precios2[$i]=='0'){

            DB::tABLE('producto_empresa')
            ->where('IdProducto',$pro)
            ->where('id_empresa_negocio',$sucursal)
            ->update(['precio2'=>$precios2[$i]]);

          }

//          if(!empty($costact[$i]) || $costact[$i]=='0'){

 //           DB::tABLE('productos')
 //           ->where('IdProducto',$pro)
 //           ->update(['costo'=>$precios2[$i]]);

  //        }


          if(!empty($costnue[$i]) || $costnue[$i]=='0'){

            DB::tABLE('productos')
            ->where('IdProducto',$pro)
            ->update(['costo'=>$costnue[$i]]);

          }
         
       }

        if($request->ajax()){
         return response()->json(['mensaje'=>'Precios Modificados']);

        }
    }

    public function exportar_productos_inventario(Request $request, $sucursal, $alm){

       
    
              $negocio = DB::tABLE('empresa_negocios')
              ->where('id_empresa_negocio',$sucursal)
              ->first();

              $almacen = DB::tABLE('almacenes')->where('id_almacen',$alm)->first();

              $productos = DB::tABLE('productos')
              ->join('producto_stock','producto_stock.IdProducto','productos.IdProducto')
              ->join('producto_empresa','producto_empresa.IdProducto','productos.IdProducto')
              ->where('producto_stock.id_empresa_negocio',$sucursal)
              ->where('producto_stock.id_almacen',$alm)
              ->get();



    

         
                        Excel::create('INVENTARIO_PRODUCTOS', function($excel) use ($productos,$negocio,$almacen) {

                        $excel->sheet('Comprobantes', function($sheet) use ($productos,$negocio,$almacen) {

                            /*$sheet->setColumnFormat(array(
                                    'A' => 'dd/mm/yy',
                                    'G' => '0.00',
                                    'H' => '0.00',
                                    'I' => '0.00',
                                    'J' => '0.00',

                                    
                                ));*/
                                
                                $sheet->loadView('empresas.reportes.reporte_pdf_stock_productos',compact('productos','negocio','almacen'));

                        });

                    })->export('xlsx'); 
    
        }

    public function exportar_productos_excel(Request $request)
    {
        $rucemp = trim(Auth::user()->IdEmpresa);
        $sucursal = Auth::user()->id_empresa_negocio;

        $productos = DB::table('productos as p')
            ->select(
                'p.IdProducto',
                DB::raw("CASE WHEN p.promocion = '0' THEN 'PRODUCTO' WHEN p.promocion = '1' THEN 'COMBO' WHEN p.promocion = '2' THEN 'PREPARADOS' WHEN p.promocion = '4' THEN 'INSUMO' WHEN p.promocion = '5' THEN 'ENTRADA' WHEN p.promocion = '6' THEN 'COMBO' ELSE 'OTRO' END as TIPO_PRODUCTO"),
                'p.procod',
                'tp.tip_pro_nom as LINEA',
                'cat.cat_nom as FAMILIA',
                'u.umenom as UM',
                'p.pronom as PRODUCTO',
                'p.propun as PRECIO',
                'p.costo as COSTO'
            )
            ->leftJoin('producto_empresa', 'producto_empresa.IdProducto', 'p.IdProducto')
            ->leftJoin('categorias as cat', 'cat.cat_id', 'p.cat_id')
            ->leftJoin('tipo_producto as tp', 'tp.tip_pro_id', 'p.tip_pro_id')
            ->leftJoin('unidad_medida as u', 'u.umecod', 'p.umecod')
            ->where('producto_empresa.id_empresa_negocio', $sucursal)
            ->where('p.IdEmpresa', $rucemp)
            ->where('p.tipo', '1')
            ->groupBy('p.IdProducto')
            ->orderBy('p.IdProducto', 'asc')
            ->get();

        $productoslista = [];
        foreach ($productos as $producto) {
            $productoslista[] = [
                'IDPRODUCTO' => $producto->IdProducto,
                'TIPO_PRODUCTO' => $producto->TIPO_PRODUCTO,
                'CODIGO' => $producto->procod,
                'LINEA' => $producto->LINEA,
                'FAMILIA' => $producto->FAMILIA,
                'UM' => $producto->UM,
                'PRODUCTO' => $producto->PRODUCTO,
                'PRECIO' => $producto->PRECIO,
                'COSTO' => $producto->COSTO,
            ];
        }

        Excel::create('PRODUCTOS', function ($excel) use ($productoslista) {
            $excel->sheet('PRODUCTOS', function ($sheet) use ($productoslista) {
                $sheet->fromArray($productoslista);
            });
        })->export('xlsx');
    }

    public function ImportarProductos(Request $request)
    {
        try {
            if (!$request->hasFile('archivo')) {
                return Redirect::back()->with('error', 'No se subió ningún archivo.');
            }

            $empresaRuc = trim(Auth::user()->IdEmpresa);
            $empresaNegocio = Auth::user()->id_empresa_negocio;
            $almacenDefault = DB::table('almacenes')
                ->where('id_empresa_negocio', $empresaNegocio)
                ->orderBy('id_almacen', 'asc')
                ->first();

            $idAlmacen = $almacenDefault ? $almacenDefault->id_almacen : 1;

            \Excel::load(Input::file('archivo'), function ($reader) use ($empresaRuc, $empresaNegocio, $idAlmacen) {
                $rows = $reader->get();

                foreach ($rows as $row) {
                    $data = [];
                    foreach ($row as $key => $value) {
                        $keyNormalized = strtolower(str_replace([' ', '.', '-'], ['_', '_', '_'], trim($key)));
                        $data[$keyNormalized] = $value;
                    }

                    $idProducto = trim($data['item'] ?? $data['idproducto'] ?? '');
                    if (empty($idProducto)) {
                        continue;
                    }

                    $procod = trim($data['codigo'] ?? $data['prodoc'] ?? '');
                    $pronom = trim($data['producto'] ?? $data['pronom'] ?? '');
                    $umecod = trim($data['um'] ?? $data['umecod'] ?? '');
                    $costo = floatval(str_replace(',', '.', $data['costo'] ?? 0));
                    $propun = floatval(str_replace(',', '.', $data['precio'] ?? 0));
                    $moncod = trim($data['moneda'] ?? $data['nomcod'] ?? '');
                    $tigcod = trim($data['tipo_igv'] ?? $data['tigcod'] ?? '');
                    $factor = floatval(str_replace(',', '.', $data['factor'] ?? 1));
                    $tipo = trim($data['tipo'] ?? '1');
                    $stock = floatval(str_replace(',', '.', $data['stock'] ?? 0));

                    $productData = [
                        'IdProducto' => $idProducto,
                        'IdEmpresa' => $empresaRuc,
                        'procod' => $procod,
                        'pronom' => $pronom,
                        'umecod' => $umecod,
                        'costo' => $costo,
                        'costo_total' => $costo,
                        'propun' => $propun,
                        'precio' => $propun,
                        'moncod' => $moncod,
                        'tigcod' => $tigcod,
                        'factor' => $factor,
                        'tipo' => $tipo,
                        'promocion' => 2,
                        'proest' => 'Activo',
                        'id_empresa_negocio' => $empresaNegocio,
                        'cat_id' => null,
                        'subcat_id' => null,
                        'tip_pro_id' => null,                        
                        'flete' => 0,
                        'pro_rel' => null,
                    ];

                    $existsProduct = DB::table('productos')->where('IdProducto', $idProducto)->first();
                    if ($existsProduct) {
                        DB::table('productos')->where('IdProducto', $idProducto)->update($productData);
                    } else {
                        DB::table('productos')->insert($productData);
                    }

                    if (!empty($procod)) {
                        $existsCodigo = DB::table('producto_codigo')->where('IdProducto', $idProducto)->first();
                        if ($existsCodigo) {
                            DB::table('producto_codigo')
                                ->where('IdProducto', $idProducto)
                                ->update(['cod_bar' => $procod]);
                        } else {
                            DB::table('producto_codigo')->insert([
                                'IdProducto' => $idProducto,
                                'cod_bar' => $procod,
                            ]);
                        }
                    }

                    $existsEmpresa = DB::table('producto_empresa')
                        ->where('IdProducto', $idProducto)
                        ->where('id_empresa_negocio', $empresaNegocio)
                        ->first();

                    if ($existsEmpresa) {
                        DB::table('producto_empresa')
                            ->where('IdProducto', $idProducto)
                            ->where('id_empresa_negocio', $empresaNegocio)
                            ->update(['precio' => $propun]);
                    } else {
                        DB::table('producto_empresa')->insert([
                            'IdProducto' => $idProducto,
                            'id_empresa_negocio' => $empresaNegocio,
                            'precio' => $propun,
                        ]);
                    }

                    $existsStock = DB::table('producto_stock')
                        ->where('IdProducto', $idProducto)
                        ->where('id_empresa_negocio', $empresaNegocio)
                        ->where('id_almacen', $idAlmacen)
                        ->first();

                    if ($existsStock) {
                        DB::table('producto_stock')
                            ->where('IdProducto', $idProducto)
                            ->where('id_empresa_negocio', $empresaNegocio)
                            ->where('id_almacen', $idAlmacen)
                            ->update(['stock' => $stock, 'stock_inicial' => $stock]);
                    } else {
                        DB::table('producto_stock')->insert([
                            'IdProducto' => $idProducto,
                            'id_empresa_negocio' => $empresaNegocio,
                            'id_almacen' => $idAlmacen,
                            'stock' => $stock,
                            'stock_inicial' => $stock,
                        ]);
                    }
                }
            });

            return Redirect::back()->with('success', 'Productos importados correctamente.');
        } catch (\Exception $e) {
            return Redirect::back()->with('error', 'Error importando productos: ' . $e->getMessage());
        }
    }

   public function  importar_inventario(Request $request)
    {


      try{
        if(empty($request->get('almacenimport'))){
        $almacen = $request->get('almacen');
      }else{
         $almacen = $request->get('almacenimport');
      }
       

        

       $sucursal = $request->get('sucursalimport');
       $fecha = $request->get('fechaimport');

      $cabecera = DB::tABLE('inventario_cabecera')
      ->insert(['inv_fec'=>$fecha,
                'IdUsuario'=>Auth::user()->IdUsuario,
                'id_empresa_negocio'=>$sucursal,
                'id_almacen'=>$almacen
              ]);

      $inv_cab_id = DB::getPdo()->lastInsertId();

        \Excel::load(Input::file('archivo'), function($reader) use($sucursal,$almacen,$inv_cab_id)  {

            $excel = $reader->get()->toArray();;

            // iteracción
           //  $reader->each(function($row)  use($producto,$rucemp) {

            foreach ($excel as $key => $value) {

              
                $buscarproducto = DB::tABLE('productos')->where('IdProducto',$value['id'])
                ->first();

                if(!empty($buscarproducto)){

                  if(empty($buscarproducto->pro_rel)){
                    $id = $value['id'];
                    $stock = $value['cantidad']*$buscarproducto->factor;
                  }else{
                       $id = $buscarproducto->pro_rel;
                   $stock = $value['cantidad']*$buscarproducto->factor;
             
                  }
               
                
                    
                    $buscar_stock = DB::tABLE('producto_stock')
                    ->where('IdProducto',$id)
                    ->where('producto_stock.id_empresa_negocio',$sucursal)
                    ->where('producto_stock.id_almacen',$almacen)
                    ->update(['producto_stock.stock'=>$stock,'producto_stock.stock_inicial'=>$value['stock_inicial']]);

                    DB::tABLE('inventario_detalle')
                    ->insert(['inv_cab_id'=>$inv_cab_id,
                      'IdProducto'=>$id,
                      'inv_sto_ini'=>$value['stock_inicial'],
                      'inv_can'=>$stock,
                      'inv_costo'=>$value['costo']
                    ]);
                    }
                   

                }
        
        
        });



      }catch(\Exception $e){
          dd($e);
      }
      

           
        return Redirect::to('/inventarios');      
    }




 public function stock_inicial(){

      $productos = DB::tABLE('productos')->where('tipo','1')->get();

      $almacen = DB::tABLE('almacenes')->where('id_empresa_negocio',Auth::user()->id_empresa_negocio)->where('predeterminado','1')->first();

      foreach ($productos as $key => $pro) {

                DB::tABLE('movimientos_productos')->insert([
                        'IdProducto'=>$pro->IdProducto,
                        'precio'=>$pro->propun,
                        'cantidad'=>$pro->stock_migrar,
                        'costo'=>$pro->costo,
                        'mov_cab_id'=>'',
                        'stock'=>$pro->stock_migrar,
                        'IdProducto_rel'=>$pro->IdProducto,
                        'IdCpe_cabecera'=>'',
                        'com_cab_id'=>'',
                        'stock_inicial'=>$pro->stock_migrar,
                        'serie'=>'',
                        'numero'=>'',
                        'tdocod'=>'',
                        'tipo'=>'I',
                        'descripcion'=>'STOCK INICIAL',
                        'id_empresa_negocio'=>Auth::user()->id_empresa_negocio,
                        'id_almacen'=>$almacen->id_almacen,
                        'fecha_mov'=>'2021-07-01',
                    ]);


      }


           

 }


 public function calcular_stock(){


      $ventas = DB::tABLE('cpe_detalle')
      ->join('cpe_cabecera','cpe_cabecera.IdCpe_cabecera','cpe_detalle.IdCpe_cabecera')
      ->where(function ($query) {
          $query->where('tdocod','01')
              ->orWhere('tdocod','03')
              ->orWhere('tdocod','13')
              ->orWhere('tdocod','14');
          })
      ->where('cpe_cabecera.id_empresa_negocio',Auth::user()->id_empresa_negocio)
      ->orderby('ccafem','asc')
      ->get();


      $compras = DB::tABLE('compras_cabecera')
      ->join('compras_detalle','compras_detalle.com_cab_id','compras_cabecera.com_cab_id')
      ->leftjoin('proveedor','proveedor.prov_id','compras_cabecera.prov_id')
      ->where(function ($query) {
          $query->where('tdocod','01')
              ->orWhere('tdocod','03')
              ->orWhere('tdocod','13')
              ->orWhere('tdocod','14');
          })
      ->where('compras_cabecera.id_empresa_negocio',Auth::user()->id_empresa_negocio)
      ->orderby('com_fec','asc')
      ->get();



      foreach ($compras as $comp) {

                DB::tABLE('movimientos_productos')->insert([

                        'IdProducto'=>$comp->pro_id,
                        'precio'=>'',
                        'cliente'=>$comp->prov_raz,
                        'cantidad'=>$comp->cantidad,
                        'costo'=>$comp->pre_uni,
                        'mov_cab_id'=>'',
                        'stock'=>$comp->com_det_stock,
                        'IdProducto_rel'=>$comp->IdProducto_rel,
                        'IdCpe_cabecera'=>'',
                        'com_cab_id'=>$comp->com_cab_id,
                        'stock_inicial'=>$comp->com_det_stock_inicial,
                        'serie'=>$comp->com_doc_ser,
                        'numero'=>$comp->com_doc_num,
                        'tdocod'=>$comp->tdocod,
                        'tipo'=>'2',
                        'descripcion'=>'COMPRAS',
                        'id_empresa_negocio'=>Auth::user()->id_empresa_negocio,
                        'id_almacen'=>$comp->id_almacen,
                        'fecha_mov'=>$comp->com_fec_ing,

                    ]);

      }

      foreach ($ventas as $ven) {
        
        try{
               DB::tABLE('movimientos_productos')->insert([
                                'IdProducto'=>$ven->IdProducto,
                                'precio'=>$ven->cdepuni,
                                'cliente'=>$ven->ccanom,
                                'cantidad'=>$ven->cdecan,
                                'costo'=>$ven->costo,
                                'mov_cab_id'=>'',
                                'stock'=>$ven->cpe_det_stock,
                                'IdProducto_rel'=>$ven->IdProducto_rel,
                                'IdCpe_cabecera'=>$ven->IdCpe_cabecera,
                                'com_cab_id'=>'',
                                'stock_inicial'=>$ven->cpe_det_stock_inicial,
                                'serie'=>$ven->serdoc,
                                'numero'=>$ven->numdoc,
                                'tdocod'=>$ven->tdocod,
                                'tipo'=>'3',
                                'descripcion'=>'VENTAS',
                                'id_empresa_negocio'=>Auth::user()->id_empresa_negocio,
                                'id_almacen'=>$ven->id_almacen,
                                'fecha_mov'=>$ven->ccafem,


                           ]);
        }catch(\Exception $e){


        }
      
        
        }


      }



      /*---------------------------------------------RESTAURANTE------------------------------------------------------*/


      public function buscarcarta(Request $request, $id = 0, $cat = 0)
    {
        $almacen = DB::table('almacenes')
            ->where('predeterminado', '1')
            ->where('id_empresa_negocio', Auth::user()->id_empresa_negocio)
            ->first();

        $almacen_id = $almacen ? $almacen->id_almacen : 0;

        // Obtener la fecha y hora actuales usando Carbon
        $ahora = Carbon::now(); // Esto obtiene la fecha y hora completas (ej. 2025-06-12 14:30:00)
        $dia_actual_carbon = $ahora->dayOfWeek; // 0 (Domingo) through 6 (Sábado)
        $hora_actual_string = $ahora->toTimeString(); // Formato 'HH:MM:SS' (ej. '14:30:00')

        $productos = DB::table('productos as p')
            ->select(
                'p.IdProducto',
                'p.procod',
                'p.pronom',
                'p.umecod',
                'p.promocion',
                'p.color',
                'p.imagenproducto',
                'p.factor',
                'p.icbper',
                'p.acom',
                'cat.cat_sig',
                // **Lógica para obtener el precio dinámico por día y hora (incluyendo cruce de medianoche)**
                DB::raw("
                    COALESCE(
                        (SELECT psd.precio_especial
                         FROM precios_dia_semana as psd
                         WHERE psd.IdProducto = p.IdProducto
                           AND psd.id_empresa_negocio = '".Auth::user()->id_empresa_negocio."'
                           AND psd.estado = 'Activo'
                           -- Validación de rango de fechas GENERAL (si la promoción es temporal)
                           AND (psd.fecha_inicio_vigencia IS NULL OR psd.fecha_inicio_vigencia <= CURDATE())
                           AND (psd.fecha_fin_vigencia IS NULL OR psd.fecha_fin_vigencia >= CURDATE())
                           AND
                           (
                               -- Caso 1: La promoción INICIA y TERMINA en el MISMO DÍA (sin cruzar medianoche en la definición)
                               -- Ejemplo: Jueves 10:00 a Jueves 18:00
                               (
                                   psd.dia_semana = {$dia_actual_carbon}
                                   AND psd.hora_inicio_vigencia <= psd.hora_fin_vigencia -- Inicio antes o igual que fin (mismo día)
                                   AND TIME('{$hora_actual_string}') >= psd.hora_inicio_vigencia
                                   AND TIME('{$hora_actual_string}') <= psd.hora_fin_vigencia
                               )
                               OR
                               -- Caso 2: La promoción INICIA en un día y TERMINA en el DÍA SIGUIENTE (cruzando medianoche)
                               -- Ejemplo: Jueves 18:00 a Viernes 16:00
                               (
                                   -- Verifica si es el día de inicio de la promoción que cruza la medianoche
                                   psd.dia_semana = MOD({$dia_actual_carbon} - 1 + 7, 7) -- Calcula el día de la semana anterior (0-6)
                                   AND psd.hora_inicio_vigencia > psd.hora_fin_vigencia -- Inicio DESPUÉS que fin (indica cruce de medianoche)
                                   AND TIME('{$hora_actual_string}') >= psd.hora_inicio_vigencia -- La hora actual es después del inicio
                               )
                               OR
                               -- Caso 3: La promoción es del DÍA ANTERIOR y todavía está vigente en el día actual
                               -- (continúa después de medianoche)
                               -- Ejemplo: Hoy es Viernes y la promo de Jueves 18:00 a Viernes 16:00 todavía está activa
                               (
                                   -- El día de inicio de la regla es el día ANTERIOR al actual
                                   psd.dia_semana = MOD({$dia_actual_carbon} - 1 + 7, 7) -- Calcula el día de la semana anterior (0-6)
                                   AND psd.hora_inicio_vigencia > psd.hora_fin_vigencia -- Indica que la regla del día anterior cruzó la medianoche
                                   AND TIME('{$hora_actual_string}') <= psd.hora_fin_vigencia -- La hora actual es ANTES o igual a la hora de fin (del día siguiente)
                               )
                           )
                           ORDER BY psd.id_precio_dia DESC -- Puedes ajustar la prioridad (ej. la regla más reciente o específica)
                           LIMIT 1
                        ),
                        p.propun -- Usa el precio normal (propun) si no hay precio especial activo para hoy y esta hora
                    ) as precio
                "),
                'p.propun1 as precio2',
                'p.propun2 as precio3',
                
                DB::raw("
                    CASE
                        WHEN p.tipo = '2' AND p.pro_rel IS NOT NULL THEN (
                            SELECT stock / p.factor
                            FROM producto_stock
                            WHERE IdProducto = p.pro_rel
                              AND id_almacen = '{$almacen_id}'
                              AND id_empresa_negocio = '".Auth::user()->id_empresa_negocio."'
                        )
                        ELSE (
                            SELECT stock
                            FROM producto_stock
                            WHERE IdProducto = p.IdProducto
                              AND id_almacen = '{$almacen_id}'
                              AND id_empresa_negocio = '".Auth::user()->id_empresa_negocio."'
                        )
                    END as stock_disponible
                ")
            )
            ->join('producto_empresa as pe', 'pe.IdProducto', 'p.IdProducto')
            ->leftjoin('categorias as cat', 'cat.cat_id', '=', 'p.cat_id')
            ->where(function ($query) use ($id) {
                if ($id <> '0') {
                    $query->where('p.procod', '=', $id)
                        ->orWhere('p.pronom', 'like', '%' . $id . '%');
                }
            })
            ->where(function ($query) use ($cat) {
                if ($cat <> '0') {
                    $query->where('p.cat_id', '=', $cat);
                }
            })
            ->where('p.promocion', '!=', '4') // Excluir productos de tipo '4' (Insumos)
            //->leftjoin('pedidos_detalle as pede', 'pede.item_facturado', '!=', '0')

            ->orderBy('p.pronom', 'asc')
            ->where('pe.id_empresa_negocio', Auth::user()->id_empresa_negocio)
            ->get();

        $data_cat = DB::table('categorias')->where('cat_id', $cat)->first();

        $vista = view('empresas.productos.items_productos', compact('productos', 'data_cat'))->render();
        //$vista = view('empresas.productos.items_productos', compact('productos', 'data_cat'))->render(); // <-- ¡Esto debería ser para PC (lista)!

        if ($request->ajax()) {
            return response()->json(['vista' => $vista]);
        }
    }


    


          public function buscarcartaimg(Request $request, $id=0,$cat=0){
        $almacen = DB::table('almacenes')
            ->where('predeterminado', '1')
            ->where('id_empresa_negocio', Auth::user()->id_empresa_negocio)
            ->first();

        $almacen_id = $almacen ? $almacen->id_almacen : 0;

        // Obtener la fecha y hora actuales usando Carbon
        $ahora = Carbon::now(); // Esto obtiene la fecha y hora completas (ej. 2025-06-12 10:09:11 PM -05)
        $dia_actual_carbon = $ahora->dayOfWeek; // 0 (Domingo) through 6 (Sábado)
        $hora_actual_string = $ahora->toTimeString(); // Formato 'HH:MM:SS' (ej. '22:09:11')

        $productos = DB::table('productos as p')
            ->select(
                'p.IdProducto',
                'p.procod',
                'p.pronom',
                'p.umecod',
                'p.promocion',
                'p.color',
                'p.imagenproducto',
                'p.factor',
                'p.icbper',
                'p.acom',
                'cat.cat_sig',
                // Lógica para obtener el precio dinámico por día y hora (incluyendo cruce de medianoche)
                DB::raw("
                    COALESCE(
                        (SELECT psd.precio_especial
                         FROM precios_dia_semana as psd
                         WHERE psd.IdProducto = p.IdProducto
                           AND psd.id_empresa_negocio = '".Auth::user()->id_empresa_negocio."'
                           AND psd.estado = 'Activo'
                           -- Validación de rango de fechas GENERAL (si la promoción es temporal)
                           AND (psd.fecha_inicio_vigencia IS NULL OR psd.fecha_inicio_vigencia <= CURDATE())
                           AND (psd.fecha_fin_vigencia IS NULL OR psd.fecha_fin_vigencia >= CURDATE())
                           AND
                           (
                               -- Caso 1: La promoción INICIA y TERMINA en el MISMO DÍA (sin cruzar medianoche en la definición)
                               -- Ejemplo: Jueves 10:00 a Jueves 18:00
                               (
                                   psd.dia_semana = {$dia_actual_carbon}
                                   AND psd.hora_inicio_vigencia <= psd.hora_fin_vigencia -- Inicio antes o igual que fin (mismo día)
                                   AND TIME('{$hora_actual_string}') >= psd.hora_inicio_vigencia
                                   AND TIME('{$hora_actual_string}') <= psd.hora_fin_vigencia
                               )
                               OR
                               -- Caso 2: La promoción INICIA en un día y TERMINA en el DÍA SIGUIENTE (cruzando medianoche)
                               -- Ejemplo: Jueves 18:00 a Viernes 16:00
                               (
                                   -- Verifica si es el día de inicio de la promoción que cruza la medianoche
                                   psd.dia_semana = MOD({$dia_actual_carbon} - 1 + 7, 7) -- Calcula el día de la semana anterior (0-6)
                                   AND psd.hora_inicio_vigencia > psd.hora_fin_vigencia -- Indica que la regla del día anterior cruzó la medianoche
                                   AND TIME('{$hora_actual_string}') >= psd.hora_inicio_vigencia -- La hora actual es después del inicio
                               )
                               OR
                               -- Caso 3: La promoción es del DÍA ANTERIOR y todavía está vigente en el día actual
                               -- (continúa después de medianoche)
                               -- Ejemplo: Hoy es Viernes y la promo de Jueves 18:00 a Viernes 16:00 todavía está activa
                               (
                                   -- El día de inicio de la regla es el día ANTERIOR al actual
                                   psd.dia_semana = MOD({$dia_actual_carbon} - 1 + 7, 7) -- Calcula el día de la semana anterior (0-6)
                                   AND psd.hora_inicio_vigencia > psd.hora_fin_vigencia -- Indica que la regla del día anterior cruzó la medianoche
                                   AND TIME('{$hora_actual_string}') <= psd.hora_fin_vigencia -- La hora actual es ANTES o igual a la hora de fin (del día siguiente)
                               )
                           )
                           ORDER BY psd.id_precio_dia DESC -- Puedes ajustar la prioridad (ej. la regla más reciente o específica)
                           LIMIT 1
                        ),
                        p.propun -- Usa el precio normal (propun) si no hay precio especial activo para hoy y esta hora
                    ) as precio
                "),
                'p.propun1 as precio2',
                'p.propun2 as precio3',
                
                DB::raw("
                    CASE
                        WHEN p.tipo = '2' AND p.pro_rel IS NOT NULL THEN (
                            SELECT stock / p.factor
                            FROM producto_stock
                            WHERE IdProducto = p.pro_rel
                              AND id_almacen = '{$almacen_id}'
                              AND id_empresa_negocio = '".Auth::user()->id_empresa_negocio."'
                        )
                        ELSE (
                            SELECT stock
                            FROM producto_stock
                            WHERE IdProducto = p.IdProducto
                              AND id_almacen = '{$almacen_id}'
                              AND id_empresa_negocio = '".Auth::user()->id_empresa_negocio."'
                        )
                    END as stock_disponible
                ")
            )
            ->join('producto_empresa as pe', 'pe.IdProducto', 'p.IdProducto')
            ->leftjoin('categorias as cat', 'cat.cat_id', '=', 'p.cat_id')
            ->where(function ($query) use ($id) {
                if ($id <> '0') {
                    $query->where('p.procod', '=', $id)
                        ->orWhere('p.pronom', 'like', '%' . $id . '%');
                }
            })
            ->where(function ($query) use ($cat) {
                if ($cat <> '0') {
                    $query->where('p.cat_id', '=', $cat);
                }
            })
            ->where('p.promocion', '!=', '4') // Excluir productos de tipo '4' (Insumos)
            ->orderBy('p.pronom', 'asc')
            ->where('pe.id_empresa_negocio', Auth::user()->id_empresa_negocio)
            ->get();

        $data_cat = DB::table('categorias')->where('cat_id', $cat)->first();

        $vista = view('empresas.productos.items_productos_img', compact('productos', 'data_cat'))->render();

        if ($request->ajax()) {
            return response()->json(['vista' => $vista]);
        }
    }

        public function buscarcartallevar(Request $request, $id=0,$cat=0){
     
               $productos = DB::tABLE('productos')
            ->join('producto_empresa','producto_empresa.IdProducto','productos.IdProducto')
            ->where(function ($query) use($id){
                if($id<>'0'){
                  $query->where('procod','=',$id)
                        ->orWhere('pronom', 'like','%'.$id.'%'); 
                }
            })
            ->where(function ($query) use($cat){
                if($cat<>'0'){
                  $query->where('cat_id','=',$cat);
                }
            })

            ->where('promocion','!=','4')
            ->orderby('pronom','asc')
            ->where('producto_empresa.id_empresa_negocio',Auth::user()->id_empresa_negocio)
            ->get();

            $data_cat = DB::tABLE('categorias')->where('cat_id',$cat)->first();

            $vista = view('empresas.productos.items_productos',compact('productos','data_cat'))->render();
            //$vista = view('empresas.productos.items_productos_img', compact('productos', 'data_cat'))->render(); // <-- ¡Esto debería ser para móvil (cuadrícula)!

            if($request->ajax()){
             return response()->json(['vista'=>$vista]);

            }
        }




      /*--------------------------------------------------------------------------------------------------------------*/



      public function  importar_ajuste(Request $request)
    {

        try{

        \Excel::load(Input::file('archivo'), function($reader) {

            $excel = $reader->get()->toArray();

          
            foreach ($excel as $key => $value) {

              
               $buscar_almacen = DB::tABLE('almacenes')
               ->where('id_empresa_negocio',Auth::user()->id_empresa_negocio)
               ->where('predeterminado','1')->first();


               $cant = $value['stock'];
               $codigo = $value['codigo'];

               $bus_pro = productos::where('procod',$codigo)->where('tipo','1')->first();

               $bus_stock = DB::tABLE('producto_stock')->where('IdProducto',$bus_pro->IdProducto)->where('id_almacen',$buscar_almacen->id_almacen)->first();

               $cantidad = $cant - $bus_stock->stock;

            

              if($cantidad<0){

                 $cantidad = $cantidad*(-1);
                 $mov_tipo = 'E';
                 $tipo = '2';

                $tdocod = '84';

                $senudoc = DB::tABLE('empresa_negocios')->select('AESer','AENum')->where('id_empresa_negocio',Auth::user()->id_empresa_negocio)->first();
                $numdoc =  $senudoc->AENum+1;
                $sercomp =  $senudoc->AESer;


                $emp_nego = EmpresaNegocios::findOrFail(Auth::user()->id_empresa_negocio);
                $emp_nego->AENum = $numdoc;
                $emp_nego->update();


    
              }else{

                $cantidad = $cantidad;
                $mov_tipo = 'I';
                $tipo = '3';

                $tdocod = '83';
                $senudoc = DB::tABLE('empresa_negocios')->select('AISer','AINum')->where('id_empresa_negocio',Auth::user()->id_empresa_negocio)->first();
                $numdoc =  $senudoc->AINum+1;
                $sercomp =  $senudoc->AISer;


                $emp_nego = EmpresaNegocios::findOrFail(Auth::user()->id_empresa_negocio);
                $emp_nego->AINum = $numdoc;
                $emp_nego->update();
              }

             DB::tABLE('producto_stock')
             ->where('IdProducto',$bus_pro->IdProducto)
             ->update(['stock'=>$cant,
              'stock_inicial'=>$cant]);

               DB::tABLE('movimientos_productos')->insert([
                    'IdProducto'=>$bus_pro->IdProducto,
                    'precio'=>'',
                    'cantidad'=>$cantidad,
                    'costo'=>'',
                    'mov_cab_id'=>'',
                    'stock'=>'',
                    'IdProducto_rel'=>$bus_pro->IdProducto,
                    'IdCpe_cabecera'=>'',
                    'com_cab_id'=>'',
                    'stock_inicial'=>'',
                    'serie'=>$sercomp,
                    'numero'=>$numdoc,
                    'tdocod'=>$tdocod,
                    'tipo'=>$tipo,
                    'mov_tip'=>$mov_tipo,
                    'descripcion'=>'AJUSTE',
                    'id_empresa_negocio'=>Auth::user()->id_empresa_negocio,
                    'id_almacen'=>$buscar_almacen->id_almacen,
                    'fecha_mov'=>now()->format('Y-m-d'),
            ]);
            }        
        });
      }catch(\Exception $e){
       dd($e);
      }
           
        return Redirect::to('/productos');      
    }

        public function consultar_catalogo(Request $request){
      $search = $request->get('bus_cata'); 
        $bus_suc = DB::tABLE('empresa_negocios')
       ->where('id_empresa_negocio',Auth::user()->id_empresa_negocio)
       ->first();
       $bus_alm = DB::tABLE('almacenes')->where('id_empresa_negocio',Auth::user()->id_empresa_negocio)->where('predeterminado','1')->first();
      $id_almacen = $bus_alm->id_almacen;       
      $rucemp = trim(Auth::user()->IdEmpresa);
       $bus_alm = DB::tABLE('almacenes')
        ->where('id_almacen',$id_almacen)
        ->first();
            $catalogo = DB::tABLE('productos')
            ->select('imagenproducto','costo_total','pro_rel','costo','mar_nom','procod','pronom','propun','productos.IdProducto','umecod','promocion','color','imagenproducto','precio2','precio3',DB::raw("(SELECT stock FROM producto_stock as pro WHERE productos.IdProducto = pro.IdProducto AND id_almacen='".$bus_alm->id_almacen."' AND pro.id_empresa_negocio='".$bus_alm->id_empresa_negocio."') as stock"),'precio',DB::raw("(SELECT count(*) FROM productos as pro WHERE pro.pro_rel =  productos.IdProducto) as cont_pre"),'icbper','mon_icbper')
            ->join('producto_empresa','producto_empresa.IdProducto','productos.IdProducto')
            
          ->leftjoin('marcas','marcas.mar_id','productos.marca')
        ->where(function ($query) use($search){
                $query->where('pronom','like','%'.$search.'%')
          ->orwhere('codigo_barra',$search)
          ->orwhere('procod',$search);
          })           
            ->where('promocion','!=','2')           
            ->groupby('productos.IdProducto')
            ->orderby('productos.pronom')
            ->orderby('productos.umecod')
            ->get();
       $vista = view('empresas.puntosventas.div_catalogo',compact('catalogo'))->render();
             if($request->ajax()){
         return response()->json(['vista'=>$vista]);

        }
    }


}