<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddOrderPickupDate extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
      Schema::table('orders', function(Blueprint $table) {
        $table->dateTime('pickup_date')->nullable();
      });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
      Schema::table('orders', function(Blueprint $table) {
        if(Schema::hasColumn('orders', 'pickup_date')) {
          $table->dropColumn('pickup_date');
        }
      });
    }
}
