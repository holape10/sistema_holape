<?php

namespace MasterSoft\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class FacturaCreateFormRequest extends FormRequest
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
           'tdicod' => 'required',
           'clinum' => 'required|alpha_num|digits_between:0,15',
           'clinom' => 'required',
           'serdoc' => 'required',
           'numdoc' => 'required|numeric',
           'camdoc' => 'required_if:mondoc,USD|numeric',
           'obser' => 'max:250',
           'topcod' => 'required',
           'mondoc' => 'required',
           'fecEmi' => 'required|date',
           'fecVen' => 'date',
           'codpro' => 'required',
           'detpro'=>'required',
           'codunique'=>'unique:cpe_cabecera',

        ];  
    }

    public function messages()
    {
        return [
           'tdicod.required' => 'Elegir el tipo de documento de identidad.',
           'clinum.required' => 'El número de documento de identidad es obligatorio.',
           'clinum.alpha_num' => 'Ingresar un número de documento válido.',
           'clinum.digits' => 'El número de documento de identidad puede tener máximo 15 caracteres.',
           'clinom.required' => 'El nombre del cliente es obligatorio.',
           'serdoc.required' => 'Ingresar el número de serie.',
           'numdoc.required' => 'El número de documento es obligatorio.',
           'topcod.required' => 'Elegir el tipo de operación.',
           'mondoc.required' => 'Elegir el tipo de moneda.',
           'camdoc.required' => 'El tipo de cambio es obligatorio.',
           'fecEmi.required' => 'La Fecha de Emisión es obligatorio.',
           'fecVen.required' => 'La Fecha de Vencimiento es obligatorio.',
           'fecEmi.required' => 'Ingresar una fecha válida.',
           'fecVen.date' => 'Ingresar una fecha válida.',
           'camdoc.numeric' => 'El tipo de cambio es numérico.',
           'numdoc.numeric' => 'Ingresar un número de documento válido.',
           'obser.max' => 'Ingresar máximo 250 caracteres',
           //'descpro.required' =>'No hay productos o servicios para facturar',
           'codunique.unique' =>'El Documento ya fue registrado',
           'codpro.required'=>'El codigo del producto es obligatorio',
           'detpro.required'=>'El detalle del producto es obligatorio',
           
       
        ];
    }
}
