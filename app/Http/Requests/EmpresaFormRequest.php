<?php

namespace MasterSoft\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;


class EmpresaFormRequest extends FormRequest
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
            'rucEmpresa'=>'required|numeric|digits:11|unique:empresa,IdEmpresa',
            'nomEmpresa'=>'required|max:100',
            'logEmpresa'=>'mimes:jpeg,png',
            'fseempresa'=>'required|alpha_num|min:4|max:4',
            'fnuempresa'=>'required|numeric|digits_between:1,8',
            'bseempresa'=>'required|alpha_num|min:4|max:4',
            'bnuempresa'=>'required|numeric|digits_between:1,8',
            'fcseempresa'=>'required|alpha_num|min:4|max:4',
            'fdseempresa'=>'required|alpha_num|min:4|max:4',
            'bcseempresa'=>'required|alpha_num|min:4|max:4',
            'bdseempresa'=>'required|alpha_num|min:4|max:4',
            'fdnuempresa'=>'required|numeric|digits_between:1,8',
            'fcnuempresa'=>'required|numeric|digits_between:1,8',
            'bdnuempresa'=>'required|numeric|digits_between:1,8',
            'bcnuempresa'=>'required|numeric|digits_between:1,8',
            'txtWsUrl'=>'required|max:255',
            'txtWsUsuario'=>'required|max:255',
            'txtWsContrasena'=>'required|max:255',
            'corEmpresa'=>'nullable|email',
            'telEmpresa'=>'max:20',
            'dirEmpresa'=>'max:250'
            
        ];
    }

    public function messages()
    {
        return [
            'rucEmpresa.unique'=>'El RUC ya se encuentra registrado',
             'rucEmpresa.required' => 'RUC es un campo obligatorio.',
            'nomEmpresa.required' => 'Razón Social es un campo obligatorio.',
            'rucEmpresa.digits' => 'RUC debe tener 11 caracteres numéricos.',
            'rucEmpresa.numeric'=>'RUC debe ser numérico.',

            'fseempresa.required'=>'Serie de factura es obligatorio',
            'fseempresa.alpha_num'=>'Serie de factura es alfanumérico',
            'fseempresa.max'=>'Serie de factura es de 4 digitos',
            'fseempresa.min'=>'Serie de factura es de 4 digitos',
            'fnuempresa.required'=>'Número de factura es obligatorio',
            'fnuempresa.numeric'=>'Número de factura es numérico',
            'fnuempresa.digits'=>'Número de factura debe tener entre 1 y 8 digitos',

            'bseempresa.required'=>'Serie de factura es obligatorio',
            'bseempresa.alpha_num'=>'Serie de factura es alfanumérico',
            'bseempresa.max'=>'Serie de factura es de 4 digitos',
            'bseempresa.min'=>'Serie de factura es de 4 digitos',
            'bnuempresa.required'=>'Número de boleta es obligatorio',
            'bnuempresa.numeric'=>'Número de boleta es numérico',
            'bnuempresa.digits'=>'Número de boleta debe tener entre 1 y 8 digitos',

            'fcseempresa.required'=>'Serie de nota de crédito es obligatorio',
            'fcseempresa.alpha_num'=>'Serie de nota de crédito es alfanumérico',
            'fcseempresa.max'=>'Serie de nota de crédito es de 4 digitos',
            'fcseempresa.min'=>'Serie de nota de crédito es de 4 digitos',
            'fdseempresa.required'=>'Serie de nota de débito es obligatorio',
            'fdseempresa.alpha_num'=>'Serie de nota de débito es alfanumérico',
            'fdseempresa.max'=>'Serie de nota de débito es de 4 digitos',
            'fdseempresa.min'=>'Serie de nota de débito es de 4 digitos',

            'bcseempresa.required'=>'Serie de nota de crédito es obligatorio',
            'bcseempresa.alpha_num'=>'Serie de nota de crédito es alfanumérico',
            'bcseempresa.max'=>'Serie de nota de crédito es de 4 digitos',
            'bcseempresa.min'=>'Serie de nota de crédito es de 4 digitos',
            'bdseempresa.required'=>'Serie de nota de débito es obligatorio',
            'bdseempresa.alpha_num'=>'Serie de nota de débito es alfanumérico',
            'bdseempresa.max'=>'Serie de nota de débito es de 4 digitos',
            'bdseempresa.min'=>'Serie de nota de débito es de 4 digitos',

            'fcnuempresa.required'=>'Número de nota de crédito es obligatorio',
            'fcnuempresa.numeric'=>'Número de nota de crédito es numérico',
            'fcnuempresa.digits'=>'Número de nota de crédito debe tener entre 1 y 8 digitos',
            'fdnuempresa.required'=>'Número de nota de débito es obligatorio',
            'fdnuempresa.numeric'=>'Número de nota de débito es numérico',
            'fdnuempresa.digits'=>'Número de nota de débito debe tener entre 1 y 8 digitos',
            'bcnuempresa.required'=>'Número de nota de crédito es obligatorio',
            'bcnuempresa.numeric'=>'Número de nota de crédito es numérico',
            'bcnuempresa.digits'=>'Número de nota de crédito debe tener entre 1 y 8 digitos',
            'bdnuempresa.required'=>'Número de nota de débito es obligatorio',
            'bdnuempresa.numeric'=>'Número de nota de débito es numérico',
            'bdnuempresa.digits'=>'Número de nota de débito debe tener entre 1 y 8 digitos',

            'txtWsUrl.required'=>'La url del webservice es obligatorio',
            'txtWsUrl.max'=>'Longitud máximo 255 caractéres',
            
            'txtWsUsuario.required'=>'El usuario del webservice es obligatorio',
            'txtWsUsuario.max'=>'Longitud máximo 255 caractéres',
            
            'txtWsContrasena.required'=>'La contraseña del webservice es obligatorio',
            'txtWsContrasena.max'=>'Longitud máximo 255 caractéres',

            'corEmpresa.email'=>'Ingresar un correo válido',
            'telEmpresa.max'=>'El teléfono puede tener máximo 20 digitos',
            'dirEmpresa.max'=>'La dirección puede tener máximo 250 caracteres'

        ];
    }
}
