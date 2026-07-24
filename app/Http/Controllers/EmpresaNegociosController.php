<?php

namespace MasterSoft\Http\Controllers;


use Illuminate\Http\Request;
use MasterSoft\Http\Requests;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Input;
use MasterSoft\EmpresaNegocios;
use MasterSoft\Empresa;
use MasterSoft\Almacenes;
use DB;

class EmpresaNegociosController extends Controller
{

      public function __construct()
    {
        $this->middleware('auth');
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
         if($request){

            $rucemp = trim(Auth::user()->IdEmpresa);

            $buscar = trim($request->get('buscar'));

            if(empty($buscar)){
                $negocios= EmpresaNegocios::
                where('IdEmpresa','=',$rucemp)
                ->orderby('tipo_negocio','asc')
                ->paginate(7);

            } else{

                $negocios= EmpresaNegocios::
                where('IdEmpresa','=',$rucemp)
                ->where('tipo_negocio','like','%'.$buscar.'%')
                ->orderby('tipo_negocio','asc')
                ->paginate(7);
               
            }


            return view('empresas.negocios.index',compact('negocios','buscar'));
        }
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $formatos = DB::tABLE('formatos_comprobantes')->get();

        $tipo_igv = DB::tABLE('tipo_igv')->get();

        $documentos = DB::tABLE('tipo_documento')->where('caja','1')->get();


        return view('empresas.negocios.create',compact('formatos','tipo_igv','documentos'));
    }
    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {

        $productos = DB::tABLE('productos')->get();

        $negocios = new EmpresaNegocios;
        $negocios->tipo_negocio = $request->get('txt_tipo_negocio');
        $negocios->cod_suc = $request->get('cod_suc');
        $negocios->codigofiscal = $request->get('cod_fis_sun');
        $negocios->tdocod_pred = $request->get('tdocod_pred');
        $negocios->ticket_pantalla = $request->get('ticket_pantalla');
        $negocios->formato = $request->get('formato');
        $negocios->nombre_comercial = $request->get('txt_nombre_comercial');
        $negocios->estado = 'Activo';
        $negocios->tip_igv_pred = $request->get('tipo_igv');
        $negocios->direccion = $request->get('txt_direccion');
        $negocios->descripcion1 = $request->get('descripcion1');
        $negocios->descripcion2 = $request->get('descripcion2');
        $negocios->serv_selv = $request->get('serv_selv');

        $negocios->boton_precuenta = $request->get('boton_precuenta');
        $negocios->boton_cobrar = $request->get('boton_cobrar');
        $negocios->boton_descuento = $request->get('boton_descuento');
        $negocios->boton_recargo = $request->get('boton_recargo');
        $negocios->boton_imagenes = $request->get('boton_imagenes');

        $negocios->telefono = $request->get('txt_telefono');
        $negocios->correo = $request->get('txt_correo');
        $negocios->web = $request->get('txt_web');
        $negocios->tip_conex_imp = $request->get('tip_conex_imp');
        $negocios->cod_for_com = $request->get('cod_for_com');
        $negocios->cab_comp = $request->get('cab_comp');
        $negocios->pie_comp = $request->get('pie_comp');
        $negocios->comision = $request->get('comision');

        if(Input::hasFile('logosuc')){
            $file=Input::file('logosuc');
            $file->move(public_path().'/',$file->getClientOriginalName());
            $negocios->logosuc=$file->getClientOriginalName();
        }


        $negocios->IdEmpresa = Auth::user()->IdEmpresa;
        
         //serie - numero factura
        $negocios->FseEmpresa = $request->get('FseEmpresa');
        $negocios->FnuEmpresa = $request->get('FnuEmpresa');

        //serie - numero boleta
        $negocios->BseEmpresa = $request->get('BseEmpresa');
        $negocios->BnuEmpresa = $request->get('BnuEmpresa');

        //serie - numero proforma
        $negocios->ProSer = $request->get('ProSer');
        $negocios->ProNum = $request->get('ProNum');

        //serie - numero nota de venta
        $negocios->SerNota = $request->get('sernota');
        $negocios->NumNota = $request->get('numnota');

        //SERIE NOTA DE CREDITO FACTURA
        $negocios->FcseEmpresa = $request->get('FcseEmpresa');
        $negocios->FcnuEmpresa = $request->get('FcnuEmpresa');

        //SERIE NOTA DE debito FACTURA
        $negocios->FdseEmpresa = $request->get('FdseEmpresa');
        $negocios->FdnuEmpresa = $request->get('FdnuEmpresa');

         //SERIE NOTA DE CREDITO BOLETA
        $negocios->BcseEmpresa = $request->get('BcseEmpresa');
        $negocios->BcnuEmpresa = $request->get('BcnuEmpresa');

        //SERIE NOTA DE debito BOLETA
        $negocios->BdseEmpresa = $request->get('BdseEmpresa');
        $negocios->BdnuEmpresa = $request->get('BdnuEmpresa');

        //GUIA REMISION
        $negocios->serieguia = $request->get('serieguia');
        $negocios->numeroguia = $request->get('numeroguia');

        //PEDIDOS
        $negocios->serieNP = $request->get('SerPed');
        $negocios->numNP = $request->get('NumPed');

        $negocios->ven_sin_sto = $request->get('ven_sin_sto');
        $negocios->save();


        $almacen = new Almacenes;
        $almacen->descripcion = 'TIENDA';
        $almacen->predeterminado ='1';
        $almacen->id_empresa_negocio = $negocios->id_empresa_negocio;
        $almacen->save();

         foreach ($productos as $pro) {
            

            $buspro = DB::tABLE('producto_empresa')
            ->where('id_empresa_negocio',$negocios->id_empresa_negocio)
            ->where('IdProducto',$pro->IdProducto)
            ->first();

       
            if(empty($bus_pro)){

               DB::tABLE('producto_empresa')->insert(['IdProducto'=>$pro->IdProducto,'id_empresa_negocio'=>$negocios->id_empresa_negocio]); 
            }

           
        }

            foreach ($productos as $pro) {
              
                $buspro = DB::tABLE('producto_stock')
                ->where('id_empresa_negocio',$negocios->id_empresa_negocio)
                ->where('id_almacen',$almacen->id_almacen)
                ->where('IdProducto',$pro->IdProducto)
                ->first();

                if(empty($bus_pro)){

                   DB::tABLE('producto_stock')->insert(['IdProducto'=>$pro->IdProducto,'id_empresa_negocio'=>$negocios->id_empresa_negocio,'id_almacen'=>$almacen->id_almacen]); 
                } 
              }


        $empresa = Empresa::findOrFail(Auth::user()->IdEmpresa);

        $bus_pro_icbper = DB::tABLE('productos')->where('icbper','1')->where('IdEmpresa',Auth::user()->IdEmpresa)->update(['mon_icbper'=>$empresa->icbper]);


        return Redirect::to('/negocios');
    }

    /**
     * Display the specified resource.
     *
     * @param  \MasterSoft\EmpresaNegocios  $empresaNegocios
     * @return \Illuminate\Http\Response
     */
    public function show()
    {
        return Redirect::to('/negocios');
    }

    public function buscarsucursales(Request $request,$empresa){

        $negocios = DB::tABLE('empresa_negocios')->where('IdEmpresa',$empresa)->get();

        $vista = view('administrador.usuarios.divsucursales',compact('negocios'))->render();

        if($request->ajax()){
         return response()->json(['vista'=>$vista]);

        }

    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \MasterSoft\EmpresaNegocios  $empresaNegocios
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $negocios = EmpresaNegocios::FindOrFail($id);

        $formatos = DB::tABLE('formatos_comprobantes')->get();

        $tipo_igv = DB::tABLE('tipo_igv')->get();

        $documentos = DB::tABLE('tipo_documento')->where('caja','1')->get();

        return view('empresas.negocios.edit',compact('negocios','formatos','tipo_igv','documentos'));

    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \MasterSoft\EmpresaNegocios  $empresaNegocios
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $negocios = EmpresaNegocios::FindOrFail($id);
        $negocios->tipo_negocio = $request->get('txt_tipo_negocio');
        $negocios->nombre_comercial = $request->get('txt_nombre_comercial');
        $negocios->cod_suc = $request->get('cod_suc');
        $negocios->codigofiscal = $request->get('cod_fis_sun');
        $negocios->tdocod_pred = $request->get('tdocod_pred');
        $negocios->estado = 'Activo';
        $negocios->ticket_pantalla = $request->get('ticket_pantalla');
        $negocios->formato = $request->get('formato');
        $negocios->direccion = $request->get('txt_direccion');
        $negocios->descripcion1 = $request->get('descripcion1');
        $negocios->descripcion2 = $request->get('descripcion2');
        $negocios->descripcion2 = $request->get('descripcion2');
        $negocios->serv_selv = $request->get('serv_selv');

        $negocios->boton_precuenta = $request->get('boton_precuenta');
        $negocios->boton_cobrar = $request->get('boton_cobrar');
        $negocios->boton_descuento = $request->get('boton_descuento');
        $negocios->boton_recargo = $request->get('boton_recargo');
        $negocios->boton_imagenes = $request->get('boton_imagenes');
        
        $negocios->telefono = $request->get('txt_telefono');
        $negocios->correo = $request->get('txt_correo');
        $negocios->web = $request->get('txt_web');
        $negocios->tip_conex_imp = $request->get('tip_conex_imp');
        $negocios->tip_igv_pred = $request->get('tipo_igv');
        $negocios->cod_for_com = $request->get('cod_for_com');
        $negocios->IdEmpresa = Auth::user()->IdEmpresa;
        $negocios->cab_comp = $request->get('cab_comp');
        $negocios->pie_comp = $request->get('pie_comp');
        $negocios->comision = $request->get('comision');


          if(Input::hasFile('logosuc')){
            $file=Input::file('logosuc');
            $file->move(public_path().'/',$file->getClientOriginalName());
            $negocios->logosuc=$file->getClientOriginalName();
        }


         
         //serie - numero factura
        $negocios->FseEmpresa = $request->get('FseEmpresa');
        $negocios->FnuEmpresa = $request->get('FnuEmpresa');

        //serie - numero boleta
        $negocios->BseEmpresa = $request->get('BseEmpresa');
        $negocios->BnuEmpresa = $request->get('BnuEmpresa');

        //serie - numero proforma
        $negocios->ProSer = $request->get('ProSer');
        $negocios->ProNum = $request->get('ProNum');

        //serie - numero nota de venta
        $negocios->SerNota = $request->get('sernota');
        $negocios->NumNota = $request->get('numnota');

        //SERIE NOTA DE CREDITO FACTURA
        $negocios->FcseEmpresa = $request->get('FcseEmpresa');
        $negocios->FcnuEmpresa = $request->get('FcnuEmpresa');

        //SERIE NOTA DE debito FACTURA
        $negocios->FdseEmpresa = $request->get('FdseEmpresa');
        $negocios->FdnuEmpresa = $request->get('FdnuEmpresa');

         //SERIE NOTA DE CREDITO BOLETA
        $negocios->BcseEmpresa = $request->get('BcseEmpresa');
        $negocios->BcnuEmpresa = $request->get('BcnuEmpresa');

        //SERIE NOTA DE debito BOLETA
        $negocios->BdseEmpresa = $request->get('BdseEmpresa');
        $negocios->BdnuEmpresa = $request->get('BdnuEmpresa');

        //GUIA REMISION
        $negocios->serieguia = $request->get('serieguia');
        $negocios->numeroguia = $request->get('numeroguia');

        //PEDIDOS
        $negocios->serieNP = $request->get('SerPed');
        $negocios->numNP = $request->get('NumPed');

        $negocios->ven_sin_sto = $request->get('ven_sin_sto');
        
        $negocios->update();

        $empresa = Empresa::findOrFail(Auth::user()->IdEmpresa);

        $bus_pro_icbper = DB::tABLE('productos')->where('icbper','1')->where('IdEmpresa',Auth::user()->IdEmpresa)->update(['mon_icbper'=>$empresa->icbper]);

        return Redirect::to('/negocios');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \MasterSoft\EmpresaNegocios  $empresaNegocios
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $negocios = EmpresaNegocios::FindOrFail($id);
        $negocios->estado = 'Eliminado';
        $negocios->update();

        return Redirect::to('/negocios');
    }
}
