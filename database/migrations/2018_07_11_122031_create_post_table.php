<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePostTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('post', function (Blueprint $table) {
            $table->increments('id');
            $table->bigInteger('user_id')->unsigned();
            $table->string('post');
            $table->integer('img_id')->unsigned();
            $table->tinyInteger('privacy')->default('1')->comment("1 - Public, 2 - Private, 3 - Friends");
            $table->boolean('is_published')->default('0')->comment("0 - UnPublished, 1 - Published");
            $table->boolean('is_deleted')->default('0')->comment("0 - Live, 1 - Delete");

            $table->foreign('img_id')->references('id')->on('user_photos');
            $table->foreign('user_id')->references('id')->on('users');

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
        Schema::dropIfExists('post');
    }
}
