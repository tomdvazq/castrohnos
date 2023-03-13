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
        Schema::create('pedido_piedras', function (Blueprint $table) {
            $table->id();
            // Base de datos de los pedidos de piedras: Relación con cliente y datos básicos
            $table->unsignedBigInteger('cliente_id');
            $table->string('identificacion');
            $table->enum('estado', ['Retira', 'Avisa por la entrega', 'Entregar', 'Reclama entrega de piedras'])
                ->nullable();
            // Base de datos de los pedidos de piedras: Fechas
            $table->date('entrega')
                ->nullable();
            // Base de datos de los pedidos de piedras: TimeStamps
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
        Schema::dropIfExists('pedido_piedras');
    }
};
