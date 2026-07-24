<?php

namespace MasterSoft\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Auth;


class ProveedorUpdateFormRequest extends FormRequest
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
            
           
           'txtProvRuc'=>['required','max:11',Rule::unique('proveedor','prov_id')->where(function ($query) {
                            return $query->where('IdEmpresa','=',Auth::user()->IdEmpresa)->where('prov_id','<>',$this->route('proveedor'));}),],
            
        ];
    }

    public function messages()
    {
        return [
           
            'txtProvRuc.unique'=>'El código del laboratorio ya está registrado',
       
 
        ];
    }
}
