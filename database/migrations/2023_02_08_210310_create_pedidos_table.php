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
        Schema::create('pedidos', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('cliente_id');
            $table->string('identificacion');
            $table->enum('estado', ['Medir', 'Avisa para medir', 'Remedir', 'Reclama medición', 'Medido', 'Medida del cliente', 'Corte', 'En taller', 'Cortado', 'Entregas'])
                ->nullable();
            $table->date('entrega')
                ->nullable();
            $table->timestamps();

            $table->foreign('cliente_id')->references('id')->on('clientes');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('pedidos');
    }
};
