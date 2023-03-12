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
        Schema::create('accesorio_listados', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('accesorio_id');
            $table->string('tipo');
            $table->string('modelo')
                ->nullable();
            $table->integer('stock');
            $table->timestamps();

            //Llave foránea de la marca del accesorio
            $table->foreign('accesorio_id')->references('id')->on('accesorios'); 
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('accesorio_listados');
    }
};
