<?php

namespace MasterSoft\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Auth;

class ClienteFormRequest extends FormRequest
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
            'clinum'=> ['required','numeric','digits_between:8,11',Rule::unique('cliente','clinum')->where(function ($query) {
                            return $query->where('rucemp','=',Auth::user()->IdEmpresa);}),],
            'clinom'=>'required|max:250',
            'clidir'=>'required|max:250',
            'clicor'=>'nullable|email|max:250',
            'tdicod'=>'required',
            
        ];
    }

    public function messages()  
    {
        return [
            'clinum.required' => 'Documento de Identidad es obligatorio.',
            'clinum.numeric' => '´Documento de Identidad debe ser númerico',
            'clinum.digits_between' => 'Documento de Identidad debe tener entre 8 y 11 dígitos',
            'clinum.unique' => 'Documento de Identidad ya está registrado',

            'clinom.required' => 'Nombre del cliente es obligatorio.',
            'clinom.max'=>'Nombre del cliente no puede tener más de 250 caracteres',

            'clidir.required' => 'Dirección del cliente es obligatorio',
            'clidir.max'=>'Dirección no puede tener más de 250 caracteres',

            'clicor.email' =>'Correo Electrónico no es válido',
            'clicor.max'=>'Correo eléctrico no puede tener más de 250 caracteres',

            'tdicod.required' => 'Tipo de documento es obligatorio',

        ];
    }
}
