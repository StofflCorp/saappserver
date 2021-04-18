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

    public function getPriceSumAttribute() {
      $priceSum = 0;
      $orderedProducts = $this->products;
      foreach ($orderedProducts as $product) {
        $orderedStock = 0;
        if($product->type == 0) {
          //Normales Produkt => Mengenangabe verwenden
          $orderedStock = $product->pivot->quantity;
        }
        else {
          //Siehe UserController für Berechnung
          $partition = MeatPartition::findOrFail($product->pivot->partition_id);
          if($partition->type == 1 || ($partition->type == 2 && $product->pivot->partition_value == 1)) {
            $orderedStock = $product->pivot->quantity * ($partition->partition_weight / 1000);
          }
          else {
            $orderedStock = $product->pivot->quantity;
          }
        }
        //Preis berechnen
        $priceSum += $orderedStock * $product->price;
      }
      return $priceSum;
    }

}

?>
