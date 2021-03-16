<?php

namespace App;

use Illuminate\Auth\Authenticatable;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Database\Eloquent\Model;
use Laravel\Lumen\Auth\Authorizable;

class User extends Model implements AuthenticatableContract, AuthorizableContract
{
    use Authenticatable, Authorizable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'prename', 'surname', 'email', 'password', 'currentOrder', 'email_verified_at', 'temp_hash'
    ];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'email_verified_at', 'temp_hash'
    ];

    public function events() {
      return $this->belongsToMany('App\Event');
    }

    public function jokes() {
      return $this->belongsToMany('App\Joke');
    }

    public function orders() {
      return $this->hasMany('App\Order');
    }

    public function shoppingCart() {
      return $this->belongsTo('App\Order','currentOrder');
    }

}
