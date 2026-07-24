<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableCpeCabecera extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('cpe_cabecera', function (Blueprint $table) {
            $table->increments('IdCpe_cabecera');
            $table->numeric('tipcambio',10,3)->unsigned()->default('0');
            $table->numeric('ccades',10,2)->unsigned()->default('0');
            $table->numeric('ccacar',10,2)->unsigned()->default('0');
            $table->numeric('ccatde',10,2)->unsigned()->default('0');
            $table->numeric('ccatvg',10,2)->unsigned()->default('0');
            $table->numeric('ccatvi',10,2)->unsigned()->default('0');
            $table->numeric('ccatve',10,2)->unsigned()->default('0');
            $table->numeric('ccaivg',10,2)->unsigned()->default('0');
            $table->numeric('ccaisc',10,2)->unsigned()->default('0');
            $table->numeric('ccaotr',10,2)->unsigned()->default('0');
            $table->numeric('ccaitv',10,2)->unsigned()->default('0');
            $table->numeric('ccatvgr',10,2)->unsigned()->default('0');
            $table->integer('ccacde',3)->unsigned()->default('0');
            $table->integer('numdoc',8)->unsigned()->default('0');
            $table->integer('IdUsuario',10)->unsigned()->default('0')->references('IdUsuario')->on('users')->onUpdate('cascade');
            $table->date('ccafem');
            $table->date('ccafve');
            $table->string('tdicod',1)->references('tdicod')->on('tipo_documento_identidad')->onUpdate('cascade');
            $table->string('ccanom',100)->nullable();
            $table->string('IdEmpresa',11)->references('IdEmpresa')->on('empresa')->onUpdate('cascade');
            $table->string('ccandi',15)->nullable();
            $table->string('ccanot',16)->nullable();
            $table->string('ccabaj',16)->nullable();
            $table->string('tdocod',2)->references('tdocod')->on('tipo_documento')->onUpdate('cascade');
            $table->string('topcod',2)->references('topcod')->on('tipo_operacion')->onUpdate('cascade');
            $table->string('ccaobs',250)->nullable();
            $table->string('codhash',250)->nullable();
            $table->string('ccacodsun',250)->nullable();
            $table->string('ccasunrescod',250)->nullable();
            $table->string('ccasunsoaperr',250)->nullable();
            $table->string('ccapdfzip',250)->nullable();
            $table->string('ccaxmlzip',250)->nullable();
            $table->string('ccacdrzip',250)->nullable();
            $table->string('ccaqr',250)->nullable();
            $table->string('ccaenlace',250)->nullable();
            $table->string('ccadessun',250)->nullable();
            $table->string('moncod',3)->references('moncod')->on('moneda')->onUpdate('cascade');
            $table->string('serdoc',4)->nullable();
            $table->string('ccasunnot',250)->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('cpe_cabecera');
    }
}
