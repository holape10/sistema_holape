<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableCpeNota extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('cpe_nota', function (Blueprint $table) {
            $table->increments('IdCpe_nota');
            $table->string('ccasunnot',250)->nullable();
            $table->string('ccaxmlzip',250)->nullable();
            $table->string('serdoc',4)->nullable();
            $table->string('codhash',250)->nullable();
            $table->string('ccadessun',250)->nullable();
            $table->string('ccacodsun',250)->nullable();
            $table->string('ccaenlace',250)->nullable();
            $table->string('ccaqr',250)->nullable();
            $table->string('ccacdrzip',250)->nullable();
            $table->string('ccapdfzip',250)->nullable();
            $table->string('ccasunsoaperr',250)->nullable();
            $table->string('ccasunrescod',255)->nullable();
            $table->string('ccaobs',250)->nullable();
            $table->string('tipnot',2)->nullable();
            $table->string('tdocod',2);
            $table->string('ccabaj',16)->nullable();
            $table->string('IdEmpresa',11)->nullable();
            $table->decimal('ccatvgr',10,2)->default('0');
            $table->decimal('ccaitv',10,2)->default('0');
            $table->decimal('ccaotr',10,2)->default('0');
            $table->decimal('ccaisc',10,2)->default('0');
            $table->decimal('ccaigv',10,2)->default('0');
            $table->decimal('ccatve',10,2)->default('0');
            $table->decimal('ccatvi',10,2)->default('0');
            $table->decimal('ccatvg',10,2)->default('0');
            $table->decimal('ccacar',10,2)->default('0');
            $table->decimal('tipcambio',10,3);
            $table->integer('numdoc')->unsigned();
            $table->integer('IdCpe_cabecera',11)->unsigned()->references('IdCpe_cabecera')->on('cpe_cabecera')->onUpdate('cascade');
            $table->integer('IdUsuario',10)->unsigned();
            $table->date('ccafem');

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
        Schema::dropIfExists('cpe_nota');
    }
}
