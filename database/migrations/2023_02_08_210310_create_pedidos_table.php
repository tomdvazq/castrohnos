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
            // Base de datos de los pedidos: Relación con cliente y datos básicos
            $table->unsignedBigInteger('cliente_id');
            $table->string('identificacion');
            $table->enum('estado', ['Medir', 'Avisa para medir', 'Remedir', 'Reclama medición', 'Medido', 'Medida del cliente', 'Corte', 'En taller', 'Cortado', 'Entregas'])
                ->nullable();
            // Base de datos de los pedidos: Fechas
            $table->date('entrega')
                ->nullable();
            $table->date('remedir')
                ->nullable();
            $table->date('avisa')
                ->nullable();
            $table->date('medido')
                ->nullable();
            // Base de datos de los pedidos: Confirmacion
            $table->enum('confirmacion', ['No seleccionado', 'No confirmado', 'Confirmado'])
                ->nullable();
            // Base de datos de los pedidos: TimeStamps (created_at/updated_at)
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
