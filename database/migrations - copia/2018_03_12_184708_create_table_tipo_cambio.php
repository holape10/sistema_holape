<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableTipoCambio extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tipo_cambio', function (Blueprint $table) {
            $table->increments('IdTipCambio');
            $table->decimal('CamVenta',10,2);
            $table->decimal('CamCompra',10,2);
            $table->date('FecTipCambio');
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
        Schema::dropIfExists('tipo_cambio');
    }
}
