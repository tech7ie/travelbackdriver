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
        Schema::create('profiles', function (Blueprint $table) {
            $table->id();
            $table->integer('user_id');
            $table->string('first_name', 256);
            $table->string('last_name', 256);
            $table->string('name', 256)->nullable();
            $table->string('english_lvl', 256)->nullable();
            $table->string('whatsapp', 256)->nullable();
            $table->string('country')->nullable();
            $table->string('city')->nullable();
            $table->string('passport', 256)->nullable();
            $table->string('driver_licence', 256)->nullable();
            $table->string('criminal_check', 256)->nullable();
            $table->string('photo', 256)->nullable();
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
        Schema::dropIfExists('profiles');
    }
};
