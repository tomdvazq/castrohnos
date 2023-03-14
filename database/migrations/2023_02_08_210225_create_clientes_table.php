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
        Schema::create('clientes', function (Blueprint $table) {
            $table->id();
            $table->string('nombre')
                ->nullable();
            $table->string('direccion')
                ->nullable();
            $table->string('entrecalle_1')
                ->nullable();
            $table->string('entrecalle_2')
                ->nullable();
            $table->string('direccion_detalles')
                ->nullable();
            $table->string('localidad')
                ->nullable();
            $table->string('contacto')
                ->nullable();
            $table->string('documento')
                ->nullable();
            $table->string('cuit_cuil')
                ->nullable();
            $table->string('razon_social')
                ->nullable();
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
        Schema::dropIfExists('clientes');
    }
};
