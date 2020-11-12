<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddImageAttributes extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
      Schema::table('news', function(Blueprint $table) {
        $table->integer('image_id')->nullable()->unsigned();
        $table->foreign('image_id')->references('id')->on('images')->onDelete('set null');
      });
      Schema::table('events', function(Blueprint $table) {
        $table->integer('image_id')->nullable()->unsigned();
        $table->foreign('image_id')->references('id')->on('images')->onDelete('set null');
      });
      Schema::table('categories', function(Blueprint $table) {
        $table->integer('image_id')->nullable()->unsigned();
        $table->foreign('image_id')->references('id')->on('images')->onDelete('set null');
      });
      Schema::table('products', function(Blueprint $table) {
        $table->integer('image_id')->nullable()->unsigned();
        $table->foreign('image_id')->references('id')->on('images')->onDelete('set null');
      });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
      Schema::table('news', function(Blueprint $table) {
        $table->dropForeign(['image_id']);
        $table->dropColumn('image_id');
      });
      Schema::table('events', function(Blueprint $table) {
        $table->dropForeign(['image_id']);
        $table->dropColumn('image_id');
      });
      Schema::table('categories', function(Blueprint $table) {
        $table->dropForeign(['image_id']);
        $table->dropColumn('image_id');
      });
      Schema::table('products', function(Blueprint $table) {
        $table->dropForeign(['image_id']);
        $table->dropColumn('image_id');
      });
    }
}
