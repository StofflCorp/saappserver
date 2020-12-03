<?php

namespace App\Http\Controllers;

use App\Category;
use App\Product;
use App\Http\Controllers\ImageController;
use Illuminate\Http\Request;

class CategoryController extends Controller {

  public function showAllCategories() {
    return response()->json(Category::with('image:id,savedFileName')->get());
  }

  public function showAllCategoriesOfType($id) {
    return response()->json(Category::where('shop_id',$id)->with('image:id,savedFileName')->get());
  }

  public function showOneCategory($id) {
    return response()->json(Category::with('image:id,savedFileName')->find($id));
  }

  public function showProducts($id) {
    $category = Category::findOrFail($id);
    return response()->json($category->products()->with('image:id,savedFileName')->get());
  }

  public function addProduct($id, Request $request) {
    $this->validate($request, [
      'product' => 'required|numeric|exists:products,id'
    ]);
    $category = Category::findOrFail($id);
    $category->products()->attach($request->input('product'));

    return response()->json($category->products()->get());
  }

  public function removeProduct($category_id, $product_id) {
    $category = Category::findOrFail($category_id);
    $category->products()->detach($product_id);

    return response()->json($category->products()->get());
  }

  public function create(Request $request) {
    $this->validate($request, [
      'name' => 'required',
      'image_id' => 'numeric|exists:images,id',
      'shop_id' => 'numeric',
      'image' => 'image',
      'image_name' => 'required_with:image'
    ]);

    $category = Category::create($request->all());

    if($request->hasFile('image')) {
      $createdImg = ImageController::createImage($request->file('image'), $request->input('image_name'));
      if($createdImg->success == 1) {
        $category->image()->associate($createdImg->data->image->id);
        $category->save();
      }
    }
    $category = Category::with('image:id,savedFileName')->find($category->id);

    return response()->json($category, 201);
  }

  public function update($id, Request $request) {
    $this->validate($request, [
      'image_id' => 'numeric|exists:images,id',
      'shop_id' => 'numeric'
    ]);

    $category = Category::findOrFail($id);
    $category->update($request->all());

    return response()->json(Category::with('image:id,savedFileName')->findOrFail($id), 200);
  }

  public function delete($id) {
    $category = Category::findOrFail($id);
    if($category->image != null) {
      (new ImageController())->delete($category->image->id);
    }
    $category->delete();
    return response('Deleted Successfully', 200);
  }

}

?>