<?php

namespace MasterSoft;

use Illuminate\Database\Eloquent\Model;

class moneda extends Model
{
    protected $table = 'moneda';

   protected $primaryKey = 'moncod';

   protected  $keyType = 'string';
   
   public $timestamps = false;

   protected $fillable = [
   		'monnom',
   		'monest'
   ];

   protected $guarded = [

   ];
}
