<?php

namespace MasterSoft\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Auth;

class ProductoUpdateFormRequest extends FormRequest
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
            'txt_procod'=>['max:20',Rule::unique('productos','procod')->where(function ($query) {
                            return $query->where('IdEmpresa','=',Auth::user()->IdEmpresa)->where('procod','<>',$this->route('producto'));}),],
            'txt_pronom'=>'required|max:250',
            'txt_umecod'=>'required',
            'txt_moncod'=>'required',
            'txt_provun'=>'required|numeric',
            'txt_propun'=>'required|numeric',
            
        ];
    }

    public function messages()
    {
        return [
        
            'txt_procod.unique'=>'El código del producto ya está registrado',
            'txt_pronom.required'=>'Nombre del producto es obligatorio',
            'txt_procod.max'=>'Código de producto puede tener máximo 20 caracteres',
            'txt_pronom.max'=>'Nombre del producto puede tener máximo 250 caracteres',
            'txt_umecod.required'=>'Unidad de medida es obligatorio',
            'txt_moncod.required'=>'Tipo de moneda es obligatorio',
            'txt_provun.required'=>'Valor unitario es obligatorio',
            'txt_propun.required'=>'Precio unitario es obligatorio',
            'txt_provun.numeric'=>'Ingresar un monto valido',
            'txt_propun.numeric'=>'Ingresar un monto valido',
        ];
    }
}
