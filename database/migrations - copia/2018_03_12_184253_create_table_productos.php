<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableProductos extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('productos', function (Blueprint $table) {
            $table->increments('IdProducto');
            $table->decimal('provun',10,2);
            $table->decimal('propun',10,2);
            $table->string('procod',20);
            $table->string('pronom',250);
            $table->string('proest',8)->default('Activo');
            $table->timestamps();
            $table->string('umecod',3)->references('umecod')->on('unidad_medida')->onUpdate('cascade');
            $table->string('moncod',3)->references('moncod')->on('moneda')->onUpdate('cascade');
            $table->string('IdEmpresa',11)->references('IdEmpresa')->on('empresa')->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('productos');
    }
}
