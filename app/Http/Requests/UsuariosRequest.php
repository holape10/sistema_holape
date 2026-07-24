<?php

namespace MasterSoft\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;


class UsuariosRequest extends FormRequest
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
            'name' => 'required|string|max:255',
          //  'email' => 'required|string|email|max:255',
            'password' => 'required|string|min:6|confirmed',
          //  'idIngreso'=>'unique:users',
        ];
    }

    public function messages()
    {
        return [
            'name.required' => 'El nombre es campo obligatorio',
            'name.string' => 'El nombre debe contener solo letras',
            'name.max' => 'El nombre debe tener como máximo 255 caracteres',
        //    'email.required' =>'El correo es un campo obligatorio'
            'password.required' => 'La contraseña es obligatoria',
            'password.confirmed' => 'Confirmar la contraseña',
            'password.min' => 'Contraseña debe tener mínimo 6 caracteres',
         //   'idIngreso.unique' => 'El usuario ya fue asignado a una empresa',
        ];
    }

   
}
