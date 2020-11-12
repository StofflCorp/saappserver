<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class MeatPartition extends Model {

  /**
   *  The attributes that are mass assignable
   *
   *  @var array
   */
   protected $fillable = [
     'name', 'type', 'partition_weight'
   ];

   /**
    *  The attributes excluded from the model's JS form
    *
    *  @var array
    */
    protected $hidden = [];

}

?>
