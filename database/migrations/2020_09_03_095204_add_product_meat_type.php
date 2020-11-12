<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddProductMeatType extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
      Schema::table('products', function(Blueprint $table) {
        $table->integer('type');
        $table->text('usage_recommendations')->nullable();
        $table->double('bone_weight',8,2);
      });
      Schema::create('meat_partitions', function(Blueprint $table) {
        $table->increments('id');
        $table->string('name');
        $table->integer('type');
        $table->double('partition_weight',8,2);
        $table->timestamps();
      });
      Schema::create('meat_partition_product', function(Blueprint $table) {
        $table->integer('meat_partition_id')->unsigned();
        $table->integer('product_id')->unsigned();
        $table->primary(['meat_partition_id','product_id']);
        $table->foreign('meat_partition_id')->references('id')->on('meat_partitions')->onDelete('cascade');
        $table->foreign('product_id')->references('id')->on('products')->onDelete('cascade');
      });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
      Schema::dropIfExists('meat_partitions_products');
      Schema::dropIfExists('meat_partitions');
      Schema::table('products', function(Blueprint $table) {
        if(Schema::hasColumn('products', 'type')) {
          $table->dropColumn('type');
        }
        if(Schema::hasColumn('products', 'usage_recommendations')) {
          $table->dropColumn('usage_recommendations');
        }
        if(Schema::hasColumn('products', 'bone_weight')) {
          $table->dropColumn('bone_weight');
        }
      });
    }
}
