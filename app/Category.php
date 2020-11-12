<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Category extends Model {

  /**
   *  The attributes that are mass assignable
   *
   *  @var array
   */
   protected $fillable = [
     'name', 'image_id', 'shop_id'
   ];

   /**
    *  The attributes excluded from the model's JS form
    *
    *  @var array
    */
    protected $hidden = ['image_id'];

    public function products() {
      return $this->hasMany('App\Product');
    }

    public function image() {
      return $this->belongsTo('App\Image');
    }

}

?>
