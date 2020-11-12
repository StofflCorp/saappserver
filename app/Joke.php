<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Joke extends Model {

  /**
   *  The attributes that are mass assignable
   *
   *  @var array
   */
   protected $fillable = [
     'content'
   ];

   /**
    *  The attributes excluded from the model's JS form
    *
    *  @var array
    */
    protected $hidden = [];

    public function user() {
      return $this->belongsToMany('App\User', 'author');
    }

}

?>
