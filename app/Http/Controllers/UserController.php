<?php

namespace App\Http\Controllers;

use App\User;
use App\Event;
use App\Order;
use App\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class UserController extends Controller {

  public function showAllUsers() {
    return response()->json(User::all());
  }

  public function showOneUser($id) {
    return response()->json(User::find($id));
  }

  public function showEvents($id) {
    $user = User::findOrFail($id);
    return response()->json($user->events()->get());
  }

  public function addEvent($id, Request $request) {
    $this->validate($request, [
      'event' => 'required|numeric|exists:events,id'
    ]);
    $user = User::findOrFail($id);
    $user->events()->attach($request->input('event'));

    return response()->json($user->events()->get());
  }

  public function removeEvent($user_id, $event_id) {
    $user = User::findOrFail($user_id);
    $user->events()->detach($event_id);

    return response()->json($user->events()->get());
  }

  public function showJokes($id) {
    $user = User::findOrFail($id);
    return response()->json($user->jokes()->get());
  }

  public function addJoke($id, Request $request) {
    $this->validate($request, [
      'joke' => 'required|numeric|exists:jokes,id'
    ]);
    $user = User::findOrFail($id);
    $user->jokes()->attach($request->input('joke'));

    return response()->json($user->jokes()->get());
  }

  public function removeJoke($user_id, $joke_id) {
    $user = User::findOrFail($user_id);
    $user->jokes()->detach($joke_id);

    return response()->json($user->jokes()->get());
  }

  public function showOrders($user_id) {
    $user = User::findOrFail($user_id);
    return response()->json($user->orders()->get());
  }

  public function showPreparingOrders($user_id) {
    $user = User::findOrFail($user_id);
    return response()->json($user->orders()->where('status','ordered')->orWhere('status','ready')->withCount(['products', 'products as price_sum' => function($query) {
      $query->select(DB::raw('ROUND(SUM(price*quantity),2)'));
    }])->orderBy('pickup_date')->get());
  }

  public function showStatistics($user_id) {
    $user = User::findOrFail($user_id);
    $stats = array();
    //Order Count
    $stats[] = (object)array(
      'name' => 'Gesamt abgeschlossene Einkäufe',
      'value' => $user->orders()->where('status','finished')->count()
    );

    //Order Sum
    $orderSums = $user->orders()->where('status','finished')->withCount(['products as price_sum' => function($query) {
      $query->select(DB::raw('ROUND(SUM(price*quantity),2)'));
    }])->get();
    $fullSum = 0;
    foreach ($orderSums as $s) {
      $fullSum = $fullSum + $s->price_sum;
    }
    $stats[] = (object)array(
      'name' => 'Gesamtwert aller Einkäufe',
      'value' => $fullSum
    );

    //Distinct Product Count
    $stats[] = (object)array(
      'name' => 'Anzahl gekaufter unterschiedlicher Artikel',
      'value' => Product::whereIn('id', $user->orders()->where('status','finished')->select('id')->get())->distinct('id')->count()
    );
    return response()->json($stats);
  }

  public function showLatestOrders($user_id) {
    $user = User::findOrFail($user_id);
    return response()->json($user->orders()->where('status','finished')->withCount(['products', 'products as price_sum' => function($query) {
      $query->select(DB::raw('ROUND(SUM(price*quantity),2)'));
    }])->orderBy('pickup_date','desc')->take(3)->get());
  }

  private function associateNewOrder($user) {
    $order = Order::create(['user_id' => $user->id, 'status' => 'not_ordered']);
    $user->shoppingCart()->associate($order);
    $user->save();
  }

  public function order($user_id, Request $request) {
    $this->validate($request, [
      'pickup_date' => 'required|date_format:Y-m-d H:i:s',
    ]);

    $user = User::findOrFail($user_id);
    $user->shoppingCart()->update(['status' => 'ordered', 'pickup_date' => $request->input('pickup_date')]);

    $result = response()->json($user->shoppingCart()->with('products')->get());
    $this->associateNewOrder($user);
    return $result;
  }

  public function copyOrderToShoppingCart($user_id, Request $request) {
    $this->validate($request, [
      'order_id' => 'required|numeric|exists:orders,id',
      'merge_tactic' => 'numeric'
    ]);
    $user = User::findOrFail($user_id);
    $order = Order::with('products')->findOrFail($request->input('order_id'));
    if($user->shoppingCart == null) {
      $this->associateNewOrder($user);
    }

    //Copy products
    $copiedProducts = [];
    foreach($order->products as $p) {
      $copiedProducts[$p->id] = ['quantity' => $p->pivot->quantity,
                                  'partition_id' => $p->pivot->partition_id,
                                  'partition_value' => $p->pivot->partition_value,
                                  'include_bone' => $p->pivot->include_bone];
    }

    //Merge, copy, override, etc.
    if($user->shoppingCart->products()->count() > 0) {
      if($request->filled('merge_tactic')) {
        if($request->input('merge_tactic') == 0) { // 0 = soft merge, Produkte ergänzen
          $user->shoppingCart->products()->syncWithoutDetaching($copiedProducts);
          return response()->json(['success' => 'Added items.']);
        }
        else if($request->input('merge_tactic') == 1) { // 1 = override merge, Produktliste überschreiben
          $user->shoppingCart->products()->sync($copiedProducts);
          return response()->json(['success' => 'Overrode items.']);
        }
      }
      return response()->json([
        'warning' => 'Es sind bereits Produkte im Kühlschrank. Möchten Sie die alte Bestellung ergänzen oder überschreiben?',
        'merge_tactics' => [
          ['code' => '0', 'name' => 'Ergänzen'],
          ['code' => '1', 'name' => 'Überschreiben']
        ]
      ]);
    }

    $user->shoppingCart->products()->attach($copiedProducts);
    return response()->json(['success' => 'Copied items.']);
  }

  public function showShoppingCartProducts($id) {
    $user = User::findOrFail($id);
    if($user->shoppingCart == null) {
      $this->associateNewOrder($user);
    }
    return response()->json(['order' => $user->shoppingCart, 'products' => $user->shoppingCart->products()->with('image:id,savedFileName')->get()]);
  }

  public function addShoppingCartProduct($id, Request $request) {
    $this->validate($request, [
      'product' => 'required|numeric|exists:products,id',
      'quantity' => 'required|numeric'
    ]);
    $user = User::findOrFail($id);

    if($user->shoppingCart == null) {
      $this->associateNewOrder($user);
    }
    if($user->shoppingCart->products()->get()->contains($request->input('product'))) {
      return response()->json(['error' => 'Product already in shopping cart']);
    }

    $user->shoppingCart->products()->attach($request->input('product'), ['quantity' => $request->input('quantity')]);
    return response()->json(['order' => $user->shoppingCart, 'products' => $user->shoppingCart->products()->get()]);
  }

  public function removeShoppingCartProduct($user_id, $product_id) {
    $user = User::findOrFail($user_id);
    if($user->shoppingCart == null) {
      $this->associateNewOrder($user);
    }
    $user->shoppingCart->products()->detach($product_id);
    return response()->json(['order' => $user->shoppingCart, 'products' => $user->shoppingCart->products()->get()]);
  }

  public function changeShoppingCartProductQuantity($user_id, $product_id, Request $request) {
    $this->validate($request, [
      'quantity' => 'required|numeric'
    ]);
    $user = User::findOrFail($user_id);
    $user->shoppingCart->products()->updateExistingPivot($product_id, ['quantity' => $request->input('quantity')]);
    return response()->json(['order' => $user->shoppingCart, 'products' => $user->shoppingCart->products()->get()]);
  }

  public function update($id, Request $request) {
    $user = User::findOrFail($id);
    $user->update($request->all());

    return response()->json($user, 200);
  }

  public function delete($id) {
    User::findOrFail($id)->delete();
    return response('Deleted Successfully', 200);
  }

}

?>
