<?php

namespace MasterSoft;

use Illuminate\Database\Eloquent\Model;

class Attendance extends Model
{
    // Definimos la tabla explícitamente
    protected $table = 'attendances';

    // Campos permitidos para asignación masiva
    protected $fillable = [
        'user_id', 
        'turno_id', 
        'check_in_1', 'check_out_1', 
        'check_in_2', 'check_out_2', 
        'tardanza_minutos', 
        'date', 
        'status',
        'autorizado_por',
        'motivo_tardanza'
    ];

 

    public static function getLeyendas() {
        return [
            '1'  => ['label' => 'Asistencia',       'texto' => '✔', 'bg' => '#28a745', 'color' => '#ffffff'],
            '0'  => ['label' => 'Falta',            'texto' => '0', 'bg' => '#dc3545', 'color' => '#ffffff'],
            'T'  => ['label' => 'Tardanza',         'texto' => 'T', 'bg' => '#DFE60E', 'color' => '#000000'],
            // AQUÍ ESTÁ EL CAMBIO DE COLORES:
            'D'  => ['label' => 'Descanso',         'texto' => 'D', 'bg' => '#ffc107', 'color' => '#000000'], // Ámbar
            'DM' => ['label' => 'Descanso Médico',  'texto' => 'DM','bg' => '#17a2b8', 'color' => '#ffffff'], // Celeste
            
            'J'  => ['label' => 'Justificacion',    'texto' => 'J', 'bg' => '#007bff', 'color' => '#ffffff'],
            'S'  => ['label' => 'Suspensión',       'texto' => 'S', 'bg' => '#343a40', 'color' => '#ffffff'],
            'V'  => ['label' => 'Vacaciones',       'texto' => 'V', 'bg' => '#20c997', 'color' => '#ffffff'],
            'F'  => ['label' => 'Feriados',         'texto' => 'F', 'bg' => '#e83e8c', 'color' => '#ffffff'],
        ];
    }

    /**
     * IMPORTANTE:
     * Laravel busca por defecto 'created_at' y 'updated_at'.
     * Como tu tabla no tiene estas columnas (o solo tiene created_at), 
     * deshabilitamos la funcionalidad automática de timestamps.
     */
    public $timestamps = false; 

    // Opcional: Si quisieras mantener el created_at pero ignorar el updated_at,
    // podrías usar: const UPDATED_AT = null;
    // Pero con 'public $timestamps = false;' te aseguras de que no falle nada.
}