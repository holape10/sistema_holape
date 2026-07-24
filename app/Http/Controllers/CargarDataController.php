<?php

namespace MasterSoft\Http\Controllers;

use Illuminate\Http\Request;
use MasterSoft\Http\Requests;
use MasterSoft\orden_pedido_cabecera;
use MasterSoft\orden_pedido_detalle;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Input;
use DB;
use Maatwebsite\Excel\Facades\Excel;


class CargarDataController extends Controller
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

    }

    public function CargarData(Request $request)
    {
        /** El método load permite cargar el archivo definido como primer parámetro */
        Excel::load( public_path().'\productos.xlsx', function ($reader) {
            /**
             * $reader->get() nos permite obtener todas las filas de nuestro archivo
             */
            foreach ($reader->get() as  $row) {
                $producto = [
                    //'orden_pedido' => $row['orden_pedido'],
                    'orden_pedido' => array_get($row, 'orden_pedido'),
                       'codigo' => array_get($row, 'codigo'),
                          'descripcion' => array_get($row, 'descripcion'),
                             'cantidad' => array_get($row, 'cantidad'),
                   // 'codigo' => $row['codigo'],
                   // 'descripcion' => $row['descripcion'],
                   // 'cantidad' => $row['cantidad'],
      
                ];
                /** Una vez obtenido los datos de la fila procedemos a registrarlos */
                if (!empty($producto)) {

                    if(!empty($producto['orden_pedido'])){
                        $verif_op = orden_pedido_cabecera::where('orden_pedido','=',$producto['orden_pedido'])->first();
                        
                        if ($verif_op === null) {

                            $or_ped_cab = new orden_pedido_cabecera;
                            $or_ped_cab->orden_pedido = $producto['orden_pedido'];
                            $or_ped_cab->save();

                            $or_ped_det = new orden_pedido_detalle;
                            $or_ped_det->IdOP= $or_ped_cab->IdOP;
                            $or_ped_det->codigo= $producto['codigo'];
                            $or_ped_det->descripcion= $producto['descripcion'];
                            $or_ped_det->cantidad= $producto['cantidad'];
                            $or_ped_det->save();


                        }else{

                           
                            $verif_op_det = orden_pedido_detalle::where('IdOP','=',$verif_op->IdOP)->where('codigo','=',$producto['codigo'])->where('descripcion','=',$producto['descripcion'])->where('cantidad','=',$producto['cantidad'])->first();

                            if ($verif_op_det === null) {
                                $or_ped_det = new orden_pedido_detalle;
                                $or_ped_det->IdOP= $verif_op->IdOP;
                                $or_ped_det->codigo= $producto['codigo'];
                                $or_ped_det->descripcion= $producto['descripcion'];
                                $or_ped_det->cantidad= $producto['cantidad'];
                                $or_ped_det->save();
                            }
                        }
                    
                    }
                
                }
            }
            echo 'Los productos han sido importados exitosamente';
        });

    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
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
        //
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
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
