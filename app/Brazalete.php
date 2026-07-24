<?php

namespace MasterSoft;

use Illuminate\Database\Eloquent\Model;

class Brazalete extends Model
{
    protected $table = 'brazaletes';
    protected $fillable = ['codigo_rfid', 'numero_casillero', 'estado'];
}