<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddOrderProductPartitionValue extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
      Schema::table('order_product', function(Blueprint $table) {
        $table->integer('partition_id')->unsigned()->nullable();
        $table->foreign('partition_id')->references('id')->on('meat_partitions')->onDelete('set null');
        $table->integer('partition_value')->nullable();
        $table->integer('include_bone')->nullable();
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
        if(Schema::hasColumn('order_product', 'partition_id')) {
          $table->dropForeign(['partition_id']);
          $table->dropColumn('partition_id');
        }
        if(Schema::hasColumn('order_product', 'partition_value')) {
          $table->dropColumn('partition_value');
        }
        if(Schema::hasColumn('order_product', 'include_bone')) {
          $table->dropColumn('include_bone');
        }
      });
    }
}
