<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableEmpresa extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('empresa', function (Blueprint $table) {
            $table->increments('Id');
            $table->string('IdEmpresa',11);
            $table->integer('BanuEmpresa',3)->unsigned();
            $table->integer('puerto',4)->unsigned();
            $table->integer('FnuEmpresa',8)->unsigned();
            $table->integer('BnuEmpresa',8)->unsigned();
            $table->integer('BcnuEmpresa',8)->unsigned();
            $table->integer('FcnuEmpresa',8)->unsigned();
            $table->integer('BdnuEmpresa',8)->unsigned();
            $table->integer('FdnuEmpresa',8)->unsigned();
            $table->string('TelEmpresa',20);
            $table->string('CorEmpresa',250);
            $table->string('NomEmpresa',250);
            $table->string('LogEmpresa',250);
            $table->string('DirEmpresa',250);
            $table->string('FseEmpresa',4);
            $table->string('BseEmpresa',4);
            $table->string('CseEmpresa',4);
            $table->string('FdseEmpresa',4);
            $table->string('BdseEmpresa',4);
            $table->string('BcseEmpresa',4);
            $table->string('BcseEmpresa',4);
            $table->string('EstEmpresa',10);
            $table->date();
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
        Schema::dropIfExists('empresa');
    }
}
