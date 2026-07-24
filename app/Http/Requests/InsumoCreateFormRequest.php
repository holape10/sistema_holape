<?php

namespace MasterSoft\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Auth;


class InsumoCreateFormRequest extends FormRequest
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
            
            'txt_procod'=>['required','max:20',Rule::unique('insumos','procod')->where(function ($query) {
                            return $query->where('IdEmpresa','=',Auth::user()->IdEmpresa);}),],
            'txt_pronom'=>'required|max:250',
            'txt_umecod'=>'required',
            'txt_moncod'=>'required',
           
         
            
        ];
    }

    public function messages()
    {
        return [
            'txt_procod.required'=>'Código de insumo es obligatorio',
            'txt_procod.unique'=>'El código del insumo ya está registrado',
            'txt_pronom.required'=>'Nombre del insumo es obligatorio',
            'txt_procod.max'=>'Código de insumo puede tener máximo 20 caracteres',
            'txt_pronom.max'=>'Nombre del insumo puede tener máximo 250 caracteres',
            'txt_umecod.required'=>'Unidad de medida es obligatorio',
            'txt_moncod.required'=>'Tipo de moneda es obligatorio',
            
          
        ];
    }
}
