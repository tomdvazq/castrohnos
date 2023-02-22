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
        Schema::create('bacha_listados', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('bacha_id');
            $table->string('linea');
            $table->string('modelo')
                ->nullable();
            $table->integer('stock');
            $table->timestamps();

            $table->foreign('bacha_id')->references('id')->on('bachas');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('bacha_listados');
    }
};
