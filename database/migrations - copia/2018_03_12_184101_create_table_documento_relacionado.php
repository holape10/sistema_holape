<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableDocumentoRelacionado extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('documento_relacionado', function (Blueprint $table) {
            $table->increments('IdDr');
            $table->integer('dornum',8)->unsigned();
            $table->string('tdocod',2);
            $table->string('dorser',4);
            $table->integer('IdCpe_cabecera',11)->unsigned()->references('IdEmpresa')->on('empresa')->onUpdate('cascade');
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
        Schema::dropIfExists('documento_relacionado');
    }
}
