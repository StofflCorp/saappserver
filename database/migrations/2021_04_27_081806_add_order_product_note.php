<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddOrderProductNote extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
      Schema::table('order_product', function(Blueprint $table) {
        $table->string('note')->nullable();
      });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
      Schema::table('order_product', function(Blueprint $table) {
        if(Schema::hasColumn('order_product', 'note')) {
          $table->dropColumn('note');
        }
      });
    }
}
