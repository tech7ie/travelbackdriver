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
        Schema::table('vehicles', function (Blueprint $table) {
            $table->dropColumn('name');
            $table->string('mark', 256);
            $table->string('model', 256);
            $table->string('registration', 256);
            $table->string('inspection', 256);
            $table->string('green_card', 256);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('vehicles', function (Blueprint $table) {
            $table->string('name');
            $table->dropColumn('mark');
            $table->dropColumn('model');
            $table->dropColumn('registration');
            $table->dropColumn('inspection');
            $table->dropColumn('green_card');
        });
    }
};
