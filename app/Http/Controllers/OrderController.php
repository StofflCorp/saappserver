<?php

namespace App\Http\Controllers;

use App\Order;
use Illuminate\Http\Request;

class OrderController extends Controller {

  public function showAllOrders() {
    return response()->json(Order::all());
  }

  public function showOneOrder($id) {
    return response()->json(Order::find($id));
  }

  public function showProducts($id) {
    $order = Order::find($id);
    return response()->json($order->products()->get());
  }

  public function addProduct($id, Request $request) {
    $this->validate($request, [
      'product' => 'required|numeric|exists:products,id',
      'quantity' => 'required|numeric'
    ]);
    $order = Order::find($id);
    $order->products()->attach($request->input('product'), ['quantity' => $request->input('quantity')]);

    return response()->json($order->products()->get());
  }

  public function changeProductAmount($order_id, $product_id, Request $request) {
    $this->validate($request, [
      'quantity' => 'required|numeric'
    ]);
    $order = Order::find($id);
    $order->products()->updateExistingPivot($product_id, ['quantity' => $request->input('quantity')]);

    return response()->json($order->products()->get());
  }

  public function removeProduct($order_id, $product_id) {
    $order = Order::find($order_id);
    $order->products()->detach($product_id);

    return response()->json($order->products()->get());
  }

  public function create(Request $request) {
    $this->validate($request, [
      'user_id' => 'required|numeric|exists:users,id',
      'status' => 'required'
    ]);

    $order = Order::create($request->all());

    return response()->json($order, 201);
  }

  public function update($id, Request $request) {
    $order = Order::findOrFail($id);
    $this->validate($request, [
      'user_id' => 'numeric|exists:users,id'
    ]);
    if($request->input('pickup_date') === '' || $request->input('pickup_date') === 'null') {
      $request->merge(['pickup_date' => null]);
    }
    $order->update($request->all());

    return response()->json($order, 200);
  }

  public function delete($id) {
    Order::findOrFail($id)->delete();
    return response('Deleted Successfully', 200);
  }

}

?>
