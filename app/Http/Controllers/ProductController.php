<?php

namespace App\Http\Controllers;

use App\Product;
use App\ImageController;
use Illuminate\Http\Request;

class ProductController extends Controller {

  public function showAllProducts() {
    return response()->json(Product::with('image:id,savedFileName','partitions')->get());
  }

  public function showOneProduct($id) {
    return response()->json(Product::with('image:id,savedFileName')->find($id));
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
    $product = Product::with('image:id,savedFileName')->find($product->id);

    return response()->json($product, 201);
  }

  public function update($id, Request $request) {
    $product = Product::with('image:id,savedFileName')->findOrFail($id);
    $this->validate($request, [
      'category_id' => 'numeric|exists:categories,id',
      'price' => 'numeric',
      'stock' => 'numeric',
      'image_id' => 'numeric|exists:images,id'
    ]);
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
