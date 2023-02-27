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
        Schema::create('material_listados', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('material_id');
            $table->string('material');
            $table->string('size')
                ->nullable();
            $table->decimal('stock', 19, 2);
            $table->timestamps();

            $table->foreign('material_id')->references('id')->on('materials');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('material_listados');
    }
};
