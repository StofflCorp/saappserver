<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class News extends Model {

  /**
   *  The attributes that are mass assignable
   *
   *  @var array
   */
   protected $fillable = [
     'name', 'description', 'author', 'image_id'
   ];

   /**
    *  The attributes excluded from the model's JS form
    *
    *  @var array
    */
    protected $hidden = ['image_id'];

    public function author() {
      return $this->belongsTo('App\Employee', 'author');
    }

    public function image() {
      return $this->belongsTo('App\Image');
    }

}

?>
