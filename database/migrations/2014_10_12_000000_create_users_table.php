<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

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
            $table->id()->autoIncrement();
            $table->string('email')->unique();
            $table->timestamps();
            $table->integer('balance');

        });

        Schema::create('Token', function (Blueprint $table) {
            $table->foreignId('idUsuario')->references('id')->on('users');
            $table->integer('token');
            $table->dateTime('timestop');
            //$table->timestamps();

        });
    
        /*
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('email')->unique();
            $table->integer('balance');
            //$table->timestamp('email_verified_at')->nullable();
            $table->timestamps();
        });
        */
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
