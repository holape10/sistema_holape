<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMenusTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
{
    Schema::create('menus', function (Blueprint $table) {
        $table->increments('id');
        $table->string('name'); 
        $table->string('url'); 
        $table->string('icon')->nullable(); 
        $table->integer('parent_id')->unsigned()->nullable(); 
        $table->foreign('parent_id')->references('id')->on('menus')->onDelete('cascade');
        $table->string('permission_name'); 
        $table->integer('order')->default(0);
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
        Schema::dropIfExists('menus');
    }
}
