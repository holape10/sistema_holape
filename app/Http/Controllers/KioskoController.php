<?php

namespace MasterSoft\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Redirect;

use MasterSoft\User;

use MasterSoft\mesas;
use MasterSoft\pedidos;
use MasterSoft\Empresa;
use MasterSoft\EmpresaNegocios;
use MasterSoft\productos;
use MasterSoft\Cliente;
use MasterSoft\cpe_cabecera;
use MasterSoft\cpe_detalle;
use MasterSoft\Movimientos; // O movimientos (si tu modelo se llama así)
use MasterSoft\Modelos\Almacen;
use MasterSoft\MontoLetras; // Si esta es una clase de ayuda
use MasterSoft\usuario_facturacion;

use Mike42\Escpos\PrintConnectors\WindowsPrintConnector;
use Mike42\Escpos\PrintConnectors\NetworkPrintConnector;
use Mike42\Escpos\Printer;
use Mike42\Escpos\EscposImage;
use Mike42\Escpos\PrintConnectors\DummyPrintConnector;

use QR_Code\Exceptions\InvalidVCardAddressEntryException;
use QR_Code\Exceptions\InvalidVCardPhoneEntryException;
use QR_Code\Types\QR_CalendarEvent;
use QR_Code\Types\QR_EmailMessage;
use QR_Code\Types\QR_meCard;
use QR_Code\Types\QR_Phone;
use QR_Code\Types\QR_Sms;
use QR_Code\Types\QR_Text;
use QR_Code\Types\QR_Url;
use QR_Code\Types\QR_VCard;
use QR_Code\Types\QR_WiFi;
use QR_Code\Types\vCard\Person;
use QR_Code\Types\vCard\Phone;

use MasterSoft\pedidos_detalle;

class KioskoController extends Controller
{

    public function __construct()
    {
        $this->middleware('auth'); // Requiere que el usuario esté logueado
    }
    // Muestra la pantalla de bienvenida
    public function bienvenida()
    {
        return view('empresas.kiosko.bienvenida');
    }

    public function buscarMesasLibresParaUnir()
{
    $id_empresa_negocio = Auth::user()->id_empresa_negocio;

    // Hacemos el JOIN con la tabla 'pisos' para obtener el nombre del ambiente
    $mesas_para_el_parcial = DB::table('mesas')
        ->join('pisos', 'mesas.pis_id', '=', 'pisos.pis_id')
        ->select('mesas.*', 'pisos.pis_nom')
        ->where('mesas.id_empresa_negocio', $id_empresa_negocio)
        ->where('mesas.mes_est', 'Libre')
        ->where(function($query) {
            $query->where('mesas.ind_union', '0')
                  ->orWhereNull('mesas.ind_union');
        })
        ->orderBy('pisos.pis_nom', 'asc')
        ->orderBy('mesas.mes_nom', 'asc')
        ->get();

    return response()->json([
        'vista' => view('empresas.kiosko.partials.mesas_desocupadas_list', [
            'mesas_desocupadas' => $mesas_para_el_parcial
        ])->render()
    ]);
}

    public function buscarMesasDesocupadas()
    {
        $id_empresa_negocio = Auth::user()->id_empresa_negocio;

        $mesas_desocupadas = DB::table('mesas')
            ->join('pisos', 'mesas.pis_id', '=', 'pisos.pis_id') // Nota: Si la llave primaria de tu tabla pisos es 'id', cámbialo a 'pisos.id'
            ->select('mesas.*', 'pisos.pis_nom')
            ->where('mesas.id_empresa_negocio', $id_empresa_negocio)
            ->where('mesas.mes_est', 'Libre')
            ->where('mesas.ind_union', '0')
            ->orderBy('mesas.mes_nom', 'asc')
            ->get();

        return response()->json([
            'vista' => view('empresas.kiosko.partials.mesas_desocupadas_list', compact('mesas_desocupadas'))->render()
        ]);
    }

    public function buscarMesasDesocupadasUnir($mesa_actual_id)
    {
        $id_empresa_negocio = Auth::user()->id_empresa_negocio;
        $mesas_para_unir = DB::table('mesas')
            ->where('id_empresa_negocio', $id_empresa_negocio)
            ->where('mes_est', 'Ocupado')
            ->where('mes_id', '!=', $mesa_actual_id)
            ->where('ind_union', '0')
            ->orderBy('mes_nom', 'asc')
            ->get();
        return response()->json([
            'vista' => view('empresas.kiosko.partials.mesas_para_unir_list', compact('mesas_para_unir'))->render()
        ]);
    }

    public function getTakeAwayAndDeliveryOrdersForDisplay()
    {
        $id_empresa_negocio = Auth::user()->id_empresa_negocio;

        $pedidos = DB::table('pedidos')
            ->select('ped_id', 'ped_tip', 'ped_cli_nom', 'ped_tel', 'fecha_hora', 'ped_tot')
            ->where('id_empresa_negocio', $id_empresa_negocio)
            ->whereIn('ped_tip', ['Llevar', 'Delivery'])
            ->whereIn('ped_est', ['Aperturado', 'Ocupado', 'En Preparacion', 'Impreso']) // Pedidos activos
            ->orderBy('fecha_hora', 'asc') // Ordenar por el más antiguo primero
            ->get();

        return response()->json(['success' => true, 'pedidos' => $pedidos]);
    }

    public function indexKiosko()
    {
        // 1. Datos de usuario
        $user = Auth::user();
        $id_empresa = $user->IdEmpresa;

        // 2. Cargar Categorías
        $categorias = DB::table('categorias')
            ->where('id_empresa_negocio', $user->id_empresa_negocio)
            ->where('visible', '1')
            ->get();

        // 3. Cargar Productos (Traemos todo con el select *)
        $productos = DB::table('productos')
            ->where('IdEmpresa', $id_empresa)
            ->select('*') 
            ->get();

        // ---------------------------------------------------------
        // LÓGICA DE PRECIOS DINÁMICOS Y DESCUENTO MANUAL
        // ---------------------------------------------------------
        $now = \Carbon\Carbon::now();
        $fecha_actual = $now->toDateString();
        $hora_actual = $now->toTimeString();
        $dia_semana_actual = $now->dayOfWeekIso;

        // Consultamos precios por horario/día
        $precios_especiales = DB::table('precios_dia_semana')
            ->where('id_empresa_negocio', $user->id_empresa_negocio)
            ->where('estado', 'Activo')
            ->where('dia_semana', $dia_semana_actual)
            ->where(function($query) use ($fecha_actual) {
                $query->whereNull('fecha_inicio_vigencia')
                      ->orWhere('fecha_inicio_vigencia', '<=', $fecha_actual);
            })
            ->where(function($query) use ($fecha_actual) {
                $query->whereNull('fecha_fin_vigencia')
                      ->orWhere('fecha_fin_vigencia', '>=', $fecha_actual);
            })
            ->where(function($query) use ($hora_actual) {
                $query->whereNull('hora_inicio_vigencia')
                      ->orWhere('hora_inicio_vigencia', '<=', $hora_actual);
            })
            ->where(function($query) use ($hora_actual) {
                $query->whereNull('hora_fin_vigencia')
                      ->orWhere('hora_fin_vigencia', '>=', $hora_actual);
            })
            ->get()
            ->keyBy('IdProducto');

        // 4. Aplicar los descuentos
        foreach ($productos as $producto) {
            // Primero: ¿Tiene precio dinámico programado para hoy/ahora?
            if (isset($precios_especiales[$producto->IdProducto])) {
                $precio_base = $precios_especiales[$producto->IdProducto]->precio_especial;
            } else {
                // CORRECCIÓN: Buscamos 'propun' o 'pre_ven' si 'precio' no existe en la BD
                $precio_base = $producto->propun ?? $producto->pre_ven ?? $producto->precio ?? 0;
            }

            // Segundo: ¿Tiene el check de 50% de descuento activo en el administrador?
            if (($producto->mitad_precio ?? 0) == 1) {
                $precio_base = $precio_base / 2;
            }

            // Seteamos el precio final en todas las variables que usa el sistema
            $producto->precio = $precio_base;  
            $producto->propun = $precio_base; 
            $producto->pre_ven = $precio_base; 
        }

        return view('empresas.kiosko.index', compact('categorias', 'productos'));
    }

    public function getEntradasKiosko()
        {
            // Auth::user() funciona igual en 5.6
            $id_empresa_negocio = Auth::user()->id_empresa_negocio;

            $entradas = DB::table('productos as p')
                ->join('producto_empresa as pe', 'pe.IdProducto', '=', 'p.IdProducto')
                ->select('p.IdProducto', 'p.pronom as nombre')
                ->where('pe.id_empresa_negocio', $id_empresa_negocio)
                ->where('p.promocion', '5') 
                ->orderBy('p.pronom', 'asc')
                ->get();

            // response()->json() es estándar desde hace muchas versiones
            return response()->json($entradas);
        }

        public function getCombosKiosko()
        {
            $id_empresa_negocio = Auth::user()->id_empresa_negocio;

            $combos = DB::table('productos as p')
                ->join('producto_empresa as pe', 'pe.IdProducto', '=', 'p.IdProducto')
                ->select('p.IdProducto', 'p.pronom as nombre')
                ->where('pe.id_empresa_negocio', $id_empresa_negocio)
                ->where('p.promocion', '6') // Filtramos por promoción 6
                ->orderBy('p.pronom', 'asc')
                ->get();

            return response()->json($combos);
        }

    public function enviarComandaKiosko(Request $request)
    {
        DB::beginTransaction();
        try {
            $user = Auth::user();
            
            // 1. Crear Cabecera del Pedido
            $pedido = new pedidos();
            $pedido->IdEmpresa = $user->IdEmpresa;
            $pedido->id_empresa_negocio = $user->id_empresa_negocio;
            $pedido->ped_cli_nom = $request->input('nombre_cliente', 'Cliente Rapido');
            $pedido->ped_fec = date('Y-m-d');
            $pedido->fecha_hora = date('Y-m-d H:i:s');
            $pedido->ped_est = 'Aperturado';
            $pedido->ped_tip = 'Llevar';
            $pedido->tdicod = '1';
            $pedido->icbper_val = '0.50';
            $pedido->icbper_tot = '0.00';
            $pedido->ped_tot = $request->input('total_venta');
            $pedido->IdUsuario = $user->IdUsuario;
            $pedido->mozo = $user->IdUsuario;
            $pedido->save();

            // CORRECCIÓN 1: Preparamos el array para imprimir
            $itemsToPrint = []; 

            // 2. Guardar Detalles
            $items = $request->input('items');
            foreach ($items as $item) {
                $detalle = new pedidos_detalle();
                $detalle->ped_id = $pedido->ped_id;
                $detalle->IdEmpresa = $user->IdEmpresa;
                $detalle->impreso = 'impreso'; 
                $detalle->mod_cant = '0'; 
                $detalle->icbper_ind = '0'; 
                $detalle->IdProducto = $item['id'];
                $detalle->detalle = $item['nombre'];
                $detalle->descripcion = $item['nombre'];
                $detalle->ped_det_can = $item['cantidad'];
                $detalle->ped_det_pre = $item['precio'];
                $detalle->item_obs = isset($item['observacion']) ? $item['observacion'] : '';
                $detalle->fecha_hora = date('Y-m-d H:i:s');
                $detalle->estadoitem = 'Ingresado';
                
                $detalle->save();

                // CORRECCIÓN 2: Llenamos el array con el formato que pide tu sistema
                // (Copiado de tu lógica antigua)
                $itemsToPrint[] = (object)[
                    'descripcion' => $item['nombre'],
                    'ped_det_can' => $item['cantidad'],
                    'item_obs'    => isset($item['observacion']) ? $item['observacion'] : '',
                    'ped_det_id'  => $detalle->ped_det_id,
                    'IdProducto'  => $item['id'],
                ];
            }

            DB::commit();

            // 3. Imprimir Comanda
            try {
                // CORRECCIÓN 3: Le enviamos la lista de items, NO el texto 'kiosko'
                $this->imprimirComandaPorCategorias($pedido->ped_id, $itemsToPrint);
                
            } catch (\Exception $e) {
                Log::error("Error impresión Kiosko: " . $e->getMessage());
            }

            // 4. Retornar URL
            return response()->json([
                'status' => 'success',
                'redirect_url' => url('cobrarmesa/' . $pedido->ped_id)
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }

    public function solicitar_cs(Request $request) {
        // Marcamos el pedido con la solicitud
        DB::table('pedidos')
            ->where('ped_id', $request->pedido_id)
            ->update(['ped_sol_cs' => 1]);

        return response()->json(['success' => true, 'message' => 'Aviso enviado a caja']);
    }


    public function verComprobanteTicket($id_cpe_cabecera)
    {
        try {
            $cabecera = cpe_cabecera::findOrFail($id_cpe_cabecera);
            $detalle = cpe_detalle::where('IdCpe_cabecera', $id_cpe_cabecera)->get();
            $empresa = Empresa::findOrFail($cabecera->IdEmpresa);
            $sucursal = EmpresaNegocios::findOrFail($cabecera->id_empresa_negocio);

            // Obtener el nombre del vendedor (si existe)
            $data_vendedor = null;
            if ($cabecera->IdUsuario_ven) {
                $data_vendedor = DB::table('users')->where('IdUsuario', $cabecera->IdUsuario_ven)->first();
            }

            // Obtener el nombre del cajero (quien registra)
            $data_cajero = DB::table('users')->where('IdUsuario', $cabecera->IdUsuario)->first();

            // Obtener medios de pago
            $medios = DB::table('venta_medio_pago')
                        ->join('medios_pagos', 'medios_pagos.id_med_pag', '=', 'venta_medio_pago.id_med_pag')
                        ->where('IdCpe_cabecera', $cabecera->IdCpe_cabecera)
                        ->get();

            // Lógica para convertir a letras (como la que ya tienes en imprimirTicketVenta)
            $moneda_info = DB::table('moneda')->where('moncod', $cabecera->moncod)->first();
            $totalletras = (new MontoLetras())->convertir(number_format($cabecera->ccaitv, 2, '.', ''), $moneda_info->monnom, 'Centimos');

            $tipo_documento_info = DB::table('tipo_documento')->where('tdocod', $cabecera->tdocod)->first();
            $tdodes_display = $tipo_documento_info->tdodes ?? 'DOCUMENTO'; // Asigna la descripción a una variable

            $imgqr = 'qr/QR-'.$empresa->IdEmpresa.'-'.$cabecera->tdocod.'-'.$cabecera->serdoc.'-'.str_pad($cabecera->numdoc, 8, "0", STR_PAD_LEFT).'.png';

            return view('formatos_comprobantes.ticket_factura', compact('cabecera', 'detalle', 'empresa', 'sucursal', 'data_vendedor', 'data_cajero', 'medios','tdodes_display', 'totalletras', 'imgqr'));

        } catch (\Exception $e) {
            Log::error("Error al intentar ver comprobante ticket #{$id_cpe_cabecera}: " . $e->getMessage());
            return response()->json(['error' => 'No se pudo cargar el comprobante para previsualizar.'], 500);
        }
    }


    public function checkNewTakeAwayOrders(Request $request)
    {
        $id_empresa_negocio = Auth::user()->id_empresa_negocio;

        $new_take_away_orders = DB::table('pedidos')
                                  ->where('id_empresa_negocio', $id_empresa_negocio)
                                  ->where('ped_tip', 'Llevar')
                                  ->where('notificado_caja', 0)
                                  ->whereIn('ped_est', ['Aperturado', 'Cerrado', 'Unido'])
                                  ->get();

        if ($new_take_away_orders->isNotEmpty()) {
            DB::table('pedidos')
              ->whereIn('ped_id', $new_take_away_orders->pluck('ped_id'))
              ->update(['notificado_caja' => 1]);

            return response()->json(['new_orders' => true, 'count' => $new_take_away_orders->count()]);
        }

        return response()->json(['new_orders' => false]);
    }

public function buscarCliente(Request $request)
{
    $term = $request->get('term');

    $clientes = \DB::table('cliente')
        ->where('clinom', 'like', "%{$term}%")
        ->orWhere('telefono', 'like', "%{$term}%")
        ->orWhere('clinum', 'like', "%{$term}%")
        ->take(10)
        ->get(['clicod', 'clinom', 'clinum', 'telefono', 'clidir']);

    $resultados = [];
    foreach ($clientes as $c) {
        $resultados[] = [
            'id' => $c->clicod,
            'label' => $c->clinom . ' - ' . ($c->telefono ?: 'Sin teléfono'),
            'nombre' => $c->clinom,
            'documento' => $c->clinum,
            'telefono' => $c->telefono,
            'direccion' => $c->clidir,
        ];
    }

    return response()->json($resultados);
}



    public function imprimirTicketVenta($id_cpe_cabecera)
    {
        $id_empresa_negocio = Auth::user()->id_empresa_negocio;
        // ¡FIX: Definir rucemp aquí!
        $rucemp = Auth::user()->IdEmpresa; 
    
        // Cargar la cabecera del comprobante
        $cabecera = cpe_cabecera::findOrFail($id_cpe_cabecera);
        $detalles = cpe_detalle::where('IdCpe_cabecera', $id_cpe_cabecera)->get();
        $empresa = Empresa::findOrFail($cabecera->IdEmpresa);
        $negocio = EmpresaNegocios::findOrFail($cabecera->id_empresa_negocio); // La variable es $negocio
        
        // Obtener la información del vendedor
        $bus_vendedor_user = DB::table('users')->where('IdUsuario', $cabecera->IdUsuario_ven)->first();
        $nombre_vendedor = ($bus_vendedor_user->name ?? '') . ' ' . ($bus_vendedor_user->apeusu ?? '');

        // Obtener el símbolo de la moneda y la descripción del tipo de documento
        $moneda_info = DB::table('moneda')->where('moncod', $cabecera->moncod)->first();
        $simbolo = $moneda_info->simbolo ?? 'S/'; // Símbolo de la moneda

        $tipo_documento_info = DB::table('tipo_documento')->where('tdocod', $cabecera->tdocod)->first();
        $tdodes_display = $tipo_documento_info->tdodes ?? 'DOCUMENTO'; // Descripción del tipo de documento

        // Obtener los medios de pago
        $medios = DB::table('venta_medio_pago')
                      ->join('medios_pagos', 'medios_pagos.id_med_pag', '=', 'venta_medio_pago.id_med_pag')
                      ->where('IdCpe_cabecera', $cabecera->IdCpe_cabecera)
                      ->get();

        // Inicializar MontoLetras
        $montoLetras = new MontoLetras();
        // Asegurarse que number_format no devuelva una cadena con comas si se usa para el cálculo.
        // La función convertir ya lo formatea internamente para letras.
        $totalletras = $montoLetras->convertir(number_format($cabecera->ccaitv, 2, '.', ''), $moneda_info->monnom, 'Centimos'); // Usar $moneda_info->monnom

        $impresora_caja = DB::table('configuracion_impresoras')
                            ->where('id_empresa_negocio', $id_empresa_negocio)
                            ->where('predeterminado', '1')
                            ->first();
    
        if (!$impresora_caja) {
            Log::error("Impresión Venta Directa: No se encontró impresora predeterminada para el negocio ID: " . $id_empresa_negocio);
            return; // Detiene la ejecución si no hay impresora
        }
    
        try {
            $connector = null;
            if ($impresora_caja->tip_conex_imp == 'COMPARTIDO') {
                $connector = new WindowsPrintConnector("smb://" . $impresora_caja->ruta);
            } elseif ($impresora_caja->tip_conex_imp == 'RED') {
                $connector = new NetworkPrintConnector($impresora_caja->ruta, 9100);
            }
    
            if (!$connector) {
                Log::error("Impresión Venta Directa: Tipo de conexión no soportado: " . $impresora_caja->tip_conex_imp);
                return; // Detiene la ejecución si el conector no es válido
            }
    
            $printer = new Printer($connector);
            $printer->setJustification(Printer::JUSTIFY_CENTER);
            
            // LOGO (si existe)
            if (!empty($negocio->logosuc) && file_exists(public_path('/' . $negocio->logosuc))) {
                try {
                    $logo = EscposImage::load(public_path('/' . $negocio->logosuc), false);
                    $printer->bitImage($logo);
                    $printer->feed();
                } catch (\Exception $e) { Log::error("Error al cargar logo para ticket: " . $e->getMessage()); }
            }
    
            $printer->setEmphasis(true);
            $printer->text($empresa->NomEmpresa . "\n");
            $printer->setEmphasis(false);
            if($negocio->nombre_comercial) $printer->text($negocio->nombre_comercial . "\n");
            $printer->text("RUC: " . $empresa->IdEmpresa . "\n");
            $printer->text($negocio->direccion . "\n");
            $printer->text($negocio->distrito . " - " . $negocio->provincia . " - " . $negocio->departamento. "\n");
            if($negocio->telefono) $printer->text("Teléfono: " . $negocio->telefono . "\n");
            $printer->feed(); // Salto de línea
    
            $printer->setEmphasis(true);
            // Tipo de comprobante en mayúsculas y negrita
            $printer->text(strtoupper($tdodes_display) . "\n"); 
            $printer->text($cabecera->serdoc . " - " . str_pad($cabecera->numdoc, 8, "0", STR_PAD_LEFT) . "\n");
            $printer->setEmphasis(false);
            $printer->feed(); // Salto de línea
    
            $printer->setJustification(Printer::JUSTIFY_LEFT);
            $printer->text("Fecha: " . Carbon::parse($cabecera->ccafem)->format('d/m/Y H:i:s') . "\n");
            $printer->text("Vendedor: " . $nombre_vendedor . "\n"); // Muestra el nombre del vendedor
            $printer->text("Cliente: " . $cabecera->ccanom . "\n");
            if($cabecera->tdicod == '6'){ $printer->text("RUC: " . $cabecera->ccandi . "\n"); } 
            else if ($cabecera->tdicod == '1'){ $printer->text("DNI: " . $cabecera->ccandi . "\n"); }
            if($cabecera->direccion && $cabecera->direccion != '--') $printer->text("Dirección: " . $cabecera->direccion . "\n");
            
            // Mostrar Condición de Pago
            $printer->text("Condición de Pago: " . ($cabecera->estadopago ?? 'N/A') . "\n"); 
            
            // Línea separadora a 60 caracteres
            $printer->text("------------------------------------------------------------\n");
            
            // Cabecera de la tabla de productos (60 caracteres de ancho total)
            $printer->text("Descripción                      Cant. U.M. P.U.    Total\n"); 
            
            // Línea de guiones
            $printer->text("------------------------------------------------------------\n");
            
            foreach ($detalles as $detalle) {
                $printer->setJustification(Printer::JUSTIFY_LEFT);
                
                // Descripción del producto (primera línea del detalle)
                $description_display = strtoupper($detalle->cdedes);
                // Si la descripción es más larga de lo que permitimos en la línea (60 caracteres),
                // la truncamos para evitar desbordes. Ajusta '57' si necesitas más o menos.
                if (mb_strlen($description_display, 'UTF-8') > 60) { // Usar mb_strlen para caracteres UTF-8
                    $description_display = mb_substr($description_display, 0, 57, 'UTF-8') . "..."; 
                }
                $printer->text($description_display . "\n");
                
                // Cantidad, U.M., P.U., y Total en una segunda línea, con alineación manual
                // Ajusta los espacios con " " para que se vean bien en tu impresora
                $cant_formatted = number_format($detalle->cdecan, 2); 
                $um_formatted   = $detalle->umecod;
                $pu_formatted   = number_format($detalle->cdepuni, 2); 
                $total_formatted= number_format($detalle->cdevve, 2); 

                $printer->text(
                    str_pad($cant_formatted, 30, " ", STR_PAD_LEFT) . // Cantidad (ej. "   1.00")
                    " " . // Espacio
                    str_pad($um_formatted, 4, " ", STR_PAD_RIGHT) . // U.M. (ej. "NIU ")
                    " " . // Espacio
                    str_pad($pu_formatted, 8, " ", STR_PAD_RIGHT) . // P.U. (ej. "10.00   ")
                    "  " . // Espacio
                    str_pad($total_formatted, 10, " ", STR_PAD_LEFT) . // Total (ej. "     10.00")
                    "\n"
                );
            }
            
            // Línea separadora
            $printer->text("------------------------------------------------------------\n");

            // MEDIOS DE PAGO (Listar todos los medios de pago)
            $printer->setJustification(Printer::JUSTIFY_LEFT); // Alinea el texto a la izquierda
            foreach ($medios as $m) {
                $left_text = "Medio Pago: " . $m->nom_med_pag;
                $amount_text = $simbolo . " " . number_format($m->monto, 2, '.', ',');
                // Rellenar con espacios hasta el ancho total (60)
                $padding_needed = 60 - mb_strlen($left_text, 'UTF-8') - mb_strlen($amount_text, 'UTF-8'); // Usar mb_strlen
                $printer->text($left_text . str_repeat(" ", $padding_needed) . $amount_text . "\n");
            }

            // Totales (Gravada, Exonerada, IGV, ICBPER)
            $printer->setJustification(Printer::JUSTIFY_RIGHT); // Alinea los montos a la derecha

            if($cabecera->ccatvg > 0){
                $label_gravada = "GRAVADA: " . $simbolo;
                $value_gravada = number_format($cabecera->ccatvg, 2, '.', ',');
                $line_gravada = str_pad($label_gravada, 60 - mb_strlen($value_gravada, 'UTF-8'), " ", STR_PAD_RIGHT) . $value_gravada;
                $printer->text($line_gravada . "\n");
            }
            if($cabecera->ccatexo > 0){ 
                $label_exo = "EXONERADA: " . $simbolo;
                $value_exo = number_format($cabecera->ccatexo, 2, '.', ',');
                $line_exo = str_pad($label_exo, 60 - mb_strlen($value_exo, 'UTF-8'), " ", STR_PAD_RIGHT) . $value_exo;
                $printer->text($line_exo . "\n");
            }
            // IGV siempre se muestra (descomentado de tu código anterior)
            $label_igv = "IGV: " . $simbolo;
            $value_igv = number_format($cabecera->ccaigv, 2, '.', ',');
            $line_igv = str_pad($label_igv, 60 - mb_strlen($value_igv, 'UTF-8'), " ", STR_PAD_RIGHT) . $value_igv;
            $printer->text($line_igv . "\n");


            if($cabecera->tot_icbper > 0){ 
                $label_icbper = "ICBPER: " . $simbolo;
                $value_icbper = number_format($cabecera->tot_icbper, 2, '.', ',');
                $line_icbper = str_pad($label_icbper, 60 - mb_strlen($value_icbper, 'UTF-8'), " ", STR_PAD_RIGHT) . $value_icbper;
                $printer->text($line_icbper . "\n");
            }
            
            // TOTAL (doble tamaño y negrita)
            $printer->setEmphasis(true);
            $printer->setTextSize(2, 2); // Ancho normal, alto doble
            $label_total = "TOTAL: " . $simbolo;
            $value_total = number_format($cabecera->ccaitv, 2, '.', ',');
            $line_total = str_pad($label_total, 60 - mb_strlen($value_total, 'UTF-8'), " ", STR_PAD_RIGHT) . $value_total;
            $printer->text($line_total . "\n");
            $printer->setTextSize(1, 1); // Volver a tamaño normal
            $printer->setEmphasis(false);

            // PAGA CON
            if ($cabecera->paga != 0) {
                $printer->setJustification(Printer::JUSTIFY_RIGHT); // Mantener alineación a la derecha
                $label_paga = "PAGA CON: " . $simbolo;
                $value_paga = number_format($cabecera->paga, 2, '.', ',');
                $line_paga = str_pad($label_paga, 60 - mb_strlen($value_paga, 'UTF-8'), " ", STR_PAD_RIGHT) . $value_paga;
                $printer->text($line_paga . "\n");
            }

            // VUELTO
            if ($cabecera->vuelto != 0) {
                $printer->setJustification(Printer::JUSTIFY_RIGHT); // Mantener alineación a la derecha
                $label_vuelto = "VUELTO: " . $simbolo;
                $value_vuelto = number_format($cabecera->vuelto, 2, '.', ',');
                $line_vuelto = str_pad($label_vuelto, 60 - mb_strlen($value_vuelto, 'UTF-8'), " ", STR_PAD_RIGHT) . $value_vuelto;
                $printer->text($line_vuelto . "\n");
            }
            $printer->feed(); // Salto de línea después del vuelto
            
            // Monto en letras (formato con centavos como fracción)
            $printer->setJustification(Printer::JUSTIFY_LEFT); // Alineación izquierda
            $printer->text("SON: " . $totalletras . " SOLES". "\n"); 
            $printer->feed(); 

            // Representación dinámica del documento (ajustado a 60 caracteres y con el nombre correcto)
            $printer->setJustification(Printer::JUSTIFY_CENTER);
            $printer->setFont(Printer::FONT_B); // Fuente más condensada si quieres que quepa en menos espacio

            $representacion_text = "REPRESENTACIÓN IMPRESA DE LA " . strtoupper($tdodes_display) . " ELECTRÓNICA";
            // Si el texto es más largo que 60 caracteres, lo truncamos o lo dividimos en dos líneas
            if (mb_strlen($representacion_text, 'UTF-8') > 60) { // Usar mb_strlen para caracteres UTF-8
                $representacion_text = mb_substr($representacion_text, 0, 57, 'UTF-8') . "...";
                $printer->text($representacion_text . "\n");
            } else {
                $printer->text($representacion_text . "\n");
            }
            $printer->feed(); // Salto de línea después del texto de representación
            
            
            // Mensaje de Amazonia y pie de página (Si aplica)
            if($cabecera->tdocod !='13'){  
                $printer->setFont(Printer::FONT_B);
                $printer->text("\nBIENES TRANSFERIDOS EN LA AMAZONIA PARA SER CONSUMIDOS EN LA MISMA. SERVICIOS PRESTADOS EN LA AMAZONIA\n");
                $printer->setJustification(Printer::JUSTIFY_CENTER);
              //  $printer->text("\n".$negocio->pie."\n"); // CORRECCIÓN AQUÍ: $negocio->pie en lugar de $empresanegocios->pie
            }

            // Sección de cuotas (si aplica)
            if($cabecera->estadopago=='CREDITO'){
                $printer->setJustification(Printer::JUSTIFY_CENTER);
                $printer->text("CUOTAS\n");
                $printer->setJustification(Printer::JUSTIFY_LEFT);
                $printer->text(" #   F.VENCI.    MONEDA    MONTO\n"); // Ajusta el ancho de las columnas
                $printer->text("------------------------------------------------------------\n"); // Línea de guiones para cuotas (60 caracteres)
                // Es necesario cargar las cuotas para la cabecera actual.
                $cuotas = DB::table('ventas_cuotas')->where('IdCpe_cabecera', $cabecera->IdCpe_cabecera)->get();
                foreach ($cuotas as $cuota){
                    // Ajusta estos str_pad para que sumen 60 caracteres de ancho total por línea
                    $num_cuota = str_pad($cuota->ven_cuo_num, 3, " ", STR_PAD_RIGHT);
                    $fec_venc = str_pad(Carbon::parse($cuota->ven_cuo_fec_ven)->format('d/m/Y'), 12, " ", STR_PAD_RIGHT);
                    $moneda_cuota = str_pad($cabecera->moncod, 9, " ", STR_PAD_RIGHT); // Ej: PEN
                    $monto_cuota = str_pad(number_format($cuota->ven_cuo_mon, 2), 10, " ", STR_PAD_LEFT);
                    $printer->text($num_cuota . "  " . $fec_venc . "  " . $moneda_cuota . "  " . $monto_cuota . "\n");
                }  
            }
        
            $printer->feed(); // Un salto de línea final
            $printer->cut(); // Cortar papel
            $printer->pulse(); // Abrir cajón de dinero
            $printer->close(); // Cerrar conexión con la impresora

        } catch (\Exception $e) {
            // Aquí agregamos más detalles al log para saber exactamente qué falló
            Log::error("Error al imprimir ticket de venta #{$id_cpe_cabecera}: " . $e->getMessage() . " en el archivo " . $e->getFile() . " en la línea " . $e->getLine());
            // Puedes agregar un mensaje al usuario aquí si es necesario, pero evita 'alert()'
        }
    }

    public function registrarVentaDirecta(Request $request){

        $rucemp = trim(Auth::user()->IdEmpresa);
        $empresa = Empresa::findOrFail($rucemp);

        $mozo = $request->get('mozo');
        $tdicod = $request->get('tdicod');
        $imprimir = $request->get('imprimir'); // This variable is not used in your code, remove if not needed.
        $consumo = $request->get('consumo'); // This variable is not used in your code, remove if not needed.

        // Estos son para pagos a crédito, no para medios de pago al contado
        $mon_cuo = $request->get('mon_cuo');
        $fec_cuo = $request->get('fec_cuo');

        $clinum = $request->get('clinum');
        $clinom = $request->get('clinom');
        $clidir = $request->get('clidir');
        $clicor = $request->get('clicor');

        $mondoc = 'PEN';
        $observaciones = $request->get('observaciones'); // This variable is not used in your code, remove if not needed.

        $ped_id = $request->get('ped_id'); // This variable is not used in your code, remove if not needed.
        $total_venta = $request->get('total_venta');
        $vuelto = $request->get('vuelto');

        $tdocod = $request->get('tdocod');
        $estadopago = $request->get('estadopago'); // Condición de pago (CONTADO/CRÉDITO)
        $fecEmi = $request->get('fecEmi');
        $fecVen = $request->get('fecVen');

        // Petición 2: Múltiples medios de pago
        $id_med_pag_array = $request->get('id_med_pag');   // Array de IDs de medios de pago
        $mon_med_pag_array = $request->get('mon_med_pag'); // Array de montos para cada medio de pago

        $items = $request->get('txt_id_producto');
        $cantidades = $request->get('txt_cantidad');
        $precios = $request->get('precios');
        $descripciones_item = $request->get('descripcion'); // Descripciones para detalles
        $observaciones_item = $request->get('item_obs');   // Observaciones para detalles
        $icbper_ind_item = $request->get('icbper_ind');   // ICBPER indicador para detalles

        $buscre = DB::table('credito_dias')->where('cre_dia_id',$estadopago)->first();

        $sucursal = DB::table('empresa_negocios')->where('id_empresa_negocio',Auth::user()->id_empresa_negocio)->first();

        $bus_alm= DB::table('almacenes')->where('id_empresa_negocio',Auth::user()->id_empresa_negocio)->where('predeterminado','1')->first();

        $cont_carac = strlen($clinum);
        $obt_dig = substr(trim($clinum), 0, 2);

        if($tdocod=='01'){
            if($cont_carac =='11' && ($obt_dig=='10' || $obt_dig=='20' || $obt_dig=='15' || $obt_dig=='17') && $tdicod=='6'){
                // RUC válido
            }else{
                return response()->json(['estado'=>'error','mensaje'=>'TIPO DE DOCUMENTO NO PERMITIDO PARA EMITIR UNA FACTURA']);
            }
        }

        $empresanegocio = EmpresaNegocios::findOrFail($sucursal->id_empresa_negocio);

        // Lógica de numeración de comprobantes
        if($tdocod == '01'){
            $senudoc = DB::table('empresa_negocios')->select('FseEmpresa','FnuEmpresa')->where('id_empresa_negocio',$sucursal->id_empresa_negocio)->first();
            $numcomp =  $senudoc->FnuEmpresa+1;
            $sercomp =  $senudoc->FseEmpresa;
        }elseif ($tdocod =='03') {
            $senudoc = DB::table('empresa_negocios')->select('BseEmpresa','BnuEmpresa')->where('id_empresa_negocio',$sucursal->id_empresa_negocio)->first();
            $numcomp =  $senudoc->BnuEmpresa+1;
            $sercomp =  $senudoc->BseEmpresa;
        }elseif ($tdocod =='13') {
            $senudoc = DB::table('empresa_negocios')->select('SerNota','NumNota')->where('id_empresa_negocio',$sucursal->id_empresa_negocio)->first();
            $numcomp =  $senudoc->NumNota+1;
            $sercomp =  $senudoc->SerNota;
        }elseif ($tdocod =='15') {
            $senudoc = DB::table('empresa_negocios')->select('ProNum','ProSer')->where('id_empresa_negocio',$sucursal->id_empresa_negocio)->first();
            $numcomp =  $senudoc->ProNum+1;
            $sercomp =  $senudoc->ProSer;
        }elseif ($tdocod =='14') {
            $senudoc = DB::table('empresa_negocios')->select('NumVal','SerVal')->where('id_empresa_negocio',$sucursal->id_empresa_negocio)->first();
            $numcomp =  $senudoc->NumVal+1;
            $sercomp =  $senudoc->SerVal;
        }

        $cliente = Cliente::updateOrCreate(['clinum'=>$clinum],['clinom'=>$clinom,'clidir'=>$clidir,'clicor'=>$clicor,'tdicod'=>$tdicod,'telefono'=>$request->get('clitel'),'rucemp'=>Auth::user()->IdEmpresa]); 

        $cabecera = new cpe_cabecera;
        $cabecera->tdocod = $tdocod;
        $cabecera->ccafem = $fecEmi;
        $cabecera->topcod = '0101';
        $cabecera->id_almacen = $bus_alm->id_almacen;

        if($buscre->cre_dia_tip=='CONTADO' || $buscre->cre_dia_tip=='PERSONALIZADO'){
            $cabecera->ccafve = $fecVen;
        }else{
            $cabecera->ccafve = Carbon::parse($fecEmi)->addDays($buscre->cre_dia_fac)->format('Y-m-d');
        }

        if($buscre->cre_dia_tip=='CONTADO'){
            $cabecera->totalcontado = $total_venta;
            $cabecera->totalcredito = '0';
        }elseif($buscre->cre_dia_tip=='CREDITO' || $buscre->cre_dia_tip=='PERSONALIZADO'){
            $cabecera->totalcredito = $total_venta;
            $cabecera->totalcontado = '0';
        }
      
        $cabecera->ccaobs = $observaciones; // Assign $observaciones (from request) to ccaobs.
        $cabecera->consumo = null; // Assuming 'consumo' field is not directly from request in this context
        $cabecera->tdicod = $tdicod;
        $cabecera->ccandi = $clinum;
        $cabecera->ccanom = $clinom;
        $cabecera->ped_id = null; // Assuming 'ped_id' is not directly from request in this context
        $cabecera->moncod = $mondoc;
        $cabecera->direccion = $clidir;
        $cabecera->clicorcli = $clicor;
        $cabecera->cre_dia_id = $estadopago;
        $cabecera->id_turno = Auth::user()->id_turno;
  
        if($sucursal->tip_igv_pred =='10'){
            $cabecera->ccatvg =  $total_venta;
            $cabecera->ccaigv =  $total_venta-$total_venta/1.1055;
        }

        if($sucursal->tip_igv_pred =='20'){
            $cabecera->ccatexo =  $total_venta;
            $cabecera->ccaigv = '0.00';
        }
        
        $cabecera->ccatinaf =  '0.00';
        $cabecera->ccaitv = $total_venta;
        $cabecera->id_empresa_negocio = $sucursal->id_empresa_negocio;
        $cabecera->clicod = $cliente->clicod;
        $cabecera->vuelto = $vuelto;

        if($buscre->cre_dia_tip=='CONTADO'){
           $cabecera->estadopago = 'CONTADO';
        }else{
           $cabecera->estadopago = 'CREDITO';
        }
        
        $cabecera->IdUsuario = Auth::user()->IdUsuario;
        $cabecera->IdUsuario_ven = $mozo; 
        $cabecera->IdEmpresa =  Auth::user()->IdEmpresa;

        // Actualizar la numeración del negocio
        if($tdocod=='01'){
            if( $empresanegocio->FnuEmpresa == $numcomp){ $modnumcomp = $numcomp+1; }else{ $modnumcomp = $numcomp; }
            $empresanegocio->FseEmpresa = $sercomp;
            $empresanegocio->FnuEmpresa = $modnumcomp;
        }elseif($tdocod=='03'){
            if( $empresanegocio->BnuEmpresa == $numcomp){ $modnumcomp = $numcomp+1; }else{ $modnumcomp = $numcomp; }
            $empresanegocio->BseEmpresa = $sercomp;
            $empresanegocio->BnuEmpresa = $modnumcomp;
        }elseif($tdocod=='13'){
            if( $empresanegocio->NumNota == $numcomp){ $modnumcomp = $numcomp+1; }else{ $modnumcomp = $numcomp; }
            $empresanegocio->SerNota = $sercomp;
            $empresanegocio->NumNota = $modnumcomp;
        }elseif($tdocod=='15'){
            if( $empresanegocio->ProNum == $numcomp){ $modnumcomp = $numcomp+1; }else{ $modnumcomp = $numcomp; }
            $empresanegocio->ProSer = $sercomp;
            $empresanegocio->ProNum = $modnumcomp;
        }elseif($tdocod=='14'){
            if( $empresanegocio->NumVal == $numcomp){ $modnumcomp = $numcomp+1; }else{ $modnumcomp = $numcomp; }
            $empresanegocio->SerVal = $sercomp;
            $empresanegocio->NumVal = $modnumcomp;
        }

        $numdoc_padded = str_pad($modnumcomp,8,"0", STR_PAD_LEFT);
        $cabecera->serdoc = $sercomp;
        $cabecera->numdoc = $numdoc_padded;

        $empresanegocio->update(); 
        $cabecera->save(); // Save the cabecera to ensure IdCpe_cabecera is available.

        self::generar_codigo_movimiento($cabecera->IdCpe_cabecera);

        // Si la condición de pago es a CRÉDITO, se manejan las cuotas
        if($buscre->cre_dia_tip=='CREDITO' || $buscre->cre_dia_tip=='PERSONALIZADO'){
            if(!empty($mon_cuo)){
                foreach ($mon_cuo as $key => $mc){
                    DB::table('ventas_cuotas')->insert([
                        'ven_cuo_num' => $key + 1, // Número de cuota
                        'ven_cuo_fec_ven' => $fec_cuo[$key],
                        'ven_cuo_mon' => $mc,
                        'IdCpe_cabecera' => $cabecera->IdCpe_cabecera
                    ]);
                }
            } else {
                // Si es crédito pero no se definieron cuotas, se crea una única cuota por el total
                DB::table('ventas_cuotas')->insert([
                    'ven_cuo_num' => 1,
                    'ven_cuo_fec_ven' => $cabecera->ccafve,
                    'ven_cuo_mon' => $cabecera->ccaitv,
                    'IdCpe_cabecera' => $cabecera->IdCpe_cabecera
                ]);
            }
        }
        
        // Petición 2: Múltiples Medios de Pago
        if(!empty($id_med_pag_array)){
            foreach($id_med_pag_array as $index_mp => $id_medio){
                DB::table('venta_medio_pago')->insert([
                    'id_turno' => Auth::user()->id_turno,
                    'IdCpe_cabecera' => $cabecera->IdCpe_cabecera,
                    'id_med_pag' => $id_medio,
                    'monto' => $mon_med_pag_array[$index_mp]
                ]);
            }
        } else {
            // Esto no debería pasar si el frontend valida que haya al menos un medio de pago con monto > 0
            // Pero como fallback, si no hay medios de pago enviados y es CONTADO, asigna el total a un medio predeterminado
            if ($buscre->cre_dia_tip == 'CONTADO') {
                $bus_med_pag_pred = DB::table('medios_pagos')->where('id_empresa_negocio', $sucursal->id_empresa_negocio)->where('predeterminado', '1')->first();
                if ($bus_med_pag_pred) {
                    DB::table('venta_medio_pago')->insert([
                        'id_turno' => Auth::user()->id_turno,
                        'IdCpe_cabecera' => $cabecera->IdCpe_cabecera,
                        'id_med_pag' => $bus_med_pag_pred->id_med_pag,
                        'monto' => $total_venta
                    ]);
                }
            }
        }

        foreach($items as $index => $item_id){
            $dat_pro = productos::findOrFail($item_id);

            $id_prod_base = empty($dat_pro->pro_rel) ? $dat_pro->IdProducto : $dat_pro->pro_rel;
            // Get id_almacen_pro from the product, or use the default business almacén if not set.
            $id_almacen_pro = $bus_alm ? $bus_alm->id_almacen : null; 

            $precio_uni = $precios[$index];
            $cantidad_item = $cantidades[$index];
            $observacion_item = $observaciones_item[$index];
            $icbper_item = $icbper_ind_item[$index];
            $descripcion_item = $descripciones_item[$index];

            // Calcula valores según el tipo de IGV predeterminado del negocio
            if($sucursal->tip_igv_pred == '10'){ // Gravado
                $valor_uni = $precio_uni / 1.18;
                $valor_subtotal = ($precio_uni * $cantidad_item) / 1.18;
                $valor_total = $precio_uni * $cantidad_item;
                $valor_igv_total = $valor_total - $valor_subtotal;
            } elseif($sucursal->tip_igv_pred == '20'){ // Exonerado
                $valor_uni = $precio_uni;
                $valor_subtotal = $precio_uni * $cantidad_item;
                $valor_total = $precio_uni * $cantidad_item;
                $valor_igv_total = '0.00';
            } else { // Otros casos o por defecto
                $valor_uni = $precio_uni;
                $valor_subtotal = $precio_uni * $cantidad_item;
                $valor_total = $precio_uni * $cantidad_item;
                $valor_igv_total = '0.00';
            }
           
            $detalle = new cpe_detalle;
            $detalle->IdCpe_cabecera =  $cabecera->IdCpe_cabecera;
            $detalle->cdecan = $cantidad_item;
            $detalle->cdepuni = $precio_uni;
            $detalle->cdevun = $valor_uni;
            $detalle->cdevve  = $valor_total;
            $detalle->cdepve  = $valor_subtotal;
            $detalle->cdeigv = $valor_igv_total;
            $detalle->costo = $dat_pro->costo_total;
            $detalle->tigcod = $dat_pro->tigcod;
            $detalle->umecod = $dat_pro->umecod;
            $detalle->cpe_det_factor = $dat_pro->factor;
            $detalle->procod = $dat_pro->procod;
            $detalle->IdProducto = $item_id;
            $detalle->IdProducto_rel = $id_prod_base;
            $detalle->cdedes = $descripcion_item;
            $detalle->pronomobs = $observacion_item;
            $detalle->icbper = $icbper_item;
            $detalle->id_almacen_pro = $id_almacen_pro; // Assign the fetched almacén ID
            $detalle->save();
        }

        // Se registra la comanda (si aplica) y el movimiento de salida de stock IMPRIMIR COMANDA DIRECTA
        //$this->imprimir_comanda_venta_directa($cabecera->IdCpe_cabecera); // Asumo que esto aún se usa
        self::registrar_movimiento_salida($cabecera->IdCpe_cabecera);

        // Generación y envío a SUNAT si es necesario
        if($tdocod == '01' || $tdocod == '03'){
            $sunat = new cpe_cabecera;
            $sunat->generar_nuevo_qr($cabecera->IdCpe_cabecera);
            $sunat->generar_xml_boleta_factura($cabecera->IdCpe_cabecera);

            if($empresa->tipo_envio == '1'){
                $sunat->enviar_sunat($cabecera->IdCpe_cabecera); 
            }
        }

        // Registrar en tabla de usuario_facturacion
        $usuario_facturacion = new usuario_facturacion;
        $usuario_facturacion->IdCpe_cabecera = $cabecera->IdCpe_cabecera;
        $usuario_facturacion->id_turno = Auth::user()->id_turno;
        $usuario_facturacion->id_empresa_negocio = $sucursal->id_empresa_negocio;
        $usuario_facturacion->IdEmpresa = $rucemp;
        $usuario_facturacion->referencia = "Registro";
        $usuario_facturacion->save();

        // Lógica de impresión directa SIEMPRE en el backend si el formato es TICKET y ticket_pantalla es 0
        if ($sucursal->formato == 'TICKET' && $sucursal->ticket_pantalla == '0') {
            try {
                $empresa_data = DB::table('empresa')->where('IdEmpresa', Auth::user()->IdEmpresa)->first();
                $num_impresiones = $empresa_data ? $empresa_data->imp_venta : 1;
                
                for($i = 0; $i < $num_impresiones; $i++){
                    $this->imprimirTicketVenta($cabecera->IdCpe_cabecera); // Llama a la función de impresión física
                }

            } catch (\Exception $e) {
                Log::error("Fallo en el proceso de impresión directa post-venta para IdCpe_cabecera: {$cabecera->IdCpe_cabecera}. Error: " . $e->getMessage());
                // Puedes optar por no devolver un error al frontend aquí si la venta se registró correctamente,
                // pero sí loguear la falla de impresión.
            }
        }

        return response()->json(['estado'=>'success','codfact' =>$cabecera->IdCpe_cabecera,'tdocod'=>$tdocod,'mensaje'=>'Comprobante Emitido']);
    }

    public function cambiarMesa(Request $request)
    {
        $mes_id_actual = $request->input('mes_id_act');
        $mes_id_nueva = $request->input('mesas');
        $ped_id = $request->input('ped_id_act');

        if (empty($mes_id_actual) || empty($mes_id_nueva) || empty($ped_id)) {
            return response()->json(['success' => false, 'message' => 'Datos incompletos para cambiar mesa.']);
        }

        DB::beginTransaction();
        try {
            $pedido = pedidos::findOrFail($ped_id);
            $pedido->mes_id = $mes_id_nueva;
            $nueva_mesa_obj = mesas::findOrFail($mes_id_nueva);
            $pedido->pis_id = $nueva_mesa_obj->pis_id;
            $pedido->save();

            $nueva_mesa_obj->mes_est = 'Ocupado';
            $nueva_mesa_obj->save();

            $mesa_antigua_obj = mesas::findOrFail($mes_id_actual);
            $mesa_antigua_obj->mes_est = 'Libre';
            $mesa_antigua_obj->save();

            DB::commit();
            return response()->json(['success' => true, 'message' => 'Mesa cambiada exitosamente.']);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Error al cambiar mesa en Kiosko: " . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Error al cambiar la mesa: ' . $e->getMessage()]);
        }
    }

    private function calculateCartTotal($cart)
    {
        $total = 0;
        foreach ($cart as $item) {
            // Solo sumamos el precio de los componentes o productos individuales
            // No sumamos el 'precio' del ítem 'is_combo_main' si lo pusiste a 0
            if (!$item['is_combo_main']) {
                $total += $item['price'] * $item['quantity'];
            }
        }
        return $total;
    }

    

    public function registrar_movimiento_salida_comanda($ped_id){
            $pedido = pedidos::findOrFail($ped_id);
            $detalle_pedido = pedidos_detalle::where('ped_id', $ped_id)->get();
            $sucursal = DB::table('empresa_negocios')->where('id_empresa_negocio', Auth::user()->id_empresa_negocio)->first();
            $bus_alm = DB::table('almacenes')->where('id_empresa_negocio', Auth::user()->id_empresa_negocio)->where('predeterminado','1')->first();

            foreach($detalle_pedido as $det){
                $bus_pro = productos::findOrFail($det->IdProducto);

                $id_prod = empty($bus_pro->pro_rel) ? $bus_pro->IdProducto : $bus_pro->pro_rel;

                // Si es un producto simple
                if($bus_pro->promocion == '0'){
                    DB::table('movimientos_productos')->insert([
                        'IdProducto' => $det->IdProducto,
                        'IdProducto_rel' => $id_prod,
                        'precio' => $det->ped_det_pre,
                        'cantidad' => $det->ped_det_can * $bus_pro->factor,
                        'costo' => $bus_pro->costo,
                        'cliente' => $pedido->ped_cli_nom,
                        'IdCpe_cabecera' => null, // No hay comprobante de venta aún
                        'serie' => null,
                        'numero' => null,
                        'tdocod' => null,
                        'tipo' => '3', // Salida por venta/comanda
                        'mov_tip' => 'E', // Egreso
                        'id_empresa_negocio' => $pedido->id_empresa_negocio,
                        'id_almacen' => $bus_alm->id_almacen,
                        'fecha_mov' => $pedido->ped_fec,
                        'descripcion' => 'Salida por Comanda (Pedido ID: ' . $ped_id . ')',
                        'mov_lote' => $bus_pro->lote,
                        'mov_vencimiento' => $bus_pro->vencimiento,
                    ]);

                    $stock_prod = DB::table('producto_stock')
                        ->where('IdProducto', $id_prod)
                        ->where('id_almacen', $bus_alm->id_almacen)
                        ->first();

                    if ($stock_prod) {
                        DB::table('producto_stock')->where('pro_sto_id', $stock_prod->pro_sto_id)->update(['stock' => $stock_prod->stock - ($det->ped_det_can * $bus_pro->factor)]);
                    }
                } 
                // Si es un producto que tiene receta (ej. Tragos/Comidas)
                elseif($bus_pro->promocion == '2'){
                    $bus_receta = DB::table('recetas')->where('prod_id', $id_prod)->get();

                    if(count($bus_receta) > 0){
                        foreach($bus_receta as $rec){
                            DB::table('movimientos_productos')->insert([
                                'IdProducto' => $rec->prod_insu,
                                'IdProducto_rel' => $rec->prod_insu,
                                'precio' => '0',
                                'cantidad' => $det->ped_det_can * $rec->rec_cant,
                                'costo' => $rec->ins_costo,
                                'cliente' => $pedido->ped_cli_nom,
                                'IdCpe_cabecera' => null,
                                'serie' => null,
                                'numero' => null,
                                'tdocod' => null,
                                'tipo' => '3',
                                'mov_tip' => 'E',
                                'id_empresa_negocio' => $pedido->id_empresa_negocio,
                                'id_almacen' => $bus_alm->id_almacen,
                                'fecha_mov' => $pedido->ped_fec,
                                'descripcion' => 'Salida por Receta (Pedido ID: ' . $ped_id . ')',
                            ]);

                            $stock_prod_ins = DB::table('producto_stock')
                                ->where('IdProducto', $rec->prod_insu)
                                ->where('id_almacen', $bus_alm->id_almacen)
                                ->first();

                            if ($stock_prod_ins) {
                                DB::table('producto_stock')->where('pro_sto_id', $stock_prod_ins->pro_sto_id)->update(['stock' => $stock_prod_ins->stock - ($det->ped_det_can * $rec->rec_cant)]);
                            }
                        }
                    }
                } 
                // Si es un combo
                elseif($bus_pro->promocion == '3'){
                    $bus_combo_items = DB::table('combos')->where('IdProducto_rel', $id_prod)->get();

                    foreach($bus_combo_items as $combo_item){
                        $prod_combo = productos::findOrFail($combo_item->IdProducto_comb);
                        $id_prod_combo = empty($prod_combo->pro_rel) ? $prod_combo->IdProducto : $prod_combo->pro_rel;

                        // Si el item del combo es un producto simple
                        if($prod_combo->promocion == '0'){
                            DB::table('movimientos_productos')->insert([
                                'IdProducto' => $prod_combo->IdProducto,
                                'IdProducto_rel' => $id_prod_combo,
                                'precio' => $prod_combo->propun,
                                'cantidad' => $det->ped_det_can * $combo_item->prod_comb_cant,
                                'costo' => $prod_combo->costo,
                                'cliente' => $pedido->ped_cli_nom,
                                'IdCpe_cabecera' => null,
                                'serie' => null,
                                'numero' => null,
                                'tdocod' => null,
                                'tipo' => '3',
                                'mov_tip' => 'E',
                                'id_empresa_negocio' => $pedido->id_empresa_negocio,
                                'id_almacen' => $bus_alm->id_almacen,
                                'fecha_mov' => $pedido->ped_fec,
                                'descripcion' => 'Salida por Combo (Pedido ID: ' . $ped_id . ') - Item: ' . $prod_combo->pronom,
                                'mov_lote' => $prod_combo->lote,
                                'mov_vencimiento' => $prod_combo->vencimiento,
                            ]);

                            $stock_prod_combo = DB::table('producto_stock')
                                ->where('IdProducto', $id_prod_combo)
                                ->where('id_almacen', $bus_alm->id_almacen)
                                ->first();

                            if ($stock_prod_combo) {
                                DB::table('producto_stock')->where('pro_sto_id', $stock_prod_combo->pro_sto_id)->update(['stock' => $stock_prod_combo->stock - ($det->ped_det_can * $combo_item->prod_comb_cant)]);
                            }
                        } 
                        // Si el item del combo tiene receta (ej. Tragos/Comidas dentro de un combo)
                        elseif($prod_combo->promocion == '2'){
                            $bus_receta_combo_item = DB::table('recetas')->where('prod_id', $id_prod_combo)->get();

                            if(count($bus_receta_combo_item) > 0){
                                foreach($bus_receta_combo_item as $rec_combo_item){
                                    DB::table('movimientos_productos')->insert([
                                        'IdProducto' => $rec_combo_item->prod_insu,
                                        'IdProducto_rel' => $rec_combo_item->prod_insu,
                                        'precio' => '0',
                                        'cantidad' => $det->ped_det_can * $combo_item->prod_comb_cant * $rec_combo_item->rec_cant,
                                        'costo' => $rec_combo_item->ins_costo,
                                        'cliente' => $pedido->ped_cli_nom,
                                        'IdCpe_cabecera' => null,
                                        'serie' => null,
                                        'numero' => null,
                                        'tdocod' => null,
                                        'tipo' => '3',
                                        'mov_tip' => 'E',
                                        'id_empresa_negocio' => $pedido->id_empresa_negocio,
                                        'id_almacen' => $bus_alm->id_almacen,
                                        'fecha_mov' => $pedido->ped_fec,
                                        'descripcion' => 'Salida por Receta de Combo (Pedido ID: ' . $ped_id . ') - Item: ' . $prod_combo->pronom,
                                    ]);

                                    $stock_prod_rec_combo = DB::table('producto_stock')
                                        ->where('IdProducto', $rec_combo_item->prod_insu)
                                        ->where('id_almacen', $bus_alm->id_almacen)
                                        ->first();

                                    if ($stock_prod_rec_combo) {
                                        DB::table('producto_stock')->where('pro_sto_id', $stock_prod_rec_combo->pro_sto_id)->update(['stock' => $stock_prod_rec_combo->stock - ($det->ped_det_can * $combo_item->prod_comb_cant * $rec_combo_item->rec_cant)]);
                                    }
                                }
                            }
                        }
                    }
                }
            }
            return 'Registrado';
        }

    public function unirMesas(Request $request)
    {
        $mes_id_principal = $request->input('mes_id_act_unir');
        $ped_id_principal = $request->input('ped_id_act_unir');
        $mes_id_a_unir = $request->input('mes_unir');

        if (empty($mes_id_principal) || empty($ped_id_principal) || empty($mes_id_a_unir)) {
            return response()->json(['success' => false, 'message' => 'Datos incompletos para unir mesas.']);
        }

        DB::beginTransaction();
        try {
            // 1. Verificar que la mesa a unir esté realmente LIBRE y no esté ya unida
            $mesa_a_unir_obj = mesas::findOrFail($mes_id_a_unir);

            // Diagnóstico (dejarlo mientras depuras)
            Log::info("Intentando unir mesa. Mesa Principal ID: {$mes_id_principal}, Pedido Principal ID: {$ped_id_principal}, Mesa a Unir ID: {$mes_id_a_unir}");
            Log::info("Estado de mesa a unir (ID: {$mes_id_a_unir}): mes_est = '{$mesa_a_unir_obj->mes_est}', ind_union = '{$mesa_a_unir_obj->ind_union}'");

            // La validación corregida para el estado 'Libre' y 'ind_union'
            // Usamos '!=' para comparación flexible con '0' y verificamos 'null'
            if ($mesa_a_unir_obj->mes_est !== 'Libre' || ($mesa_a_unir_obj->ind_union != '0' && $mesa_a_unir_obj->ind_union !== null)) {
                DB::rollBack();
                Log::warning("Fallo de validación al unir mesas. Estado actual: {$mesa_a_unir_obj->mes_est}, ind_union: {$mesa_a_unir_obj->ind_union}");
                return response()->json(['success' => false, 'message' => 'La mesa seleccionada para unir no está libre o ya está asociada.']);
            }

            // 2. Asociar la mesa libre al pedido principal
            $mesa_a_unir_obj->mes_est = 'Ocupado'; // La mesa libre ahora se considera ocupada
            $mesa_a_unir_obj->ind_union = $mes_id_principal; // Referencia a la mesa principal
            $mesa_a_unir_obj->save();

            // Registrar la unión en la tabla mesas_union.
            // ¡IMPORTANTE! Eliminamos created_at y updated_at de aquí porque tu tabla no los tiene.
            DB::table('mesas_union')->insert([
                'mes_id' => $mes_id_a_unir,
                'mes_id_act' => $mes_id_principal,
                'ped_id' => $ped_id_principal,
                'mes_uni_est' => 'APERTURADO',
                // Eliminadas las líneas de created_at y updated_at
            ]);


            DB::commit();
            return response()->json(['success' => true, 'message' => 'Mesa libre unida al pedido principal exitosamente.']);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Error al unir mesa libre en Kiosko: " . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Error al unir la mesa: ' . $e->getMessage()]);
        }
    }

    public function puntoVentaDirecta()
    {
        // 1. Obtener el usuario autenticado
        $user = Auth::user();

        // 2. Verificar el estado del turno del usuario
        // Asumo que el estado del turno se guarda en `Auth::user()->turno` como 'Aperturado' o 'Cerrado'
        if ($user->turno == 'Cerrado' || is_null($user->turno) || $user->id_turno == null) {
            // Si el turno está cerrado o no definido, redirige a la vista de caja con un mensaje
            return Redirect::to('/caja')->with('danger', 'Necesitas aperturar turno para poder vender.');
        }

        // Si el turno está aperturado, continúa con la lógica normal de la vista
        $id_empresa_negocio = $user->id_empresa_negocio; // Usar el id_empresa_negocio del usuario
        $id_empresa = $user->IdEmpresa; // Usar el IdEmpresa del usuario

        $categorias = DB::table('categorias')
                        ->where('id_empresa_negocio', $id_empresa_negocio)
                        ->where('visible', '1')
                        ->orderBy('cat_nom', 'asc')
                        ->get();

        $comprobantes = DB::table('tipo_documento')
                          ->where('ventas', '1')
                          ->get();

        $documentos = DB::table('tipo_documento_identidad')
                        ->orderBy('orden', 'asc')
                        ->get();

        $estadopagos = DB::table('credito_dias')->get();

        $mediospagos = DB::table('medios_pagos')
                         ->where('id_empresa_negocio', $id_empresa_negocio)
                         ->get();

        $negocio = DB::table('empresa_negocios')->where('id_empresa_negocio', $id_empresa_negocio)->first();

        $empresa_data = DB::table('empresa')->where('IdEmpresa', $id_empresa)->first();
        $icbper_val = $empresa_data ? $empresa_data->icbper : 0;

        // Se inicializa la variable productos como una colección vacía
        $productos = collect(); 

        return view('empresas.kiosko.punto_venta_directa', compact(
            'categorias', 'comprobantes', 'documentos', 'estadopagos',
            'mediospagos', 'negocio', 'productos', 'icbper_val'
        ));
    }




    public function desunirMesas(Request $request)
    {
        $mes_id_desunir = $request->input('mes_desunir');

        if (empty($mes_id_desunir)) {
            return response()->json(['success' => false, 'message' => 'No se proporcionó mesa para desunir.']);
        }

        DB::beginTransaction();
        try {
            $mesa_desunir_obj = mesas::findOrFail($mes_id_desunir);

            $mesa_desunir_obj->mes_est = 'Libre';
            $mesa_desunir_obj->ind_union = '0';
            $mesa_desunir_obj->save();

            DB::table('mesas_union')
                ->where('mes_id', $mes_id_desunir)
                ->where('mes_uni_est', 'APERTURADO')
                ->update(['mes_uni_est' => 'CERRADO']);

            DB::commit();
            return response()->json(['success' => true, 'message' => 'Mesa desunida y liberada.']);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Error al desunir mesa en Kiosko: " . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Error al desunir la mesa: ' . $e->getMessage()]);
        }
    }

    
public function autocompleteClient(Request $request)
    {
        $query = $request->input('query');
        $field = $request->input('field'); // 'clinum' or 'clinom'
        $IdEmpresa = Auth::user()->IdEmpresa; // Asumiendo que esta es la ID principal del RUC de tu empresa.

        // Primero, intentar buscar en la base de datos local
        $clientes_locales = Cliente::where('rucemp', $IdEmpresa)
                                    ->where(function($q) use ($query, $field) {
                                        if ($field === 'clinum') {
                                            $q->where('clinum', 'like', $query . '%');
                                        } else { // field is 'clinom'
                                            $q->where('clinom', 'like', '%' . $query . '%');
                                        }
                                    })
                                    ->limit(10)
                                    ->get(['clicod', 'clinum', 'clinom', 'clidir', 'tdicod', 'telefono']);

        $results = [];

        // Añadir clientes locales a los resultados
        foreach ($clientes_locales as $cli) {
            $results[] = [
                'label' => "{$cli->clinom} ({$cli->clinum})", // Mostrar ambos en el autocompletado
                'value' => ($field === 'clinum') ? $cli->clinum : $cli->clinom, // Valor que se pone en el input
                'nom' => $cli->clinom,
                'num' => $cli->clinum, // Para rellenar el otro campo
                'dir' => $cli->clidir,
                'tdicod' => $cli->tdicod,
                'clicod' => $cli->clicod,
                'tel' => $cli->telefono
            ];
        }

        // Si la búsqueda es por número y no se encontraron suficientes resultados localmente, intentar con la API
        if ($field === 'clinum' && count($results) < 10) { 
            $api_token = 'c7c656604942b0a6df5fa225835e99eb7376cf841d38781b91f651f72e03cc09'; 
                                                                                           
            $tipo_documento_api = '';
            $numero_documento_api = '';

            if (strlen($query) == 11 && is_numeric($query)) {
                $tipo_documento_api = 'ruc';
                $numero_documento_api = $query;
            } elseif (strlen($query) == 8 && is_numeric($query)) {
                $tipo_documento_api = 'dni';
                $numero_documento_api = $query;
            }
            
            if (!empty($tipo_documento_api)) {
                try {
                    $params = json_encode([$tipo_documento_api => $numero_documento_api]);
                    $curl = curl_init();
                    curl_setopt_array($curl, array(
                        CURLOPT_URL => "https://apiperu.dev/api/{$tipo_documento_api}",
                        CURLOPT_RETURNTRANSFER => true,
                        CURLOPT_CUSTOMREQUEST => "POST",
                        CURLOPT_SSL_VERIFYPEER => false, 
                        CURLOPT_POSTFIELDS => $params,     
                        CURLOPT_HTTPHEADER => [
                            'Accept: application/json',
                            'Content-Type: application/json',
                            'Authorization: Bearer ' . $api_token
                        ],     
                    ));
                    $response = curl_exec($curl);
                    $err = curl_error($curl);
                    curl_close($curl);

                    if ($err) {
                        Log::error("Error de cURL al consultar API: " . $err);
                    } else {
                        $api_data = json_decode($response, true);

                        if (!empty($api_data) && isset($api_data['data'])) { 
                            $data_from_api = $api_data['data']; 

                            $numero_documento = $data_from_api['numero'] ?? $data_from_api['ruc'] ?? $query;
                            $nombre_razon_social = $data_from_api['nombres'] ?? $data_from_api['nombre_o_razon_social'] ?? 'N/A';
                            if (isset($data_from_api['apellido_paterno'])) $nombre_razon_social .= ' ' . $data_from_api['apellido_paterno'];
                            if (isset($data_from_api['apellido_materno'])) $nombre_razon_social .= ' ' . $data_from_api['apellido_materno'];
                            $direccion = $data_from_api['direccion_completa'] ?? $data_from_api['direccion'] ?? 'No disponible';
                            $tipo_doc = (strlen($numero_documento) == 11) ? '6' : '1'; 

                            $already_in_results = false;
                            foreach ($results as $res) {
                                if ($res['num'] === $numero_documento) {
                                    $already_in_results = true;
                                    break;
                                }
                            }

                            if (!$already_in_results) {
                                $results[] = [
                                    'label' => "(API) {$nombre_razon_social} ({$numero_documento})",
                                    'value' => ($field === 'clinum') ? $numero_documento : $nombre_razon_social,
                                    'nom' => $nombre_razon_social,
                                    'num' => $numero_documento,
                                    'dir' => $direccion,
                                    'tdicod' => $tipo_doc,
                                    'clicod' => null,
                                    'tel' => null
                                ];
                            }
                        }
                    }
                } catch (\Exception $e) {
                    Log::error("Error al consultar API de DNI/RUC en KioskoController: " . $e->getMessage());
                }
            }
        }
        
        return response()->json($results);
    }

    public function autocomplete(Request $request, $cliente)
    {
        try {
            $rucemp = trim(Auth::user()->IdEmpresa);
            $field = $request->input('field', 'clinum'); 
            $results = [];

            // Si el cliente escribió letras, buscamos por NOMBRE
            if (!is_numeric($cliente)) {
                $busqueda = DB::table('cliente')
                         ->where('clinom', 'LIKE', '%' . $cliente . '%')
                         ->take(15)
                         ->get();

                foreach ($busqueda as $cli) {
                    $results[] = [
                        'num' => $cli->clinum,
                        'value' => $cli->clinom,
                        'nom' => $cli->clinom,
                        'dir' => $cli->clidir,
                        'tdicod' => $cli->tdicod,
                        'tel' => $cli->telefono,
                        'clicod' => $cli->clicod,
                    ];
                }
            } 
            
            // Si el resultado está vacío o si escribió NÚMEROS, buscamos por DNI/RUC
            if (is_numeric($cliente) || empty($results)) {
                $busquedaLocal = DB::table('cliente')
                         ->where('clinum', 'LIKE', $cliente . '%')
                         ->take(10)
                         ->get();

                foreach ($busquedaLocal as $cli) {
                    $results[] = [
                        'num' => $cli->clinum,
                        'value' => ($field === 'clinom') ? $cli->clinom : $cli->clinum,
                        'nom' => $cli->clinom,
                        'dir' => $cli->clidir,
                        'tdicod' => $cli->tdicod,
                        'tel' => $cli->telefono,
                        'clicod' => $cli->clicod,
                    ];
                }

                // Si es número y no hay nada en la DB local, llamamos a la API
                if (empty($results) && is_numeric($cliente)) {
                    if (strlen($cliente) === 8) {
                        $leer_respuesta = $this->consultardni($cliente);
                        if (isset($leer_respuesta['data'])) {
                            $results[] = [
                                'num' => $leer_respuesta['data']['numero'],
                                'value' => $leer_respuesta['data']['numero'],
                                'nom' => $leer_respuesta['data']['nombres'] . ' ' . $leer_respuesta['data']['apellido_paterno'] . ' ' . $leer_respuesta['data']['apellido_materno'],
                                'dir' => '--',
                                'tdicod' => '1',
                                'clicod' => null,
                            ];
                        }
                    } elseif (strlen($cliente) === 11) {
                        $leer_respuesta = $this->consultaruc($cliente);
                        if (isset($leer_respuesta['data'])) {
                            $results[] = [
                                'num' => $leer_respuesta['data']['ruc'],
                                'value' => $leer_respuesta['data']['ruc'],
                                'nom' => $leer_respuesta['data']['nombre_o_razon_social'],
                                'dir' => $leer_respuesta['data']['direccion_completa'],
                                'tdicod' => '6',
                                'clicod' => null,
                            ];
                        }
                    }
                }
            }

            return response()->json($results);

        } catch (\Exception $e) {
            Log::error('Error en autocomplete Kiosko: ' . $e->getMessage());
            return response()->json(['error' => 'Error en el servidor']);
        }
    }

    
    private function consultaruc($ruc)
    {
        $params = json_encode(['ruc' => $ruc]);
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => "https://apiperu.dev/api/ruc",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_POSTFIELDS => $params,
            CURLOPT_HTTPHEADER => [
                'Accept: application/json',
                'Content-Type: application/json',
                'Authorization: Bearer c7c656604942b0a6df5fa225835e99eb7376cf841d38781b91f651f72e03cc09'
            ],
        ));
        $response = curl_exec($curl);
        $err = curl_error($curl);
        curl_close($curl);

        if ($err) {
            throw new \Exception("Error cURL al consultar RUC: " . $err);
        } else {
            return json_decode($response, true);
        }
    }

    private function consultardni($dni)
    {
        $params = json_encode(['dni' => $dni]);
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => "https://apiperu.dev/api/dni",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_POSTFIELDS => $params,
            CURLOPT_HTTPHEADER => [
                'Accept: application/json',
                'Content-Type: application/json',
                'Authorization: Bearer c7c656604942b0a6df5fa225835e99eb7376cf841d38781b91f651f72e03cc09'
            ],
        ));
        $response = curl_exec($curl);
        $err = curl_error($curl);
        curl_close($curl);

        if ($err) {
            throw new \Exception("Error cURL al consultar DNI: " . $err);
        } else {
            return json_decode($response, true);
        }
    }

    public function mostrarComandasCocina()
    {
        $id_empresa_negocio = Auth::user()->id_empresa_negocio;

        // Obtener los ítems de pedidos_detalle que aún no han sido despachados
        // y que pertenecen a pedidos "Aperturados" o "Ocupado" o "En Preparacion"
        // Asegúrate de que ped_est sea el estado correcto para pedidos activos.
        $comandas = DB::table('pedidos_detalle as pd')
                      ->select(
                          'pd.ped_det_id',
                          'pd.ped_id',
                          'p.fecha_hora', // Usar fecha_hora del pedido para el momento inicial
                          'p.fecha_hora_modificacion', // Si hay un campo para la última mod del pedido
                          'pd.descripcion as producto_nombre',
                          'pd.item_obs as producto_observacion',
                          'pd.ped_det_can as cantidad',
                          'pd.estadoitem as estado_item', // 'Ingresado', 'En Preparacion', 'Despachado', 'Anulado'
                          'm.mes_nom as mesa_nombre',
                          'p.ped_tip as tipo_pedido', // 'Salon' o 'Llevar'
                          'p.ped_cli_nom as cliente_nombre' // Para "Para Llevar"
                      )
                      ->join('pedidos as p', 'pd.ped_id', '=', 'p.ped_id')
                      ->leftJoin('mesas as m', 'p.mes_id', '=', 'm.mes_id') // Left join porque puede ser 'Llevar'
                      ->where('p.id_empresa_negocio', $id_empresa_negocio)
                      ->whereIn('p.ped_est', ['Aperturado', 'Ocupado', 'En Preparacion', 'Impreso']) // Estados de pedidos que la cocina debe ver
                      ->whereIn('pd.estadoitem', ['Ingresado', 'En Preparacion']) // Estados de ítems que la cocina debe ver
                      ->orderBy('p.ped_id', 'asc') // Ordenar por ID de pedido
                      ->orderBy('pd.ped_det_id', 'asc') // Luego por el detalle para mantener el orden de adición
                      ->get();


        return view('empresas.kiosko.comandas_cocina', compact('comandas')); // O 'cocina.comandas' si creas una carpeta 'cocina'
    }

    public function getComandasCocinaJson() 
    {
        $id_empresa_negocio = Auth::user()->id_empresa_negocio;

        $detalles = DB::table('pedidos_detalle as pd')
            ->select('pd.*', 'p.ped_tip as tipo_pedido', 'p.ped_cli_nom as cliente_nombre', 'm.mes_nom as mesa_nombre')
            ->join('pedidos as p', 'pd.ped_id', '=', 'p.ped_id')
            ->leftJoin('mesas as m', 'p.mes_id', '=', 'm.mes_id')
            ->where('p.id_empresa_negocio', $id_empresa_negocio)
            ->whereIn('p.ped_est', ['Aperturado', 'Ocupado', 'En Preparacion', 'Impreso'])
            ->whereIn('pd.estadoitem', ['Ingresado', 'En Preparacion'])
            ->whereNull('pd.fecha_hora_despacho')
            ->get(); // Quitamos los orderBy de SQL para manejar el orden real por tiempo abajo

        $comandasProcesadas = [];

        foreach ($detalles as $det) {
            if (!empty($det->rondas_envio)) {
                $rondas = json_decode($det->rondas_envio, true);
                foreach ($rondas as $index => $ronda) {
                    if (isset($ronda['despachado']) && $ronda['despachado'] == true) {
                        continue;
                    }

                    $comandasProcesadas[] = [
                        'ped_det_id'          => $det->ped_det_id . '-' . $index,
                        'ped_id'              => $det->ped_id,
                        'fecha_hora_item'     => $ronda['hora'], // Ordenaremos por este campo
                        'fecha_hora_despacho' => $det->fecha_hora_despacho,
                        'producto_nombre'     => $det->descripcion,
                        'producto_observacion'=> $det->item_obs,
                        'cantidad'            => $ronda['cant'],
                        'estado_item'         => $det->estadoitem,
                        'mesa_nombre'         => $det->mesa_nombre,
                        'tipo_pedido'         => $det->tipo_pedido,
                        'cliente_nombre'      => $det->cliente_nombre,
                        'ronda_index'         => $index
                    ];
                }
            } else {
                $comandasProcesadas[] = [
                    'ped_det_id'          => $det->ped_det_id,
                    'ped_id'              => $det->ped_id,
                    'fecha_hora_item'     => $det->fecha_hora, // Ordenaremos por este campo
                    'fecha_hora_despacho' => $det->fecha_hora_despacho,
                    'producto_nombre'     => $det->descripcion,
                    'producto_observacion'=> $det->item_obs,
                    'cantidad'            => $det->ped_det_can,
                    'estado_item'         => $det->estadoitem,
                    'mesa_nombre'         => $det->mesa_nombre,
                    'tipo_pedido'         => $det->tipo_pedido,
                    'cliente_nombre'      => $det->cliente_nombre,
                    'ronda_index'         => null
                ];
            }
        }

        // ✨ LA JUGADA MAESTRA: Ordenar de forma ascendente por la hora de llegada real del plato/ronda
        $comandasOrdenadas = collect($comandasProcesadas)->sortBy('fecha_hora_item')->values()->all();

        return response()->json($comandasOrdenadas);
    }

    // Función para marcar un ítem como despachado/preparado
    public function despacharItemComanda(Request $request)
    {
        $idInput = $request->input('ped_det_id'); // Puede venir "7172" o "7172-1"
        $usuario_id = Auth::user()->IdUsuario; // Quién despacha

        if (empty($idInput)) {
            return response()->json(['success' => false, 'message' => 'ID de detalle de pedido no proporcionado.']);
        }

        // Separamos el ID por el guion por si viene de una ronda desglosada
        $parts = explode('-', $idInput);
        $ped_det_id = $parts[0];
        $ronda_index = isset($parts[1]) ? (int)$parts[1] : null;

        DB::beginTransaction();
        try {
            $detalle_pedido = DB::table('pedidos_detalle')->where('ped_det_id', $ped_det_id)->first();

            if (!$detalle_pedido) {
                DB::rollBack();
                return response()->json(['success' => false, 'message' => 'Detalle de pedido no encontrado.']);
            }

            // Caso A: Si el ítem viene de una RONDA MÚLTIPLE (tiene formato ID-INDEX)
            if ($ronda_index !== null && isset($detalle_pedido->rondas_envio)) {
                $rondas = json_decode($detalle_pedido->rondas_envio, true);
                
                if (isset($rondas[$ronda_index])) {
                    // Marcamos la ronda específica como despachada internamente
                    $rondas[$ronda_index]['despachado'] = true;
                    $rondas[$ronda_index]['fecha_despacho'] = Carbon::now()->toDateTimeString();
                    $rondas[$ronda_index]['usuario_despacho'] = $usuario_id;
                }

                // Verificamos si aún quedan más rondas pendientes por cocinar en este plato
                $quedanRondasPendientes = false;
                foreach ($rondas as $r) {
                    if (!isset($r['despachado']) || $r['despachado'] == false) {
                        $quedanRondasPendientes = true;
                        break;
                    }
                }

                $updateData = [
                    'rondas_envio' => json_encode($rondas)
                ];

                // Si ya se despachó la ÚLTIMA ronda que faltaba, ocultamos el ítem completo de la cocina
                if (!$quedanRondasPendientes) {
                    $updateData['fecha_hora_despacho'] = Carbon::now();
                    $updateData['usuario_despacho_id'] = $usuario_id;
                }

                DB::table('pedidos_detalle')
                    ->where('ped_det_id', $ped_det_id)
                    ->update($updateData);

            } else {
                // Caso B: Despacho tradicional (Ítem único que se pidió de un solo golpe)
                DB::table('pedidos_detalle')
                    ->where('ped_det_id', $ped_det_id)
                    ->update([
                        'fecha_hora_despacho' => Carbon::now(), // Registrar la hora de despacho
                        'usuario_despacho_id' => $usuario_id // Registrar quién despachó
                    ]);
            }

            $pedido_id = $detalle_pedido->ped_id;

            DB::commit();
            return response()->json(['success' => true, 'message' => 'Ítem marcado como despachado por cocina.']);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Error al despachar ítem de comanda: " . $e->getMessage() . " en línea " . $e->getLine());
            return response()->json(['success' => false, 'message' => 'Error al despachar el ítem: ' . $e->getMessage()]);
        }
    }

    


    public function seleccionServicio()
        {
            $id_empresa_negocio = Auth::user()->id_empresa_negocio;

            $pisos = DB::table('pisos')
                ->where('suc_id', $id_empresa_negocio)
                ->get();

            $primerPisoId = $pisos->first() ? $pisos->first()->pis_id : null;
            $mesas = [];
            
            if ($primerPisoId) {
                $mesas = DB::table('mesas')
                    ->leftJoin('pedidos', function($join) {
                        $join->on('mesas.mes_id', '=', 'pedidos.mes_id')
                             ->where('pedidos.ped_est', 'Aperturado');
                    })
                    ->where('mesas.pis_id', $primerPisoId)
                    ->where('mesas.id_empresa_negocio', $id_empresa_negocio)
                    // INICIO DE LA CORRECCIÓN: Usar MAX y GROUP BY
                    ->select(
                        'mesas.*', 
                        // Usamos MAX para elegir el ped_id más alto (más reciente)
                        DB::raw('MAX(pedidos.ped_id) as pedido_asociado_id'), 
                        DB::raw('MAX(pedidos.fecha_hora) as pedido_fecha_hora'),
                        DB::raw('MAX(pedidos.ped_tot) as ped_tot'),
                        DB::raw('MAX(pedidos.ped_sol_cs) as ped_sol_cs')
                    )
                    ->groupBy('mesas.mes_id') // CRÍTICO: Elimina las filas duplicadas
                    // FIN DE LA CORRECCIÓN
                    ->orderBy('mesas.mes_nom')
                    ->get();
            }

            session()->forget('kiosko_cart');
            session()->forget('kiosko_order_type');
            session()->forget('kiosko_mesa_id');
            session()->forget('kiosko_mesa_nombre');
            session()->forget('kiosko_last_pedido_id');

            return view('empresas.kiosko.seleccion_servicio', compact('pisos', 'mesas', 'primerPisoId'));
        }

    public function setServiceData(Request $request)
    {
        $orderType = $request->input('order_type');
        $mesaId = $request->input('mesa_id');
        $mesaNombre = $request->input('mesa_nombre');
        $pedidoId = $request->input('pedido_id');

        session()->put('kiosko_order_type', $orderType);
        session()->put('kiosko_mesa_id', $mesaId);
        session()->put('kiosko_mesa_nombre', $mesaNombre);

        // Si estamos seleccionando una mesa con pedido existente, forzar recarga del carrito
        if (!empty($pedidoId) && $orderType === 'salon') {
            session()->put('kiosko_last_pedido_id', $pedidoId);
            session()->put('kiosko_cart_pedido_id', $pedidoId);
            session()->forget('kiosko_cart');
        } else {
            session()->forget('kiosko_last_pedido_id');
            session()->forget('kiosko_cart_pedido_id');
            session()->forget('kiosko_cart');
        }

        // Aseguramos que los datos de sesión se guarden antes de responder
        session()->save();

        return response()->json(['success' => true]);
    }

    
    public function getMesasPorPiso($piso_id)
        {
            $id_empresa_negocio = Auth::user()->id_empresa_negocio;

            // INICIO DE LA CORRECCIÓN: Usar MAX y GROUP BY para garantizar una fila por mesa
            $mesas_raw = DB::table('mesas')
                ->leftJoin('pedidos', function($join) {
                    $join->on('mesas.mes_id', '=', 'pedidos.mes_id')
                         ->where('pedidos.ped_est', 'Aperturado');
                })
                ->where('mesas.pis_id', $piso_id)
                ->where('mesas.id_empresa_negocio', $id_empresa_negocio)
                ->select(
                    'mesas.*', 
                    DB::raw('MAX(pedidos.ped_id) as pedido_asociado_id'), 
                    DB::raw('MAX(pedidos.fecha_hora) as pedido_fecha_hora'),
                    DB::raw('MAX(pedidos.ped_tot) as ped_tot'),
                    DB::raw('MAX(pedidos.ped_sol_cs) as ped_sol_cs')
                )
                ->groupBy('mesas.mes_id') // CRÍTICO: Elimina las filas duplicadas
                ->orderBy('mesas.mes_nom')
                ->get();
            // FIN DE LA CORRECCIÓN

            $mesas_procesadas = [];
            $mesas_ya_procesadas_ids = []; 

            // Primera pasada: Procesar mesas principales con uniones
            foreach ($mesas_raw as $mesa_principal_candidata) {
                // La validación ahora es mucho más robusta porque mesas_raw no tiene duplicados
                if (in_array($mesa_principal_candidata->mes_id, $mesas_ya_procesadas_ids)) {
                    continue;
                }

                if ($mesa_principal_candidata->ind_union == '0' && 
                    $mesa_principal_candidata->mes_est != 'Libre' && 
                    !is_null($mesa_principal_candidata->pedido_asociado_id)) {
                    
                    $mesas_unidas_a_esta_principal = DB::table('mesas_union')
                                                        ->where('mes_id_act', $mesa_principal_candidata->mes_id)
                                                        ->where('mes_uni_est', 'APERTURADO')
                                                        ->pluck('mes_id')
                                                        ->toArray();

                    if (!empty($mesas_unidas_a_esta_principal)) {
                        $nombres_mesas_en_union = [$mesa_principal_candidata->mes_nom];
                        $nombres_mesas_secundarias_objs = DB::table('mesas')
                                                            ->whereIn('mes_id', $mesas_unidas_a_esta_principal)
                                                            ->pluck('mes_nom')
                                                            ->toArray();
                        $nombres_mesas_en_union = array_merge($nombres_mesas_en_union, $nombres_mesas_secundarias_objs);

                        $mesa_combinada = (object)[
                            'mes_id' => $mesa_principal_candidata->mes_id,
                            'mes_nom' => implode(' - ', $nombres_mesas_en_union),
                            'mes_est' => $mesa_principal_candidata->mes_est,
                            'pedido_asociado_id' => $mesa_principal_candidata->pedido_asociado_id,
                            'pedido_fecha_hora' => $mesa_principal_candidata->pedido_fecha_hora,
                            'ped_tot' => $mesa_principal_candidata->ped_tot,
                            'ped_sol_cs' => $mesa_principal_candidata->ped_sol_cs,
                            'is_combined_table' => true,
                            'ind_union' => '0'
                        ];
                        $mesas_procesadas[] = $mesa_combinada;
                        
                        // CRÍTICO: Marcar la mesa principal y secundarias como procesadas
                        $mesas_ya_procesadas_ids[] = $mesa_principal_candidata->mes_id;
                        $mesas_ya_procesadas_ids = array_merge($mesas_ya_procesadas_ids, $mesas_unidas_a_esta_principal);
                    }
                }
            }

            // Segunda pasada: Procesar mesas individuales
            foreach ($mesas_raw as $mesa_individual_candidata) {
                // Validar si ya fue procesada
                if (in_array($mesa_individual_candidata->mes_id, $mesas_ya_procesadas_ids)) {
                    continue;
                }

                // Saltar mesas que están unidas a otra mesa principal
                if ($mesa_individual_candidata->ind_union != '0' && 
                    $mesa_individual_candidata->ind_union !== null) {
                    continue;
                }

                $mesas_procesadas[] = (object)[
                    'mes_id' => $mesa_individual_candidata->mes_id,
                    'mes_nom' => $mesa_individual_candidata->mes_nom,
                    'mes_est' => $mesa_individual_candidata->mes_est,
                    'pedido_asociado_id' => $mesa_individual_candidata->pedido_asociado_id,
                    'pedido_fecha_hora' => $mesa_individual_candidata->pedido_fecha_hora,
                    'ped_tot' => $mesa_individual_candidata->ped_tot,
                    'ped_sol_cs' => $mesa_individual_candidata->ped_sol_cs,
                    'is_combined_table' => false,
                    'ind_union' => $mesa_individual_candidata->ind_union
                ];
                
                // Marcar como procesada
                $mesas_ya_procesadas_ids[] = $mesa_individual_candidata->mes_id;
            }

            usort($mesas_procesadas, function($a, $b) {
                return strcmp($a->mes_nom, $b->mes_nom);
            });

            return response()->json([
                'vista' => view('empresas.kiosko.partials.mesas_grid', ['mesas' => collect($mesas_procesadas)])->render()
            ]);
        }


    

public function getPedidoDetails($pedido_id)
{
    $pedido = DB::table('pedidos')
                ->where('ped_id', $pedido_id)
                ->where('ped_est', 'Aperturado')
                ->first();
    
    if (!$pedido) {
        return response()->json(['success' => false, 'message' => 'Pedido no encontrado o no está activo.']);
    }
    
    // MODIFICADO: Solo items pendientes con cantidad_pendiente calculada
    $pedido_detalles = DB::table('pedidos_detalle')
                        ->select('pedidos_detalle.*',
                            DB::raw('(ped_det_can - IFNULL(item_facturado, 0)) as cantidad_pendiente'))
                        ->where('ped_id', $pedido_id)
                        ->where('estadoitem', '!=', 'Eliminado')
                        ->whereRaw('ROUND(ped_det_can - IFNULL(item_facturado, 0), 4) > 0') 
                        ->get();
    
    $empresa_data = DB::table('empresa')->where('IdEmpresa', Auth::user()->IdEmpresa)->first();
    $icbper_val = $empresa_data ? $empresa_data->icbper : 0;
    
    $total_venta = 0;
    $total_icbper = 0;
    
    // Obtener el almacén predeterminado para el cálculo del stock
    $almacen = DB::table('almacenes')
                  ->where('predeterminado', '1')
                  ->where('id_empresa_negocio', Auth::user()->id_empresa_negocio)
                  ->first();
    $almacen_id = $almacen ? $almacen->id_almacen : 0;
    $pedido_empresa_negocio_id = $pedido->id_empresa_negocio;
    
    foreach ($pedido_detalles as $item) {
        // Recalcular stock disponible para cada ítem
        $producto_info = DB::table('productos')
                            ->leftJoin('producto_stock', function($join) use ($almacen_id, $pedido_empresa_negocio_id) {
                                $join->on('productos.IdProducto', '=', 'producto_stock.IdProducto')
                                     ->where('producto_stock.id_almacen', '=', $almacen_id)
                                     ->where('producto_stock.id_empresa_negocio', '=', $pedido_empresa_negocio_id);
                            })
                            ->where('productos.IdProducto', $item->IdProducto)
                            ->select('productos.pro_rel', 'productos.factor', 'productos.tipo', 'producto_stock.stock')
                            ->first();
        
        $stock_disponible = 0;
        if ($producto_info) {
            if ($producto_info->tipo == '2' && !empty($producto_info->pro_rel) && $producto_info->factor > 0) {
                $stock_base = DB::table('producto_stock')
                                ->where('IdProducto', $producto_info->pro_rel)
                                ->where('id_almacen', $almacen_id)
                                ->where('id_empresa_negocio', $pedido_empresa_negocio_id)
                                ->first();
                $stock_disponible = ($stock_base && $producto_info->factor > 0) ? ($stock_base->stock / $producto_info->factor) : 0;
            } else {
                $stock_disponible = $producto_info->stock ?? 0;
            }
        }
        
        // --- CÓDIGO NUEVO AQUÍ ---
        // Solo sumamos al total si el ítem NO está pagado
        if ($item->pagado != 1) {
            $total_venta += $item->cantidad_pendiente * $item->ped_det_pre;
            if (isset($item->icbper_ind) && $item->icbper_ind == 1) {
                $total_icbper += $item->cantidad_pendiente * $icbper_val;
            }
        }
        // -------------------------
        
        $item->stock_disponible = $stock_disponible;
        $item->is_old_item = true;
    }
    
    $total_venta += $total_icbper;
    $total_venta = round($total_venta, 2);
    
    return response()->json([
        'success' => true,
        'pedido' => $pedido,
        'detalles' => $pedido_detalles, 
        'total' => $total_venta, // Ahora este total refleja correctamente solo lo NO pagado
        'total_original' => $pedido->ped_tot, 
        'icbper_val' => $icbper_val
    ]);
}

    public function searchProductsJson(Request $request)
{
    $id_empresa_negocio = Auth::user()->id_empresa_negocio;
    $id_almacen_pred = Almacen::where('id_empresa_negocio', $id_empresa_negocio)->where('predeterminado', 1)->first()->id_almacen;

    $query = DB::table('productos as p')
        ->join('producto_stock as ps', 'p.IdProducto', '=', 'ps.IdProducto')
        ->join('producto_empresa as pe', 'p.IdProducto', '=', 'pe.IdProducto')
        ->where('ps.id_almacen', $id_almacen_pred)
        ->where('pe.id_empresa_negocio', $id_empresa_negocio)
        ->where('p.proest', 'Activo')
        ->select(
            'p.IdProducto as prod_id',
            'p.pronom as prod_nom',
            'pe.precio as prod_pre_ven',
            'ps.stock',
            'p.imagenproducto as imagen', // Asegúrate de que tu columna se llame así
            'p.icbper'
        );

    if ($request->has('category_id') && $request->category_id != '0') {
        $query->where('p.cat_id', $request->category_id);
    }

    if ($request->has('search_text') && !empty($request->search_text)) {
        $query->where(function ($q) use ($request) {
            $q->where('p.pronom', 'like', '%' . $request->search_text . '%')
              ->orWhere('p.procod', 'like', '%' . $request->search_text . '%');
        });
    }

    $productos = $query->get();

    return response()->json(['productos' => $productos]);
}


    public function searchProducts(Request $request)
    {
        $searchText = $request->input('search_text');
        $categoryId = $request->input('category_id');

        $id_empresa_negocio = Auth::user()->id_empresa_negocio;
        $almacen_obj = DB::table('almacenes')
            ->where('predeterminado', '1')
            ->where('id_empresa_negocio', $id_empresa_negocio)
            ->first();

        $almacen_id = $almacen_obj ? $almacen_obj->id_almacen : 0; // Obtener el ID del almacén dinámicamente

        $ahora = Carbon::now();
        $dia_actual_carbon = $ahora->dayOfWeek;
        $hora_actual_string = $ahora->toTimeString();

        $query = DB::table('productos as p')
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
                DB::raw("
                    COALESCE(
                        (SELECT psd.precio_especial
                         FROM precios_dia_semana as psd
                         WHERE psd.IdProducto = p.IdProducto
                           AND psd.id_empresa_negocio = '{$id_empresa_negocio}'
                           AND psd.estado = 'Activo'
                           AND (psd.fecha_inicio_vigencia IS NULL OR psd.fecha_inicio_vigencia <= CURDATE())
                           AND (psd.fecha_fin_vigencia IS NULL OR psd.fecha_fin_vigencia >= CURDATE())
                           AND
                           (
                               (
                                   psd.dia_semana = {$dia_actual_carbon}
                                   AND psd.hora_inicio_vigencia <= psd.hora_fin_vigencia
                                   AND TIME('{$hora_actual_string}') >= psd.hora_inicio_vigencia
                                   AND TIME('{$hora_actual_string}') <= psd.hora_fin_vigencia
                               )
                               OR
                               (
                                   psd.dia_semana = MOD({$dia_actual_carbon} - 1 + 7, 7)
                                   AND psd.hora_inicio_vigencia > psd.hora_fin_vigencia
                                   AND TIME('{$hora_actual_string}') >= psd.hora_inicio_vigencia
                               )
                               OR
                               (
                                   psd.dia_semana = {$dia_actual_carbon}
                                   AND psd.hora_inicio_vigencia > psd.hora_fin_vigencia
                                   AND TIME('{$hora_actual_string}') <= psd.hora_fin_vigencia
                               )
                           )
                           ORDER BY psd.id_precio_dia DESC
                           LIMIT 1
                        ),
                        p.propun
                    ) as precio
                "),
                'p.propun1 as precio2',
                'p.propun2 as precio3',
                // CAMBIO AQUI: Usar el $almacen_id dinámico en lugar de hardcodeado
                DB::raw("
                    CASE
                        WHEN p.tipo = '2' AND p.pro_rel IS NOT NULL THEN (
                            SELECT stock / p.factor
                            FROM producto_stock
                            WHERE IdProducto = p.pro_rel
                              AND id_almacen = '{$almacen_id}'
                              AND id_empresa_negocio = '{$id_empresa_negocio}'
                        )
                        ELSE (
                            SELECT stock
                            FROM producto_stock
                            WHERE IdProducto = p.IdProducto
                              AND id_almacen = '{$almacen_id}'
                              AND id_empresa_negocio = '{$id_empresa_negocio}'
                        )
                    END as stock_disponible
                ")
            )
            ->join('producto_empresa as pe', 'pe.IdProducto', 'p.IdProducto')
            ->leftjoin('categorias as cat', 'cat.cat_id', '=', 'p.cat_id')
            ->leftjoin('producto_codigo as pc', 'pc.IdProducto', '=', 'p.IdProducto')
            ->where('p.promocion', '!=', '4')
            ->where('pe.id_empresa_negocio', $id_empresa_negocio);

        if ($searchText) {
            $query->where(function ($q) use ($searchText) {
                $q->where('p.pronom', 'like', '%' . $searchText . '%')
                  ->orWhere('p.codigo_barra', $searchText)
                  ->orWhere('p.procod', $searchText);
            });
        } elseif ($categoryId && $categoryId != '0') {
            $query->where('p.cat_id', $categoryId);
        }

        $productos = $query->groupBy('p.IdProducto')
                           ->orderBy('p.pronom', 'asc')
                           ->get();
        $negocio = DB::table('empresa_negocios')->where('id_empresa_negocio', $id_empresa_negocio)->first();

        return response()->json([
            'vista' => view('empresas.kiosko.partials.productos_grid_directa', compact('productos', 'negocio'))->render()
        ]);
    }

    //BUSCA PRODUCTOS DEL MENU
    public function searchProductsKiosko(Request $request)
    {
        $searchText = $request->input('search_text');
        $categoryId = $request->input('category_id');

        $id_empresa_negocio = Auth::user()->id_empresa_negocio;
        $almacen_obj = DB::table('almacenes')
            ->where('predeterminado', '1')
            ->where('id_empresa_negocio', $id_empresa_negocio)
            ->first();

        $almacen_id = $almacen_obj ? $almacen_obj->id_almacen : 0; // Obtener el ID del almacén dinámicamente

        $ahora = Carbon::now();
        $dia_actual_carbon = $ahora->dayOfWeek;
        $hora_actual_string = $ahora->toTimeString();

        $query = DB::table('productos as p')
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
                'p.tiene_entrada',
                'p.stock_preparados',
                'cat.cat_sig',
                DB::raw("
                    COALESCE(
                        (SELECT psd.precio_especial
                         FROM precios_dia_semana as psd
                         WHERE psd.IdProducto = p.IdProducto
                           AND psd.id_empresa_negocio = '{$id_empresa_negocio}'
                           AND psd.estado = 'Activo'
                           AND (psd.fecha_inicio_vigencia IS NULL OR psd.fecha_inicio_vigencia <= CURDATE())
                           AND (psd.fecha_fin_vigencia IS NULL OR psd.fecha_fin_vigencia >= CURDATE())
                           AND
                           (
                               (
                                   psd.dia_semana = {$dia_actual_carbon}
                                   AND psd.hora_inicio_vigencia <= psd.hora_fin_vigencia
                                   AND TIME('{$hora_actual_string}') >= psd.hora_inicio_vigencia
                                   AND TIME('{$hora_actual_string}') <= psd.hora_fin_vigencia
                               )
                               OR
                               (
                                   psd.dia_semana = MOD({$dia_actual_carbon} - 1 + 7, 7)
                                   AND psd.hora_inicio_vigencia > psd.hora_fin_vigencia
                                   AND TIME('{$hora_actual_string}') >= psd.hora_inicio_vigencia
                               )
                               OR
                               (
                                   psd.dia_semana = {$dia_actual_carbon}
                                   AND psd.hora_inicio_vigencia > psd.hora_fin_vigencia
                                   AND TIME('{$hora_actual_string}') <= psd.hora_fin_vigencia
                               )
                           )
                           ORDER BY psd.id_precio_dia DESC
                           LIMIT 1
                        ),
                        p.propun
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
                              AND id_empresa_negocio = '{$id_empresa_negocio}'
                        )
                        ELSE (
                            SELECT stock
                            FROM producto_stock
                            WHERE IdProducto = p.IdProducto
                              AND id_almacen = '{$almacen_id}'
                              AND id_empresa_negocio = '{$id_empresa_negocio}'
                        )
                    END as stock_disponible
                ")
            )
            ->join('producto_empresa as pe', 'pe.IdProducto', 'p.IdProducto')
            ->leftjoin('categorias as cat', 'cat.cat_id', '=', 'p.cat_id')
            ->leftjoin('producto_codigo as pc', 'pc.IdProducto', '=', 'p.IdProducto')
            ->where('p.promocion', '!=', '4')
            ->where('pe.id_empresa_negocio', $id_empresa_negocio);

        if ($searchText) {
            $query->where(function ($q) use ($searchText) {
                // Buscamos primero si es un código exacto (barras o procod)
                $q->where('p.codigo_barra', $searchText)
                  ->orWhere('p.procod', $searchText);

                // Luego, buscamos por palabras/iniciales en el nombre
                $words = explode(' ', $searchText);
                $q->orWhere(function ($subQuery) use ($words) {
                    foreach ($words as $word) {
                        if (!empty(trim($word))) {
                            $subQuery->where('p.pronom', 'like', '%' . $word . '%');
                        }
                    }
                });
            });
        } elseif ($categoryId) {
            // FIX: Filtramos por la categoría seleccionada si no hay búsqueda de texto
            $query->where('p.cat_id', $categoryId);
        }

        $productos = $query->groupBy('p.IdProducto')
                           ->orderBy('p.pronom', 'asc')
                           ->get();
                           
        $negocio = DB::table('empresa_negocios')->where('id_empresa_negocio', $id_empresa_negocio)->first();

        return response()->json([
            'vista' => view('empresas.kiosko.partials.productos_grid', compact('productos', 'negocio'))->render()
        ]);
    }


    public function menuPedido(Request $request)
    {
        $id_empresa_negocio = Auth::user()->id_empresa_negocio;
        $id_empresa = Auth::user()->IdEmpresa;

        $order_type = session()->get('kiosko_order_type');
        $mesa_id = session()->get('kiosko_mesa_id');
        $mesa_nombre = session()->get('kiosko_mesa_nombre');

        if (empty($order_type)) {
            return redirect()->route('kiosko.seleccion_servicio')->with('error', 'Por favor, inicia tu pedido seleccionando una mesa o "Para Llevar".');
        }

        $mesa_info = null;
        if ($order_type == 'salon' && $mesa_id) {
            $mesa_info = ['id' => $mesa_id, 'nombre' => $mesa_nombre];
        } else if ($order_type == 'llevar') {
            $mesa_info = ['nombre' => 'PARA LLEVAR'];
        }

        // ✅ MEJORA: Caché de categorías (1 hora = 3600 seg)
        $categorias = cache()->remember("categorias_empresa_{$id_empresa_negocio}", 3600, function() use ($id_empresa_negocio) {
            return DB::table('categorias')
                        ->where('id_empresa_negocio', $id_empresa_negocio)
                        ->where('visible', '1')
                        ->get();
        });

        // ✅ MEJORA: Obtener categoría predeterminada desde la caché (sin consulta extra)
        $cat_pred = $categorias->where('predeterminado', 1)->first();
        $cat_default_id = $cat_pred ? $cat_pred->cat_id : ($categorias->first() ? $categorias->first()->cat_id : null);

        $productos = collect();
        if ($cat_default_id) {
            // No se cargan productos aquí, se hace con AJAX.
        }

        // ✅ MEJORA: Caché de la configuración del negocio
        $negocio = cache()->remember("negocio_{$id_empresa_negocio}", 3600, function() use ($id_empresa_negocio) {
            return DB::table('empresa_negocios')->where('id_empresa_negocio', $id_empresa_negocio)->first();
        });

        $pedidoIdExistente = session()->get('kiosko_last_pedido_id');
        $cart = session()->get('kiosko_cart', []);
        $cartPedidoId = session()->get('kiosko_cart_pedido_id');

        // Si la mesa es de salón y no tiene pedido abierto, asegurar que no quede carrito viejo.
        if ($order_type == 'salon' && !empty($mesa_id) && empty($pedidoIdExistente) && !empty($cart)) {
            Log::info("Limpiando carrito anterior porque la mesa {$mesa_id} no tiene pedido abierto.");
            session()->forget('kiosko_cart');
            session()->forget('kiosko_cart_pedido_id');
            $cart = [];
        }

        $shouldLoadExistingPedido = $order_type == 'salon'
            && !empty($mesa_id)
            && !empty($pedidoIdExistente)
            && (
                empty($cart)
                || $cartPedidoId !== $pedidoIdExistente
            );

        if ($shouldLoadExistingPedido) {
            Log::info("Intentando cargar pedido existente ID: {$pedidoIdExistente} para mesa: {$mesa_id}");

            // ✅ MEJORA: Traer detalles del pedido con productos en UNA sola query y CORRECCIÓN de pronom
            $detallesPedidoDb = DB::table('pedidos_detalle')
                                  ->leftJoin('productos', 'pedidos_detalle.IdProducto', '=', 'productos.IdProducto')
                                  ->leftJoin('producto_stock', function($join) use ($id_empresa_negocio) {
                                        $join->on('productos.IdProducto', '=', 'producto_stock.IdProducto')
                                             ->where('producto_stock.id_almacen', '=', 1)
                                             ->where('producto_stock.id_empresa_negocio', '=', $id_empresa_negocio);
                                  })
                                  ->select(
                                      'pedidos_detalle.*',
                                      DB::raw('(pedidos_detalle.ped_det_can - IFNULL(pedidos_detalle.item_facturado, 0)) as cantidad_pendiente'),
                                      'producto_stock.stock as stock_disponible',
                                      'productos.pronom as descripcion' // ✅ CORRECCIÓN: 'pronom' pasa al carrito como 'descripcion'
                                  )
                                  ->where('pedidos_detalle.ped_id', $pedidoIdExistente)
                                  ->where('pedidos_detalle.estadoitem', '!=', 'Eliminado')
                                  ->whereRaw('ROUND(pedidos_detalle.ped_det_can - IFNULL(pedidos_detalle.item_facturado, 0), 4) > 0') 
                                  ->get();
            
            $newCart = [];
            foreach ($detallesPedidoDb as $detalle) {
                // ✅ MODIFICADO: Usar cantidad_pendiente en lugar de ped_det_can
                $newCart[(string)$detalle->IdProducto] = [
                    "id" => (string)$detalle->IdProducto,
                    "nombre" => $detalle->descripcion,
                    "precio" => (float)$detalle->ped_det_pre,
                    "cantidad" => (int)$detalle->cantidad_pendiente,
                    "icbper" => (int)$detalle->icbper_ind,
                    "stock" => (float)($detalle->stock_disponible ?? 0),
                    "observaciones" => $detalle->item_obs,
                    "is_old_item" => true,
                    // ✅ NUEVO: Agregar info adicional para referencia
                    "cantidad_original" => (int)$detalle->ped_det_can,
                    "cantidad_facturada" => (int)$detalle->item_facturado,
                    "pagado" => (int)($detalle->pagado ?? 0),
                    "entrada" => $detalle->entrada ?? null,
                ];
            }
            session()->put('kiosko_cart', $newCart);
            session()->put('kiosko_cart_pedido_id', $pedidoIdExistente);
            $cart = $newCart;
            Log::info("Pedido existente cargado con items pendientes. Carrito: " . json_encode($cart));
        }

        // ✅ MEJORA: Caché del ICBPER. Usamos 'value' para obtener solo la columna.
        $icbper_val = cache()->remember("icbper_empresa_{$id_empresa}", 3600, function() use ($id_empresa) {
            return DB::table('empresa')->where('IdEmpresa', $id_empresa)->value('icbper') ?? 0;
        });

        $mostrar_descuento_50 = false; // Cambia a true si quieres que se muestre el descuento del 50%

        return view('empresas.kiosko.menu_pedido', compact('categorias', 'productos', 'negocio', 'cat_default_id', 'cart', 'order_type', 'mesa_info', 'icbper_val', 'mostrar_descuento_50'));
    }

    public function getProductosPorCategoria($categoria_id)
    {
        $id_empresa_negocio = Auth::user()->id_empresa_negocio;
        $productos = DB::table('productos')
                        ->leftJoin('producto_stock', function($join) {
                            $join->on('productos.IdProducto', '=', 'producto_stock.IdProducto')
                                 ->where('producto_stock.id_almacen', '=', 1);
                        })
                        ->where('productos.cat_id', $categoria_id)
                        ->where('productos.promocion', '0')
                        ->select('productos.*', 'productos.propun as precio', 'producto_stock.stock as stock_disponible', 'tiene_entrada')
                        ->get();
        $negocio = DB::table('empresa_negocios')->where('id_empresa_negocio', $id_empresa_negocio)->first();

        return response()->json([
            'vista' => view('empresas.kiosko.partials.productos_grid', compact('productos', 'negocio'))->render()
        ]);
    }


    public function addToCart(Request $request)
{
    $productId = $request->input('id');
    $productName = $request->input('producto');
    $productPrice = $request->input('precio');
    $productIcbper = $request->input('icbper');
    $productStock = $request->input('stock');
    $productAcomp = $request->input('acompa');
    $productEntrada = $request->input('entrada');

    $cart = session()->get('kiosko_cart', []);

    $negocio = DB::table('empresa_negocios')->where('id_empresa_negocio', Auth::user()->id_empresa_negocio)->first();
    $permitir_venta_sin_stock = $negocio ? (int)$negocio->ven_sin_sto : 0;

    if (isset($cart[$productId])) {
        $newQuantity = $cart[$productId]['cantidad'] + 1;
        if ($permitir_venta_sin_stock == 0 && (float)$productStock >= 0 && $newQuantity > (float)$productStock) {
            return response()->json(['success' => false, 'message' => 'No hay suficiente stock.']);
        }
        $cart[$productId]['cantidad'] = $newQuantity;
    } else {
        if ($permitir_venta_sin_stock == 0 && (float)$productStock <= 0) {
             return response()->json(['success' => false, 'message' => 'No hay stock disponible.']);
        }
        $cart[$productId] = [
            "id" => $productId,
            "nombre" => $productName . ($productAcomp && $productAcomp != '0' ? ' - ' . $productAcomp : ''),
            "precio" => (float)$productPrice,
            "cantidad" => 1,
            "icbper" => (int)$productIcbper,
            "stock" => (float)$productStock,
            "observaciones" => "",
            "is_old_item" => false,
            "pagado" => 0, // <-- AGREGADO: Todo producto nuevo empieza sin pagar
            "entrada" => $productEntrada,
        ];
    }

    session()->put('kiosko_cart', $cart);
    return response()->json(['success' => true, 'cart' => array_values($cart)]);
}

    public function updateCartItem(Request $request)
{
    $productId = $request->input('id');
    $newQuantity = (int)$request->input('cantidad');
    $itemObs = $request->input('observaciones');
    $isOldItem = filter_var($request->input('is_old_item'), FILTER_VALIDATE_BOOLEAN);
    $newUnitPrice = $request->input('new_unit_price');
    $estadoPagado = (int)$request->input('pagado', 0);

    $cart = session()->get('kiosko_cart', []);

    if (isset($cart[$productId])) {
        // 1. Actualizamos la sesión (esto mantiene la vista rápida)
        $cart[$productId]['cantidad'] = $newQuantity;
        $cart[$productId]['observaciones'] = $itemObs;
        $cart[$productId]['pagado'] = $estadoPagado;

        if ($newUnitPrice !== null && Auth::user()->hasRole('admin')) {
            $cart[$productId]['precio'] = (float)$newUnitPrice;
        }

        // 2. PERSISTENCIA REAL: Si es un ítem que ya está en la mesa (base de datos)
        if ($isOldItem) {
            // Buscamos el ID del pedido actual que está en la sesión
            $pedidoId = session()->get('kiosko_last_pedido_id');

            if ($pedidoId) {
                DB::table('pedidos_detalle')
                    ->where('ped_id', $pedidoId)
                    ->where('IdProducto', $productId)
                    ->update([
                        'pagado' => $estadoPagado,
                        'item_obs' => $itemObs,
                        // Si el admin cambió el precio, también lo actualizamos en la BD
                        'ped_det_pre' => (Auth::user()->hasRole('admin') && $newUnitPrice !== null) ? (float)$newUnitPrice : DB::raw('ped_det_pre')
                    ]);
            }
        }

        session()->put('kiosko_cart', $cart);
        return response()->json(['success' => true]);
    }

    return response()->json(['success' => false, 'message' => 'Producto no encontrado.']);
}

    public function removeCartItem(Request $request)
    {
        $productId = $request->input('id');
        $cart = session()->get('kiosko_cart', []);

        if (isset($cart[$productId])) {
            // IMPROTANTE: Esta función solo debe permitir eliminar ítems que NO son del pedido original
            if (isset($cart[$productId]['is_old_item']) && $cart[$productId]['is_old_item'] === true) {
                return response()->json(['success' => false, 'message' => 'No se pueden eliminar ítems del pedido original desde aquí. Utiliza la opción de eliminación con autorización.']);
            }
            unset($cart[$productId]);
            session()->put('kiosko_cart', $cart);
            return response()->json(['success' => true, 'cart' => array_values($cart)]);
        }
        return response()->json(['success' => false, 'message' => 'Producto no encontrado en el carrito.']);
    }

    public function clearCart()
    {
        $cart = session()->get('kiosko_cart', []);
        $newCart = [];
        foreach ($cart as $id => $item) {
            if (isset($item['is_old_item']) && $item['is_old_item'] === true) {
                $newCart[$id] = $item;
            }
        }
        session()->put('kiosko_cart', $newCart);
        return response()->json(['success' => true, 'cart' => array_values($newCart)]);
    }

    public function getCartDetails()
    {
        $cart = session()->get('kiosko_cart', []);
        $empresa = DB::table('empresa')->where('IdEmpresa', Auth::user()->IdEmpresa)->first();
        $icbper_val = $empresa ? $empresa->icbper : 0;

        return response()->json([
            'success' => true,
            'vista' => view('empresas.kiosko.partials.cart_details', compact('cart', 'icbper_val'))->render()
        ]);
    }

    public function confirmacionPedido()
    {
        $cart = session()->get('kiosko_cart', []);
        if (empty($cart)) {
            return redirect()->route('kiosko.menu_pedido')->with('error', 'El carrito está vacío.');
        }

        $order_type = session()->get('kiosko_order_type', 'salon');
        $mesa_info = null;
        if ($order_type == 'salon') {
            $mesa_info = [
                'id' => session()->get('kiosko_mesa_id'),
                'nombre' => session()->get('kiosko_mesa_nombre')
            ];
        }

        $empresa_data = DB::table('empresa')->where('IdEmpresa', Auth::user()->IdEmpresa)->first();
        $icbper_val = $empresa_data ? $empresa_data->icbper : 0;

        $total_venta = 0;
        $total_icbper = 0;

        foreach ($cart as $item) {
            $total_venta += $item['cantidad'] * $item['precio'];
            if (isset($item['icbper']) && $item['icbper'] == 1) {
                $total_icbper += $item['cantidad'] * $icbper_val;
            }
        }
        $total_venta += $total_icbper;
        $total_venta = round($total_venta, 2);

        $tipo_documentos_identidad = DB::table('tipo_documento_identidad')->get();

        $last_pedido_id = session()->get('kiosko_last_pedido_id');

        return view('empresas.kiosko.confirmacion_pedido', compact('cart', 'total_venta', 'total_icbper', 'order_type', 'mesa_info', 'tipo_documentos_identidad', 'last_pedido_id'));
    }



    
    public function enviarComanda(Request $request)
    {
        if (Auth::user()->hasRole('admin')) {
            return $this->enviarComandaAdmin($request);
        } else {
            return $this->enviarComandaNormal($request);
        }
    }

    private function enviarComandaNormal(Request $request)
    {
        $cart = session()->get('kiosko_cart', []);
        if (empty($cart)) {
            if ($request->ajax()) {
                return response()->json(['success' => false, 'message' => 'El carrito está vacío, no se puede enviar un pedido vacío.']);
            }
            return redirect()->route('kiosko.menu_pedido')->with('error', 'El carrito está vacío, no se puede enviar un pedido vacío.');
        }

        $order_type = $request->input('order_type');
        if (empty($order_type)) {
            $order_type = session()->get('kiosko_order_type', 'salon');
        }

        $mesa_id = $request->input('mesa_id');
        if (empty($mesa_id)) {
            $mesa_id = session()->get('kiosko_mesa_id');
        }
        $id_empresa_negocio = Auth::user()->id_empresa_negocio;
        $id_empresa         = Auth::user()->IdEmpresa;
        $usuario_id         = Auth::user()->IdUsuario;
        $esAdmin            = Auth::user()->hasRole('admin');
        
        $envio_directo = $request->input('envio_directo', false);
        if ($envio_directo) {
            $cliente_num_doc        = '00000000';
            $nombre_recibido        = $request->input('nombre_cliente');
            $cliente_nom            = !empty($nombre_recibido) ? strtoupper($nombre_recibido) : 'VENTA AL PORTADOR';
            $cliente_dir            = '--';
            $cliente_tel            = null;
            $cliente_ref            = null;
            $Motorizado_delivery    = $request->input('Motorizado_delivery', null);
            $pagar                  = $request->input('pagar', null);
            $vuelto                 = $request->input('vuelto', null);
            $cliente_tdicod         = '1';
            $observaciones_generales = null;
        } else {
            $cliente_num_doc        = $request->input('cliente_num_doc', '00000000');
            $nombre_recibido        = $request->input('nombre_cliente', $request->input('cliente_nom'));
            $cliente_nom            = !empty($nombre_recibido) ? strtoupper($nombre_recibido) : 'VENTA AL PORTADOR';
            $cliente_dir            = $request->input('cliente_dir', '--');
            $cliente_tel            = $request->input('cliente_tel', null);
            $cliente_ref            = $request->input('cliente_ref', null);
            $Motorizado_delivery    = $request->input('Motorizado_delivery', null);
            $pagar                  = $request->input('pagar', null);
            $vuelto                 = $request->input('vuelto', null);
            $cliente_tdicod         = $request->input('cliente_tdicod', '1');
            $observaciones_generales = $request->input('observaciones_generales', null);
        }

        $empresa_data  = DB::table('empresa')->where('IdEmpresa', $id_empresa)->first();
        $icbper_val    = $empresa_data ? $empresa_data->icbper : 0;
        $total_venta_calculado  = 0;
        $total_icbper_calculado = 0;
        
        foreach ($cart as $item) {
            $total_venta_calculado += $item['cantidad'] * $item['precio'];
            if (isset($item['icbper']) && $item['icbper'] == 1) {
                $total_icbper_calculado += $item['cantidad'] * $icbper_val;
            }
        }
        $total_venta_calculado += $total_icbper_calculado;
        $total_venta_calculado  = round($total_venta_calculado, 2);
        
        $pedidoId = session()->get('kiosko_last_pedido_id');
        
        if ($pedidoId) {
            $pedidoExistente = DB::table('pedidos')
                ->where('ped_id', $pedidoId)
                ->where('id_empresa_negocio', $id_empresa_negocio)
                ->where('ped_est', '!=', 'Eliminado')
                ->first();
            $pedidoValido = false;
            if ($pedidoExistente) {
                if ($order_type === 'salon') {
                    $pedidoValido = $pedidoExistente->mes_id == $mesa_id;
                } else {
                    $pedidoValido = is_null($pedidoExistente->mes_id);
                }
            }
            if (!$pedidoValido) {
                Log::warning("KioskoController::enviarComandaNormal - Pedido {$pedidoId} en sesión no corresponde a la mesa actual. Se descarta y se creará uno nuevo.");
                session()->forget('kiosko_last_pedido_id');
                session()->forget('kiosko_cart_pedido_id');
                $pedidoId = null;
            }
        }

        // BLINDAJE CRÍTICO (Solo para mozos)
        if ($pedidoId) {
            if (empty($cart)) {
                Log::critical("BLINDAJE CRÍTICO: Carrito vacío detectado para pedido activo ID: {$pedidoId}. Usuario: " . Auth::user()->name);
                $mensaje_error = "⚠️ ERROR DE SESIÓN: El sistema no detectó los productos en tu pantalla. Esto ocurre por saturación del servidor. POR FAVOR, NO GUARDES. Recarga la página.";
                return response()->json(['success' => false, 'message' => $mensaje_error]);
            }
            $idsEnCarrito = array_unique(
                array_map(function ($item) {
                    return (string)(int)$item['id'];
                }, $cart)
            );
            $detallesExistentesEnBD = DB::table('pedidos_detalle')
                ->where('ped_id', $pedidoId)
                ->whereRaw('(ped_det_can - IFNULL(item_facturado, 0)) > 0')
                ->pluck('IdProducto')
                ->map(function ($id) { return (string)$id; })
                ->toArray();
            $productosFaltantes = array_diff($detallesExistentesEnBD, $idsEnCarrito);
            if (!empty($productosFaltantes)) {
                Log::warning("BLINDAJE PRE-TRANSACCION activado", [
                    'pedido_id'           => $pedidoId,
                    'productos_faltantes' => array_values($productosFaltantes),
                ]);
                $mensaje_alerta = "⚠️ ALERTA DE SEGURIDAD: Tu pantalla parece no tener todos los productos actuales de la mesa. Se bloqueó el guardado para evitar borrar productos existentes. Recarga la página (F5) para sincronizar.";
                return response()->json(['success' => false, 'message' => $mensaje_alerta]);
            }
        }

        // ESCÁNER DE STOCK ESTRICTO OPTIMIZADO (O(1) en lugar de O(N))
        $negocio_config = DB::table('empresa_negocios')->where('id_empresa_negocio', $id_empresa_negocio)->first();
        $vender_sin_stock = $negocio_config ? $negocio_config->ven_sin_sto == '1' : false;

        if (!$vender_sin_stock && !empty($cart)) {
            // 1. Extraer todos los IDs únicos del carrito
            $idsProductosCarrito = array_unique(array_map(function($item) {
                return (int)$item['id'];
            }, $cart));

            // 2. Traer todos los productos y stocks involucrados en DOS consultas, no en un bucle
            $productosDb = DB::table('productos')
                ->whereIn('IdProducto', $idsProductosCarrito)
                ->get()
                ->keyBy('IdProducto');

            $stocksDb = DB::table('producto_stock')
                ->whereIn('IdProducto', $idsProductosCarrito)
                ->get()
                ->keyBy('IdProducto');

            // --- NUEVA OPTIMIZACIÓN AQUÍ ---
            // Traemos todos los detalles viejos en una sola consulta
            $itemsViejosDb = collect();
            if ($pedidoId) {
                $itemsViejosDb = DB::table('pedidos_detalle')
                    ->where('ped_id', $pedidoId)
                    ->whereIn('IdProducto', $idsProductosCarrito)
                    ->get()
                    ->keyBy('IdProducto');
            }

            foreach ($cart as $item) {
                $productId = (int)$item['id'];
                $qty_solicitada = (float)$item['cantidad'];
                $qty_extra_pedida = $qty_solicitada; 
                
                if ($pedidoId) {
                    // Ahora se lee desde la RAM (O(1)), sin ir a la base de datos
                    $itemViejo = $itemsViejosDb->get($productId);
                        
                    if ($itemViejo) {
                        $qty_extra_pedida = $qty_solicitada - $itemViejo->ped_det_can;
                    }
                }

                if ($qty_extra_pedida > 0) {
                    $producto_db = $productosDb->get($productId);
                    
                    if ($producto_db) {
                        if ($producto_db->promocion == '2') {
                            $stock_real = (float)$producto_db->stock_preparados;
                        } elseif ($producto_db->promocion == '0') {
                            $stock_externo = $stocksDb->get($productId);
                            $stock_real = $stock_externo ? (float)$stock_externo->stock : 0;
                        } else {
                            $stock_real = 0;
                        }

                        if ($stock_real < $qty_extra_pedida) {
                            $nombre_plato = $producto_db->pronom;
                            return response()->json([
                                'success' => false, 
                                'message' => "⛔ STOCK INSUFICIENTE: Solo quedan {$stock_real} disponibles de '{$nombre_plato}'."
                            ]);
                        }
                    }
                }
            }
        }

        DB::beginTransaction();
        try {
            $oldCartQuantities = [];
            $almacen_obj = DB::table('almacenes')
                            ->where('predeterminado', '1')
                            ->where('id_empresa_negocio', $id_empresa_negocio)
                            ->first();
            $almacen_id = $almacen_obj ? $almacen_obj->id_almacen : 0;
            $mesa_pis_id = null;
            if ($order_type == 'salon' && $mesa_id) {
                $mesaObj = DB::table('mesas')->where('mes_id', $mesa_id)->first();
                $mesa_pis_id = $mesaObj ? $mesaObj->pis_id : null;
            }

            if ($pedidoId) {
                $oldDetails = DB::table('pedidos_detalle')
                                ->select('pedidos_detalle.*', DB::raw('(ped_det_can - IFNULL(item_facturado, 0)) as cantidad_pendiente'))
                                ->where('ped_id', $pedidoId)
                                ->whereRaw('(ped_det_can - IFNULL(item_facturado, 0)) > 0')
                                ->get();
                foreach ($oldDetails as $oldDet) {
                    $oldCartQuantities[(string)$oldDet->IdProducto] = [
                        'cantidad'             => (int)$oldDet->cantidad_pendiente,
                        'ped_det_id'           => (int)$oldDet->ped_det_id,
                        'precio'               => (float)$oldDet->ped_det_pre,
                        'observaciones'        => $oldDet->item_obs,
                        'icbper_ind'           => (int)$oldDet->icbper_ind,
                        'impreso'              => $oldDet->impreso,
                        'nombre'               => $oldDet->descripcion,
                        'cantidad_original_db' => (int)$oldDet->cantidad_pendiente,
                        'ped_det_can_total'    => (int)$oldDet->ped_det_can,
                    ];
                }
                DB::table('pedidos')->where('ped_id', $pedidoId)->update([
                    'ped_fac'                 => 0,
                    'ped_tip'                 => ucfirst($order_type),
                    'ped_fec'                 => Carbon::now()->toDateString(),
                    'pis_id'                  => $mesa_pis_id,
                    'mes_id'                  => $order_type == 'salon' ? $mesa_id : null,
                    'IdUsuarioMod'            => $usuario_id,
                    'ped_dir'                 => $cliente_dir,
                    'ped_cli_nom'             => $cliente_nom,
                    'ped_tel'                 => $cliente_tel,
                    'motorizado'              => $Motorizado_delivery,
                    'pagar'                   => $pagar,
                    'vuelto'                  => $vuelto,
                    'ped_ref'                 => $cliente_ref,
                    'ped_obs'                 => $observaciones_generales,
                    'tdicod'                  => $cliente_tdicod,
                    'ped_num_doc'             => $cliente_num_doc,
                    'ped_tot'                 => $total_venta_calculado,
                    'icbper_val'              => $icbper_val,
                    'icbper_tot'              => round($total_icbper_calculado, 2),
                    'fecha_hora_modificacion' => Carbon::now(),
                    'ped_est'                 => 'Aperturado',
                    'mozo'                    => $usuario_id,
                ]);
            } else {
                $pedidoId = DB::table('pedidos')->insertGetId([
                    'ped_fac'            => 0,
                    'ped_tip'            => ucfirst($order_type),
                    'ped_fec'            => Carbon::now()->toDateString(),
                    'pis_id'             => $mesa_pis_id,
                    'mes_id'             => $order_type == 'salon' ? $mesa_id : null,
                    'IdEmpresa'          => $id_empresa,
                    'id_empresa_negocio' => $id_empresa_negocio,
                    'ped_est'            => 'Aperturado',
                    'IdUsuario'          => $usuario_id,
                    'mozo'               => $usuario_id,
                    'ped_dir'            => $cliente_dir,
                    'ped_cli_nom'        => $cliente_nom,
                    'ped_num_doc'        => $cliente_num_doc,
                    'tdicod'             => $cliente_tdicod,
                    'ped_tel'            => $cliente_tel,
                    'ped_ref'            => $cliente_ref,
                    'motorizado'         => $Motorizado_delivery,
                    'pagar'              => $pagar,
                    'vuelto'             => $vuelto,
                    'ped_obs'            => $observaciones_generales,
                    'ped_tot'            => $total_venta_calculado,
                    'icbper_val'         => $icbper_val,
                    'icbper_tot'         => round($total_icbper_calculado, 2),
                    'fecha_hora'         => Carbon::now(),
                ]);
            }

            $itemsToPrint = [];
            
            foreach ($cart as $item) {
                $productId              = (int)$item['id'];
                $current_cart_qty       = (float)$item['cantidad'];
                $item_price             = (float)$item['precio'];
                $item_obs               = $item['observaciones'] ?? '';
                $item_icbper            = (int)$item['icbper'];
                $product_name_for_print = $item['nombre'];
                $entrada_seleccionada   = $item['entrada'] ?? null;
                $original_qty_db         = isset($oldCartQuantities[(string)$productId]) ? (float)$oldCartQuantities[(string)$productId]['cantidad_original_db'] : 0.0;
                $original_ped_det_id     = isset($oldCartQuantities[(string)$productId]) ? (int)$oldCartQuantities[(string)$productId]['ped_det_id'] : null;
                $original_impreso_status = isset($oldCartQuantities[(string)$productId]) ? $oldCartQuantities[(string)$productId]['impreso'] : 'impreso';
                $ped_det_can_total       = isset($oldCartQuantities[(string)$productId]) ? (int)$oldCartQuantities[(string)$productId]['ped_det_can_total'] : 0;
                
                $cantidad_a_comandar_y_descontar_stock = 0.0;
                $impreso_status_for_db = $original_impreso_status;

                if ($original_ped_det_id) {                    
                    if ($current_cart_qty > $original_qty_db) {
                        $cantidad_a_comandar_y_descontar_stock = $current_cart_qty - $original_qty_db;
                        $impreso_status_for_db                 = 'imprimir';
                        $nuevo_ped_det_can_total               = $ped_det_can_total + $cantidad_a_comandar_y_descontar_stock;
                        
                        // 1. Recuperar el registro actual para ver si ya tiene rondas previas
                        $itemDetalleActual = DB::table('pedidos_detalle')->where('ped_det_id', $original_ped_det_id)->first();
                        $rondas = [];

                        if (!empty($itemDetalleActual->rondas_envio)) {
                            $rondas = json_decode($itemDetalleActual->rondas_envio, true);
                        } else {
                            // Si es la primera vez que se convierte en "múltiple", registramos la primera ronda con la hora que ya tenía guardada
                            $rondas[] = [
                                'cant' => (float)$original_qty_db,
                                'hora' => $itemDetalleActual->fecha_hora,
                                'despachado' => false
                            ];
                        }

                        // 2. Insertar la nueva ronda que se está pidiendo JUSTO AHORA (las 3:50)
                        $rondas[] = [
                            'cant' => (float)$cantidad_a_comandar_y_descontar_stock,
                            'hora' => Carbon::now()->toDateTimeString(),
                            'despachado' => false
                        ];

                        // 3. Actualizar la fila única en la base de datos
                        DB::table('pedidos_detalle')->where('ped_det_id', $original_ped_det_id)->update([
                            'ped_det_can'  => $nuevo_ped_det_can_total,
                            'ped_det_pre'  => $item_price,
                            'item_obs'     => $item_obs,
                            'icbper_ind'   => $item_icbper,
                            'impreso'      => $impreso_status_for_db,
                            'mod_cant'     => $cantidad_a_comandar_y_descontar_stock,
                            'rondas_envio' => json_encode($rondas), // Guardamos el historial de tiempos
                            // NO tocamos el campo fecha_hora principal de la tabla para no alterar auditorías
                        ]);
                        
                        $affected = DB::table('productos')->where('IdProducto', $productId)->where('promocion', '2')->decrement('stock_preparados', $cantidad_a_comandar_y_descontar_stock);
                        if ($affected) {
                            $stockActual = DB::table('productos')->where('IdProducto', $productId)->value('stock_preparados');
                            DB::table('movimientos_preparados')->insert([
                                'producto_id'      => $productId,
                                'pedido_id'        => $pedidoId,
                                'tipo_movimiento'  => 'venta_comanda',
                                'cantidad'         => $cantidad_a_comandar_y_descontar_stock,
                                'stock_resultante' => $stockActual,
                                'observacion'      => 'Aumento en comanda existente',
                                'fecha_proceso'    => Carbon::today()->toDateString(),
                                'created_at'       => Carbon::now(),
                                'updated_at'       => Carbon::now(),
                            ]);
                        }
                        
                        $itemsToPrint[] = (object)[
                            'descripcion' => $product_name_for_print,
                            'ped_det_can' => $cantidad_a_comandar_y_descontar_stock,
                            'item_obs'    => $item_obs,
                            'ped_det_id'  => $original_ped_det_id,
                            'IdProducto'  => $productId,
                        ];
                    } elseif ($current_cart_qty < $original_qty_db) {
                        DB::rollBack();
                        return response()->json(['success' => false, 'message' => '⛔ Acción bloqueada. No tienes permisos para reducir cantidades que ya fueron comandadas.']);
                    } else {
                        DB::table('pedidos_detalle')->where('ped_det_id', $original_ped_det_id)->update([
                            'item_obs'   => $item_obs,
                            //'fecha_hora' => Carbon::now(),
                        ]);
                    }
                } elseif ($current_cart_qty > 0) {
                    $cantidad_a_comandar_y_descontar_stock = $current_cart_qty;
                    $impreso_status_for_db                 = 'imprimir';
                    $new_ped_det_id = DB::table('pedidos_detalle')->insertGetId([
                        'ped_id'         => $pedidoId,
                        'IdProducto'     => $productId,
                        'pagado'         => isset($item['pagado']) ? $item['pagado'] : 0,
                        'IdEmpresa'      => $id_empresa,
                        'detalle'        => $product_name_for_print,
                        'estadoitem'     => 'Ingresado',
                        'impreso'        => $impreso_status_for_db,
                        'ped_det_can'    => $current_cart_qty,
                        'ped_det_pre'    => $item_price,
                        'descripcion'    => $product_name_for_print,
                        'item_obs'       => $item_obs,
                        'entrada'        => $entrada_seleccionada,
                        'icbper_ind'     => $item_icbper,
                        'item_facturado' => 0.00,
                        'mod_cant'       => $cantidad_a_comandar_y_descontar_stock,
                        'fecha_hora'     => Carbon::now(),
                    ]);
                    
                    $affected = DB::table('productos')->where('IdProducto', $productId)->where('promocion', '2')->decrement('stock_preparados', $current_cart_qty);
                    if ($affected) {
                        $stockActual = DB::table('productos')->where('IdProducto', $productId)->value('stock_preparados');
                        DB::table('movimientos_preparados')->insert([
                            'producto_id'      => $productId,
                            'pedido_id'        => $pedidoId,
                            'tipo_movimiento'  => 'venta_comanda',
                            'cantidad'         => $current_cart_qty,
                            'stock_resultante' => $stockActual,
                            'observacion'      => 'Venta inicial en comanda',
                            'fecha_proceso'    => Carbon::today()->toDateString(),
                            'created_at'       => Carbon::now(),
                            'updated_at'       => Carbon::now(),
                        ]);
                    }
                    
                    $itemsToPrint[] = (object)[
                        'descripcion' => $product_name_for_print,
                        'ped_det_can' => $current_cart_qty,
                        'item_obs'    => $item_obs,
                        'ped_det_id'  => $new_ped_det_id,
                        'IdProducto'  => $productId,
                    ];
                }
                
                if (isset($oldCartQuantities[(string)$productId])) {
                    unset($oldCartQuantities[(string)$productId]);
                }
            }

            if (!empty($oldCartQuantities)) {
                DB::rollBack();
                return response()->json(['success' => false, 'message' => '⛔ Acción bloqueada. No tienes permisos para eliminar platos enteros.']);
            }

            if ($order_type == 'salon' && $mesa_id) {
                DB::table('mesas')->where('mes_id', $mesa_id)->update(['mes_est' => 'Ocupado']);
            }

            DB::commit();

            $empresanegocios_config = DB::table('empresa_negocios')->where('id_empresa_negocio', $id_empresa_negocio)->first();
            $this->imprimirComandaPorCategorias($pedidoId, $itemsToPrint);
            
            if ($order_type == 'llevar' || $order_type == 'delivery') {
                if (Auth::user()->hasRole('mozo')) {
                    $this->generarPrecuenta(new Request(['pedido_id' => $pedidoId]));
                    
                    session()->put('kiosko_last_pedido_id', $pedidoId);
                    session()->forget(['kiosko_cart', 'kiosko_order_type', 'kiosko_mesa_id', 'kiosko_mesa_nombre']);
                    if ($request->ajax()) {
                        return response()->json(['success' => true, 'message' => 'Pedido registrado exitosamente.']);
                    }
                    return redirect()->route('kiosko.seleccion_servicio');
                } else {
                    $this->generarPrecuenta(new Request(['pedido_id' => $pedidoId]));
                    
                    session()->put('kiosko_last_pedido_id', $pedidoId);
                    session()->forget(['kiosko_cart', 'kiosko_order_type', 'kiosko_mesa_id', 'kiosko_mesa_nombre']);
                    if ($request->ajax()) {
                        return response()->json(['success' => true, 'redirect' => url('/cobrarmesa/' . $pedidoId)]);
                    }
                    return redirect()->to('/cobrarmesa/' . $pedidoId);
                }
            } else {
                
                session()->put('kiosko_last_pedido_id', $pedidoId);
                session()->forget(['kiosko_cart', 'kiosko_order_type', 'kiosko_mesa_id', 'kiosko_mesa_nombre']);
                if ($request->ajax() || $envio_directo) {
                    return response()->json([
                        'success'   => true,
                        'message'   => 'Comanda enviada a cocina exitosamente.',
                        'pedido_id' => $pedidoId,
                    ]);
                }

                if ($empresanegocios_config->formato == 'TICKET' && $empresanegocios_config->ticket_pantalla == '1') {
                    return redirect()->route('kiosko.previsualizar_ticket', ['pedido_id' => $pedidoId]);
                } else {
                    return redirect()->route('kiosko.seleccion_servicio');
                }
            }

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Error al enviar comanda normal: " . $e->getMessage() . " en línea " . $e->getLine());

            if ($request->ajax()) {
                return response()->json(['success' => false, 'message' => 'Hubo un error al procesar tu pedido. Detalle: ' . $e->getMessage()]);
            }
            return redirect()->back()->withInput()->with('error', 'Hubo un error al procesar tu pedido. Detalle: ' . $e->getMessage());
        }
    }

    private function enviarComandaAdmin(Request $request)
    {
        $cart = session()->get('kiosko_cart', []);
        if (empty($cart)) {
            if ($request->ajax()) {
                return response()->json(['success' => false, 'message' => 'El carrito está vacío, no se puede enviar un pedido vacío.']);
            }
            return redirect()->route('kiosko.menu_pedido')->with('error', 'El carrito está vacío, no se puede enviar un pedido vacío.');
        }

        $order_type = $request->input('order_type');
        if (empty($order_type)) {
            $order_type = session()->get('kiosko_order_type', 'salon');
        }

        $mesa_id = $request->input('mesa_id');
        if (empty($mesa_id)) {
            $mesa_id = session()->get('kiosko_mesa_id');
        }
        $id_empresa_negocio = Auth::user()->id_empresa_negocio;
        $id_empresa         = Auth::user()->IdEmpresa;
        $usuario_id         = Auth::user()->IdUsuario;
        $esAdmin            = Auth::user()->hasRole('admin');
        
        $envio_directo = $request->input('envio_directo', false);
        if ($envio_directo) {
            $cliente_num_doc        = '00000000';
            $nombre_recibido        = $request->input('nombre_cliente');
            $cliente_nom            = !empty($nombre_recibido) ? strtoupper($nombre_recibido) : 'VENTA AL PORTADOR';
            $cliente_dir            = '--';
            $cliente_tel            = null;
            $cliente_ref            = null;
            $Motorizado_delivery    = $request->input('Motorizado_delivery', null);
            $pagar                  = $request->input('pagar', null);
            $vuelto                 = $request->input('vuelto', null);
            $cliente_tdicod         = '1';
            $observaciones_generales = null;
        } else {
            $cliente_num_doc        = $request->input('cliente_num_doc', '00000000');
            $nombre_recibido        = $request->input('nombre_cliente', $request->input('cliente_nom'));
            $cliente_nom            = !empty($nombre_recibido) ? strtoupper($nombre_recibido) : 'VENTA AL PORTADOR';
            $cliente_dir            = $request->input('cliente_dir', '--');
            $cliente_tel            = $request->input('cliente_tel', null);
            $cliente_ref            = $request->input('cliente_ref', null);
            $Motorizado_delivery    = $request->input('Motorizado_delivery', null);
            $pagar                  = $request->input('pagar', null);
            $vuelto                 = $request->input('vuelto', null);
            $cliente_tdicod         = $request->input('cliente_tdicod', '1');
            $observaciones_generales = $request->input('observaciones_generales', null);
        }

        $empresa_data  = DB::table('empresa')->where('IdEmpresa', $id_empresa)->first();
        $icbper_val    = $empresa_data ? $empresa_data->icbper : 0;
        $total_venta_calculado  = 0;
        $total_icbper_calculado = 0;
        
        foreach ($cart as $item) {
            $total_venta_calculado += $item['cantidad'] * $item['precio'];
            if (isset($item['icbper']) && $item['icbper'] == 1) {
                $total_icbper_calculado += $item['cantidad'] * $icbper_val;
            }
        }
        $total_venta_calculado += $total_icbper_calculado;
        $total_venta_calculado  = round($total_venta_calculado, 2);
        
        $pedidoId = session()->get('kiosko_last_pedido_id');
        
        if ($pedidoId) {
            $pedidoExistente = DB::table('pedidos')
                ->where('ped_id', $pedidoId)
                ->where('id_empresa_negocio', $id_empresa_negocio)
                ->where('ped_est', '!=', 'Eliminado')
                ->first();
            $pedidoValido = false;
            if ($pedidoExistente) {
                if ($order_type === 'salon') {
                    $pedidoValido = $pedidoExistente->mes_id == $mesa_id;
                } else {
                    $pedidoValido = is_null($pedidoExistente->mes_id);
                }
            }
            if (!$pedidoValido) {
                Log::warning("KioskoController::enviarComandaAdmin - Pedido {$pedidoId} en sesión no corresponde a la mesa actual. Se descarta y se creará uno nuevo.");
                session()->forget('kiosko_last_pedido_id');
                session()->forget('kiosko_cart_pedido_id');
                $pedidoId = null;
            }
        }

        // BLINDAJE CRÍTICO
        if ($pedidoId) {
            if (empty($cart)) {
                Log::critical("BLINDAJE CRÍTICO: Carrito vacío detectado para pedido activo ID: {$pedidoId}. Usuario: " . Auth::user()->name);
                $mensaje_error = "⚠️ ERROR DE SESIÓN: El sistema no detectó los productos en tu pantalla. Esto ocurre por saturación del servidor. POR FAVOR, NO GUARDES. Recarga la página.";
                return response()->json(['success' => false, 'message' => $mensaje_error]);
            }
            $idsEnCarrito = array_unique(
                array_map(function ($item) {
                    return (string)(int)$item['id'];
                }, $cart)
            );
            $detallesExistentesEnBD = DB::table('pedidos_detalle')
                ->where('ped_id', $pedidoId)
                ->whereRaw('(ped_det_can - IFNULL(item_facturado, 0)) > 0')
                ->pluck('IdProducto')
                ->map(function ($id) { return (string)$id; })
                ->toArray();
            $productosFaltantes = array_diff($detallesExistentesEnBD, $idsEnCarrito);
            if (!empty($productosFaltantes)) {
                Log::warning("BLINDAJE PRE-TRANSACCION activado", [
                    'pedido_id'           => $pedidoId,
                    'productos_faltantes' => array_values($productosFaltantes),
                ]);
                $mensaje_alerta = "⚠️ ALERTA DE SEGURIDAD: Tu pantalla parece no tener todos los productos actuales de la mesa. Se bloqueó el guardado para evitar borrar productos existentes. Recarga la página (F5) para sincronizar.";
                return response()->json(['success' => false, 'message' => $mensaje_alerta]);
            }
        }

        // ESCÁNER DE STOCK ESTRICTO OPTIMIZADO (O(1) en lugar de O(N))
        $negocio_config = DB::table('empresa_negocios')->where('id_empresa_negocio', $id_empresa_negocio)->first();
        $vender_sin_stock = $negocio_config ? $negocio_config->ven_sin_sto == '1' : false;

        if (!$vender_sin_stock && !empty($cart)) {
            // 1. Extraer todos los IDs únicos del carrito
            $idsProductosCarrito = array_unique(array_map(function($item) {
                return (int)$item['id'];
            }, $cart));

            // 2. Traer todos los productos y stocks involucrados en DOS consultas, no en un bucle
            $productosDb = DB::table('productos')
                ->whereIn('IdProducto', $idsProductosCarrito)
                ->get()
                ->keyBy('IdProducto');

            $stocksDb = DB::table('producto_stock')
                ->whereIn('IdProducto', $idsProductosCarrito)
                ->get()
                ->keyBy('IdProducto');

            // --- NUEVA OPTIMIZACIÓN AQUÍ ---
            // Traemos todos los detalles viejos en una sola consulta
            $itemsViejosDb = collect();
            if ($pedidoId) {
                $itemsViejosDb = DB::table('pedidos_detalle')
                    ->where('ped_id', $pedidoId)
                    ->whereIn('IdProducto', $idsProductosCarrito)
                    ->get()
                    ->keyBy('IdProducto');
            }

            foreach ($cart as $item) {
                $productId = (int)$item['id'];
                $qty_solicitada = (float)$item['cantidad'];
                $qty_extra_pedida = $qty_solicitada; 
                
                if ($pedidoId) {
                    // Ahora se lee desde la RAM (O(1)), sin ir a la base de datos
                    $itemViejo = $itemsViejosDb->get($productId);
                        
                    if ($itemViejo) {
                        $qty_extra_pedida = $qty_solicitada - $itemViejo->ped_det_can;
                    }
                }

                if ($qty_extra_pedida > 0) {
                    $producto_db = $productosDb->get($productId);
                    
                    if ($producto_db) {
                        if ($producto_db->promocion == '2') {
                            $stock_real = (float)$producto_db->stock_preparados;
                        } elseif ($producto_db->promocion == '0') {
                            $stock_externo = $stocksDb->get($productId);
                            $stock_real = $stock_externo ? (float)$stock_externo->stock : 0;
                        } else {
                            $stock_real = 0;
                        }

                        if ($stock_real < $qty_extra_pedida) {
                            $nombre_plato = $producto_db->pronom;
                            return response()->json([
                                'success' => false, 
                                'message' => "⛔ STOCK INSUFICIENTE: Solo quedan {$stock_real} disponibles de '{$nombre_plato}'."
                            ]);
                        }
                    }
                }
            }
        }

        DB::beginTransaction();
        try {
            $oldCartQuantities = [];
            $almacen_obj = DB::table('almacenes')
                            ->where('predeterminado', '1')
                            ->where('id_empresa_negocio', $id_empresa_negocio)
                            ->first();
            $almacen_id = $almacen_obj ? $almacen_obj->id_almacen : 0;
            $mesa_pis_id = null;
            if ($order_type == 'salon' && $mesa_id) {
                $mesaObj = DB::table('mesas')->where('mes_id', $mesa_id)->first();
                $mesa_pis_id = $mesaObj ? $mesaObj->pis_id : null;
            }

            if ($pedidoId) {
                $oldDetails = DB::table('pedidos_detalle')
                                ->select('pedidos_detalle.*', DB::raw('(ped_det_can - IFNULL(item_facturado, 0)) as cantidad_pendiente'))
                                ->where('ped_id', $pedidoId)
                                ->whereRaw('(ped_det_can - IFNULL(item_facturado, 0)) > 0')
                                ->get();
                foreach ($oldDetails as $oldDet) {
                    $oldCartQuantities[(string)$oldDet->IdProducto] = [
                        'cantidad'             => (int)$oldDet->cantidad_pendiente,
                        'ped_det_id'           => (int)$oldDet->ped_det_id,
                        'precio'               => (float)$oldDet->ped_det_pre,
                        'observaciones'        => $oldDet->item_obs,
                        'icbper_ind'           => (int)$oldDet->icbper_ind,
                        'impreso'              => $oldDet->impreso,
                        'nombre'               => $oldDet->descripcion,
                        'cantidad_original_db' => (int)$oldDet->cantidad_pendiente,
                        'ped_det_can_total'    => (int)$oldDet->ped_det_can,
                    ];
                }
                DB::table('pedidos')->where('ped_id', $pedidoId)->update([
                    'ped_fac'                 => 0,
                    'ped_tip'                 => ucfirst($order_type),
                    'ped_fec'                 => Carbon::now()->toDateString(),
                    'pis_id'                  => $mesa_pis_id,
                    'mes_id'                  => $order_type == 'salon' ? $mesa_id : null,
                    'IdUsuarioMod'            => $usuario_id,
                    'ped_dir'                 => $cliente_dir,
                    'ped_cli_nom'             => $cliente_nom,
                    'ped_tel'                 => $cliente_tel,
                    'motorizado'              => $Motorizado_delivery,
                    'pagar'                   => $pagar,
                    'vuelto'                  => $vuelto,
                    'ped_ref'                 => $cliente_ref,
                    'ped_obs'                 => $observaciones_generales,
                    'tdicod'                  => $cliente_tdicod,
                    'ped_num_doc'             => $cliente_num_doc,
                    'ped_tot'                 => $total_venta_calculado,
                    'icbper_val'              => $icbper_val,
                    'icbper_tot'              => round($total_icbper_calculado, 2),
                    'fecha_hora_modificacion' => Carbon::now(),
                    'ped_est'                 => 'Aperturado',
                    'mozo'                    => $usuario_id,
                ]);
            } else {
                $pedidoId = DB::table('pedidos')->insertGetId([
                    'ped_fac'            => 0,
                    'ped_tip'            => ucfirst($order_type),
                    'ped_fec'            => Carbon::now()->toDateString(),
                    'pis_id'             => $mesa_pis_id,
                    'mes_id'             => $order_type == 'salon' ? $mesa_id : null,
                    'IdEmpresa'          => $id_empresa,
                    'id_empresa_negocio' => $id_empresa_negocio,
                    'ped_est'            => 'Aperturado',
                    'IdUsuario'          => $usuario_id,
                    'mozo'               => $usuario_id,
                    'ped_dir'            => $cliente_dir,
                    'ped_cli_nom'        => $cliente_nom,
                    'ped_num_doc'        => $cliente_num_doc,
                    'tdicod'             => $cliente_tdicod,
                    'ped_tel'            => $cliente_tel,
                    'ped_ref'            => $cliente_ref,
                    'motorizado'         => $Motorizado_delivery,
                    'pagar'              => $pagar,
                    'vuelto'             => $vuelto,
                    'ped_obs'            => $observaciones_generales,
                    'ped_tot'            => $total_venta_calculado,
                    'icbper_val'         => $icbper_val,
                    'icbper_tot'         => round($total_icbper_calculado, 2),
                    'fecha_hora'         => Carbon::now(),
                ]);
            }

            $itemsToPrint = [];
            
            foreach ($cart as $item) {
                $productId              = (int)$item['id'];
                $current_cart_qty       = (float)$item['cantidad'];
                $item_price             = (float)$item['precio'];
                $item_obs               = $item['observaciones'] ?? '';
                $item_icbper            = (int)$item['icbper'];
                $product_name_for_print = $item['nombre'];
                $entrada_seleccionada   = $item['entrada'] ?? null;
                $original_qty_db         = isset($oldCartQuantities[(string)$productId]) ? (float)$oldCartQuantities[(string)$productId]['cantidad_original_db'] : 0.0;
                $original_ped_det_id     = isset($oldCartQuantities[(string)$productId]) ? (int)$oldCartQuantities[(string)$productId]['ped_det_id'] : null;
                $original_impreso_status = isset($oldCartQuantities[(string)$productId]) ? $oldCartQuantities[(string)$productId]['impreso'] : 'impreso';
                $ped_det_can_total       = isset($oldCartQuantities[(string)$productId]) ? (int)$oldCartQuantities[(string)$productId]['ped_det_can_total'] : 0;
                
                $cantidad_a_comandar_y_descontar_stock = 0.0;
                $impreso_status_for_db = $original_impreso_status;

                if ($original_ped_det_id) {                    
                    if ($current_cart_qty > $original_qty_db) {
                        $cantidad_a_comandar_y_descontar_stock = $current_cart_qty - $original_qty_db;
                        $impreso_status_for_db                 = 'imprimir';
                        $nuevo_ped_det_can_total               = $ped_det_can_total + $cantidad_a_comandar_y_descontar_stock;
                        
                        // 1. Recuperar el registro actual para ver si ya tiene rondas previas
                        $itemDetalleActual = DB::table('pedidos_detalle')->where('ped_det_id', $original_ped_det_id)->first();
                        $rondas = [];

                        if (!empty($itemDetalleActual->rondas_envio)) {
                            $rondas = json_decode($itemDetalleActual->rondas_envio, true);
                        } else {
                            // Si es la primera vez que se convierte en "múltiple", registramos la primera ronda con la hora que ya tenía guardada
                            $rondas[] = [
                                'cant' => (float)$original_qty_db,
                                'hora' => $itemDetalleActual->fecha_hora,
                                'despachado' => false
                            ];
                        }

                        // 2. Insertar la nueva ronda que se está pidiendo JUSTO AHORA (las 3:50)
                        $rondas[] = [
                            'cant' => (float)$cantidad_a_comandar_y_descontar_stock,
                            'hora' => Carbon::now()->toDateTimeString(),
                            'despachado' => false
                        ];

                        // 3. Actualizar la fila única en la base de datos
                        DB::table('pedidos_detalle')->where('ped_det_id', $original_ped_det_id)->update([
                            'ped_det_can'  => $nuevo_ped_det_can_total,
                            'ped_det_pre'  => $item_price,
                            'item_obs'     => $item_obs,
                            'icbper_ind'   => $item_icbper,
                            'impreso'      => $impreso_status_for_db,
                            'mod_cant'     => $cantidad_a_comandar_y_descontar_stock,
                            'rondas_envio' => json_encode($rondas), // Guardamos el historial de tiempos
                            // NO tocamos el campo fecha_hora principal de la tabla para no alterar auditorías
                        ]);
                        
                        $affected = DB::table('productos')->where('IdProducto', $productId)->where('promocion', '2')->decrement('stock_preparados', $cantidad_a_comandar_y_descontar_stock);
                        if ($affected) {
                            $stockActual = DB::table('productos')->where('IdProducto', $productId)->value('stock_preparados');
                            DB::table('movimientos_preparados')->insert([
                                'producto_id'      => $productId,
                                'pedido_id'        => $pedidoId,
                                'tipo_movimiento'  => 'venta_comanda',
                                'cantidad'         => $cantidad_a_comandar_y_descontar_stock,
                                'stock_resultante' => $stockActual,
                                'observacion'      => 'Aumento en comanda existente por Admin',
                                'fecha_proceso'    => Carbon::today()->toDateString(),
                                'created_at'       => Carbon::now(),
                                'updated_at'       => Carbon::now(),
                            ]);
                        }
                        
                        $itemsToPrint[] = (object)[
                            'descripcion' => $product_name_for_print,
                            'ped_det_can' => $cantidad_a_comandar_y_descontar_stock,
                            'item_obs'    => $item_obs,
                            'ped_det_id'  => $original_ped_det_id,
                            'IdProducto'  => $productId,
                        ];
                    } elseif ($current_cart_qty < $original_qty_db) {
                        $cantidad_a_revertir     = $original_qty_db - $current_cart_qty;
                        $impreso_status_for_db   = 'impreso';
                        $nuevo_ped_det_can_total = $ped_det_can_total - $cantidad_a_revertir;
                        
                        DB::table('pedidos_detalle')->where('ped_det_id', $original_ped_det_id)->update([
                            'ped_det_can' => $nuevo_ped_det_can_total,
                            'ped_det_pre' => $item_price,
                            'item_obs'    => $item_obs,
                            'icbper_ind'  => $item_icbper,
                            'impreso'     => $impreso_status_for_db,
                            'mod_cant'    => -$cantidad_a_revertir,
                            'fecha_hora'  => Carbon::now(),
                        ]);
                        
                        $affected = DB::table('productos')->where('IdProducto', $productId)->where('promocion', '2')->increment('stock_preparados', $cantidad_a_revertir);
                        if ($affected) {
                            $stockActual = DB::table('productos')->where('IdProducto', $productId)->value('stock_preparados');
                            DB::table('movimientos_preparados')->insert([
                                'producto_id'      => $productId,
                                'pedido_id'        => $pedidoId,
                                'tipo_movimiento'  => 'devolucion_comanda',
                                'cantidad'         => $cantidad_a_revertir,
                                'stock_resultante' => $stockActual,
                                'observacion'      => 'Anulación parcial en comanda por Admin',
                                'fecha_proceso'    => Carbon::today()->toDateString(),
                                'created_at'       => Carbon::now(),
                                'updated_at'       => Carbon::now(),
                            ]);
                        }
                        
                        $itemAnuladoParaImprimir = (object)[
                            'IdProducto'  => $productId,
                            'descripcion' => $product_name_for_print,
                            'ped_det_can' => $cantidad_a_revertir,
                            'item_obs'    => $item_obs,
                            'ped_det_id'  => $original_ped_det_id,
                        ];
                        $this->imprimirComandaItemAnulado($pedidoId, $itemAnuladoParaImprimir, 'Anulación Parcial de Cantidad', Auth::user()->name);
                    } else {
                        DB::table('pedidos_detalle')->where('ped_det_id', $original_ped_det_id)->update([
                            'ped_det_pre' => $item_price,
                            'item_obs'    => $item_obs,
                            //'fecha_hora'  => Carbon::now(),
                        ]);
                    }
                } elseif ($current_cart_qty > 0) {
                    $cantidad_a_comandar_y_descontar_stock = $current_cart_qty;
                    $impreso_status_for_db                 = 'imprimir';
                    $new_ped_det_id = DB::table('pedidos_detalle')->insertGetId([
                        'ped_id'         => $pedidoId,
                        'IdProducto'     => $productId,
                        'pagado'         => isset($item['pagado']) ? $item['pagado'] : 0,
                        'IdEmpresa'      => $id_empresa,
                        'detalle'        => $product_name_for_print,
                        'estadoitem'     => 'Ingresado',
                        'impreso'        => $impreso_status_for_db,
                        'ped_det_can'    => $current_cart_qty,
                        'ped_det_pre'    => $item_price,
                        'descripcion'    => $product_name_for_print,
                        'item_obs'       => $item_obs,
                        'entrada'        => $entrada_seleccionada,
                        'icbper_ind'     => $item_icbper,
                        'item_facturado' => 0.00,
                        'mod_cant'       => $cantidad_a_comandar_y_descontar_stock,
                        'fecha_hora'     => Carbon::now(),
                    ]);
                    
                    $affected = DB::table('productos')->where('IdProducto', $productId)->where('promocion', '2')->decrement('stock_preparados', $current_cart_qty);
                    if ($affected) {
                        $stockActual = DB::table('productos')->where('IdProducto', $productId)->value('stock_preparados');
                        DB::table('movimientos_preparados')->insert([
                            'producto_id'      => $productId,
                            'pedido_id'        => $pedidoId,
                            'tipo_movimiento'  => 'venta_comanda',
                            'cantidad'         => $current_cart_qty,
                            'stock_resultante' => $stockActual,
                            'observacion'      => 'Venta inicial en comanda por Admin',
                            'fecha_proceso'    => Carbon::today()->toDateString(),
                            'created_at'       => Carbon::now(),
                            'updated_at'       => Carbon::now(),
                        ]);
                    }
                    
                    $itemsToPrint[] = (object)[
                        'descripcion' => $product_name_for_print,
                        'ped_det_can' => $current_cart_qty,
                        'item_obs'    => $item_obs,
                        'ped_det_id'  => $new_ped_det_id,
                        'IdProducto'  => $productId,
                    ];
                }
                
                if (isset($oldCartQuantities[(string)$productId])) {
                    unset($oldCartQuantities[(string)$productId]);
                }
            }

            if (!empty($oldCartQuantities)) {
                foreach ($oldCartQuantities as $productIdStr => $oldDet) {
                    $ped_det_id          = $oldDet['ped_det_id'];
                    $cantidad_a_revertir = $oldDet['cantidad_original_db'];
                    
                    DB::table('pedidos_detalle')->where('ped_det_id', $ped_det_id)->update([
                        'ped_det_can' => 0,
                        'estadoitem'  => 'Eliminado',
                        'mod_cant'    => -$cantidad_a_revertir,
                        'fecha_hora'  => Carbon::now(),
                    ]);
                    
                    $affected = DB::table('productos')->where('IdProducto', $productIdStr)->where('promocion', '2')->increment('stock_preparados', $cantidad_a_revertir);
                    if ($affected) {
                        $stockActual = DB::table('productos')->where('IdProducto', $productIdStr)->value('stock_preparados');
                        DB::table('movimientos_preparados')->insert([
                            'producto_id'      => $productIdStr,
                            'pedido_id'        => $pedidoId,
                            'tipo_movimiento'  => 'devolucion_comanda',
                            'cantidad'         => $cantidad_a_revertir,
                            'stock_resultante' => $stockActual,
                            'observacion'      => 'Eliminación total en comanda por Admin',
                            'fecha_proceso'    => Carbon::today()->toDateString(),
                            'created_at'       => Carbon::now(),
                            'updated_at'       => Carbon::now(),
                        ]);
                    }
                    
                    $itemAnuladoParaImprimir = (object)[
                        'IdProducto'  => $productIdStr,
                        'descripcion' => $oldDet['nombre'],
                        'ped_det_can' => $cantidad_a_revertir,
                        'item_obs'    => $oldDet['observaciones'],
                        'ped_det_id'  => $ped_det_id,
                    ];
                    $this->imprimirComandaItemAnulado($pedidoId, $itemAnuladoParaImprimir, 'Eliminación Total de Ítem', Auth::user()->name);
                }
            }

            if ($order_type == 'salon' && $mesa_id) {
                DB::table('mesas')->where('mes_id', $mesa_id)->update(['mes_est' => 'Ocupado']);
            }

            DB::commit();

            $empresanegocios_config = DB::table('empresa_negocios')->where('id_empresa_negocio', $id_empresa_negocio)->first();
            $this->imprimirComandaPorCategorias($pedidoId, $itemsToPrint);
            
            $activeItemsCount = DB::table('pedidos_detalle')
                ->where('ped_id', $pedidoId)
                ->where('estadoitem', '!=', 'Eliminado')
                ->where('ped_det_can', '>', 0)
                ->count();
                
            if ($activeItemsCount === 0) {
                Log::warning("Pedido ID {$pedidoId} quedó sin ítems activos tras eliminación por Admin.");
                DB::rollBack();
                if ($request->ajax()) {
                    return response()->json(['success' => false, 'message' => 'El pedido quedó sin productos. Agrega al menos un producto para continuar.']);
                }
                return redirect()->route('kiosko.menu_pedido')->with('error', 'El pedido quedó sin productos. Agrega al menos un producto para continuar.');
            }

            if ($order_type == 'llevar' || $order_type == 'delivery') {
                if (Auth::user()->hasRole('mozo')) {
                    $this->generarPrecuenta(new Request(['pedido_id' => $pedidoId]));
                    
                    session()->put('kiosko_last_pedido_id', $pedidoId);
                    session()->forget(['kiosko_cart', 'kiosko_order_type', 'kiosko_mesa_id', 'kiosko_mesa_nombre']);
                    if ($request->ajax()) {
                        return response()->json(['success' => true, 'message' => 'Pedido registrado exitosamente.']);
                    }
                    return redirect()->route('kiosko.seleccion_servicio');
                } else {
                    $this->generarPrecuenta(new Request(['pedido_id' => $pedidoId]));
                    
                    session()->put('kiosko_last_pedido_id', $pedidoId);
                    session()->forget(['kiosko_cart', 'kiosko_order_type', 'kiosko_mesa_id', 'kiosko_mesa_nombre']);
                    if ($request->ajax()) {
                        return response()->json(['success' => true, 'redirect' => url('/cobrarmesa/' . $pedidoId)]);
                    }
                    return redirect()->to('/cobrarmesa/' . $pedidoId);
                }
            } else {
                
                session()->put('kiosko_last_pedido_id', $pedidoId);
                session()->forget(['kiosko_cart', 'kiosko_order_type', 'kiosko_mesa_id', 'kiosko_mesa_nombre']);
                if ($request->ajax() || $envio_directo) {
                    return response()->json([
                        'success'   => true,
                        'message'   => 'Comanda enviada a cocina exitosamente.',
                        'pedido_id' => $pedidoId,
                    ]);
                }

                if ($empresanegocios_config->formato == 'TICKET' && $empresanegocios_config->ticket_pantalla == '1') {
                    return redirect()->route('kiosko.previsualizar_ticket', ['pedido_id' => $pedidoId]);
                } else {
                    return redirect()->route('kiosko.seleccion_servicio');
                }
            }

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Error al enviar comanda admin: " . $e->getMessage() . " en línea " . $e->getLine());

            if ($request->ajax()) {
                return response()->json(['success' => false, 'message' => 'Hubo un error al procesar tu pedido. Detalle: ' . $e->getMessage()]);
            }
            return redirect()->back()->withInput()->with('error', 'Hubo un error al procesar tu pedido. Detalle: ' . $e->getMessage());
        }
    }
    
    public function indexStockPreparados()
    {
        // Consultamos los productos preparados que tienen stock mayor a 0 actualmente
        // Esto sirve como un "Reporte de disponibilidad actual"
        $preparados = DB::table('productos')
                    ->where('promocion', '2') //
                    ->where('stock_preparados', '>', 0)
                    ->orderBy('pronom', 'asc')
                    ->get();

        return view('empresas.kiosko.index_preparados', compact('preparados'));
    }

    public function vistaStockPreparados()
    {
        // Buscamos solo los productos preparados (promocion = 2)
        // Ordenamos por nombre para que sea más fácil para el chef buscar
        $productos = DB::table('productos')
                    ->where('promocion', '2')
                    ->orderBy('pronom', 'asc')
                    ->get();

        return view('empresas.kiosko.stock_preparados', compact('productos'));
    }

    public function guardarStockPreparados(Request $request)
    {
        // Recibimos un array desde la vista donde la llave es el IdProducto y el valor es la cantidad
        $stocks = $request->input('stocks');

        if ($stocks) {
            foreach ($stocks as $idProducto => $cantidad) {
                // Actualizamos el campo stock_preparados de cada producto enviado
                DB::table('productos')
                    ->where('IdProducto', $idProducto)
                    ->update(['stock_preparados' => $cantidad]);
            }
        }

        // Retornamos a la vista con un mensaje de éxito
        return redirect()->back()->with('success', '¡El stock del día se ha guardado correctamente!');
    }


    private function descontar_stock_por_item($productId, $quantity, $id_empresa_negocio, $pedidoId = null, $productName = null, $almacenId)
    {
        try {
            // Busca el producto en la tabla `productos` para obtener su tipo, factor, etc.
            $product_info = productos::find($productId); 

            if (!$product_info) {
                Log::warning("Descontar Stock: Producto ID {$productId} no encontrado.");
                return;
            }

            // Intenta obtener el nombre del cliente del pedido para la descripción del movimiento
            $cliente_nom = DB::table('pedidos')->where('ped_id', $pedidoId)->first()->ped_cli_nom ?? 'N/A';
            $descripcion_movimiento_base = "Salida por Comanda (Pedido ID: {$pedidoId})";

            // Lógica para afectar el stock según el tipo de producto ('promocion' es el campo que usas para clasificar)
            if ($product_info->promocion == '0' || $product_info->promocion == '1') { // Producto normal o simple
                // Si es una presentación (tipo 2) y tiene producto relacionado (pro_rel), se descuenta el stock del producto base.
                $id_prod_a_afectar = empty($product_info->pro_rel) ? $product_info->IdProducto : $product_info->pro_rel;
                $factor_conversion = $product_info->factor ?: 1;
                $cantidad_real_a_afectar = $quantity * $factor_conversion;

                // 1. Insertar registro en `movimientos_productos` (historial de salida)
                DB::table('movimientos_productos')->insert([
                    'IdProducto' => $product_info->IdProducto, // ID del producto tal como se seleccionó en el carrito
                    'IdProducto_rel' => $id_prod_a_afectar,   // ID del producto que realmente se ve afectado en stock (el base)
                    'precio' => $product_info->propun,
                    'cantidad' => $cantidad_real_a_afectar, // Cantidad en la unidad del stock base
                    'costo' => $product_info->costo_total,
                    'cliente' => $cliente_nom,
                    'IdCpe_cabecera' => null, 
                    'serie' => null,
                    'numero' => null,
                    'tdocod' => 'NP', // Tipo de documento: Nota de Pedido
                    'tipo' => '3', // Categoría de movimiento: Salida por venta/comanda
                    'mov_tip' => 'E', // Tipo de operación: Egreso
                    'id_empresa_negocio' => $id_empresa_negocio,
                    'id_almacen' => $almacenId,
                    'fecha_mov' => Carbon::now()->toDateString(),
                    'descripcion' => $descripcion_movimiento_base . " - " . ($productName ?? $product_info->pronom),
                    'mov_lote' => $product_info->lote, // Lote del producto vendido (si aplica)
                    'mov_vencimiento' => $product_info->vencimiento, // Vencimiento del producto vendido (si aplica)
                    'fecha_registro' => Carbon::now(), // Fecha y hora del registro del movimiento
                ]);

                // 2. Actualizar el stock en la tabla `producto_stock`
                $stock_query = DB::table('producto_stock')
                                ->where('IdProducto', $id_prod_a_afectar)
                                ->where('id_almacen', $almacenId)
                                ->where('id_empresa_negocio', $id_empresa_negocio);
                
                // Si el producto maneja lotes y vencimientos, intentamos filtrar por ellos.
                // *NOTA*: Si tu `pedidos_detalle` o el carrito no guarda el lote/vencimiento específico
                // del stock que se vendió, esta parte podría necesitar un ajuste para seleccionar el lote "correcto"
                // (ej. el más antiguo, o el que tiene más stock) o para no usar el lote en la condición si no es relevante para tu operativa.
                if ($product_info->requiere_lote_vencimiento) {
                    // Aquí, por simplicidad, si no hay lote/vencimiento específico en el detalle del pedido,
                    // busca el primer registro de stock con lote/vencimiento para ese producto y almacén.
                    // Si prefieres descontar del stock general sin importar el lote, elimina este `if`.
                    $stock_item_lote_venc = DB::table('producto_stock')
                                            ->where('IdProducto', $id_prod_a_afectar)
                                            ->where('id_almacen', $almacenId)
                                            ->where('id_empresa_negocio', $id_empresa_negocio)
                                            ->whereNotNull('lote') // Busca una entrada con lote definido
                                            ->whereNotNull('vencimiento') // Busca una entrada con vencimiento definido
                                            ->first(); 
                    if ($stock_item_lote_venc) {
                        $stock_query->where('lote', $stock_item_lote_venc->lote)
                                    ->where('vencimiento', $stock_item_lote_venc->vencimiento);
                    }
                }
                $stock_query->decrement('stock', $cantidad_real_a_afectar); // Realiza el descuento en `producto_stock`
                
                Log::info("Descontado stock de producto simple ID {$productId} (base: {$id_prod_a_afectar}) en cantidad: {$cantidad_real_a_afectar} en almacén {$almacenId}.");

            } elseif ($product_info->promocion == '2') { // Producto tipo 'receta' (ej. Tragos/Comidas)
                // Para productos de tipo 'receta', se descuenta el stock de sus INSUMOS.
                $recetas = DB::table('recetas')->where('prod_id', $productId)->get(); 

                foreach ($recetas as $rec) {
                    $insumo_cantidad_a_afectar = $quantity * $rec->rec_cant; // Cantidad del insumo a descontar
                    $insumo_info = productos::find($rec->prod_insu); // Obtener información del insumo

                    if (!$insumo_info) {
                        Log::warning("Descontar Stock (Receta): Insumo ID {$rec->prod_insu} no encontrado para receta del producto {$productId}. Saltando.");
                        continue;
                    }

                    // 1. Insertar registro en `movimientos_productos` para el INSUMO
                    DB::table('movimientos_productos')->insert([
                        'IdProducto' => $rec->prod_insu,
                        'IdProducto_rel' => $rec->prod_insu,
                        'precio' => 0, // Insumo, no tiene precio de venta directo
                        'cantidad' => $insumo_cantidad_a_afectar,
                        'costo' => $rec->ins_costo, // Costo del insumo
                        'cliente' => $cliente_nom,
                        'IdCpe_cabecera' => null,
                        'serie' => null,
                        'numero' => null,
                        'tdocod' => 'NP',
                        'tipo' => '3',
                        'mov_tip' => 'E',
                        'id_empresa_negocio' => $id_empresa_negocio,
                        'id_almacen' => $almacenId,
                        'fecha_mov' => Carbon::now()->toDateString(),
                        'descripcion' => $descripcion_movimiento_base . " - Consumo Receta: " . ($productName ?? $product_info->pronom) . " (Insumo: " . $insumo_info->pronom . ")",
                        'fecha_registro' => Carbon::now(),
                    ]);

                    // 2. Actualizar el stock del INSUMO en `producto_stock`
                    $stock_insumo_query = DB::table('producto_stock')
                                            ->where('IdProducto', $rec->prod_insu)
                                            ->where('id_almacen', $almacenId)
                                            ->where('id_empresa_negocio', $id_empresa_negocio);

                    if ($insumo_info->requiere_lote_vencimiento) {
                        $stock_insumo_lote_venc = DB::table('producto_stock')
                                                ->where('IdProducto', $rec->prod_insu)
                                                ->where('id_almacen', $almacenId)
                                                ->where('id_empresa_negocio', $id_empresa_negocio)
                                                ->whereNotNull('lote')
                                                ->whereNotNull('vencimiento')
                                                ->first();
                        if ($stock_insumo_lote_venc) {
                            $stock_insumo_query->where('lote', $stock_insumo_lote_venc->lote)
                                               ->where('vencimiento', $stock_insumo_lote_venc->vencimiento);
                        }
                    }
                    $stock_insumo_query->decrement('stock', $insumo_cantidad_a_afectar);
                    
                    Log::info("Descontado stock de insumo ID {$rec->prod_insu} (de receta de {$productId}) en cantidad: {$insumo_cantidad_a_afectar} en almacén {$almacenId}.");
                }
            } elseif ($product_info->promocion == '3') { // Producto tipo 'combo'
                // Para combos, se descuentan los COMPONENTES del combo.
                // Asume que `IdProducto_rel` en `combos` es el ID del combo principal.
                $combo_items = DB::table('combos')->where('IdProducto_rel', $productId)->get(); 

                foreach ($combo_items as $combo_item) {
                    $component_product = productos::find($combo_item->IdProducto_comb); // ID del producto componente

                    if (!$component_product) {
                        Log::warning("Descontar Stock (Combo): Componente ID {$combo_item->IdProducto_comb} no encontrado para combo {$productId}. Saltando.");
                        continue;
                    }
                    
                    // Cantidad del componente a afectar, multiplicada por la cantidad del combo vendido.
                    $component_quantity_to_affect = $quantity * $combo_item->prod_comb_cant; 

                    // La lógica para descontar el componente depende de si es un producto simple o una receta
                    if ($component_product->promocion == '0' || $component_product->promocion == '1') { // Componente simple dentro del combo
                        // 1. Insertar registro en `movimientos_productos` para el componente simple
                        DB::table('movimientos_productos')->insert([
                            'IdProducto' => $component_product->IdProducto,
                            'IdProducto_rel' => $component_product->IdProducto,
                            'precio' => $component_product->propun,
                            'cantidad' => $component_quantity_to_affect,
                            'costo' => $component_product->costo_total,
                            'cliente' => $cliente_nom,
                            'IdCpe_cabecera' => null,
                            'serie' => null,
                            'numero' => null,
                            'tdocod' => 'NP',
                            'tipo' => '3',
                            'mov_tip' => 'E',
                            'id_empresa_negocio' => $id_empresa_negocio,
                            'id_almacen' => $almacenId,
                            'fecha_mov' => Carbon::now()->toDateString(),
                            'descripcion' => $descripcion_movimiento_base . " - Componente Combo: " . ($productName ?? $product_info->pronom) . " (Componente: " . $component_product->pronom . ")",
                            'mov_lote' => $component_product->lote,
                            'mov_vencimiento' => $component_product->vencimiento,
                            'fecha_registro' => Carbon::now(),
                        ]);

                        // 2. Actualizar stock del COMPONENTE SIMPLE en `producto_stock`
                        $stock_componente_query = DB::table('producto_stock')
                                                ->where('IdProducto', $component_product->IdProducto)
                                                ->where('id_almacen', $almacenId)
                                                ->where('id_empresa_negocio', $id_empresa_negocio);

                        if ($component_product->requiere_lote_vencimiento) {
                            $stock_comp_lote_venc = DB::table('producto_stock')
                                                    ->where('IdProducto', $component_product->IdProducto)
                                                    ->where('id_almacen', $almacenId)
                                                    ->where('id_empresa_negocio', $id_empresa_negocio)
                                                    ->whereNotNull('lote')
                                                    ->whereNotNull('vencimiento')
                                                    ->first();
                            if ($stock_comp_lote_venc) {
                                $stock_componente_query->where('lote', $stock_comp_lote_venc->lote)
                                                        ->where('vencimiento', $stock_comp_lote_venc->vencimiento);
                            }
                        }
                        $stock_componente_query->decrement('stock', $component_quantity_to_affect);

                        Log::info("Descontado stock de componente simple ID {$component_product->IdProducto} (de combo {$productId}) en cantidad: {$component_quantity_to_affect} en almacén {$almacenId}.");

                    } elseif ($component_product->promocion == '2') { // Componente con receta dentro del combo
                        $recetas_componente = DB::table('recetas')->where('prod_id', $component_product->IdProducto)->get();

                        foreach ($recetas_componente as $rec_comp) {
                            $insumo_combo_receta_cantidad_afectar = $component_quantity_to_affect * $rec_comp->rec_cant;
                            $insumo_info = productos::find($rec_comp->prod_insu);

                            if (!$insumo_info) {
                                Log::warning("Descontar Stock (Combo Receta): Insumo ID {$rec_comp->prod_insu} no encontrado para componente {$component_product->IdProducto} de combo {$productId}. Saltando.");
                                continue;
                            }

                            // 1. Insertar registro en `movimientos_productos` para el insumo del componente de receta
                            DB::table('movimientos_productos')->insert([
                                'IdProducto' => $rec_comp->prod_insu,
                                'IdProducto_rel' => $rec_comp->prod_insu,
                                'precio' => 0,
                                'cantidad' => $insumo_combo_receta_cantidad_afectar,
                                'costo' => $rec_comp->ins_costo,
                                'cliente' => $cliente_nom,
                                'IdCpe_cabecera' => null,
                                'serie' => null,
                                'numero' => null,
                                'tdocod' => 'NP',
                                'tipo' => '3',
                                'mov_tip' => 'E',
                                'id_empresa_negocio' => $id_empresa_negocio,
                                'id_almacen' => $almacenId,
                                'fecha_mov' => Carbon::now()->toDateString(),
                                'descripcion' => $descripcion_movimiento_base . " - Consumo Receta Combo: " . ($productName ?? $product_info->pronom) . " (Insumo: " . $insumo_info->pronom . ")",
                                'fecha_registro' => Carbon::now(),
                            ]);

                            // 2. Actualizar stock del INSUMO DEL COMPONENTE DE RECETA en `producto_stock`
                            $stock_insumo_combo_query = DB::table('producto_stock')
                                                    ->where('IdProducto', $rec_comp->prod_insu)
                                                    ->where('id_almacen', $almacenId)
                                                    ->where('id_empresa_negocio', $id_empresa_negocio);

                            if ($insumo_info->requiere_lote_vencimiento) {
                                $stock_insumo_combo_lote_venc = DB::table('producto_stock')
                                                                ->where('IdProducto', $rec_comp->prod_insu)
                                                                ->where('id_almacen', $almacenId)
                                                                ->where('id_empresa_negocio', $id_empresa_negocio)
                                                                ->whereNotNull('lote')
                                                                ->whereNotNull('vencimiento')
                                                                ->first();
                                if ($stock_insumo_combo_lote_venc) {
                                    $stock_insumo_combo_query->where('lote', $stock_insumo_combo_lote_venc->lote)
                                                            ->where('vencimiento', $stock_insumo_combo_lote_venc->vencimiento);
                                }
                            }
                            $stock_insumo_combo_query->decrement('stock', $insumo_combo_receta_cantidad_afectar);

                            Log::info("Descontado stock de insumo ID {$rec_comp->prod_insu} (de receta de componente de combo {$productId}) en cantidad: {$insumo_combo_receta_cantidad_afectar} en almacén {$almacenId}.");
                        }
                    }
                }
            }
        } catch (\Exception $e) {
            Log::error("Error en descontar_stock_por_item para producto ID {$productId}: " . $e->getMessage() . " en línea " . $e->getLine());
            throw $e; // Re-lanza la excepción para que la transacción se revierta
        }
    }

    /**
     * Reverte el stock de un producto (o sus componentes/insumos si es receta/combo)
     * al anular un ítem. Registra el movimiento en `movimientos_productos` y actualiza `producto_stock`.
     * @param int $productId El ID del producto que se anula (puede ser principal, presentación, o combo)
     * @param float $quantity La cantidad del producto principal que se anula
     * @param int $id_empresa_negocio El ID del negocio
     * @param int|null $pedidoId El ID del pedido (opcional, para descripción del movimiento)
     * @param string|null $productName El nombre del producto (opcional, para descripción del movimiento)
     * @param int|null $almacenId El ID del almacén (si es null, se intenta buscar el predeterminado)
     * @param string $reason_text Texto de la razón para el movimiento
     */



    public function eliminarPedidoCompleto(Request $request)
    {
        $pedidoId = $request->input('pedido_id');
        $mesaId = $request->input('mesa_id'); // Mesa asociada al pedido, para liberar
        $authUser = trim($request->input('auth_user'));
        $authPassword = $request->input('auth_password');
        $reason = $request->input('reason');

        // 1. Validar datos de entrada
        if (empty($pedidoId) || empty($reason) || empty($authUser) || empty($authPassword)) {
            return response()->json(['success' => false, 'message' => 'Faltan datos obligatorios para eliminar el pedido.'], 400);
        }

        // 2. Autenticación y Verificación de Roles (similar a removeOldCartItem)
        $user_to_authenticate = User::where('email', $authUser)->first();

        if (!$user_to_authenticate || !Hash::check($authPassword, $user_to_authenticate->password)) {
            Log::warning("Eliminar Pedido Completo: Autenticación fallida para usuario: '{$authUser}'.");
            return response()->json(['success' => false, 'message' => 'Usuario o contraseña incorrectos, o no tienes permiso para esta acción.'], 403);
        }

        $allowedRoleIds = [2, 4]; // 2:admin, 4:caja
        $user_to_authenticate->load('roles');
        $has_permission = $user_to_authenticate->roles->contains(function ($role) use ($allowedRoleIds) {
            return in_array($role->id, $allowedRoleIds);
        });

        if (!$has_permission) {
            $user_roles = $user_to_authenticate->roles->pluck('name')->toArray();
            Log::warning("Eliminar Pedido Completo: Usuario '{$authUser}' (ID: {$user_to_authenticate->IdUsuario}) autenticado, pero sin rol permitido. Roles encontrados: [" . implode(', ', $user_roles) . "].");
            return response()->json(['success' => false, 'message' => 'No tienes los permisos necesarios (Admin o Caja) para eliminar un pedido completo.'], 403);
        }

        // 3. Iniciar Transacción
        DB::beginTransaction();
        try {
            // Obtener el pedido y sus detalles
            $pedido = pedidos::findOrFail($pedidoId);
            $detalles_pedido = pedidos_detalle::where('ped_id', $pedidoId)->get();

            // Obtener el valor del ICBPER para cálculos de stock
            $empresa_data = DB::table('empresa')->where('IdEmpresa', Auth::user()->IdEmpresa)->first();
            $icbper_val = $empresa_data ? $empresa_data->icbper : 0;
            
            // Obtener el ID del negocio
            $id_empresa_negocio = Auth::user()->id_empresa_negocio;

            // 4. Revertir Stock y Registrar Movimientos de Anulación para cada ítem
            foreach ($detalles_pedido as $detalle) {
                // Solo revertimos stock para ítems que NO ESTÉN ANULADOS previamente
                if ($detalle->estadoitem !== 'Anulado') {
                    // Revertir stock del producto
                    $this->revertir_stock_por_item($detalle->IdProducto, $detalle->ped_det_can, $id_empresa_negocio);

                    // === INICIO KARDEX: ANULACIÓN DE PEDIDO COMPLETO ===
                    $affected = DB::table('productos')
                        ->where('IdProducto', $detalle->IdProducto)
                        ->where('promocion', '2')
                        ->increment('stock_preparados', $detalle->ped_det_can);

                    if ($affected) {
                        $stockActual = DB::table('productos')->where('IdProducto', $detalle->IdProducto)->value('stock_preparados');
                        DB::table('movimientos_preparados')->insert([
                            'producto_id' => $detalle->IdProducto,
                            'pedido_id' => $pedidoId,
                            'tipo_movimiento' => 'anulacion_pedido',
                            'cantidad' => $detalle->ped_det_can,
                            'stock_resultante' => $stockActual,
                            'observacion' => 'Anulación total de pedido por ' . $authUser . ' - Motivo: ' . $reason,
                            'fecha_proceso' => Carbon::today()->toDateString(),
                            'created_at' => Carbon::now(),
                            'updated_at' => Carbon::now()
                        ]);
                    }
                    // === FIN KARDEX ===

                    // Registrar movimiento de anulación/entrada de stock
                    DB::table('movimientos_productos')->insert([
                        'IdProducto' => $detalle->IdProducto,
                        'IdProducto_rel' => $detalle->IdProducto_rel ?: $detalle->IdProducto, // Usar IdProducto_rel si existe, sino IdProducto
                        'precio' => $detalle->ped_det_pre,
                        'cantidad' => $detalle->ped_det_can,
                        'costo' => 0.00, // Ajusta si manejas costos aquí
                        'mov_cab_id' => $pedidoId,
                        'stock' => 0.00, // Esto se actualiza en revertir_stock_por_item
                        'stock_inicial' => 0.00,
                        'serie' => null,
                        'numero' => null,
                        'tdocod' => '83',
                        'tipo' => '4', // Tipo de movimiento para anulación/ajuste
                        'id_empresa_negocio' => $id_empresa_negocio,
                        'id_almacen' => $detalle->id_almacen_pro, // Usar el almacén del detalle
                        'fecha_mov' => Carbon::now()->toDateString(),
                        'fecha_registro' => Carbon::now(),
                        'mov_tip' => 'I', // Entrada (revertir salida)
                        'descripcion' => 'Anulación total de pedido (' . $detalle->descripcion . ') por ' . $authUser . ' - Motivo: ' . $reason,
                        'cliente' => $pedido->ped_cli_nom ?? 'N/A',
                    ]);
                }

                // Marcar el detalle del pedido como "Anulado"
                pedidos_detalle::where('ped_det_id', $detalle->ped_det_id)
                    ->update([
                        'ped_det_can' => 0, // Cantidad en cero
                        'item_obs' => trim($detalle->item_obs . ' (Eliminado completo por ' . $authUser . ': ' . $reason . ')'),
                        'estadoitem' => 'Anulado',
                        'impreso' => 'impreso',
                        'mod_cant' => -$detalle->ped_det_can, // Cantidad modificada (negativa para reflejar anulación)
                    ]);
            }

            // 5. Marcar el pedido principal como "Anulado" y actualizar totales
            $pedido->ped_est = 'Anulado'; // O 'Eliminado'
            $pedido->ped_tot = 0; // Total del pedido en cero
            $pedido->icbper_tot = 0; // ICBPER total en cero
            $pedido->fecha_hora_modificacion = Carbon::now();
            $pedido->save();

            // 6. Liberar la mesa (si es un pedido de salón)
            if ($pedido->ped_tip == 'Salon' && $mesaId) {
                $mesa = mesas::find($mesaId);
                if ($mesa) {
                    $mesa->mes_est = 'Libre';
                    $mesa->ind_union = '0'; // Asegurar que el indicador de unión se resetee
                    $mesa->save();
                    Log::info("Mesa ID: {$mesaId} liberada al eliminar el pedido completo ID: {$pedidoId}.");

                    // Si la mesa estaba unida, también liberar las mesas secundarias
                    DB::table('mesas_union')
                        ->where('mes_id_act', $mesaId)
                        ->where('mes_uni_est', 'APERTURADO')
                        ->update(['mes_uni_est' => 'CERRADO']);
                    
                    $mesas_unidas_secundarias = DB::table('mesas')
                                                ->where('ind_union', $mesaId)
                                                ->get();
                    foreach ($mesas_unidas_secundarias as $sec_mesa) {
                        $sec_mesa_obj = mesas::find($sec_mesa->mes_id);
                        if ($sec_mesa_obj) {
                            $sec_mesa_obj->mes_est = 'Libre';
                            $sec_mesa_obj->ind_union = '0';
                            $sec_mesa_obj->save();
                            Log::info("Mesa secundaria unida ID: {$sec_mesa->mes_id} también liberada.");
                        }
                    }

                } else {
                    Log::warning("Eliminar Pedido Completo: Mesa con ID {$mesaId} no encontrada para liberar.");
                }
            }

            // 7. Imprimir comanda de "PEDIDO ELIMINADO"
            $this->imprimirComandaPedidoEliminado(
                $pedido,
                $detalles_pedido, // Todos los detalles, incluso los que ya estaban anulados
                $reason,
                $user_to_authenticate->name // Nombre del usuario que eliminó
            );
            
            // 8. Limpiar sesión relacionada con el pedido
            session()->forget('kiosko_last_pedido_id');
            session()->forget('kiosko_cart');
            session()->forget('kiosko_order_type');
            session()->forget('kiosko_mesa_id');
            session()->forget('kiosko_mesa_nombre');


            DB::commit(); // Confirmar la transacción
            Log::info("Pedido completo ID: {$pedidoId} eliminado exitosamente por '{$authUser}'.");
            return response()->json(['success' => true, 'message' => 'El pedido ha sido eliminado, el stock revertido y la mesa liberada.']);

        } catch (\Exception $e) {
            DB::rollBack(); // Revertir la transacción si algo falla
            Log::error("Error al eliminar pedido completo ID: {$pedidoId}: " . $e->getMessage() . " en línea " . $e->getLine());
            return response()->json(['success' => false, 'message' => 'Error al eliminar el pedido: ' . $e->getMessage() . ' (Contacte a soporte)'], 500);
        }
    }

    /**
     * Imprime una comanda especial para un pedido completo eliminado.
     * Utiliza la impresora predeterminada de caja.
     * @param object $pedido El objeto del pedido principal
     * @param Collection $detalles_pedido Colección de detalles del pedido
     * @param string $razonEliminacion Motivo por el que se eliminó
     * @param string $usuarioEliminacion Nombre del usuario que eliminó
     */
    private function imprimirComandaPedidoEliminado($pedido, $detalles_pedido, $razonEliminacion, $usuarioEliminacion)
    {
        Log::info("Intentando imprimir comanda de PEDIDO ELIMINADO para Pedido ID: {$pedido->ped_id}");

        $id_empresa_negocio = Auth::user()->id_empresa_negocio;
        $empresanegocios = DB::table('empresa_negocios')->where('id_empresa_negocio', $id_empresa_negocio)->first();

        // Obtener la impresora de caja predeterminada
        $impresora_caja = DB::table('configuracion_impresoras')
                            ->where('id_empresa_negocio', $id_empresa_negocio)
                            ->where('predeterminado', '1')
                            ->first();

        if (!$impresora_caja) {
            Log::warning("Impresión Comanda Eliminada: No se encontró impresora predeterminada de caja para el negocio ID: {$id_empresa_negocio}. No se puede imprimir la comanda de anulación.");
            return false;
        }

        $mesa = null;
        $piso_nombre = '';
        if (!empty($pedido->mes_id)) {
            $mesa = DB::table('mesas')->where('mes_id', $pedido->mes_id)->first();
            if ($mesa && !empty($mesa->pis_id)) {
                $piso = DB::table('pisos')->where('pis_id', $mesa->pis_id)->first();
                $piso_nombre = $piso ? $piso->pis_nom : '';
            }
        }

        $mozo_nombre_completo = '';
        if (!empty($pedido->mozo)) {
            $mozo_user = DB::table('users')->where('IdUsuario', $pedido->mozo)->first();
            $mozo_nombre_completo = ($mozo_user->name ?? '') . ' ' . ($mozo_user->apeusu ?? '');
        }

        $fecha_hora_eliminacion = Carbon::now()->format('d/m/Y H:i:s');

        try {
            //IMPRESORA NORMAL
            /*$connector = null;
            if ($impresora_caja->tip_conex_imp == 'COMPARTIDO') {
                $connector = new WindowsPrintConnector("smb://" . $impresora_caja->ruta);
            } elseif ($impresora_caja->tip_conex_imp == 'RED') {
                $connector = new NetworkPrintConnector($impresora_caja->ruta, 9100);
            }*/

            //IMPRESORA VIRTUAL
                $connector = new DummyPrintConnector();
                $printer = new Printer($connector);

            if (!$connector) {
                Log::error("Impresión Comanda Eliminada: Conector no inicializado para impresora {$impresora_caja->ruta}.");
                return false;
            }

            $printer = new Printer($connector);
            $printer->setJustification(Printer::JUSTIFY_CENTER);
            
            if (!empty($empresanegocios->logosuc) && file_exists(public_path('/' . $empresanegocios->logosuc))) {
                try {
                    $logo = EscposImage::load(public_path('/' . $empresanegocios->logosuc), false);
                    $printer->bitImage($logo);
                } catch (\Exception $e) { Log::error("Error al cargar logo para comanda eliminada: " . $e->getMessage()); }
            }

            $printer->setTextSize(1, 1);
            $printer->setEmphasis(true);
            $printer->text("--- PEDIDO ELIMINADO ---\n"); // Encabezado claro de anulación total
            $printer->setTextSize(2, 2);
            $printer->text("PEDIDO #{$pedido->ped_id}\n");
            $printer->setTextSize(1, 1);
            $printer->text("Tipo de Pedido: " . ($pedido->ped_tip ?? 'N/A') . "\n");

            if ($pedido->ped_tip == 'Salon' && $mesa) {
                $printer->setTextSize(3, 3); // Mesa grande y destacada
                $printer->text("MESA: " . ($mesa->mes_nom ?? 'N/A') . "\n");
                $printer->setTextSize(1, 1); // Volver a tamaño normal
                $printer->text("Zona: " . ($piso_nombre ? $piso_nombre . "\n" : "") . "\n");
            } else {
                 $printer->setTextSize(2, 2);
                 $printer->text("PARA LLEVAR / DELIVERY\n");
                 $printer->setTextSize(1, 1);
                 // Información del cliente para pedidos Para Llevar/Delivery
                 if (!empty($pedido->ped_cli_nom) && $pedido->ped_cli_nom != 'VENTA AL PORTADOR') {
                     $printer->text("Cliente: " . $pedido->ped_cli_nom . "\n");
                     if (!empty($pedido->ped_num_doc) && $pedido->ped_num_doc != '00000000') {
                         $printer->text("Doc: " . $pedido->ped_num_doc . "\n");
                     }
                     if (!empty($pedido->ped_dir) && $pedido->ped_dir != '--') {
                         $printer->text("Dir: " . $pedido->ped_dir . "\n");
                     }
                     if (!empty($pedido->ped_tel)) {
                         $printer->text("Tel: " . $pedido->ped_tel . "\n");
                     }
                 }
            }
            $printer->setEmphasis(false); // Desactivar negrita

            $printer->setJustification(Printer::JUSTIFY_LEFT);
            $printer->text("Fecha/Hora Eliminación: " . $fecha_hora_eliminacion . "\n");
            $printer->text("Eliminado por: " . $usuarioEliminacion . "\n");
            if (!empty($mozo_nombre_completo)) {
                $printer->text("Mozo original: " . $mozo_nombre_completo . "\n");
            }
            $printer->text("Motivo: " . $razonEliminacion . "\n");
            $printer->text("-----------------------------------\n");
            $printer->text("DETALLES ELIMINADOS:\n");
            $printer->text("-----------------------------------\n");

            $printer->setTextSize(1, 1);
            $printer->setJustification(Printer::JUSTIFY_LEFT);
            $printer->text(sprintf("%-4s %-25s %-10s\n", "CANT", "CONCEPTO", "ESTADO"));
            $printer->text("-----------------------------------\n");

            foreach ($detalles_pedido as $detalle) {
                // Mostrar la cantidad original y el estado en que quedó el ítem
                $line_detalle = sprintf("%-4s %-25s %-10s\n",
                    $detalle->ped_det_can_orig ?? $detalle->ped_det_can, // Si tuvieras una columna para cantidad original
                    mb_substr($detalle->descripcion, 0, 25, 'UTF-8'),
                    mb_substr(mb_strtoupper($detalle->estadoitem, 'UTF-8'), 0, 10, 'UTF-8') // Estado como "ANULADO"
                );
                $printer->text($line_detalle);
                if (!empty($detalle->item_obs)) {
                    $printer->text("    (Obs: " . mb_substr($detalle->item_obs, 0, 30, 'UTF-8') . (mb_strlen($detalle->item_obs, 'UTF-8') > 30 ? '...' : '') . ")\n");
                }
            }
            $printer->text("\n");
            $printer->feed();
            $printer->cut();
            $printer->pulse();
            $codigo_raw = $connector->getData();
            $printer->close();

            //IMPRESORA VIRTUAL
                    DB::table('cola_impresion')->insert([
                        'contenido' => base64_encode($codigo_raw),
                        'impresora' => $impresora_caja->descripcion,
                        'estado'    => '0'                        
                    ]);

            Log::info("Comanda de PEDIDO ELIMINADO para Pedido ID {$pedido->ped_id} enviada a impresora {$impresora_caja->ruta}.");
            return true;

        } catch (\Exception $e) {
            Log::error("Error de impresión de comanda PEDIDO ELIMINADO para Pedido #{$pedido->ped_id} en impresora {$impresora_caja->ruta}: " . $e->getMessage() . " en línea " . $e->getLine());
            return false;
        }
    }

    public function reimprimirItemComanda(Request $request)
    {
        // Validación estricta para Laravel 5.6
        if (!Auth::user()->hasRole('admin')) {
            return response()->json(['success' => false, 'message' => 'Acceso denegado. Solo un administrador puede reimprimir tickets.']);
        }

        $pedidoId = session()->get('kiosko_last_pedido_id');
        $productoId = $request->input('producto_id');

        if (!$pedidoId) {
            return response()->json(['success' => false, 'message' => 'No hay un pedido activo para reimprimir.']);
        }

        try {
            // Buscamos el detalle original
            $detalle = DB::table('pedidos_detalle')
                ->where('ped_id', $pedidoId)
                ->where('IdProducto', $productoId)
                ->where('estadoitem', '!=', 'Eliminado')
                ->first();

            if (!$detalle) {
                return response()->json(['success' => false, 'message' => 'El ítem no se encontró en el pedido actual.']);
            }

            // Armamos el array con cast a object estándar en PHP 7.2
            $itemsToPrint = [];
            $itemsToPrint[] = (object)[
                'descripcion' => "[REIMPRESION] " . $detalle->descripcion,
                'ped_det_can' => $detalle->ped_det_can,
                'item_obs'    => $detalle->item_obs,
                'ped_det_id'  => $detalle->ped_det_id,
                'IdProducto'  => $detalle->IdProducto,
            ];

            // Al usar esta función, el sistema respetará la impresora predeterminada de la categoría
            $this->imprimirComandaPorCategorias($pedidoId, $itemsToPrint);

            return response()->json(['success' => true, 'message' => 'Ticket de reimpresión enviado a su área correspondiente.']);

        } catch (\Exception $e) {
            Log::error("Error al reimprimir ítem en Hola P: " . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Error interno al imprimir: ' . $e->getMessage()]);
        }
    }

    private function imprimirComandaPorCategorias($pedidoId, $itemsToPrint)
    {

        $verPrecio = false;
    
        $IdEmpresa = Auth::user()->IdEmpresa;
        $id_empresa_negocio = Auth::user()->id_empresa_negocio;

        $empresanegocios = DB::table('empresa_negocios')->where('id_empresa_negocio', $id_empresa_negocio)->first();

        $cab_pedido = DB::table('pedidos')
            ->where('ped_id', $pedidoId)
            ->leftJoin('users', 'users.IdUsuario', 'pedidos.mozo')
            ->first();

        if (!$cab_pedido) {
            Log::error("Generación Comanda: Pedido #{$pedidoId} no encontrado.");
            return false;
        }

        $mesa = null;
        $piso_nombre = '';
        if (!empty($cab_pedido->mes_id)) {
            $mesa = DB::table('mesas')->where('mes_id', $cab_pedido->mes_id)->first();
            if ($mesa && !empty($mesa->pis_id)) {
                $piso = DB::table('pisos')->where('pis_id', $mesa->pis_id)->first();
                $piso_nombre = $piso ? $piso->pis_nom : '';
            }
        }

        $usuario_que_envio = (DB::table('users')->where('IdUsuario', $cab_pedido->IdUsuario)->first()->name ?? 'Desconocido');

        $mozo_nombre_completo = '';
        if (!empty($cab_pedido->mozo)) {
            $mozo_user = DB::table('users')->where('IdUsuario', $cab_pedido->mozo)->first();
            $mozo_nombre_completo = ($mozo_user->name ?? '') . ' ' . ($mozo_user->apeusu ?? '');
        }

        $fecha_hora_pedido = $cab_pedido->fecha_hora_modificacion
                               ? Carbon::parse($cab_pedido->fecha_hora_modificacion)->format('d/m/Y H:i:s')
                               : Carbon::parse($cab_pedido->fecha_hora)->format('d/m/Y H:i:s');


        $impresoras = DB::table('configuracion_impresoras')->where('id_empresa_negocio', $id_empresa_negocio)->get();

        
        $itemsByPrinter = [];
        
        foreach ($itemsToPrint as $item) {
            $producto = DB::table('productos')->where('IdProducto', $item->IdProducto)->first();
            
            if ($producto && $producto->cat_id) {
                $categoria = DB::table('categorias')->where('cat_id', $producto->cat_id)->first();
                
                if ($categoria) {
                    // Agregar a impresora principal si está configurada
                    if (!empty($categoria->impresora)) {
                        $itemsByPrinter[$categoria->impresora][] = $item;
                    }
                    
                    // Agregar a impresora2 si está configurada
                    if (!empty($categoria->impresora2)) {
                        $itemsByPrinter[$categoria->impresora2][] = $item;
                    }
                    
                    // Agregar a impresora3 si está configurada
                    if (!empty($categoria->impresora3)) {
                        $itemsByPrinter[$categoria->impresora3][] = $item;
                    }
                }
            }
        }

        foreach ($impresoras as $impresora) {
            $detalle_para_impresora = $itemsByPrinter[$impresora->Id] ?? [];

            if (count($detalle_para_impresora) === 0) {
                continue;
            }

            $primer_item_producto = DB::table('productos')->where('IdProducto', $detalle_para_impresora[0]->IdProducto)->first();
            $categoria_info = DB::table('categorias')->where('cat_id', $primer_item_producto->cat_id)->first();
            $categoria_nombre = $categoria_info ? $categoria_info->cat_nom : 'General';

            $comanda_content = "--- COMANDA PEDIDO #{$pedidoId} ---\n";
            $comanda_content .= "Enviado por: " . $usuario_que_envio . "\n";
            $comanda_content .= "Tipo de Pedido: " . ($cab_pedido->ped_tip ?? 'N/A') . "\n";

            if ($cab_pedido->ped_tip == 'Salon' && $mesa) {
                $comanda_content .= "Zona: " . ($piso_nombre ? $piso_nombre . " - " : "") . ($mesa->mes_nom ?? 'N/A') . "\n";
            } else {
                 $comanda_content .= "Zona: PARA LLEVAR\n";
            }
            $comanda_content .= "Fecha/Hora: " . $fecha_hora_pedido . "\n";
            if (!empty($mozo_nombre_completo)) {
                $comanda_content .= "Mozo: " . $mozo_nombre_completo . "\n";
            }

            if (!empty($cab_pedido->ped_cli_nom) && $cab_pedido->ped_cli_nom != 'VENTA AL PORTADOR') {
                $comanda_content .= "Cliente: " . $cab_pedido->ped_cli_nom . "\n";
                if (!empty($cab_pedido->ped_num_doc) && $cab_pedido->ped_num_doc != '00000000') {
                    $comanda_content .= "DNI/RUC: " . $cab_pedido->ped_num_doc . "\n";
                }
                if (!empty($cab_pedido->ped_dir) && $cab_pedido->ped_dir != '--') {
                    $comanda_content .= "Dirección: " . $cab_pedido->ped_dir . "\n";
                }
            }

            $comanda_content .= "-----------------------------------\n";
            $comanda_content .= "Categoría: {$categoria_nombre}\n";
            $comanda_content .= "-----------------------------------\n";

            if ($verPrecio) {
                $comanda_content .= sprintf("%-4s %-20s %-8s %-8s\n", "CANT", "CONCEPTO", "TOTAL", "OBS.");
            } else {
                $comanda_content .= sprintf("%-4s %-20s %-8s\n", "CANT", "CONCEPTO", "OBS.");
            }
            $comanda_content .= "____________________________________\n";

            foreach ($detalle_para_impresora as $det) {
                $primeralinea = ($det->descripcion);
                $cantidad_a_imprimir = $det->ped_det_can;

                $buscardetalle = \MasterSoft\pedidos_detalle::find($det->ped_det_id);
                $precio_unitario = $buscardetalle ? $buscardetalle->ped_det_pre : 0;
                $total_item = number_format($cantidad_a_imprimir * $precio_unitario, 2);
                
                $texto_entrada = '';
                if ($buscardetalle && !empty($buscardetalle->entrada)) {
                    $texto_entrada = "\n    >> Con: " . $buscardetalle->entrada;
                }

                if ($verPrecio) {
                    // Versión con precio y guion
                    $line = sprintf("%-4s %s - %s %s%s\n", $cantidad_a_imprimir, $primeralinea, $total_item, $det->item_obs, $texto_entrada);
                } else {
                    // Versión sin precio y sin guion
                    $line = sprintf("%-4s %s %s%s\n", $cantidad_a_imprimir, $primeralinea, $det->item_obs, $texto_entrada);
                }
                $comanda_content .= $line;

                if ($buscardetalle) {
                    $buscardetalle->impreso = 'impreso';
                    $buscardetalle->mod_cant = '0';
                    $buscardetalle->save();
                }
            }

            $comanda_content .= "-----------------------------------\n\n";

            try {

                //IMPRESORA REAL
                /*$connector = null;
                if ($impresora->tip_conex_imp == 'COMPARTIDO') {
                    $connector = new WindowsPrintConnector("smb://" . $impresora->ruta);
                } elseif ($impresora->tip_conex_imp == 'RED') {
                    $connector = new NetworkPrintConnector($impresora->ruta, 9100);
                }*/

                //IMPRESORA VIRTUAL
                $connector = new DummyPrintConnector();
                $printer = new Printer($connector);

                if ($connector) {
                    $printer = new Printer($connector);

                    $printer->setTextSize(1, 1);
                    $printer->setJustification(Printer::JUSTIFY_CENTER);
                    $printer->setFont(Printer::FONT_B);
                    $printer->setTextSize(2, 2);
                    $printer->text(($cab_pedido->etiqueta ?? "") . "\n");
                    $printer->text($cab_pedido->ped_id . "\n");
                    if ($cab_pedido->ped_tip != 'Salon') {
                        $printer->text($cab_pedido->ped_tip . "\n");
                    }

                    $printer->setFont(Printer::FONT_B);
                    $printer->setTextSize(3, 3);
                    if (!empty($cab_pedido->mes_id) && $mesa) {
                        $nombre_completo_mesa = ($piso_nombre ? $piso_nombre . " - " : "") . $mesa->mes_nom;
                        $printer->text($nombre_completo_mesa . "\n");
                    }
                    $printer->setFont(Printer::FONT_B);
                    $printer->setTextSize(2, 2);

                    $printer->text($fecha_hora_pedido . "\n");
                    
                    if (!empty($cab_pedido->ped_cli_nom) && $cab_pedido->ped_cli_nom != 'VENTA AL PORTADOR') {
                        $printer->text("Cliente: " . $cab_pedido->ped_cli_nom . "\n");
                        $printer->setFont(Printer::FONT_B);
                    $printer->setTextSize(1, 1);
                        if (!empty($cab_pedido->ped_num_doc) && $cab_pedido->ped_num_doc != '00000000') {
                            $printer->text("DNI/RUC: " . $cab_pedido->ped_num_doc . "\n");
                        }
                        if (!empty($cab_pedido->ped_dir) && $cab_pedido->ped_dir != '--') {
                            $printer->text("Direccion: " . $cab_pedido->ped_dir . "\n");
                        }
                    }
                    $printer->setFont(Printer::FONT_B);
                    $printer->setTextSize(2, 2);

                    if (!empty($mozo_nombre_completo)) {
                        $printer->text("Mozo:" . $mozo_nombre_completo . "\n" . "\n");
                    }

                    $printer->setFont(Printer::FONT_B);
                    $printer->setTextSize(1, 1);

                    $printer->setJustification(Printer::JUSTIFY_LEFT);
                    if ($verPrecio) {
                        $printer->text("CANT  CONCEPTO           TOTAL  OBS." . "\n");
                    } else {
                        $printer->text("CANT  CONCEPTO           OBS." . "\n");
                    }
                    $printer->text("____________________________________" . "\n");
                    $printer->setFont(Printer::FONT_B);
                    $printer->setTextSize(2, 2);
                    
                    foreach ($detalle_para_impresora as $det) {
                        $primeralinea = ($det->descripcion);
                        $cantidad_a_imprimir = $det->ped_det_can;

                        $buscardetalle = \MasterSoft\pedidos_detalle::find($det->ped_det_id);
                        $precio_unitario = $buscardetalle ? $buscardetalle->ped_det_pre : 0;
                        $total_item = number_format($cantidad_a_imprimir * $precio_unitario, 2);
                        
                        $texto_entrada = '';
                        if ($buscardetalle && !empty($buscardetalle->entrada)) {
                            $texto_entrada = "\n   >> Con: " . $buscardetalle->entrada;
                        }

                        if ($verPrecio) {
                            $line = sprintf("%-3s %s - %s %s%s\n", (int)$cantidad_a_imprimir, $primeralinea, $total_item, $det->item_obs, $texto_entrada);
                        } else {
                            $line = sprintf("%-3s %s %s%s\n", (int)$cantidad_a_imprimir, $primeralinea, $det->item_obs, $texto_entrada);
                        }
                        $printer->text($line);
                    }

                    $printer->text("\n");
                    $printer->feed();
                    $printer->cut();
                    //IMPRESORA VIRTUAL
                    $codigo_raw = $connector->getData();
                    $printer->close();

                    //IMPRESORA VIRTUAL
                    
                    $numero_copias = 1; 

                    for ($i = 0; $i < $numero_copias; $i++) {
                        DB::table('cola_impresion')->insert([
                            'contenido'  => base64_encode($codigo_raw),
                            'impresora'  => $impresora->descripcion,
                            'estado'     => '0'                    
                        ]);
                    }

                    
                } else {
                    Log::error("Impresión Kiosko: Conector de impresora no inicializado.");
                }
            } catch (\Exception $e) {
                Log::error("Error general en impresión: " . $e->getMessage());
            }
        }
        DB::table('pedidos')
            ->where('ped_id', $pedidoId)
            ->update(['etiqueta' => ""]);
        return true;
    }

    public function pedidoExito(Request $request)
    {
        $pedidoId = $request->query('pedido_id');
        return view('empresas.kiosko.pedido_exito', compact('pedidoId'));
    }

    
    public function generarPrecuenta(Request $request)
{
    $pedidoId = $request->input('pedido_id');
    Log::info("generarPrecuenta: Valor de pedido_id recibido: " . $pedidoId);

    if (empty($pedidoId)) {
        return response()->json(['success' => false, 'message' => 'No se proporcionó un ID de pedido.']);
    }

    $pedido = DB::table('pedidos')
        ->where('ped_id', $pedidoId)
        ->leftJoin('users', 'users.IdUsuario', 'pedidos.mozo')
        ->first();

    if (!$pedido) {
        return response()->json(['success' => false, 'message' => 'Pedido no encontrado.']);
    }

    $monto_pagar = $pedido->pagar ?? 0.00;
    $monto_vuelto = $pedido->vuelto ?? 0.00;

    $id_empresa_negocio = Auth::user()->id_empresa_negocio;
    $impresora_caja = DB::table('configuracion_impresoras')
        ->where('id_empresa_negocio', $id_empresa_negocio)
        ->where('predeterminado', '1')
        ->first();

    $pedido_detalles = DB::table('pedidos_detalle')
        ->select('pedidos_detalle.*',
            DB::raw('(ped_det_can - IFNULL(item_facturado, 0)) as cantidad_pendiente'))
        ->where('ped_id', $pedidoId)
        ->where('estadoitem', '!=', 'Eliminado')
        ->whereRaw('(ped_det_can - IFNULL(item_facturado, 0)) > 0')
        ->get();

    $empresa_data = DB::table('empresa')->where('IdEmpresa', Auth::user()->IdEmpresa)->first();
    $icbper_val = $empresa_data ? $empresa_data->icbper : 0;

    $total_precuenta = 0;
    $total_icbper_precuenta = 0;

    foreach ($pedido_detalles as $item) {
        $total_precuenta += $item->cantidad_pendiente * $item->ped_det_pre;
        if (isset($item->icbper_ind) && $item->icbper_ind == 1) {
            $total_icbper_precuenta += $item->cantidad_pendiente * $icbper_val;
        }
    }

    $total_precuenta += $total_icbper_precuenta;
    $total_precuenta = round($total_precuenta, 2);

    $usuario_que_envio = (DB::table('users')->where('IdUsuario', $pedido->IdUsuario)->first()->name ?? 'Desconocido');

    // --- LÓGICA DE ENCABEZADO Y ESTADO DE MESA ---
    $texto_cabecera_gigante = '';
    
    if (!empty($pedido->mes_id)) {
        // 1. Cambiamos el estado de la mesa a 'Cuenta' para el color ámbar
        DB::table('mesas')->where('mes_id', $pedido->mes_id)->update(['mes_est' => 'Cuenta']);

        // 2. Obtenemos nombre de mesa y piso
        $mesa_obj = DB::table('mesas')->where('mes_id', $pedido->mes_id)->first();
        if ($mesa_obj) {
            $piso_obj = DB::table('pisos')->where('pis_id', $mesa_obj->pis_id)->first();
            $piso_nom = $piso_obj ? $piso_obj->pis_nom : '';
            $texto_cabecera_gigante = trim(($piso_nom ? $piso_nom . " - " : "") . $mesa_obj->mes_nom);
        }
    } else {
        // 3. Si no es mesa, verificamos si es Llevar o Delivery
        if ($pedido->ped_tip == 'Llevar') {
            $texto_cabecera_gigante = 'PARA LLEVAR';
        } elseif ($pedido->ped_tip == 'Delivery') {
            $texto_cabecera_gigante = 'DELIVERY';
        } else {
            $texto_cabecera_gigante = strtoupper($pedido->ped_tip ?? 'PEDIDO');
        }
    }

    // --- Construcción del cuerpo del ticket (Texto normal) ---
    $precuenta_content = "";
    $precuenta_content .= "Enviado por: " . $usuario_que_envio . "\n";
    $precuenta_content .= "Fecha: " . Carbon::parse($pedido->fecha_hora)->format('d/m/Y H:i') . "\n";

    if ($pedido->ped_tip == 'Delivery' || $pedido->ped_tip == 'Llevar') {
        $precuenta_content .= str_repeat("=", 36) . "\n";
        $precuenta_content .= "Cliente: " . ($pedido->ped_cli_nom ?? 'No especificada') . "\n";
        $precuenta_content .= "Dirección: " . ($pedido->ped_dir ?? 'No especificada') . "\n";
        $precuenta_content .= "Referencia: " . ($pedido->ped_ref ?? 'N/A') . "\n";
        $precuenta_content .= "Celular: " . ($pedido->ped_tel ?? 'N/A') . "\n";
        $precuenta_content .= "Motorizado: " . ($pedido->motorizado ?? 'No especificada') . "\n";
    }

    $precuenta_content .= str_repeat("-", 36) . "\n";
    // Ajusté un poco el ancho de las columnas para que entre mejor todo
    $precuenta_content .= sprintf("%-6s %-20s %10s\n", "CANT", "CONCEPTO", "PRECIO");
    $precuenta_content .= str_repeat("-", 36) . "\n";

    // --- BUCLE MODIFICADO PARA MOSTRAR OBSERVACIONES ---
    foreach ($pedido_detalles as $item) {
        $subtotal_item = ($item->cantidad_pendiente * $item->ped_det_pre);
        $icbper_item = (isset($item->icbper_ind) && $item->icbper_ind == 1 ? ($item->cantidad_pendiente * $icbper_val) : 0);
        $precio_total_linea = number_format($subtotal_item + $icbper_item, 2);

        // 1. Dividimos el nombre del producto en líneas de máximo 40 caracteres
        // wordwrap no corta palabras a la mitad, busca el espacio más cercano
        $nombre_producto = $item->descripcion;
        $lineas_nombre = explode("\n", wordwrap($nombre_producto, 36, "\n"));

        // 2. Imprimimos la primera línea (Lleva la CANTIDAD y el PRECIO)
        $precuenta_content .= sprintf("%-6s %-20s %10s\n",
            $item->cantidad_pendiente,
            $lineas_nombre[0],
            $precio_total_linea
        );

        // 3. Si el nombre tenía más de 40 caracteres, imprimimos las demás líneas
        // Estas líneas van con los espacios de cantidad y precio VACÍOS
        if (count($lineas_nombre) > 1) {
            for ($i = 1; $i < count($lineas_nombre); $i++) {
                $precuenta_content .= sprintf("%-6s %-30s %10s\n", 
                    "", 
                    $lineas_nombre[$i], 
                    ""
                );
            }
        }

        // 4. VERIFICAMOS SI HAY OBSERVACIÓN (También con soporte para texto largo)
        if (!empty($item->item_obs)) {
            // Aplicamos lo mismo a la observación (cortamos en 36 para dejar espacio al " >> ")
            $lineas_obs = explode("\n", wordwrap($item->item_obs, 36, "\n"));
            
            foreach ($lineas_obs as $key => $obs_part) {
                $prefijo = ($key == 0) ? "  >> " : "     "; // Solo la primera línea lleva flecha
                $precuenta_content .= sprintf("%-6s %-30s %10s\n",
                    "",
                    $prefijo . $obs_part,
                    ""
                );
            }
        }
    }
    // ---------------------------------------------------

    $precuenta_content .= str_repeat("-", 36) . "\n";
    
    try {
        if ($impresora_caja) {
            // 1. CONECTOR REAL
            /*$connector = null;
            if ($impresora_caja->tip_conex_imp == 'COMPARTIDO') {
                $connector = new WindowsPrintConnector("smb://" . $impresora_caja->ruta);
            } elseif ($impresora_caja->tip_conex_imp == 'RED') {
                $connector = new NetworkPrintConnector($impresora_caja->ruta, 9100);
            }*/

            // 1. CONECTOR IMPRESORA VIRTUAL
            $connector = new DummyPrintConnector();
            $printer = new Printer($connector);

            if ($connector) {
                $printer = new Printer($connector);

                // === ENCABEZADO EN GRANDE ===
                $printer->setJustification(Printer::JUSTIFY_CENTER);
                
                // Imprimimos el ID del pedido
                $printer->setTextSize(2, 2);
                //$printer->text("PEDIDO #" . $pedidoId . "\n");
                $printer->text($pedidoId . "\n");

                // Imprimimos la Zona o Tipo en GIGANTE
                $printer->setTextSize(2, 2);
                $printer->text($texto_cabecera_gigante . "\n");

                $printer->setTextSize(1, 1);
                $printer->feed();

                // === CUERPO DEL TICKET ===
                $printer->setJustification(Printer::JUSTIFY_LEFT);
                $printer->text($precuenta_content);

                // === TOTAL ===
                $printer->setJustification(Printer::JUSTIFY_RIGHT);
                $printer->setTextSize(2, 2);
                $printer->text("TOTAL: S/. " . number_format($total_precuenta, 2) . "\n");

                // === PAGO Y VUELTO PARA DELIVERY/LLEVAR ===
                if (($pedido->ped_tip == 'Delivery' || $pedido->ped_tip == 'Llevar') && $monto_pagar > 0) {
                    $printer->setTextSize(1, 1);
                    $printer->setJustification(Printer::JUSTIFY_LEFT);
                    $paga_con_text = "PAGA S/. " . number_format($monto_pagar, 2);
                    $vuelto_text = "VUELTO S/. " . number_format($monto_vuelto, 2);
                    $printer->text($paga_con_text . "    " . $vuelto_text . "\n");
                }

                $printer->feed(2);
                $printer->cut();
                // 1. CONECTOR IMPRESORA VIRTUAL
                $codigo_raw = $connector->getData();
                $printer->close();

                // 1. CONECTOR IMPRESORA VIRTUAL
                DB::table('cola_impresion')->insert([
                    'contenido' => base64_encode($codigo_raw),
                    'impresora' => $impresora_caja->descripcion, // Se envía a la impresora de caja (CPE)
                    'estado'    => '0'                    
                ]);

                Log::info("Pre-cuenta Pedido #{$pedidoId} enviada a impresora.");
            }
        }
    } catch (\Exception $e) {
        Log::error("Error en impresión Pre-Cuenta: " . $e->getMessage());
    }

    return response()->json(['success' => true, 'message' => 'Pre-cuenta enviada a impresión.']);
} 
    

    private function registrar_movimiento_salida($id_cpe_cabecera)
    {
        try {
            $cabecera = cpe_cabecera::find($id_cpe_cabecera);
            if (!$cabecera) {
                Log::warning("registrar_movimiento_salida (Kiosko): No se encontró la cabecera de la venta ID: {$id_cpe_cabecera}.");
                return;
            }
            
            $detalles = cpe_detalle::where('IdCpe_cabecera', $id_cpe_cabecera)->get();
            $fecha_movimiento = $cabecera->ccafem; // Fecha de emisión del comprobante
            $id_almacen = $cabecera->id_almacen; // Almacén de la cabecera del comprobante
            $id_empresa_negocio = $cabecera->id_empresa_negocio;
            $nombre_cliente = $cabecera->ccanom;
            $serie_documento = $cabecera->serdoc;
            $numero_documento = $cabecera->numdoc;
            $tdocod_documento = $cabecera->tdocod;

            foreach ($detalles as $det) {
                $bus_pro = productos::find($det->IdProducto);

                if (!$bus_pro) {
                    Log::warning("registrar_movimiento_salida (Kiosko): Producto ID {$det->IdProducto} no encontrado en el detalle de la venta ID {$id_cpe_cabecera}. Saltando.");
                    continue;
                }

                $id_prod_base = empty($bus_pro->pro_rel) ? $bus_pro->IdProducto : $bus_pro->pro_rel;
                $factor_conversion = $bus_pro->factor ?: 1; // Asegura que factor no sea nulo o cero

                // Cantidad total a afectar en stock para este ítem de detalle
                $cantidad_total_afectar = $det->cdecan * $factor_conversion;

                // --- Lógica para registrar en movimientos_productos y afectar stock por tipo de promoción ---
                if ($bus_pro->promocion == '0') { // Producto normal
                    DB::table('movimientos_productos')->insert([
                        'IdProducto' => $det->IdProducto,
                        'IdProducto_rel' => $id_prod_base,
                        'precio' => $det->cdepuni,
                        'cantidad' => $cantidad_total_afectar,
                        'costo' => $bus_pro->costo_total, // Usa costo_total si es más preciso
                        'cliente' => $nombre_cliente,
                        'descripcion' => 'VENTA',
                        'cod_tip_ope' => '01',
                        'mov_cab_id' => null, // O el ID de tu tabla 'movimientos' si lo usas para centralizar
                        'stock' => 0, // Se actualizará al final
                        'IdCpe_cabecera' => $id_cpe_cabecera,
                        'com_cab_id' => null,
                        'stock_inicial' => 0, // Se actualizará al final
                        'serie' => $serie_documento,
                        'numero' => $numero_documento,
                        'tdocod' => $tdocod_documento,
                        'tipo' => '3', // Salida por venta
                        'mov_tip' => 'E', // Egreso
                        'id_empresa_negocio' => $id_empresa_negocio,
                        'id_almacen' => $id_almacen,
                        'fecha_mov' => $fecha_movimiento,
                    ]);

                    // Actualizar el stock del producto principal o de la presentación
                    DB::table('producto_stock')
                        ->where('IdProducto', $id_prod_base)
                        ->where('id_almacen', $id_almacen)
                        ->where('id_empresa_negocio', $id_empresa_negocio)
                        ->decrement('stock', $cantidad_total_afectar);

                    $mov_cal_stock = new Almacen();
                    $mov_cal_stock->movimiento_calcular_stock($id_prod_base, $id_almacen); // Recalcula stock y puede registrarlo si tu función lo hace.

                } elseif ($bus_pro->promocion == '2') { // Producto que consume insumos (receta)
                    $bus_receta = DB::table('recetas')->where('prod_id', $id_prod_base)->get();

                    foreach ($bus_receta as $rec) {
                        $insumo_cantidad_afectar = $det->cdecan * $rec->rec_cant; // Cantidad del insumo a descontar

                        DB::table('movimientos_productos')->insert([
                            'IdProducto' => $rec->prod_insu, // ID del insumo
                            'IdProducto_rel' => $rec->prod_insu,
                            'precio' => 0, // Insumo, no tiene precio de venta directo
                            'cantidad' => $insumo_cantidad_afectar,
                            'costo' => $rec->ins_costo, // Costo del insumo
                            'cliente' => $nombre_cliente,
                            'descripcion' => 'VENTA (CONSUMO RECETA)',
                            'cod_tip_ope' => '01',
                            'mov_cab_id' => null,
                            'stock' => 0, // Se actualizará al final
                            'IdCpe_cabecera' => $id_cpe_cabecera,
                            'com_cab_id' => null,
                            'stock_inicial' => 0, // Se actualizará al final
                            'serie' => $serie_documento,
                            'numero' => $numero_documento,
                            'tdocod' => $tdocod_documento,
                            'tipo' => '3', // Salida por venta
                            'mov_tip' => 'E', // Egreso
                            'id_empresa_negocio' => $id_empresa_negocio,
                            'id_almacen' => $id_almacen,
                            'fecha_mov' => $fecha_movimiento,
                        ]);

                        // Descontar stock del insumo
                        DB::table('producto_stock')
                            ->where('IdProducto', $rec->prod_insu)
                            ->where('id_almacen', $id_almacen)
                            ->where('id_empresa_negocio', $id_empresa_negocio)
                            ->decrement('stock', $insumo_cantidad_afectar);

                        $mov_cal_stock = new Almacen();
                        $mov_cal_stock->movimiento_calcular_stock($rec->prod_insu, $id_almacen);
                    }
                } elseif ($bus_pro->promocion == '3') { // Es un combo
                    // Para combos, el `cpe_detalle` ya debe contener los componentes desagregados
                    // (como se maneja en PuntoVentaController, donde cada componente se agrega al detalle).
                    // Si tus combos se registran con el ID del combo principal en cpe_detalle,
                    // y los componentes están en una tabla separada (ej. `combos`),
                    // entonces deberías iterar sobre los componentes del combo aquí, como se hace en PuntoVentaController.

                    // Asegúrate de que $bus_pro aquí se refiera al ÍTEM DEL COMBO que se vendió, no al combo principal.
                    // Si tu cpe_detalle ya desagrega el combo en sus componentes, esta parte no sería necesaria aquí,
                    // ya que cada componente ya se habría manejado como 'promocion 0' o 'promocion 2'.

                    // Si en `cpe_detalle` guardas el ID del COMBO, y necesitas descontar los productos del combo,
                    // deberías hacer esto:
                    $combo_components = DB::table('combos')
                        ->where('IdProducto_comb', $id_prod_base) // Asumiendo que IdProducto_comb es el ID del combo
                        ->get();

                    foreach ($combo_components as $combo_comp) {
                        $componente_prod = productos::find($combo_comp->IdProducto_rel); // IdProducto_rel es el componente del combo
                        if (!$componente_prod) continue;

                        $componente_cantidad_a_afectar = $det->cdecan * $combo_comp->prod_comb_cant; // Cantidad del componente * cantidad del combo vendido

                        // Determinar si el componente es normal o con receta
                        if ($componente_prod->promocion == '0') { // Componente normal dentro del combo
                            DB::table('movimientos_productos')->insert([
                                'IdProducto' => $componente_prod->IdProducto,
                                'IdProducto_rel' => $componente_prod->IdProducto, // O su pro_rel si lo tuviera
                                'precio' => $componente_prod->propun,
                                'cantidad' => $componente_cantidad_a_afectar,
                                'costo' => $componente_prod->costo_total,
                                'cliente' => $nombre_cliente,
                                'descripcion' => 'VENTA (COMPONENTE COMBO)',
                                'cod_tip_ope' => '01',
                                'mov_cab_id' => null,
                                'stock' => 0,
                                'IdCpe_cabecera' => $id_cpe_cabecera,
                                'com_cab_id' => null,
                                'stock_inicial' => 0,
                                'serie' => $serie_documento,
                                'numero' => $numero_documento,
                                'tdocod' => $tdocod_documento,
                                'tipo' => '3',
                                'mov_tip' => 'E',
                                'id_empresa_negocio' => $id_empresa_negocio,
                                'id_almacen' => $id_almacen,
                                'fecha_mov' => $fecha_movimiento,
                            ]);

                            DB::table('producto_stock')
                                ->where('IdProducto', $componente_prod->IdProducto)
                                ->where('id_almacen', $id_almacen)
                                ->where('id_empresa_negocio', $id_empresa_negocio)
                                ->decrement('stock', $componente_cantidad_a_afectar);

                            $mov_cal_stock = new Almacen();
                            $mov_cal_stock->movimiento_calcular_stock($componente_prod->IdProducto, $id_almacen);

                        } elseif ($componente_prod->promocion == '2') { // Componente con receta dentro del combo
                            $recetas_componente = DB::table('recetas')->where('prod_id', $componente_prod->IdProducto)->get();
                            foreach ($recetas_componente as $rec_comp) {
                                $insumo_combo_cantidad_afectar = $componente_cantidad_a_afectar * $rec_comp->rec_cant;

                                DB::table('movimientos_productos')->insert([
                                    'IdProducto' => $rec_comp->prod_insu,
                                    'IdProducto_rel' => $rec_comp->prod_insu,
                                    'precio' => 0,
                                    'cantidad' => $insumo_combo_cantidad_afectar,
                                    'costo' => $rec_comp->ins_costo,
                                    'cliente' => $nombre_cliente,
                                    'descripcion' => 'VENTA (CONSUMO RECETA COMBO)',
                                    'cod_tip_ope' => '01',
                                    'mov_cab_id' => null,
                                    'stock' => 0,
                                    'IdCpe_cabecera' => $id_cpe_cabecera,
                                    'com_cab_id' => null,
                                    'stock_inicial' => 0,
                                    'serie' => $serie_documento,
                                    'numero' => $numero_documento,
                                    'tdocod' => $tdocod_documento,
                                    'tipo' => '3',
                                    'mov_tip' => 'E',
                                    'id_empresa_negocio' => $id_empresa_negocio,
                                    'id_almacen' => $id_almacen,
                                    'fecha_mov' => $fecha_movimiento,
                                ]);

                                DB::table('producto_stock')
                                    ->where('IdProducto', $rec_comp->prod_insu)
                                    ->where('id_almacen', $id_almacen)
                                    ->where('id_empresa_negocio', $id_empresa_negocio)
                                    ->decrement('stock', $insumo_combo_cantidad_afectar);

                                $mov_cal_stock = new Almacen();
                                $mov_cal_stock->movimiento_calcular_stock($rec_comp->prod_insu, $id_almacen);
                            }
                        }
                    }
                }
            }
            Log::info("Movimientos de salida registrados y stock actualizado para venta ID {$id_cpe_cabecera}.");
        } catch (\Exception $e) {
            Log::error("Error en registrar_movimiento_salida (Kiosko) para venta ID {$id_cpe_cabecera}: " . $e->getMessage() . " en línea " . $e->getLine());
            // Aquí podrías relanzar la excepción o manejarla de otra forma,
            // pero asegúrate de que se registre para depuración.
        }
    }

    public function imprimir_comanda_venta_directa($cpe_id)
    {
        $IdEmpresa = Auth::user()->IdEmpresa;
        $empresanegocios = DB::table('empresa_negocios')->where('id_empresa_negocio', Auth::user()->id_empresa_negocio)->first();

        // Obtener los datos de la cabecera de la venta
        $cabecera_venta = DB::table('cpe_cabecera as cab')
            ->leftjoin('users', 'users.IdUsuario', 'cab.IdUsuario_ven') // Suponiendo que IdUsuario_ven es el vendedor
            ->where('cab.IdCpe_cabecera', $cpe_id)
            ->first();

        // Si no hay cabecera, no hay nada que imprimir
        if (empty($cabecera_venta)) {
            return;
        }

        // Obtener todas las impresoras configuradas para este negocio
        $impresoras = DB::table('configuracion_impresoras')->where('id_empresa_negocio', Auth::user()->id_empresa_negocio)->get();

        foreach ($impresoras as $impresora) {
            // Obtener los detalles de la venta para esta impresora (por categoría de producto)
            $detalle_comanda = DB::table('cpe_detalle')
                ->where('IdCpe_cabecera', $cpe_id)
                ->join('productos', 'cpe_detalle.IdProducto', 'productos.IdProducto')
                ->leftjoin('categorias', 'categorias.cat_id', 'productos.cat_id')
                ->where('categorias.id_empresa_negocio', Auth::user()->id_empresa_negocio)
                ->where('categorias.impresora', $impresora->Id) // Filtra por la impresora asignada a la categoría
                ->get();

            // Si hay detalles para esta impresora, proceder a imprimir
            if ($detalle_comanda->count() > 0) {
                try {
                    // Configurar el conector de la impresora
                    if ($impresora->tip_conex_imp == 'COMPARTIDO') {
                        $connector = new WindowsPrintConnector("smb://" . $impresora->ruta);
                    } elseif ($impresora->tip_conex_imp == 'RED') {
                        $connector = new NetworkPrintConnector($impresora->ruta, 9100);
                    } else {
                        // Si el tipo de conexión no está definido o es desconocido, salta esta impresora
                        continue;
                    }

                    $printer = new Printer($connector);
                    $printer->setJustification(Printer::JUSTIFY_CENTER);
                    $printer->setFont(Printer::FONT_A);
                    $printer->setTextSize(1, 1);

                    
                    $printer->text($empresanegocios->nombre_comercial . "\n"); // O el nombre de la empresa
                    $printer->text("VENTA DIRECTA\n"); // Indica que es una venta directa
                    $printer->text("Comprobante: " . $cabecera_venta->serdoc . "-" . $cabecera_venta->numdoc . "\n");
                    $printer->text("Fecha: " . Carbon::parse($cabecera_venta->ccafem)->format('d/m/Y H:i') . "\n");
                    
                    // Si tienes un vendedor asociado a la venta
                    if (!empty($cabecera_venta->name)) {
                        $printer->text("Vendedor: " . $cabecera_venta->name . " " . $cabecera_venta->apeusu . "\n");
                    }
                    
                    // Si hay nombre de cliente en la venta directa
                    if (!empty($cabecera_venta->ccanom) && $cabecera_venta->ccanom != 'VENTA AL PORTADOR') {
                        $printer->text("Cliente: " . $cabecera_venta->ccanom . "\n");
                    }
                    // Puedes añadir más detalles de la cabecera aquí, como dirección si aplica, etc.

                    $printer->setJustification(Printer::JUSTIFY_LEFT);
                    $printer->text("----------------------------------------\n");
                    $printer->text("DESCRIPCION            CANT.  OBS.\n");
                    $printer->text("----------------------------------------\n");
                    $printer->setFont(Printer::FONT_B);
                    $printer->setTextSize(2, 2); // Productos más grandes para la cocina

                    foreach ($detalle_comanda as $det) {
                        // Asegúrate de que $det->cdedes contenga la descripción del producto de cpe_detalle
                        $descripcion_producto = $det->cdedes; // Asumo que cdedes es la descripción del producto en cpe_detalle
                        $cantidad = $det->cdecan; // Cantidad del producto en cpe_detalle
                        $observacion_item = $det->pronomobs ?? ''; // Si tienes observaciones a nivel de ítem en cpe_detalle

                        $printer->text($cantidad . "  " . $descripcion_producto . "\n");
                        if (!empty($observacion_item)) {
                            $printer->text("    (Obs: " . $observacion_item . ")\n");
                        }
                    }

                    $printer->text("\n");
                    $printer->setFont(Printer::FONT_B);
                    $printer->setTextSize(1, 1);
                    $printer->text("----------------------------------------\n");
                    if (!empty($cabecera_venta->ccaobs)) { // Observaciones generales de la venta
                         $printer->setJustification(Printer::JUSTIFY_CENTER);
                         $printer->text("Observaciones Generales:\n" . $cabecera_venta->ccaobs . "\n");
                         $printer->text("----------------------------------------\n");
                    }
                   
                    $printer->feed();
                    $printer->cut();
                    $printer->pulse();
                    $printer->close();

                } catch (\Exception $e) {
                    // Log the error or handle it as appropriate (e.g., alert the user)
                    // dd($e); // For debugging purposes, avoid in production
                    error_log("Error al imprimir comanda para la venta ID " . $cpe_id . ": " . $e->getMessage());
                }
            }
        }
    }

    

    private function imprimirComandaItemAnulado($pedidoId, $itemAnulado, $razonAnulacion, $usuarioAnulacion)
    {
        Log::info("Intentando imprimir comanda de ítem ANULADO para Pedido ID: {$pedidoId}, Producto: {$itemAnulado->descripcion}");

        $IdEmpresa = Auth::user()->IdEmpresa;
        $id_empresa_negocio = Auth::user()->id_empresa_negocio;

        $empresanegocios = DB::table('empresa_negocios')->where('id_empresa_negocio', $id_empresa_negocio)->first();

        $cab_pedido = DB::table('pedidos')
            ->where('ped_id', $pedidoId)
            ->leftJoin('users', 'users.IdUsuario', 'pedidos.mozo')
            ->first();

        if (!$cab_pedido) {
            Log::error("Comanda Item Anulado: Pedido #{$pedidoId} no encontrado.");
            return false;
        }

        $mesa = null;
        $piso_nombre = '';
        if (!empty($cab_pedido->mes_id)) {
            $mesa = DB::table('mesas')->where('mes_id', $cab_pedido->mes_id)->first();
            if ($mesa && !empty($mesa->pis_id)) {
                $piso = DB::table('pisos')->where('pis_id', $mesa->pis_id)->first();
                $piso_nombre = $piso ? $piso->pis_nom : '';
            }
        }

        $usuario_que_anulo = $usuarioAnulacion;
        $mozo_nombre_completo = '';
        if (!empty($cab_pedido->mozo)) {
            $mozo_user = DB::table('users')->where('IdUsuario', $cab_pedido->mozo)->first();
            $mozo_nombre_completo = ($mozo_user->name ?? '') . ' ' . ($mozo_user->apeusu ?? '');
        }

        $fecha_hora_anulacion = Carbon::now()->format('d/m/Y H:i:s');

        // Determinar a qué impresora enviar la comanda anulada
        $producto_info = DB::table('productos')->where('IdProducto', $itemAnulado->IdProducto)->first();
        if (!$producto_info || !$producto_info->cat_id) {
            Log::warning("Comanda Item Anulado: Producto ID {$itemAnulado->IdProducto} o su categoría no encontrada. No se puede determinar la impresora.");
            return false;
        }

        $categoria_info = DB::table('categorias')->where('cat_id', $producto_info->cat_id)->first();
        if (!$categoria_info || !$categoria_info->impresora) {
            Log::warning("Comanda Item Anulado: Categoría ID {$producto_info->cat_id} no tiene impresora asignada. No se imprime comanda de anulación.");
            return false;
        }

        $impresora_id = $categoria_info->impresora;
        $impresora_config = DB::table('configuracion_impresoras')->where('Id', $impresora_id)->first();

        if (!$impresora_config) {
            Log::error("Comanda Item Anulado: Configuración de impresora ID {$impresora_id} no encontrada.");
            return false;
        }

        try {
            /*$connector = null;
            if ($impresora_config->tip_conex_imp == 'COMPARTIDO') {
                $connector = new WindowsPrintConnector("smb://" . $impresora_config->ruta);
            } elseif ($impresora_config->tip_conex_imp == 'RED') {
                $connector = new NetworkPrintConnector($impresora_config->ruta, 9100);
            }*/
            //IMPRESORA VIRTUAL            
            $connector = new DummyPrintConnector();
            $printer = new Printer($connector);

            if (!$connector) {
                Log::error("Comanda Item Anulado: Conector no inicializado para impresora {$impresora_config->ruta}.");
                return false;
            }

            $printer = new Printer($connector);
            $printer->setJustification(Printer::JUSTIFY_CENTER);
            
            
            $printer->setTextSize(1, 1);
            $printer->setEmphasis(true);
            $printer->text("--- ITEM ANULADO POR USUARIO ---\n"); // Encabezado claro de anulación
            $printer->text("PEDIDO #{$pedidoId}\n");
            $printer->text("Tipo: " . ($cab_pedido->ped_tip ?? 'N/A') . "\n");

            if ($cab_pedido->ped_tip == 'Salon' && $mesa) {
                $printer->setTextSize(2, 2); // Mesa grande y destacada
                $printer->text("MESA: " . ($mesa->mes_nom ?? 'N/A') . "\n");
                $printer->setTextSize(1, 1); // Volver a tamaño normal
                $printer->text("Zona: " . ($piso_nombre ? $piso_nombre . "\n" : "") . "\n");
            } else {
                 $printer->setTextSize(2, 2);
                 $printer->text("PARA LLEVAR / DELIVERY\n");
                 $printer->setTextSize(1, 1);
            }
            $printer->setEmphasis(false); // Desactivar negrita


            $printer->setJustification(Printer::JUSTIFY_LEFT);
            $printer->text("Fecha/Hora Anulación: " . $fecha_hora_anulacion . "\n");
            $printer->text("Anulado por: " . $usuario_que_anulo . "\n");
            if (!empty($mozo_nombre_completo)) {
                $printer->text("Mozo original: " . $mozo_nombre_completo . "\n");
            }
            $printer->text("Motivo: " . $razonAnulacion . "\n");
            $printer->text("-----------------------------------\n");
            $printer->text("CONCEPTO ANULADO:\n");
            $printer->text("-----------------------------------\n");

            $printer->setTextSize(2, 2); // Detalles del producto anulado más grandes
            $line_anulado = sprintf("%-4s %-20s\n",
                $itemAnulado->ped_det_can, // Cantidad original del ítem que se anuló
                mb_strtoupper(mb_substr($itemAnulado->descripcion, 0, 20, 'UTF-8'), 'UTF-8') // Descripción en mayúsculas
            );
            $printer->text($line_anulado);
            if (!empty($itemAnulado->item_obs)) {
                $printer->setTextSize(1, 1); // Observación más pequeña
                $printer->text("    (Obs original: " . mb_substr($itemAnulado->item_obs, 0, 30, 'UTF-8') . (mb_strlen($itemAnulado->item_obs, 'UTF-8') > 30 ? '...' : '') . ")\n");
            }
            $printer->setTextSize(1, 1); // Volver a tamaño normal

            $printer->text("\n");
            $printer->feed();
            $printer->cut();
            $printer->pulse();
            //IMPRESORA VIRTUAL
            $codigo_raw = $connector->getData();
            $printer->close();

            //IMPRESORA VIRTUAL
                    DB::table('cola_impresion')->insert([
                        'contenido' => base64_encode($codigo_raw),
                        'impresora' => $impresora_config->descripcion,
                        'estado'    => '0'                        
                    ]);

            Log::info("Comanda de anulación para Pedido ID {$pedidoId}, Producto '{$itemAnulado->descripcion}' enviada a impresora {$impresora_config->ruta}.");
            return true;

        } catch (\Exception $e) {
            Log::error("Error de impresión de comanda ANULADA para Pedido #{$pedidoId}, Producto #{$itemAnulado->IdProducto} en impresora {$impresora_config->ruta}: " . $e->getMessage() . " en línea " . $e->getLine());
            return false;
        }
    }


    public function removeOldCartItem(Request $request)
    {
        $productId = $request->input('id');
        $authUser = trim($request->input('auth_user')); // Limpiar espacios en el nombre de usuario/email
        $authPassword = $request->input('auth_password');
        $reason = $request->input('reason');

        // ID del pedido actual desde la sesión
        $ped_id_from_session = session()->get('kiosko_last_pedido_id');

        // Validar que exista un pedido activo en la sesión
        if (empty($ped_id_from_session)) {
            Log::warning("removeOldCartItem: Intento de eliminar ítem sin pedido en sesión.");
            return response()->json(['success' => false, 'message' => 'No hay un pedido activo para modificar.'], 400);
        }

        // Buscar el detalle del pedido a eliminar
        $detalle_pedido = pedidos_detalle::where('IdProducto', $productId)
                                         ->where('ped_id', $ped_id_from_session)
                                         ->first();

        // Validar que el detalle del pedido exista y pertenezca al pedido actual
        if (!$detalle_pedido) {
            Log::warning("removeOldCartItem: Detalle de pedido no encontrado para IdProducto '{$productId}' en pedido '{$ped_id_from_session}'.");
            return response()->json(['success' => false, 'message' => 'Ítem del pedido no encontrado o no pertenece al pedido actual.'], 404);
        }

        // --- LÓGICA DE AUTENTICACIÓN Y ROLES ---
        // 1. Buscar el usuario por su 'email' (nombre de usuario para autenticación).
        $user_to_authenticate = User::where('email', $authUser)->first();

        // 2. Si el usuario con ese email no existe en la base de datos.
        if (!$user_to_authenticate) {
            Log::warning("removeOldCartItem: Autenticación fallida. Usuario '{$authUser}' (email) no encontrado en la tabla 'users'.");
            return response()->json(['success' => false, 'message' => 'Usuario o contraseña incorrectos, o no tienes permiso para esta acción.'], 403);
        }

        // 3. Verificar la contraseña hasheada.
        if (!Hash::check($authPassword, $user_to_authenticate->password)) {
            Log::warning("removeOldCartItem: Autenticación fallida. Contraseña incorrecta para usuario '{$authUser}' (ID: {$user_to_authenticate->IdUsuario}).");
            return response()->json(['success' => false, 'message' => 'Usuario o contraseña incorrectos, o no tienes permiso para esta acción.'], 403);
        }

        // 4. Verificar los roles del usuario.
        // Roles permitidos: 2 (admin), 4 (caja).
        $allowedRoleIds = [2, 4];
        
        // Cargar los roles del usuario si no están ya cargados.
        $user_to_authenticate->load('roles');

        // Verificar si el usuario tiene alguno de los roles permitidos.
        $has_permission = $user_to_authenticate->roles->contains(function ($role) use ($allowedRoleIds) {
            return in_array($role->id, $allowedRoleIds);
        });

        // Si el usuario no tiene los permisos necesarios.
        if (!$has_permission) {
            $user_roles = $user_to_authenticate->roles->pluck('name')->toArray();
            Log::warning("removeOldCartItem: Autenticación fallida. Usuario '{$authUser}' (ID: {$user_to_authenticate->IdUsuario}) autenticado, pero sin rol permitido. Roles encontrados: [" . implode(', ', $user_roles) . "]. Roles esperados: [" . implode(', ', $allowedRoleIds) . "].");
            return response()->json(['success' => false, 'message' => 'Usuario o contraseña incorrectos, o no tienes permiso para esta acción.'], 403);
        }
        // --- FIN DE LÓGICA DE AUTENTICACIÓN Y ROLES ---

        // Validación: El motivo de eliminación es obligatorio.
        if (empty($reason)) {
            return response()->json(['success' => false, 'message' => 'El motivo de eliminación es obligatorio.'], 400);
        }

        // Iniciar una transacción de base de datos para asegurar la consistencia.
        DB::beginTransaction();
        try {
            // Obtener el valor del ICBPER de la configuración de la empresa principal.
            $empresa_data = DB::table('empresa')->where('IdEmpresa', Auth::user()->IdEmpresa)->first();
            $icbper_val = $empresa_data ? $empresa_data->icbper : 0;

            // Registrar el movimiento de anulación/eliminación en movimientos_productos.
            DB::table('movimientos_productos')->insert([
                'IdProducto' => $productId,
                'IdProducto_rel' => $productId,
                'precio' => $detalle_pedido->ped_det_pre,
                'cantidad' => $detalle_pedido->ped_det_can,
                'costo' => 0.00,
                'mov_cab_id' => $detalle_pedido->ped_id,
                'stock' => 0.00, // No es el stock actual, se usa para el movimiento
                'stock_inicial' => 0.00, // No es el stock inicial real, se usa para el movimiento
                'serie' => null,
                'numero' => null,
                'tdocod' => '83', // Tipo de documento para anulación
                'tipo' => '4', // Tipo de movimiento para anulación/ajuste
                'id_empresa_negocio' => Auth::user()->id_empresa_negocio,
                'id_almacen' => $detalle_pedido->id_almacen_pro,
                'fecha_mov' => Carbon::now()->toDateString(),
                'fecha_registro' => Carbon::now(),
                'mov_tip' => 'I', // Entrada al stock (por anulación, revierte una salida)
                'descripcion' => 'Anulación de ítem (' . $detalle_pedido->descripcion . ') por ' . $authUser . ' - Motivo: ' . $reason,
                'cliente' => DB::table('pedidos')->where('ped_id', $detalle_pedido->ped_id)->first()->ped_cli_nom ?? 'N/A',
            ]);

            // Guardar la cantidad original del ítem antes de anularlo para la comanda de anulación
            $original_qty_for_print = $detalle_pedido->ped_det_can;
            $original_desc_for_print = $detalle_pedido->descripcion;
            $original_obs_for_print = $detalle_pedido->item_obs;

            // Actualizar el detalle del pedido a cantidad 0 y estado 'Anulado'.
            pedidos_detalle::where('ped_det_id', $detalle_pedido->ped_det_id)
                ->update([
                    'ped_det_can' => 0,
                    'ped_det_pre' => $detalle_pedido->ped_det_pre, // Mantener el precio original
                    'descripcion' => $detalle_pedido->descripcion . ' (ANULADO)', // Añadir "ANULADO" a la descripción
                    'item_obs' => trim($detalle_pedido->item_obs . ' (Eliminado por ' . $authUser . ': ' . $reason . ')'),
                    'estadoitem' => 'Eliminado',
                    'impreso' => 'impreso', // Marcar como impreso para no intentar reimprimir si se vuelve a comandar el pedido
                    'mod_cant' => -$original_qty_for_print, // Cantidad modificada (negativa para anulación)
                ]);

            // Revertir el stock del producto en el almacén.
            $this->revertir_stock_por_item($productId, $original_qty_for_print, Auth::user()->id_empresa_negocio);

            // === INICIO KARDEX: ANULACIÓN DE UN SOLO ÍTEM ===
            $affected = DB::table('productos')
                ->where('IdProducto', $productId)
                ->where('promocion', '2')
                ->increment('stock_preparados', $original_qty_for_print);

            if ($affected) {
                $stockActual = DB::table('productos')->where('IdProducto', $productId)->value('stock_preparados');
                DB::table('movimientos_preparados')->insert([
                    'producto_id' => $productId,
                    'pedido_id' => $ped_id_from_session,
                    'tipo_movimiento' => 'anulacion_item',
                    'cantidad' => $original_qty_for_print,
                    'stock_resultante' => $stockActual,
                    'observacion' => 'Ítem eliminado de comanda por ' . $authUser . ' - Motivo: ' . $reason,
                    'fecha_proceso' => Carbon::today()->toDateString(),
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now()
                ]);
            }
            // === FIN KARDEX ===

            // Actualizar el total del pedido principal.
            $pedido_cabecera = pedidos::findOrFail($detalle_pedido->ped_id);
            $total_item_anulado = ($original_qty_for_print * $detalle_pedido->ped_det_pre) +
                                  (isset($detalle_pedido->icbper_ind) && $detalle_pedido->icbper_ind == 1 ? ($original_qty_for_print * $icbper_val) : 0);
            
            $pedido_cabecera->ped_tot = round($pedido_cabecera->ped_tot - $total_item_anulado, 2);
            $pedido_cabecera->icbper_tot = round($pedido_cabecera->icbper_tot - (isset($detalle_pedido->icbper_ind) && $detalle_pedido->icbper_ind == 1 ? ($original_qty_for_print * $icbper_val) : 0), 2);
            $pedido_cabecera->fecha_hora_modificacion = Carbon::now();
            $pedido_cabecera->save();

            // --- NUEVO: Imprimir comanda de ítem ANULADO ---
            // Crear un objeto dummy con los datos necesarios para la impresión
            $itemAnuladoParaImprimir = (object)[
                'IdProducto' => $productId,
                'descripcion' => $original_desc_for_print,
                'ped_det_can' => $original_qty_for_print,
                'item_obs' => $original_obs_for_print,
                'ped_det_id' => $detalle_pedido->ped_det_id // ID del detalle si es necesario para el log de impresión
            ];

            $this->imprimirComandaItemAnulado(
                $ped_id_from_session,
                $itemAnuladoParaImprimir,
                $reason,
                $user_to_authenticate->name // O $user_to_authenticate->email si ese es el nombre a mostrar
            );
            // --- FIN NUEVO ---

            // --- LÓGICA PARA LIBERAR LA MESA SI EL PEDIDO QUEDA SIN ÍTEMS ACTIVOS ---
            $redirect_to_selection = false; // Inicializar la bandera de redirección

            // 1. Verificar si el pedido no tiene ítems activos (no anulados y con cantidad > 0)
            $activeItemsCount = pedidos_detalle::where('ped_id', $ped_id_from_session)
                                                ->where('estadoitem', '!=', 'Eliminado')
                                                ->where('ped_det_can', '>', 0) // Importante: contar solo si la cantidad es > 0
                                                ->count();

            // Si no hay ítems activos en el pedido (todos han sido anulados o su cantidad es 0)
            if ($activeItemsCount === 0) {
                $idMesa = $pedido_cabecera->mes_id;

                // Adicional: Si el pedido es de salón y tiene una mesa asociada, cambiar el estado de la mesa
                if ($pedido_cabecera->ped_tip == 'Salon' && $idMesa) {
                    $mesa = mesas::find($idMesa);
                    if ($mesa) {
                        $mesa->mes_est = 'Libre';
                        $mesa->ind_union = '0'; // Asegurar que el indicador de unión se resetee si la mesa se libera
                        $mesa->save();
                        Log::info("Mesa ID: {$idMesa} liberada porque el pedido ID: {$ped_id_from_session} quedó sin ítems activos.");
                    } else {
                        Log::warning("Mesa con ID {$idMesa} no encontrada para liberar (pedido ID: {$ped_id_from_session}).");
                    }
                }
                
                // Marcar el pedido principal como "Anulado" o "Cerrado" si ya no tiene ítems activos
                // Esto es importante para que no siga apareciendo como un pedido "activo" en otras vistas.
                $pedido_cabecera->ped_est = 'Anulado'; // O 'Cerrado' si prefieres ese estado
                $pedido_cabecera->save();

                $redirect_to_selection = true; // Establecer la bandera para redirigir
                // Limpiar la sesión del pedido si se libera la mesa
                session()->forget('kiosko_last_pedido_id');
                session()->forget('kiosko_cart');
                session()->forget('kiosko_order_type');
                session()->forget('kiosko_mesa_id');
                session()->forget('kiosko_mesa_nombre');
            }
            // --- FIN DE LA LÓGICA PARA LIBERAR LA MESA ---

            // Quitar el ítem del carrito de sesión (si no se liberó todo el pedido)
            // Esto se ejecutará solo si el pedido NO se vació por completo.
            if (!$redirect_to_selection) {
                $cart = session()->get('kiosko_cart', []);
                unset($cart[$productId]);
                session()->put('kiosko_cart', $cart);
            }

            DB::commit(); // Confirmar la transacción
            Log::info("removeOldCartItem: Ítem IdProducto '{$productId}' del pedido '{$ped_id_from_session}' eliminado exitosamente por '{$authUser}'.");
            
            // --- NUEVO: Retornar la bandera de redirección ---
            return response()->json([
                'success' => true,
                'message' => 'Ítem eliminado y stock revertido correctamente.',
                'redirect_to_selection' => $redirect_to_selection // Enviar la bandera al frontend
            ]);
            // --- FIN NUEVO ---

        } catch (\Exception $e) {
            DB::rollBack(); // Revertir la transacción si algo falla
            Log::error("Error crítico en removeOldCartItem para Pedido ID '{$ped_id_from_session}', Producto ID '{$productId}': " . $e->getMessage() . " en línea " . $e->getLine());
            return response()->json(['success' => false, 'message' => 'Error al eliminar el ítem: ' . $e->getMessage() . ' (Contacte a soporte)'], 500);
        }
    }



    /**
     * Reverte el stock de un producto.
     * Usado al anular ítems de comandas.
     * @param int $productId El ID del producto
     * @param float $quantity La cantidad a revertir
     * @param int $id_empresa_negocio El ID del negocio
     */
    private function revertir_stock_por_item($productId, $quantity, $id_empresa_negocio, $pedidoId = null, $productName = null, $almacenId = null, $reason_text = "Anulación de ítem")
    {
        try {
            // Buscar el almacén si no se proporciona (útil si esta función se llama independientemente)
            if (is_null($almacenId)) {
                $almacen_obj = DB::table('almacenes')
                                ->where('predeterminado', '1')
                                ->where('id_empresa_negocio', $id_empresa_negocio)
                                ->first();
                $almacenId = $almacen_obj ? $almacen_obj->id_almacen : 0;
            }

            $product_info = productos::find($productId);

            if (!$product_info) {
                Log::warning("Revertir Stock: Producto ID {$productId} no encontrado.");
                return;
            }

            // Nombre del cliente asociado al pedido, para el movimiento de stock
            $cliente_nom = DB::table('pedidos')->where('ped_id', $pedidoId)->first()->ped_cli_nom ?? 'N/A';
            
            // Descripción base para el movimiento de reversión
            $descripcion_movimiento_base = "{$reason_text} (Pedido ID: {$pedidoId})";

            // Lógica para revertir el stock según el tipo de producto (promocion)
            if ($product_info->promocion == '0' || $product_info->promocion == '1') { // Producto normal
                $id_prod_a_afectar = empty($product_info->pro_rel) ? $product_info->IdProducto : $product_info->pro_rel;
                $factor_conversion = $product_info->factor ?: 1;
                $cantidad_real_a_revertir = $quantity * $factor_conversion;

                // 1. Insertar registro en `movimientos_productos` (historial de reversión/entrada)
                DB::table('movimientos_productos')->insert([
                    'IdProducto' => $product_info->IdProducto,
                    'IdProducto_rel' => $id_prod_a_afectar,
                    'precio' => $product_info->propun,
                    'cantidad' => $cantidad_real_a_revertir,
                    'costo' => $product_info->costo_total,
                    'cliente' => $cliente_nom,
                    'IdCpe_cabecera' => null,
                    'serie' => null,
                    'numero' => null,
                    'tdocod' => '83', // Anulación
                    'tipo' => '4', // Tipo de ajuste
                    'mov_tip' => 'I', // Tipo de operación: Ingreso (reversión de egreso)
                    'id_empresa_negocio' => $id_empresa_negocio,
                    'id_almacen' => $almacenId,
                    'fecha_mov' => Carbon::now()->toDateString(),
                    'descripcion' => $descripcion_movimiento_base . " - " . ($productName ?? $product_info->pronom),
                    'mov_lote' => $product_info->lote,
                    'mov_vencimiento' => $product_info->vencimiento,
                    'fecha_registro' => Carbon::now(),
                ]);

                // 2. Actualizar el stock en la tabla `producto_stock`
                $stock_query = DB::table('producto_stock')
                                    ->where('IdProducto', $id_prod_a_afectar)
                                    ->where('id_almacen', $almacenId)
                                    ->where('id_empresa_negocio', $id_empresa_negocio);
                
                if ($product_info->requiere_lote_vencimiento) {
                    $stock_lote_venc = DB::table('producto_stock')
                                        ->where('IdProducto', $id_prod_a_afectar)
                                        ->where('id_almacen', $almacenId)
                                        ->where('id_empresa_negocio', $id_empresa_negocio)
                                        ->whereNotNull('lote')
                                        ->whereNotNull('vencimiento')
                                        ->first();
                    if ($stock_lote_venc) {
                        $stock_query->where('lote', $stock_lote_venc->lote)
                                    ->where('vencimiento', $stock_lote_venc->vencimiento);
                    }
                }
                $stock_query->increment('stock', $cantidad_real_a_revertir); // Realiza el incremento en `producto_stock`
                
                Log::info("Revertido stock de producto simple ID {$productId} (base: {$id_prod_a_afectar}) en cantidad: {$cantidad_real_a_revertir} en almacén {$almacenId}.");

            } elseif ($product_info->promocion == '2') { // Producto tipo 'receta'
                $recetas = DB::table('recetas')->where('prod_id', $productId)->get();

                foreach ($recetas as $rec) {
                    $insumo_cantidad_a_revertir = $quantity * $rec->rec_cant;
                    $insumo_info = productos::find($rec->prod_insu);

                    if (!$insumo_info) {
                        Log::warning("Revertir Stock (Receta): Insumo ID {$rec->prod_insu} no encontrado para receta del producto {$productId}. Saltando.");
                        continue;
                    }

                    // 1. Insertar registro en `movimientos_productos` para el INSUMO
                    DB::table('movimientos_productos')->insert([
                        'IdProducto' => $rec->prod_insu,
                        'IdProducto_rel' => $rec->prod_insu,
                        'precio' => 0,
                        'cantidad' => $insumo_cantidad_a_revertir,
                        'costo' => $rec->ins_costo,
                        'cliente' => $cliente_nom,
                        'IdCpe_cabecera' => null,
                        'serie' => null,
                        'numero' => null,
                        'tdocod' => '83',
                        'tipo' => '4',
                        'mov_tip' => 'I',
                        'id_empresa_negocio' => $id_empresa_negocio,
                        'id_almacen' => $almacenId,
                        'fecha_mov' => Carbon::now()->toDateString(),
                        'descripcion' => $descripcion_movimiento_base . " - Reversión Receta: " . ($productName ?? $product_info->pronom) . " (Insumo: " . $insumo_info->pronom . ")",
                        'fecha_registro' => Carbon::now(),
                    ]);

                    // 2. Actualizar el stock del INSUMO en `producto_stock`
                    $stock_insumo_query = DB::table('producto_stock')
                                            ->where('IdProducto', $rec->prod_insu)
                                            ->where('id_almacen', $almacenId)
                                            ->where('id_empresa_negocio', $id_empresa_negocio);

                    if ($insumo_info->requiere_lote_vencimiento) {
                        $stock_insumo_lote_venc = DB::table('producto_stock')
                                                ->where('IdProducto', $rec->prod_insu)
                                                ->where('id_almacen', $almacenId)
                                                ->where('id_empresa_negocio', $id_empresa_negocio)
                                                ->whereNotNull('lote')
                                                ->whereNotNull('vencimiento')
                                                ->first();
                        if ($stock_insumo_lote_venc) {
                            $stock_insumo_query->where('lote', $stock_insumo_lote_venc->lote)
                                               ->where('vencimiento', $stock_insumo_lote_venc->vencimiento);
                        }
                    }
                    $stock_insumo_query->increment('stock', $insumo_cantidad_a_revertir);
                    
                    Log::info("Revertido stock de insumo ID {$rec->prod_insu} (de receta de {$productId}) en cantidad: {$insumo_cantidad_a_revertir} en almacén {$almacenId}.");
                }
            } elseif ($product_info->promocion == '3') { // Es un combo
                $combo_items = DB::table('combos')->where('IdProducto_rel', $productId)->get();

                foreach ($combo_items as $combo_item) {
                    $component_product = productos::find($combo_item->IdProducto_comb);

                    if (!$component_product) {
                        Log::warning("Revertir Stock (Combo): Componente ID {$combo_item->IdProducto_comb} no encontrado para combo {$productId}. Saltando.");
                        continue;
                    }
                    
                    $component_quantity_to_revert = $quantity * $combo_item->prod_comb_cant;

                    if ($component_product->promocion == '0' || $component_product->promocion == '1') { // Componente simple dentro del combo
                        // 1. Insertar registro en `movimientos_productos` para el componente simple
                        DB::table('movimientos_productos')->insert([
                            'IdProducto' => $component_product->IdProducto,
                            'IdProducto_rel' => $component_product->IdProducto,
                            'precio' => $component_product->propun,
                            'cantidad' => $component_quantity_to_revert,
                            'costo' => $component_product->costo_total,
                            'cliente' => $cliente_nom,
                            'IdCpe_cabecera' => null,
                            'serie' => null,
                            'numero' => null,
                            'tdocod' => '83',
                            'tipo' => '4',
                            'mov_tip' => 'I',
                            'id_empresa_negocio' => $id_empresa_negocio,
                            'id_almacen' => $almacenId,
                            'fecha_mov' => Carbon::now()->toDateString(),
                            'descripcion' => $descripcion_movimiento_base . " - Reversión Componente Combo: " . ($productName ?? $product_info->pronom) . " (Componente: " . $component_product->pronom . ")",
                            'mov_lote' => $component_product->lote,
                            'mov_vencimiento' => $component_product->vencimiento,
                            'fecha_registro' => Carbon::now(),
                        ]);

                        // 2. Actualizar stock del COMPONENTE SIMPLE en `producto_stock`
                        $stock_componente_query = DB::table('producto_stock')
                                                ->where('IdProducto', $component_product->IdProducto)
                                                ->where('id_almacen', $almacenId)
                                                ->where('id_empresa_negocio', $id_empresa_negocio);

                        if ($component_product->requiere_lote_vencimiento) {
                            $stock_comp_lote_venc = DB::table('producto_stock')
                                                    ->where('IdProducto', $component_product->IdProducto)
                                                    ->where('id_almacen', $almacenId)
                                                    ->where('id_empresa_negocio', $id_empresa_negocio)
                                                    ->whereNotNull('lote')
                                                    ->whereNotNull('vencimiento')
                                                    ->first();
                            if ($stock_comp_lote_venc) {
                                $stock_componente_query->where('lote', $stock_comp_lote_venc->lote)
                                                        ->where('vencimiento', $stock_comp_lote_venc->vencimiento);
                            }
                        }
                        $stock_componente_query->increment('stock', $component_quantity_to_revert);

                        Log::info("Revertido stock de componente simple ID {$component_product->IdProducto} (de combo {$productId}) en cantidad: {$component_quantity_to_revert} en almacén {$almacenId}.");

                    } elseif ($component_product->promocion == '2') { // Componente con receta dentro del combo
                        $recetas_componente = DB::table('recetas')->where('prod_id', $component_product->IdProducto)->get();

                        foreach ($recetas_componente as $rec_comp) {
                            $insumo_combo_receta_cantidad_revertir = $component_quantity_to_revert * $rec_comp->rec_cant;
                            $insumo_info = productos::find($rec_comp->prod_insu);

                            if (!$insumo_info) {
                                Log::warning("Revertir Stock (Combo Receta): Insumo ID {$rec_comp->prod_insu} no encontrado para componente {$component_product->IdProducto} de combo {$productId}. Saltando.");
                                continue;
                            }

                            // 1. Insertar registro en `movimientos_productos` para el insumo del componente de receta
                            DB::table('movimientos_productos')->insert([
                                'IdProducto' => $rec_comp->prod_insu,
                                'IdProducto_rel' => $rec_comp->prod_insu,
                                'precio' => 0,
                                'cantidad' => $insumo_combo_receta_cantidad_revertir,
                                'costo' => $rec_comp->ins_costo,
                                'cliente' => $cliente_nom,
                                'IdCpe_cabecera' => null,
                                'serie' => null,
                                'numero' => null,
                                'tdocod' => '83',
                                'tipo' => '4',
                                'mov_tip' => 'I',
                                'id_empresa_negocio' => $id_empresa_negocio,
                                'id_almacen' => $almacenId,
                                'fecha_mov' => Carbon::now()->toDateString(),
                                'descripcion' => $descripcion_movimiento_base . " - Reversión Receta Combo: " . ($productName ?? $product_info->pronom) . " (Insumo: " . $insumo_info->pronom . ")",
                                'fecha_registro' => Carbon::now(),
                            ]);

                            // 2. Actualizar stock del INSUMO DEL COMPONENTE DE RECETA en `producto_stock`
                            $stock_insumo_combo_query = DB::table('producto_stock')
                                                    ->where('IdProducto', $rec_comp->prod_insu)
                                                    ->where('id_almacen', $almacenId)
                                                    ->where('id_empresa_negocio', $id_empresa_negocio);

                            if ($insumo_info->requiere_lote_vencimiento) {
                                $stock_insumo_combo_lote_venc = DB::table('producto_stock')
                                                                ->where('IdProducto', $rec_comp->prod_insu)
                                                                ->where('id_almacen', $almacenId)
                                                                ->where('id_empresa_negocio', $id_empresa_negocio)
                                                                ->whereNotNull('lote')
                                                                ->whereNotNull('vencimiento')
                                                                ->first();
                                if ($stock_insumo_combo_lote_venc) {
                                    $stock_insumo_combo_query->where('lote', $stock_insumo_combo_lote_venc->lote)
                                                            ->where('vencimiento', $stock_insumo_combo_lote_venc->vencimiento);
                                }
                            }
                            $stock_insumo_combo_query->increment('stock', $insumo_combo_receta_cantidad_revertir);

                            Log::info("Revertido stock de insumo ID {$rec_comp->prod_insu} (de receta de componente de combo {$productId}) en cantidad: {$insumo_combo_receta_cantidad_revertir} en almacén {$almacenId}.");
                        }
                    }
                }
            }
        } catch (\Exception $e) {
            Log::error("Error en revertir_stock_por_item para producto ID {$productId}: " . $e->getMessage() . " en línea " . $e->getLine());
            throw $e; // Re-lanza la excepción para que la transacción se revierta
        }
    }

    private function generar_codigo_movimiento($id_cpe_cabecera)
    {
        try {
            $cabecera = cpe_cabecera::find($id_cpe_cabecera);
            if ($cabecera) {
                // Genera un código simple basado en el ID. Ej: MOV-000001
                $codigo_movimiento = 'MOV-' . str_pad($id_cpe_cabecera, 6, '0', STR_PAD_LEFT);
                $cabecera->cod_mov = $codigo_movimiento;
                $cabecera->save();
                Log::info("Código de movimiento '{$codigo_movimiento}' generado para venta ID {$id_cpe_cabecera}.");
            }
        } catch (\Exception $e) {
            Log::error("Error en generar_codigo_movimiento para venta ID {$id_cpe_cabecera}: " . $e->getMessage());
        }
    }


    // ==========================================
    // NUEVAS FUNCIONES PARA RESERVAS EN KIOSKO
    // ==========================================

    public function getReservasHoy()
    {
        // Trae las reservas de hoy que estén "Confirmadas" (aún no atendidas)
        $reservas = DB::table('reservas')
            ->join('cliente', 'reservas.clicod', '=', 'cliente.clicod')
            ->join('mesas', 'reservas.mes_id', '=', 'mesas.mes_id')
            ->join('pisos', 'reservas.pis_id', '=', 'pisos.pis_id')
            ->whereDate('fecha_reserva', date('Y-m-d'))
            ->where('reservas.estado', 'Confirmada')
            ->select('reservas.*', 'cliente.clinom', 'mesas.mes_nom', 'pisos.pis_nom', 'mesas.mes_est')
            ->get();

        return response()->json(['success' => true, 'reservas' => $reservas]);
    }

    public function procesarReserva(Request $request)
    {
        $reservaId = $request->input('res_id');
        $nuevaMesaId = $request->input('nueva_mes_id'); 

        $reserva = DB::table('reservas')->where('res_id', $reservaId)->first();
        if (!$reserva) {
            return response()->json(['success' => false, 'message' => 'Reserva no encontrada.']);
        }

        $id_empresa_negocio = Auth::user()->id_empresa_negocio;
        $id_empresa         = Auth::user()->IdEmpresa;
        $usuario_id         = Auth::user()->IdUsuario;

        $mesaIdAUsar = $nuevaMesaId ?? $reserva->mes_id;
        $mesa = DB::table('mesas')->where('mes_id', $mesaIdAUsar)->first();

        // Validar si la mesa está libre
        if ($mesa->mes_est !== 'Libre' && !$nuevaMesaId) {
            $mesasLibres = DB::table('mesas')
                ->where('pis_id', $reserva->pis_id)
                ->where('mes_est', 'Libre')
                ->get();

            return response()->json([
                'success' => false, 
                'requiere_cambio' => true, 
                'mesas_libres' => $mesasLibres, 
                'mesa_original' => $mesa->mes_nom
            ]);
        }

        DB::beginTransaction();
        try {
            // --- AQUÍ ESTÁ LA CLAVE: OBTENER DATOS DEL CLIENTE ---
            $cliente = DB::table('cliente')->where('clicod', $reserva->clicod)->first();
            
            $nombreCliente = $cliente ? $cliente->clinom : 'CLIENTE DE RESERVA';
            $numDocCliente = $cliente ? $cliente->clinum : '00000000';

            // Insertar en pedidos usando las columnas exactas que tu sistema usa
            $pedidoId = DB::table('pedidos')->insertGetId([
                'pagado'             => 0,
                'mozo'               => $usuario_id,
                'IdUsuario'          => $usuario_id,
                'ped_tip'            => 'Salon',
                'ped_fec'            => date('Y-m-d'),
                'pis_id'             => $mesa->pis_id,
                'mes_id'             => $mesa->mes_id,
                'IdEmpresa'          => $id_empresa,
                'id_empresa_negocio' => $id_empresa_negocio,
                'ped_est'            => 'Aperturado',
                'ped_cli_nom'        => strtoupper($nombreCliente),
                'ped_num_doc'        => $numDocCliente, // Corregido: esta es la columna que existe
                'ped_tot'            => $reserva->total,
                'fecha_hora'         => \Carbon\Carbon::now(),
                'ped_fec'         => now(),
                //'updated_at'         => now(),
            ]);

            // Insertar detalles
            $detallesReserva = DB::table('reserva_detalle')
                ->join('productos', 'reserva_detalle.IdProducto', '=', 'productos.IdProducto')
                ->where('res_id', $reservaId)->get();

            $itemsToPrint = [];
            foreach ($detallesReserva as $det) {
                $detId = DB::table('pedidos_detalle')->insertGetId([
                    'ped_id'      => $pedidoId,
                    'IdProducto'  => $det->IdProducto,
                    'IdEmpresa'   => $id_empresa,
                    'estadoitem'  => 'Ingresado',
                    'impreso'     => 'imprimir',
                    'ped_det_can' => $det->cantidad,
                    'ped_det_pre' => $det->precio_unitario,
                    'descripcion' => $det->pronom,
                    'detalle'     => $det->pronom,
                    'item_obs'    => $det->observacion_producto,
                    'fecha_hora'  => \Carbon\Carbon::now(),
                ]);

                $itemsToPrint[] = (object)[
                    'descripcion' => $det->pronom,
                    'ped_det_can' => $det->cantidad,
                    'item_obs'    => $det->observacion_producto,
                    'ped_det_id'  => $detId,
                    'IdProducto'  => $det->IdProducto,
                ];
            }

            // Imprimir y actualizar estados
            $this->imprimirComandaPorCategorias($pedidoId, $itemsToPrint);
            DB::table('mesas')->where('mes_id', $mesa->mes_id)->update(['mes_est' => 'Ocupado']);
            DB::table('reservas')->where('res_id', $reservaId)->update(['estado' => 'Atendida', 'mes_id' => $mesa->mes_id]);

            DB::commit();
            return response()->json(['success' => true, 'pedido_id' => $pedidoId]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
        }
    }
}