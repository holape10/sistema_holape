<?php

namespace MasterSoft\Http\Controllers;

use Illuminate\Http\Request;
use MasterSoft\Http\Requests;
use MasterSoft\TipoIGV;
use MasterSoft\Empresa;
use MasterSoft\Cliente;
use MasterSoft\usuario_modificar;
use MasterSoft\usuario_eliminar;
use MasterSoft\User;
use Illuminate\Support\Facades\Auth;
use MasterSoft\Http\Requests\FacturaCreateFormRequest;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Input;
use DB;
use Excel;

class AuditoriasController extends Controller
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
	
    public function index(){

        return view('empresas.auditoria.index');

    }


    public function ReporteAuditoria(Request $request)
    {

    
        $razsoc = $request->get('searchText');
        $respse = $request->get('tiper');
        $tipdoc = $request->get('docomp');
        $fecin = $request->get('fecin');
        $fecfin = $request->get('fecfin');
        $empresa = Empresa::findOrFail(Auth::user()->IdEmpresa);
        $IdEmpresa = Auth::user()->IdEmpresa;
		
        $nomempresa = Empresa::FindOrFail($IdEmpresa);
        $datoempresa = $nomempresa->NomEmpresa;
       
		switch ($tipdoc){
		  case 1:

	               $modificaciones = usuario_modificar::select('usuario_modificar.placa as Placa','usuario_modificar.placamod as Placa_Modificacion','tv.descripcion as Vehiculo','tvm.descripcion as Vehiculo_Modificacion','ta.descripcion as Tarifa','tam.descripcion as Tarifa_Modificacion','ut.nom_uni_tie as Tarifa_Por','tiempo as Tiempo_momento_modificacion','utm.nom_uni_tie as Tarifa_Por_Modificacion','dni as DNI','dnimod as DNI_Modificacion','usuario_modificar.nombre as Nombre','usuario_modificar.nombremod as Nombre_Modificacion','usuario_modificar.descripcion as Detalle','usuario_modificar.descripcionmod as Detalle_modificacion')
                    ->join('tarifas as ta','ta.id_tarifa','usuario_modificar.id_tarifa')
                    ->join('unidad_tiempo as ut','ut.id_uni_tie','ta.id_uni_tie')
                    ->join('tipos_vehiculos as tv','tv.id_tipo_vehiculo','usuario_modificar.id_tipo_vehiculo')
                    ->join('users as u','u.IdUsuario','usuario_modificar.id_usu_mod')
                    ->join('tarifas as tam','tam.id_tarifa','usuario_modificar.id_tarifamod')
                    ->join('unidad_tiempo as utm','utm.id_uni_tie','tam.id_uni_tie')
                    ->join('tipos_vehiculos as tvm','tvm.id_tipo_vehiculo','usuario_modificar.id_tipo_vehiculomod')
                    ->leftjoin('users as um','um.IdUsuario','usuario_modificar.id_usu_aut')
                    ->where('usuario_modificar.id_empresa_negocio',Auth::user()->id_empresa_negocio)
                    ->where('fecha_modificacion','>=',$fecin)
                    ->where('fecha_modificacion','<=',$fecfin)
                    ->get();

                    $eliminaciones = usuario_eliminar::select('pe.placa as Placa','tv.descripcion as Vehiculo','ta.descripcion as Tarifa','ut.nom_uni_tie as Tarifa_Por','cantidad as Tiempo','dni as DNI','pe.nombre as Nombre','pe.descripcion as Detalle')
                    ->join('pedidos as pe','pe.ped_id','pe.ped_id')
                    ->join('pedidos_detalle as pd','pd.ped_id','pe.ped_id')
                    ->join('tarifas as ta','ta.id_tarifa','pe.tarifa')
                    ->join('unidad_tiempo as ut','ut.id_uni_tie','ta.id_uni_tie')
                    ->join('tipos_vehiculos as tv','tv.id_tipo_vehiculo','pe.tipovehiculo')
                    ->join('users as u','u.IdUsuario','usuario_eliminar.id_usu_elim')
                    ->leftjoin('users as um','um.IdUsuario','usuario_eliminar.id_usu_aut')
                    ->where('pe.id_empresa_negocio',Auth::user()->id_empresa_negocio)
                    ->where('fecha_eliminar','>=',$fecin)
                    ->where('fecha_eliminar','<=',$fecfin)
                    ->get();
                
        
                    Excel::create('AUDITORIA MOD_ELIM', function($excel) use($eliminaciones,$datoempresa,$modificaciones,$fecin,$fecfin) {

                    $excel->sheet('MODIFACIONES', function($sheet2) use($datoempresa,$modificaciones,$fecin,$fecfin) {
        

                    $sheet2->fromArray($modificaciones);
                    $sheet2->setColumnFormat(array(
                        'G' => '0.00'
                    ));

                   
                    $sheet2->prependRow(1, array(
                            $datoempresa.' '.'MODIFICACIONES DESDE  '.$fecin.'  AL  '.$fecfin
                    ));
                        
                    $sheet2->mergeCells('A1:O1');
                    $sheet2->setAllBorders('thin');
                    $sheet2->cells('A1:O1', function($cell) {
                            $cell->setAlignment('center');
                       
                    });


                   $sheet2->setColumnFormat(array(
                        'G' => '0.00'
                    ));

                    });



                    $excel->sheet('ELIMINACIONES', function($sheet) use($datoempresa,$eliminaciones,$fecin,$fecfin) {
        

                    $sheet->fromArray($eliminaciones);
                    $sheet->setColumnFormat(array(
                        'G' => '0.00'
                    ));

                   
                    $sheet->prependRow(1, array(
                            $datoempresa.' '.'ELIMINACIONES DESDE  '.$fecin.'  AL  '.$fecfin
                    ));
                        
                    $sheet->mergeCells('A1:H1');
                    $sheet->setAllBorders('thin');
                    $sheet->cells('A1:H1', function($cell) {
                            $cell->setAlignment('center');
                       
                    });


                   $sheet->setColumnFormat(array(
                        'G' => '0.00'
                    ));

                    });

        })->export('xlsx');
        
        break;

       
			
	}
			
    }

}
