<?php

namespace MasterSoft;

use Illuminate\Database\Eloquent\Model;

class guias_remision extends Model
{
   protected $table = 'guias_remision';

   protected $primaryKey = 'IdCpe_guia';

   public $timestamps = false;

   protected $fillable = [
   	"umecod",
      "ubigeopartida",
      "ubigeollegada",
      "tdocod",
      "tdicodtransportista",
      "tdicodconductor",
      "tdicod",
      "serieref",
      "serieguia",
      "ructransportista",
      "rucconductor",
      "ruccliente",
      "placa",
      "pesobruto",
      "numeroref",
      "numeroguia",
      "nomconductor",
      "nomcliente",
      "nombretransportista",
      "IdMotivo",
      "IdModalidad",
      "IdEmpresa",
      "IdCpe_guia",
      "id_empresa_negocio",
      "fechatraslado",
      "fechaemision",
      "direccionpartida",
      "direccionllegada",
      "desubigeopartida",
      "desubigeollegada",
      "datajson",
      "correo",
      "bultos",
      "codhash",
      "ccasunrescod",
      "ccasunnot",
      "ccaqr",
      "ccaenlace",
      "ccadessun",
      "ccacodsun",
      "error"
   ];


   protected $guarded = [

   ];
}
