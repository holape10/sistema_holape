<?php

namespace MasterSoft\Http\Controllers;

use Illuminate\Http\Request;

class SoporteController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth'); 
    }

    public function index()
    {
        return view('empresas.soporte.index');
    }

    public function store(Request $request)
    {
        // Validar los datos del formulario
        $this->validate($request, [
            'nombre' => 'required|string|max:100',
            'email' => 'required|email|max:100',
            'telefono' => 'required|string|max:20',
            'asunto' => 'required|string|max:150',
            'mensaje' => 'required|string|max:2000',
        ], [
            'nombre.required' => 'El nombre es requerido',
            'nombre.max' => 'El nombre no debe exceder 100 caracteres',
            'email.required' => 'El correo electrónico es requerido',
            'email.email' => 'El correo electrónico no es válido',
            'email.max' => 'El correo no debe exceder 100 caracteres',
            'telefono.required' => 'El teléfono es requerido',
            'telefono.max' => 'El teléfono no debe exceder 20 caracteres',
            'asunto.required' => 'El asunto es requerido',
            'asunto.max' => 'El asunto no debe exceder 150 caracteres',
            'mensaje.required' => 'El mensaje es requerido',
            'mensaje.max' => 'El mensaje no debe exceder 2000 caracteres',
        ]);

        try {
            // Preparar los datos del mensaje
            $data = [
                'nombre' => $request->input('nombre'),
                'email' => $request->input('email'),
                'telefono' => $request->input('telefono'),
                'asunto' => $request->input('asunto'),
                'mensaje' => $request->input('mensaje'),
                'fecha' => date('d/m/Y H:i'),
            ];

            // 1. Enviar email al equipo de soporte
            $this->enviarEmailEquipo($data);

            // 2. Enviar email de confirmación al usuario
            $this->enviarEmailConfirmacion($data);

            // 3. Enviar WhatsApp por API (sin SDK)
            $this->enviarWhatsAppAPI($data);

            return response()->json([
                'success' => true,
                'message' => '¡Mensaje enviado correctamente! Nos pondremos en contacto pronto por email y WhatsApp.'
            ], 200);

        } catch (\Exception $e) {
            \Log::error('Error en SoporteController@store: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al enviar el mensaje: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Enviar email al equipo de soporte
     */
    private function enviarEmailEquipo($data)
    {
        try {
            \Mail::send('empresas.soporte.contacto', $data, function ($message) use ($data) {
                $message->from(env('MAIL_FROM_ADDRESS', 'noreply@example.com'), $data['nombre']);
                $message->to('holapesac@gmail.com')
                        ->subject('Nuevo Mensaje de Soporte: ' . $data['asunto']);
            });

            \Log::info('Email al equipo enviado exitosamente');

        } catch (\Exception $e) {
            \Log::error('Error enviando email al equipo: ' . $e->getMessage());
        }
    }

    /**
     * Enviar email de confirmación al usuario
     */
    private function enviarEmailConfirmacion($data)
    {
        try {
            \Mail::send('empresas.soporte.confirmacion', $data, function ($message) use ($data) {
                $message->from(env('MAIL_FROM_ADDRESS', 'noreply@example.com'), 'DEVSOFT by HolaPe')
                        ->to($data['email'])
                        ->subject('Hemos recibido tu mensaje de soporte');
            });

            \Log::info('Email de confirmación enviado exitosamente a: ' . $data['email']);

        } catch (\Exception $e) {
            \Log::error('Error enviando email de confirmación: ' . $e->getMessage());
        }
    }

    /**
     * Enviar WhatsApp usando API HTTP sin SDK
     * Usa cURL en lugar de Twilio SDK
     */
    private function enviarWhatsAppAPI($data)
    {
        try {
            // Obtener credenciales del .env
            $account_sid = env('TWILIO_ACCOUNT_SID');
            $auth_token = env('TWILIO_AUTH_TOKEN');
            $whatsapp_from = env('TWILIO_WHATSAPP_FROM');
            $whatsapp_to = env('TWILIO_WHATSAPP_TO', '+51928396147');

            // Si no hay configuración, omitir
            if (!$account_sid || !$auth_token || !$whatsapp_from) {
                \Log::info('Twilio no configurado, saltando WhatsApp');
                return;
            }

            // Preparar el mensaje
            $mensaje = "*🔔 Nuevo Mensaje de Soporte*\n\n";
            $mensaje .= "👤 *Nombre:* " . $data['nombre'] . "\n";
            $mensaje .= "📧 *Email:* " . $data['email'] . "\n";
            $mensaje .= "📱 *Teléfono:* " . $data['telefono'] . "\n";
            $mensaje .= "🎯 *Asunto:* " . $data['asunto'] . "\n";
            $mensaje .= "📝 *Mensaje:*\n" . $data['mensaje'] . "\n";
            $mensaje .= "⏰ *Fecha:* " . $data['fecha'];

            // URL de Twilio API
            $url = "https://api.twilio.com/2010-04-01/Accounts/{$account_sid}/Messages.json";

            // Preparar datos para enviar
            $post_data = [
                'From' => $whatsapp_from,
                'To' => 'whatsapp:' . $whatsapp_to,
                'Body' => $mensaje,
            ];

            // Crear contexto cURL
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
            curl_setopt($ch, CURLOPT_USERPWD, $account_sid . ':' . $auth_token);
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($post_data));
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
            curl_setopt($ch, CURLOPT_TIMEOUT, 30);

            // Enviar solicitud
            $response = curl_exec($ch);
            $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);

            curl_close($ch);

            // Verificar respuesta
            if ($http_code == 201) {
                $response_data = json_decode($response, true);
                \Log::info('WhatsApp enviado exitosamente. SID: ' . $response_data['sid']);
            } else {
                \Log::error('Error enviando WhatsApp. HTTP Code: ' . $http_code . ' Response: ' . $response);
            }

        } catch (\Exception $e) {
            \Log::error('Error enviando WhatsApp: ' . $e->getMessage());
            // No lanzamos excepción para que el email se envíe igual
        }
    }
}