<?php

namespace MasterSoft;

use Illuminate\Database\Eloquent\Model;

class tipo_documento_identidad extends Model
{
    protected $table = 'tipo_documento_identidad';

    protected $primaryKey = 'tdicod';

    protected $timestamps = false;

    protected $fillable = [
    	'tdiest',
    	'tdides',
    ];

    protected $guarded = [

    ];
 }