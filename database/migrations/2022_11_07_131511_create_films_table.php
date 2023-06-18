<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFilmsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('films', function (Blueprint $table) {
            $table->id();
            $table->foreignId('collection_id');
            $table->foreignId('film_id');
            $table->integer('order');
            $table->timestamps();
        });

        Schema::table('films', function (Blueprint $table) {
            $table->foreign('collection_id')->references('id')->on('collections')
                ->cascadeOnDelete()->cascadeOnUpdate();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('films', function (Blueprint $table) {
            $table->dropForeign(['collection_id']);
        });
        Schema::dropIfExists('films');
    }
}
