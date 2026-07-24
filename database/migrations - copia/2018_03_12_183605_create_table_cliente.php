<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableCliente extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('cliente', function (Blueprint $table) {
            $table->increments('clicod');
            $table->string('tdicod',1)->references('tdicod')->on('tipo_documento_identidad')->onUpdate('cascade');
            $table->string('rucemp',11)->references('IdEmpresa')->on('empresa')->onUpdate('cascade');
            $table->string('clinum',20);
            $table->string('clinom',250);
            $table->string('clidir',250);
            $table->string('clicor',250);
            $table->string('cliest',8)->default('Activo');
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
        Schema::dropIfExists('cliente');
    }
}
