<?php

namespace MasterSoft\Http\Controllers;

use Illuminate\Http\Request;
use MasterSoft\Http\Requests;
use MasterSoft\Cliente;
use MasterSoft\User;
use MasterSoft\tipo_documento_identidad;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Input;
use MasterSoft\Http\Requests\ClienteFormRequest;
use MasterSoft\Http\Requests\ClienteUpdateFormRequest;
use Illuminate\Support\Facades\Auth;
use DB;


class ClientesController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
     public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(Request $request)
{
    $buscli = trim($request->get('buscli'));
    $mes_nac = $request->get('mes_nac');

    $clientes = DB::table('cliente')
        ->leftjoin('meses', 'meses.mes_num', 'cliente.mes_nac')
        ->where(function ($query) use ($buscli) {
            if (!empty($buscli)) {
                $query->where('clinom', 'like', '%' . $buscli . '%')
                      ->orWhere('clinum', '=', $buscli);
            }
        })
        ->where(function ($query) use ($mes_nac) {
            if (!empty($mes_nac)) {
                $query->where('mes_nac', '=', $mes_nac);
            }
        })
        ->orderBy('clinom', 'asc')
        ->paginate(100); // <--- Cambiamos get() por paginate()

    // Esto mantiene los filtros en los enlaces de la paginación
    $clientes->appends(['buscli' => $buscli, 'mes_nac' => $mes_nac]);

    return view('empresas.clientes.index', [
        "clientes" => $clientes, 
        "buscli" => $buscli
    ]);
}

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {   
        $vendedores = DB::tABLE('users')->get();

        $documentos = DB::tABLE('tipo_documento_identidad')->where('tdiest','=','Activo')->orderby('tdicod')->get();
        return view('empresas.clientes.create',compact('vendedores','documentos'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {   
        $opcion = $request->get('opcion');
        $documentos = DB::table('tipo_documento_identidad')->where('tdiest', '=', 'Activo')->get();

        // Extraer el mes para cumpleaños
        $fecha_nacimiento = $request->get('fecha_nacimiento');
        $mes = null;
        if (!empty($fecha_nacimiento)) {
            $mes = date('m', strtotime($fecha_nacimiento));
        }

        $tdicod = empty($request->get('tdicod')) ? '1' : $request->get('tdicod');

        // Arreglo de datos para no repetir código
        $datosCliente = [
            'clinom'     => $request->get('clinom'),
            'clidir'     => $request->get('clidir'),
            'clicor'     => $request->get('clicor'),
            'clicor2'    => $request->get('clicor2'),
            'clicor3'    => $request->get('clicor3'),
            'clicor4'    => $request->get('clicor4'),
            'tdicod'     => $tdicod,
            'telefono'   => $request->get('clitel'),      // CELULAR
            'fecha_nacimiento'  => $fecha_nacimiento,                   // FECHA NACIMIENTO
            'mes_nac'    => $mes,                         // MES PARA CUMPLEAÑOS
            'direccion1' => $request->get('clidir1'),
            'direccion2' => $request->get('clidir2'),
            'cuenta12' => $request->get('cuenta12'),
            'direccion3' => $request->get('clidir3'),
            'direccion4' => $request->get('clidir4'),
            'direccion5' => $request->get('clidir5')
        ];

        if (empty(trim($request->get('clinum')))) {
            $datosCliente['rucemp'] = Auth::user()->IdEmpresa;
            $clientenuevo = Cliente::create($datosCliente);

        } elseif ($request->get('clinum') == '00000000' && (trim(strtoupper($request->get('clinom'))) == 'VENTAALPORTADOR' or trim(strtoupper($request->get('clinom'))) == 'VARIOS')) {
            // Lógica para venta al portador
        } else {
            $clientenuevo = Cliente::updateOrCreate(
                ['clinum' => $request->get('clinum')],
                $datosCliente
            );   
        }

        $clientes = DB::table('cliente')->get();

        if ($opcion == '1') {
            $vista = view('empresas.clientes.divcliente', compact('clientenuevo', 'clientes', 'documentos'))->render();
            if ($request->ajax()) {
                return response()->json(['vista' => $vista]);
            }
        } else {
            return Redirect::to('/clientes'); 
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
    public function edit($id)
    {
        $clientes=Cliente::findOrFail($id);
        $documentos=DB::tABLE('tipo_documento_identidad')->where('tdiest','=','Activo')->get();
        $vendedores = DB::tABLE('users')->get();
        return view('empresas.clientes.edit',compact('clientes','documentos','vendedores'));
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
        $cliente = Cliente::findOrFail($id);
        $cliente->rucemp = trim(Auth::user()->IdEmpresa);
        $cliente->tdicod = $request->get('tdicod');
        $cliente->clinum = $request->get('clinum');
        $cliente->clidir = $request->get('clidir');
        $cliente->clinom = $request->get('clinom');
        $cliente->clicor = $request->get('clicor');
        $cliente->clicor2 = $request->get('clicor2');
        $cliente->clicor3 = $request->get('clicor3');
        $cliente->clicor4 = $request->get('clicor4');
        $cliente->cuenta12 = $request->get('cuenta12');
        $cliente->direccion1 = $request->get('clidir1');
        $cliente->direccion2 = $request->get('clidir2');
        $cliente->direccion3 = $request->get('clidir3');
        $cliente->direccion4 = $request->get('clidir4');
        $cliente->direccion5 = $request->get('clidir5');
        
        // Guardando celular y fecha
        $cliente->telefono  = $request->get('clitel');
        $cliente->fecha_nacimiento = $request->get('fecha_nacimiento');
        if (!empty($request->get('fecha_nacimiento'))) {
            $cliente->mes_nac = date('m', strtotime($request->get('fecha_nacimiento')));
        }
        
        $cliente->cliest = $request->get('cliest');
        $cliente->update();

        return Redirect::to('/clientes');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $clientes= Cliente::findOrFail($id);
        $clientes->delete();
        return Redirect::to('/clientes');
    }

    public function editarContrasena ($id)
    {
        $usuario = User::findOrFail($id);
        return view('empresas.clientes.contrasena',['usuario'=>$usuario]);
    }

    public function cambiarContrasena(Request $request)
    {
        $idUsuario = $request->get('idUsuario');
        $usuario = User::findOrFail($idUsuario);
        $usuario->password = bcrypt($request->get('password'));
        $usuario->update();
        return Redirect::to('/MasterSoft');
    }

    public function seleccionardireccion(Request $request,$id){
        $direcciones = DB::tABLE('cliente')->where('clicod',$id)->first();
        $vista = view('empresas.clientes.direcciones',compact('direcciones'))->render();
        if($request->ajax()){
         return response()->json(['vista'=>$vista]);
        }
    }


    public function buscarclientenombre(Request $request,$id){
        $clientes = DB::tABLE('cliente')
        ->join('tipo_documento_identidad','tipo_documento_identidad.tdicod','cliente.tdicod')
        ->where('clinom','like','%'.$id.'%')
         ->orwhere('telefono','like','%'.$id.'%')
        ->get();
        $vista = view('empresas.clientes.listaclientes',compact('clientes'))->render();
        if($request->ajax()){
         return response()->json(['vista'=>$vista]);
        }
    }

}