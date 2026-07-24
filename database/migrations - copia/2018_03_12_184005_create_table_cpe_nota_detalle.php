<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableCpeNotaDetalle extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('cpe_nota_detalle', function (Blueprint $table) {
            $table->increments('IdCpe_nota_detalle');
            $table->decimal('cdecan',10,2)->default('0');
            $table->string('cdepsu',20);
            $table->string('cdedes',250);
            $table->decimal('cdvun',10,2)->default('0');
            $table->decimal('cdedec',10,2)->default('0');
            $table->decimal('cdeigv',10,2)->default('0');
            $table->decimal('cdeisc',10,2)->default('0');
            $table->string('cdetis',2);
            $table->decimal('cdepve',10,2)->default('0');
            $table->decimal('cdevve',10,2)->default('0');
            $table->string('tigcod')->references('tigcod')->on('tipo_igv')->onUpdate('cascade');
            $table->string('procod',20)->references('procod')->on('productos')->onUpdate('cascade');
            $table->integer('IdCpe_nota',11)->default('0')->unsigned()->references('IdCpe_nota')->on('cpe_nota')->onUpdate('cascade');
            $table->string('umecod',3)->references('umecod')->on('unidad_medida')->onUpdate('cascade');
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
        Schema::dropIfExists('cpe_nota_detalle');
    }
}
