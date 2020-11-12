<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Image extends Model {

  /**
   *  The attributes that are mass assignable
   *
   *  @var array
   */
   protected $fillable = [
     'name', 'originalFileName', 'savedFileName'
   ];

   /**
    *  The attributes excluded from the model's JS form
    *
    *  @var array
    */
    protected $hidden = [];

}

?>
