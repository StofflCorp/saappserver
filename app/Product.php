<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Product extends Model {

  /**
   *  The attributes that are mass assignable
   *
   *  @var array
   */
   protected $fillable = [
     'name', 'description', 'category_id', 'price', 'stock', 'status', 'type', 'bone_weight', 'usage_recommendations', 'image_id'
   ];

   /**
    *  The attributes excluded from the model's JS form
    *
    *  @var array
    */
    protected $hidden = ['image_id'];

    public function category() {
      return $this->belongsTo('App\Category');
    }

    public function image() {
      return $this->belongsTo('App\Image');
    }

    public function partitions() {
      return $this->belongsToMany('App\MeatPartition');
    }

}

?>
