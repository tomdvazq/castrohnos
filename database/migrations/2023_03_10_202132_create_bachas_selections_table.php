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
        Schema::create('bachas_selections', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('pedido_id');
            $table->unsignedBigInteger('bacha_id');
            $table->unsignedBigInteger('bacha_listado_id');
            $table->enum('tipo_bacha', ['Baño', 'Cocina'])
            ->nullable();
            $table->string('material');
            $table->integer('cantidad');
            $table->timestamps();

            $table->foreign('pedido_id')->references('id')->on('pedidos')->onDelete('cascade');
            $table->foreign('bacha_id')->references('id')->on('bachas')->onDelete('cascade');
            $table->foreign('bacha_listado_id')->references('id')->on('bacha_listados')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('bachas_selections');
    }
};
