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
        Schema::create('archivos', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('pedido_id');
            $table->string('identificacion')
                ->nullable();
            $table->enum('categoria', ['Factura', 'Nota de crédito', 'Nota de débito', 'Archivo', 'Link'])
                ->nullable();
            $table->enum('tipo', ['AutoCAD', 'PDF', 'Excel', 'Dropbox'])
                ->nullable();
            $table->string('archivo')
                ->nullable();
            $table->string('dropbox')
                ->nullable();
            $table->timestamps();

            $table->foreign('pedido_id')->references('id')->on('pedidos')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('archivos');
    }
};
