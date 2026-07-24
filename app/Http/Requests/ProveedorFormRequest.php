<?php

namespace MasterSoft\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Auth;


class ProveedorFormRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
   public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        
        return [
            
            'txtProvRuc'=>['required','max:20',Rule::unique('proveedor','prov_id')->where(function ($query) {
                            return $query->where('IdEmpresa','=',Auth::user()->IdEmpresa);}),],

            'txtProvRaz'=>['required','max:250',Rule::unique('proveedor','prov_raz')->where(function ($query) {
                            return $query->where('IdEmpresa','=',Auth::user()->IdEmpresa);}),],
            
        ];
    }

    public function messages()
    {
        return [
           
            'txtProvRuc.unique'=>'El código del laboratorio ya está registrado',
            'txtProvRaz.unique'=>'El nombre del laboratorio ya está registrado',
       
 
        ];
    }
}
