<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Event extends Model {

  /**
   *  The attributes that are mass assignable
   *
   *  @var array
   */
   protected $fillable = [
     'name', 'description', 'location', 'day', 'startTime', 'endTime', 'image_id'
   ];

   /**
    *  The attributes excluded from the model's JS form
    *
    *  @var array
    */
    protected $hidden = ['image_id'];

    public function users() {
      return $this->belongsToMany('App\User');
    }

    public function image() {
      return $this->belongsTo('App\Image');
    }

}

?>
