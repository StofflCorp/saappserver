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

    //Track objects using this image
    public function news() {
      return $this->hasMany('App\News', 'image_id');
    }
    public function events() {
      return $this->hasMany('App\Event', 'image_id');
    }
    public function products() {
      return $this->hasMany('App\Product', 'image_id');
    }
    public function categories() {
      return $this->hasMany('App\Category', 'image_id');
    }

}

?>
