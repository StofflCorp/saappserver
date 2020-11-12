<?php

namespace App\Http\Controllers;

use App\User;
use App\Event;
use App\Order;
use Illuminate\Http\Request;

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

  private function associateNewOrder($user) {
    $order = Order::create(['user_id' => $user->id, 'status' => 'not_ordered']);
    $user->shoppingCart()->associate($order);
    $user->save();
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
