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
        Schema::table('route_orders', function (Blueprint $table) {
            $table->integer('vehicle_id')->default(null);
            $table->integer('driver_id')->default(null);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('route_orders', function (Blueprint $table) {
            $table->dropColumn('vehicle_id');
            $table->dropColumn('driver_id');
        });
    }
};
