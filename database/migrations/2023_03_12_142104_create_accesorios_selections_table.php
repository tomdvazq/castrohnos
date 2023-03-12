<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('accesorios_selections', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('pedido_id');
            $table->unsignedBigInteger('accesorio_id');
            $table->unsignedBigInteger('accesorio_listado_id');
            $table->string('material');
            $table->integer('cantidad');
            $table->timestamps();

            //Llave foránea del pedido
            $table->foreign('pedido_id')->references('id')->on('pedidos')->onDelete('cascade');
            //Llave foránea de la marca del accesorio
            $table->foreign('accesorio_id')->references('id')->on('accesorios')->onDelete('cascade');
            //Llave foránea del tipo de accesorio
            $table->foreign('accesorio_listado_id')->references('id')->on('accesorio_listados')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('accesorios_selections');
    }
};
