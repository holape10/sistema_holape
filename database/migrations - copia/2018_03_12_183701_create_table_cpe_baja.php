<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableCpeBaja extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('cpe_baja', function (Blueprint $table) {
            $table->increments('IdCpe_baja');
            $table->date('cbafec')->nullable();
            $table->date('cbdfco')->nullable();
            $table->string('cbanum',13)->nullable();
            $table->string('cbamot',100)->nullable();
            $table->integer('cbacor',3)->unsigned();
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
            $table->string('ccasunnot',250)->nullable();
            $table->string('ccasuntick',250)->nullable();
            $table->timestamps();
            $table->integer('IdCpe_cabecera',11)->references('IdCpe_cabecera')->on('cpe_cabecera')->onUpdate('cascade');
            $table->string('tdocod',2)->references('tdocod')->on('tipo_documento')->onUpdate('cascade');
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
        Schema::dropIfExists('cpe_baja');
    }
}
