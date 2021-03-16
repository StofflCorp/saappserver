<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddUserEmailValidation extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
      Schema::table('users', function(Blueprint $table) {
        $table->timestamp('email_verified_at')->nullable();
        $table->text('temp_hash')->nullable();
      });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
      Schema::table('users', function(Blueprint $table) {
        if(Schema::hasColumn('users', 'email_verified_at')) {
          $table->dropColumn('email_verified_at');
        }
        if(Schema::hasColumn('users', 'temp_hash')) {
          $table->dropColumn('temp_hash');
        }
      });
    }
}
