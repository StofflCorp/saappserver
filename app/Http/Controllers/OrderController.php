<?php

namespace App\Http\Controllers;

use App\Order;
use App\OrderWithProductAggregations;
use App\MeatPartition;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller {

  public function showAllOrders(Request $request) {
    $orderQuery = Order::with('products.image:id,savedFileName');

    //Status filter
    if($request->has('ready')) {
      $orderQuery = $orderQuery->where('status', '=', 'ready');
    }
    else if($request->has('finished')) {
      $orderQuery = $orderQuery->where('status', '=', 'finished');
    }
    else if($request->has('not_ordered')) {
      $orderQuery = $orderQuery->where('status', '=', 'not_ordered');
    }
    else if(!$request->has('all')) {
      $orderQuery = $orderQuery->where('status', '=', 'ordered');
    }

    $result = null;
    //Include price and product count
    if($request->has('withPrice')) {
      $result = $orderQuery->withCount('products')->get();
      foreach ($result as $o) {
        $o->append('priceSum');
      }
    }
    else {
      $result = $orderQuery->get();
    }

    return response()->json($result);
  }

  public function showOneOrder($id) {
    return response()->json(Order::with('products.image:id,savedFileName')->find($id));
  }

  public function showProducts($id) {
    $order = Order::findOrFail($id);
    return response()->json($order->products()->with('image:id,savedFileName')->get());
  }

  public function addProduct($id, Request $request) {
    $this->validate($request, [
      'product' => 'required|numeric|exists:products,id',
      'quantity' => 'required|numeric',
      'partition_id' => 'numeric|exists:meat_partitions,id',
      'partition_value' => 'numeric',
      'include_bone' => 'numeric'
    ]);
    $order = Order::findOrFail($id);
    $order->products()->attach($request->input('product'), ['quantity' => $request->input('quantity'),
                                                            'partition_id' => $request->input('partition_id'),
                                                            'partition_value' => $request->input('partition_value'),
                                                            'include_bone' => $request->input('include_bone')]);

    return response()->json($order->products()->with('image:id,savedFileName')->get());
  }

  public function changeProductAmount($order_id, $product_id, Request $request) {
    $this->validate($request, [
      'quantity' => 'required|numeric'
    ]);
    $order = Order::findOrFail($id);
    $order->products()->updateExistingPivot($product_id, ['quantity' => $request->input('quantity')]);

    return response()->json($order->products()->with('image:id,savedFileName')->get());
  }

  public function removeProduct($order_id, $product_id) {
    $order = Order::findOrFail($order_id);
    $order->products()->detach($product_id);

    return response()->json($order->products()->with('image:id,savedFileName')->get());
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
