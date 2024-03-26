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
        Schema::table('route_cars', function (Blueprint $table) {
            $table->renameColumn('routes_id', 'route_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('route_cars', function (Blueprint $table) {
            $table->renameColumn('route_id', 'routes_id');
        });
    }
};
