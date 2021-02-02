<?php

namespace App\Http\Controllers;

use App\Product;
use App\MeatPartition;
use App\Http\Controllers\ImageController;
use Illuminate\Http\Request;

class ProductController extends Controller {

  public function showAllProducts() {
    return response()->json(Product::with('image:id,savedFileName','partitions')->get());
  }

  public function showOneProduct($id) {
    return response()->json(Product::with('image:id,savedFileName','partitions')->find($id));
  }

  public function addMeatPartition($id, Request $request) {
    $this->validate($request, [
      'partition' => 'required|numeric|exists:meat_partitions,id',
    ]);
    $product = Product::find($id);
    $product->partitions()->attach($request->input('partition'));

    return response()->json($product->partitions()->get());
  }

  public function removeMeatPartition($product_id, $partition_id) {
    $product = Product::find($product_id);
    $product->partitions()->detach($partition_id);

    return response()->json($product->partitions()->get());
  }

  public function create(Request $request) {
    $this->validate($request, [
      'name' => 'required',
      'description' => 'required',
      'category_id' => 'required|numeric|exists:categories,id',
      'price' => 'required|numeric',
      'stock' => 'numeric',
      'status' => 'required',
      'type' => 'numeric',
      'boneWeight' => 'numeric',
      'image_id' => 'numeric|exists:images,id',
      'image' => 'image',
      'image_name' => 'required_with:image'
    ]);

    $product = Product::create($request->all());

    if($request->hasFile('image')) {
      $createdImg = ImageController::createImage($request->file('image'), $request->input('image_name'));
      if($createdImg->success == 1) {
        $product->image()->associate($createdImg->data->image->id);
        $product->save();
      }
    }
    $product = Product::with('image:id,savedFileName','partitions')->find($product->id);

    return response()->json($product, 201);
  }

  public function update($id, Request $request) {
    $product = Product::with('image:id,savedFileName','partitions')->findOrFail($id);
    $this->validate($request, [
      'category_id' => 'numeric|exists:categories,id',
      'price' => 'numeric',
      'stock' => 'numeric',
      'image_id' => 'numeric|exists:images,id'
    ]);
    if($request->exists('stock')) {
      if($request->input('stock') == 0) {
        $product->update(['status' => 'out_of_stock']);
      }
      else if($request->input('stock') < 10) {
        $product->update(['status' => 'running_low']);
      }
      else {
        $product->update(['status' => 'in_stock']);
      }
    }
    $product->update($request->all());

    return response()->json($product, 200);
  }

  public function delete($id) {
    $product = Product::findOrFail($id);
    if($product->image != null) {
      (new ImageController())->delete($product->image->id);
    }
    $product->delete();
    return response('Deleted Successfully', 200);
  }

}

?>
