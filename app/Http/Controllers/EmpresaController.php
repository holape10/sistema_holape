<?php

namespace MasterSoft\Http\Controllers;

use Illuminate\Http\Request;
use Greenter\XMLSecLibs\Certificate\X509Certificate;
use Greenter\XMLSecLibs\Certificate\X509ContentType;
use MasterSoft\Empresa;
use MasterSoft\User;
use MasterSoft\role_user;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use MasterSoft\EmpresaNegocios;
use MasterSoft\Almacenes;
use MasterSoft\subcategorias;
use MasterSoft\categorias;
use MasterSoft\tipoproducto;
use MasterSoft\mediospagos;
use MasterSoft\empleado;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\DB;

class EmpresaController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth')->except(['crearempresa', 'store', 'consultaRucSunat', 'borrarColaImpresion']);
    }

    public function index(Request $request)
    {
        if ($request) {
            $query = trim($request->get('searchText'));
            
            $empresas = DB::table('empresa') 
                ->select('id_empresa_negocio', 'tipo_negocio', 'empresa.IdEmpresa', 'NomEmpresa', 'LogEmpresa', 'EstEmpresa', 'DirEmpresa', 'direccion')
                ->join('empresa_negocios', 'empresa_negocios.IdEmpresa', 'empresa.IdEmpresa')
                ->where('NomEmpresa', 'like', '%' . $query . '%')
                ->orderBy('NomEmpresa', 'asc')  
                ->paginate(7);
          
            return view('administrador.empresas.index', ["empresas" => $empresas, "searchText" => $query]);
        }        
    }

    public function create()
    {   
        $tip_env_fac = DB::table('tipo_envio_facturacion')->get();
        $ubigeos = DB::table('cat_ubigeo')->get();
        return view('administrador.empresas.create', compact('ubigeos', 'tip_env_fac'));
    }

    public function crearempresa()
    {   
        $tip_env_fac = DB::table('tipo_envio_facturacion')->get();
        $ubigeos = DB::table('cat_ubigeo')->get();
        $tipos_sistemas = DB::table('tipos_sistemas')->get(); 

        return view('administrador.empresas.configurar', compact('ubigeos', 'tip_env_fac', 'tipos_sistemas'));
    }

    public function store(Request $request)
    {
        $nomEmpresa = $request->get('nomEmpresa');
        $rucEmpresa = $request->get('rucEmpresa');
        $dirEmpresa = $request->get('dirEmpresa');
        $txtWsUsuario = $request->get('txtWsUsuario');
        $txtWsContrasena = $request->get('txtWsContrasena');
        
        if (empty($txtWsUsuario)) { $txtWsUsuario = 'HOLAPE10'; }
        if (empty($txtWsContrasena)) { $txtWsContrasena = 'HolaPe10'; }        

        $validar = DB::table('empresa')->where('IdEmpresa', $request->get('rucEmpresa'))->get();
        if (count($validar) > 0) {
            if ($request->ajax()) { return response()->json(['error' => 'EL RUC YA EXISTE']); }
        }
        if (empty($rucEmpresa)) {
            if ($request->ajax()) { return response()->json(['error' => 'INGRESAR RUC DE LA EMPRESA']); }
        }
        if (empty($nomEmpresa)) {
            if ($request->ajax()) { return response()->json(['error' => 'INGRESAR NOMBRE DE LA EMPRESA']); }
        }    
        if (empty($dirEmpresa)) {
            if ($request->ajax()) { return response()->json(['error' => 'INGRESAR LA DIRECCION DE LA EMPRESA']); }
        }
    
        $empresa = new Empresa;
        $empresa->IdEmpresa = $request->get('rucEmpresa');
        $empresa->NomEmpresa = $request->get('nomEmpresa');
        $empresa->DirEmpresa = $request->get('dirEmpresa');
        $empresa->ticket_pantalla = $request->get('ticket_pantalla');
        $empresa->correo_envio = $request->get('correo_envio');
        $empresa->contrasena_envio = $request->get('contrasena_envio');
        $empresa->tip_env_fac_id = $request->get('tip_env_fac');
        $empresa->id_tipo_sistema = $request->get('id_tipo_sistema'); 
        $empresa->cor_combaja = '0000';
        $empresa->cor_resumen = '0000';
        $empresa->icbper = $request->get('icbper');
        $empresa->tipo_envio = $request->get('envio');
        $empresa->formato = $request->get('formato');
        $empresa->imp_pedido = $request->get('imp_pedido');
        $empresa->imp_venta = $request->get('imp_venta');
        $empresa->produccion = $request->get('produccion');
        $empresa->wsusuario = $txtWsUsuario;
        $empresa->claveSunat = $txtWsContrasena;
        $empresa->passcert = $request->get('txtPassCert');
        $empresa->EstEmpresa = 'Activo';
        
        // CORREGIDO: Uso de $request en lugar de Input
        if ($request->hasFile('txtCertificado')) {
            $file = $request->file('txtCertificado');
            $file->move(public_path() . '/certificados/', $request->get('rucEmpresa') . '.pfx');            
            $empresa->certificado = $request->get('rucEmpresa') . '.pfx';
            
            $pfx = file_get_contents(public_path() . '/certificados/' . $request->get('rucEmpresa') . '.pfx');
            $password = $request->get('txtPassCert');
            $certificate = new X509Certificate($pfx, $password);
            $pem = $certificate->export(X509ContentType::PEM);                
            file_put_contents(public_path() . '/certificados/' . $request->get('rucEmpresa') . '.pem', $pem);
        }
        
        $empresa->save();
        
        $buscarubigeo = DB::table('cat_ubigeo')->where('ubi_cod', $request->get('ubigeo'))->first(); 
        $formatos_comp = DB::table('formatos_comprobantes')->first();
        
        $negocios = new EmpresaNegocios;
        $negocios->tipo_negocio = 'Oficina Principal - ' . $request->get('rucEmpresa');
        $negocios->nombre_comercial = $request->get('NomComercial');
        $negocios->estado = 'Activo';
        $negocios->direccion = $request->get('dirEmpresa');
        $negocios->telefono = $request->get('telEmpresa');
        $negocios->correo = $request->get('corEmpresa');
        $negocios->web = $request->get('webEmpresa');
        $negocios->IdEmpresa = $request->get('rucEmpresa');
        $negocios->codigofiscal = '0000';
        $negocios->ubigeo = $request->get('ubigeo');
        $negocios->cod_for_com = $formatos_comp->cod_for_com ?? '0000';
        $negocios->departamento = $buscarubigeo->departamento ?? '';
        $negocios->provincia = $buscarubigeo->provincia ?? '';
        $negocios->distrito = $buscarubigeo->distrito ?? '';
        $negocios->save();

        $almacen = new Almacenes;
        $almacen->descripcion = 'ALMACEN PRINCIPAL';
        $almacen->predeterminado = '1';
        $almacen->id_empresa_negocio = $negocios->id_empresa_negocio;
        $almacen->direccion = $request->get('dirEmpresa');
        $almacen->ubigeo = $request->get('ubigeo');
        $almacen->save();

        $medpag = new mediospagos;
        $medpag->IdEmpresa = $rucEmpresa;
        $medpag->nom_med_pag = 'EFECTIVO';
        $medpag->predeterminado = '1';
        $medpag->save();
        
        DB::table('credito_dias')->insert([
            'IdEmpresa' => $rucEmpresa,            
            'cre_dia_nom' => 'CONTADO',
            'cre_dia_fac' => '0',
            'cre_dia_tip' => 'CONTADO'
        ]);
       
        DB::table('credito_dias')->insert([
            'IdEmpresa' => $rucEmpresa,
            'cre_dia_nom' => 'CREDITO',
            'cre_dia_fac' => '0',
            'cre_dia_tip' => 'PERSONALIZADO'
        ]);

        $productos = DB::table('productos')->get();

        foreach ($productos as $pro) {
            $buspro = DB::table('producto_empresa')
                ->where('id_empresa_negocio', $negocios->id_empresa_negocio)
                ->where('IdProducto', $pro->IdProducto)
                ->first();
       
            if (empty($buspro)) {
               DB::table('producto_empresa')->insert([
                   'IdProducto' => $pro->IdProducto,
                   'id_empresa_negocio' => $negocios->id_empresa_negocio
               ]); 
            }            
        }

        foreach ($productos as $pro) {              
            $buspro = DB::table('producto_stock')
                ->where('id_empresa_negocio', $negocios->id_empresa_negocio)
                ->where('id_almacen', $almacen->id_almacen)
                ->where('IdProducto', $pro->IdProducto)
                ->first();
            
            if (empty($buspro)) {
                DB::table('producto_stock')->insert([
                    'IdProducto' => $pro->IdProducto,
                    'id_empresa_negocio' => $negocios->id_empresa_negocio,
                    'id_almacen' => $almacen->id_almacen
                ]); 
            } 
        }

        $empleado = new empleado;
        $empleado->emp_nom = $rucEmpresa;
        $empleado->emp_ape_mat = '.';
        $empleado->emp_ape_pat = $nomEmpresa;      
        $empleado->est_cod = '1';
        $empleado->emp_num_doc = $rucEmpresa;
        $empleado->tdicod = '6';
        $empleado->rol_id = '2';
        $empleado->id_empresa_negocio = $negocios->id_empresa_negocio;
        $empleado->save();

        $usuario = new User;
        $usuario->name = $rucEmpresa;
        $usuario->apeusu = $nomEmpresa;
        $usuario->estusu = '1';
        $usuario->IdEmpresa = $rucEmpresa;
        $usuario->email = $rucEmpresa;
        $usuario->password = bcrypt($rucEmpresa);
        $usuario->id_empresa_negocio = $negocios->id_empresa_negocio;
        $usuario->emp_id = $empleado->emp_id;
        $usuario->save();

        $role_user = new role_user;
        $role_user->role_id = '2';
        $role_user->user_IdUsuario = $usuario->IdUsuario ?? $usuario->id;
        $role_user->save();

        $tipos = new tipoproducto;
        $tipos->tip_pro_nom = 'GENERAL';
        $tipos->IdEmpresa = $rucEmpresa;
        $tipos->id_empresa_negocio = $negocios->id_empresa_negocio;
        $tipos->save();

        $categorias = new categorias;
        $categorias->IdEmpresa = $rucEmpresa;
        $categorias->color = '#3f4aee';
        $categorias->id_empresa_negocio = $negocios->id_empresa_negocio;
        $categorias->predeterminado = '1';
        $categorias->cat_nom = 'GENERAL';
        $categorias->tip_pro_id = $tipos->tip_pro_id;
        $categorias->save();

        $subcategorias = new subcategorias;
        $subcategorias->color = '#3f4aee';
        $subcategorias->id_empresa_negocio = $negocios->id_empresa_negocio;
        $subcategorias->subcat_nom = 'GENERAL';
        $subcategorias->cat_id = $categorias->cat_id;
        $subcategorias->save();

        if ($request->ajax()) {
            return response()->json(['mensaje' => 'SE REGISTRO LA EMPRESA CON EXITO']);
        }
        
        return redirect()->route('empresas.index'); // Ajusta la ruta según tu sistema
    }

    public function show($id)
    {
        return view('administrador.empresas.show', ['empresa' => Empresa::findOrFail($id)]);
    }

    public function showLogo($id)
    {
         try {
             return view('auth.login', ['empresa' => Empresa::findOrFail($id)]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return view('auth/mensaje');
        }
    }

    public function edit($id)
    {
        $tip_env_fac = DB::table('tipo_envio_facturacion')->get();
        $empresa = Empresa::findOrFail($id);
        return view('administrador.empresas.edit', compact('empresa', 'tip_env_fac'));
    }

    public function update(Request $request, $id)
    {
        $empresa = Empresa::findOrFail($id);
        $empresa->NomEmpresa = $request->get('nomEmpresa');
        $empresa->DirEmpresa = $request->get('dirEmpresa');
        $empresa->EstEmpresa = $request->get('estEmpresa');
        $empresa->wsusuario = $request->get('txtWsUsuario');
        $empresa->claveSunat = $request->get('txtWsContrasena');
        $empresa->fec_ini_cer = $request->get('fecini');
        $empresa->fec_fin_cer = $request->get('fecfin');
        $empresa->produccion = $request->get('produccion');
        $empresa->ticket_pantalla = $request->get('ticket_pantalla');
        $empresa->correo_envio = $request->get('correo_envio');
        $empresa->contrasena_envio = $request->get('contrasena_envio');
        $empresa->passcert = $request->get('txtPassCert');
        $empresa->formato = $request->get('formato');
        $empresa->imp_pedido = $request->get('imp_pedido');
        $empresa->imp_venta = $request->get('imp_venta');
        $empresa->icbper = $request->get('icbper');
        $empresa->tip_env_fac_id = $request->get('tip_env_fac');
        $empresa->tipo_envio = $request->get('envio');
        
        // CORREGIDO: Uso de $request en lugar de Input
        if ($request->hasFile('logologin')) {
            $file = $request->file('logologin');
            $file->move(public_path() . '/', $file->getClientOriginalName());
            $empresa->LogEmpresa = $file->getClientOriginalName();
        }

        if ($request->hasFile('txtCertificado')) {
            $file = $request->file('txtCertificado');
            $file->move(public_path() . '/certificados/', $request->get('rucEmpresa') . '.pfx');            
            $empresa->certificado = $request->get('rucEmpresa') . '.pfx';
            
            $pfx = file_get_contents(public_path() . '/certificados/' . $request->get('rucEmpresa') . '.pfx');
            $password = $request->get('txtPassCert');
            $certificate = new X509Certificate($pfx, $password);
            $pem = $certificate->export(X509ContentType::PEM);                
            file_put_contents(public_path() . '/certificados/' . $request->get('rucEmpresa') . '.pem', $pem);
        }
        
        $empresa->save(); // Cambiado de update() a save() para consistencia con Eloquent
    
        DB::table('productos')->where('icbper', '1')->where('IdEmpresa', Auth::user()->IdEmpresa)->update(['mon_icbper' => $empresa->icbper]);

        return Redirect::to('administrador/empresas');
    }

    public function destroy($id)
    {
        $empresa = Empresa::findOrFail($id);
        $empresa->delete();
        return Redirect::to('administrador/empresas');
    }

    public function vistaVaciarTablas()
    {
        return view('administrador.empresas.vaciartablas');
    }

    public function ejecutarVaciado(Request $request)
    {
        if ($request->confirmacion !== 'LIMPIAR MI BASE DE DATOS') {
            return response()->json(['res' => 'error', 'msg' => 'Confirmación incorrecta.'], 422);
        }

        $tablas = [
            'academicos', 'almacenes', 'categorias', 'combos', 'combustible', 'compras_cabecera', 'compras_detalle', 'atencion_clinica',
            'configuracion_impresoras', 'credito_dias', 'cuentas_cobrar', 'cuentas_cobrar_detalle', 'cuentas_cobrar_medios', 'brazaletes', 'asistencia_horarios', 'cabecera_abono', 'ordenes_servicio',
            'cuentas_pagar', 'cuentas_pagar_detalle', 'cuentas_pagar_medios', 'cuentasbancarias', 'documento_relacionado', 'attendances',
            'empleado', 'empresa', 'empresa_negocios', 'fidelizacion_configs', 'gastos_cabecera', 'gastos_detalle', 'mermas', 'motivos_merma',
            'guias_remision', 'guias_remision_detalle', 'inventario_cabecera', 'inventario_detalle', 'marcas', 'sessions', 'medios_pagos', 'mesas', 'mesas_union', 'meses', 'movimientos', 'movimientos_productos', 'movimientosbancarios', 'movimientos_preparados', 'consultas_ginecologicas', 'movimientoscaja', 'pedidos', 'pedidos_detalle', 'pisos', 'productos', 'producto_stock', 'producto_empresa', 'producto_codigo', 'precios_dia_semana', 'proveedor', 'puntos_historial', 'recetas', 'resumenes', 'role_user', 'saldos_arqueo', 'subcategorias', 'cliente', 'cpe_detalle', 'cpe_cabecera', 'tipo_producto', 'tipocambio', 'asientos', 'asiento_detalles', 'historia_clinica', 'pacientes', 'salas', 'sesiones_sauna', 'tipos_sala', 'visitas_sauna', 'aplicativos',
            'tipos_vehiculos', 'vehiculos', 'turno_medio_pago', 'turnos', 'users', 'usuario_facturacion', 'usuario_gastos', 'cola_impresion',
            'usuario_modificar', 'usuario_pedidos', 'usuario_sucursal', 'venta_medio_pago', 'ventas_cuotas', 'reservas', 'reserva_detalle'
        ];

        try {
            DB::beginTransaction();
            DB::statement('SET FOREIGN_KEY_CHECKS = 0;');

            foreach ($tablas as $tabla) {
                if (Schema::hasTable($tabla)) {
                    DB::table($tabla)->truncate();
                }
            }

            DB::statement('SET FOREIGN_KEY_CHECKS = 1;');
            DB::commit();

            return response()->json(['res' => 'success', 'msg' => 'Tablas de Hola Pe limpias.']);
        } catch (\Exception $e) {
            DB::rollBack();
            DB::statement('SET FOREIGN_KEY_CHECKS = 1;');
            return response()->json(['res' => 'error', 'msg' => $e->getMessage()], 500);
        }
    }

    public function consultaRucSunat($ruc)
    {
        $url = "https://consultas.holape.app/api/v1/ruc/" . $ruc;

        $curl = curl_init();
        curl_setopt_array($curl, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CUSTOMREQUEST => "GET",
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_HTTPHEADER => [
                'Accept: application/json',
            ],
        ]);

        $response = curl_exec($curl);
        $err = curl_error($curl);
        curl_close($curl);

        if ($err) {
            return response()->json(['error' => 'Error de conexión con tu servidor de RUC']);
        } else {
            $data = json_decode($response);
            
            if (isset($data->success) && $data->success == true) {
                return response()->json([
                    'nom'    => $data->data->razon_social ?? '',
                    'dir'    => $data->data->direccion ?? '',
                    'ubigeo' => $data->data->ubigeo ?? ''
                ]);
            } else {
                return response()->json(['error' => 'RUC no encontrado o no válido']);
            }
        }
    }

    public function listarEmpresas()
    {
        $empresas = DB::table('empresa as e')
            ->join('users as u', 'e.IdEmpresa', '=', 'u.IdEmpresa')
            ->select('e.IdEmpresa', 'e.NomEmpresa')
            ->where('u.email', '=', Auth::user()->email)
            ->orderBy('e.NomEmpresa', 'asc')->get();

        return view('administrador.listarempresas', ["empresas" => $empresas]);
    }

    public function borrarColaImpresion()
    {
        try {
            DB::table('cola_impresion')->truncate();
            return "La cola de impresión se ha limpiado correctamente.";
        } catch (\Exception $e) {
            return "Hubo un error al intentar limpiar la cola: " . $e->getMessage();
        }
    }
}