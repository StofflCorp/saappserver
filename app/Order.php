<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Order extends Model {

  /**
   *  The attributes that are mass assignable
   *
   *  @var array
   */
   protected $fillable = [
     'user_id', 'status', 'pickup_date'
   ];

   /**
    *  The attributes excluded from the model's JS form
    *
    *  @var array
    */
    protected $hidden = [];

    public function products() {
      return $this->belongsToMany('App\Product')->withPivot('quantity', 'partition_id', 'partition_value', 'include_bone');
    }

    public function orderer() {
      return $this->belongsTo('App\User');
    }

}

?>
