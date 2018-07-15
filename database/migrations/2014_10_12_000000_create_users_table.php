<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('username')->unique();
            $table->string('email')->unique()->nullable();
            $table->string('password');
            $table->string('first_name');
            $table->string('last_name')->nullable();
            $table->string('nick_name')->nullable();
            $table->tinyInteger('gender')->comment('1 - Male, 2 - Female, 3 - Others');
            $table->dateTime('dob')->nullable();
            $table->bigInteger('fb_id')->unique()->nullable();
            $table->bigInteger('google_id')->unique()->nullable();
            $table->bigInteger('mob_no')->unique()->nullable();
            $table->string('profile_pic')->nullable();
            $table->string('website')->nullable();
            $table->string('fb_url')->nullable();
            $table->string('linkedin_url')->nullable();
            $table->string('twitter_url')->nullable();
            $table->string('instagram_url')->nullable();
            $table->string('google_plus_url')->nullable();
            $table->string('activation_token')->nullable();
            $table->boolean('is_active')->default('0')->comment("0 - Inactive, 1 - Active");
            $table->boolean('is_delete')->default('0')->comment("0 - Live, 1 - Delete");
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
        Schema::dropIfExists('users');
    }
}
