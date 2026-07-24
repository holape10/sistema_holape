<?php

namespace MasterSoft\Http\Controllers;

use Illuminate\Http\Request;
use MasterSoft\Http\Requests;
use MasterSoft\areas;
use Illuminate\Support\Facades\Auth;
use MasterSoft\Http\Requests\FacturaCreateFormRequest;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Input;
use DB;

class AreasController extends Controller
{
    
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        
      if($request){
        $buscar = $request->get('buscar');

        if(empty($buscar)){
            $areas = DB::tABLE('areas')->paginate(10);
        }else{  
             $areas = DB::tABLE('areas')->where('are_emp_des',$buscar)->paginate(10);
        }
        return view('empresas.areas.index',compact('areas','buscar'));
        }
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {

        return view('empresas.areas.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $areas = new areas;
        $areas->are_emp_cod = $request->get('are_emp_cod');
        $areas->are_emp_des = $request->get('are_emp_des');
        $areas->save();

        return Redirect::to('/areas');
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
      $areas=areas::findOrFail($id);
      return view('empresas.areas.edit',compact('areas'));
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
      $areas=areas::findOrFail($id);
      $areas->are_emp_cod = $request->get('are_emp_cod');
      $areas->are_emp_des = $request->get('are_emp_des');
      $areas->update();
      return Redirect::to('/areas');

    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
      $areas= areas::findOrFail($id);
      $areas->delete();

      return Redirect::to('/areas');
    }
}
