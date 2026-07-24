<?php

namespace MasterSoft;

use Illuminate\Database\Eloquent\Model;

class documento_relacionado extends Model
{
    protected $table = 'documento_relacionado';

    protected $primaryKey = 'dorcod';

    public $timestamps = false;

    protected $fillable = [
    	'tdocod',
    	'dorser',
    	'dornum',
    ];

    protected $guarded = [

    ];
}
