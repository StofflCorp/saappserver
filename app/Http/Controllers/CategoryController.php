<?php

namespace App\Http\Controllers;

use App\Category;
use App\Product;
use App\Image;
use App\Http\Controllers\ImageController;
use Illuminate\Http\Request;

class CategoryController extends Controller {

  public function showAllCategories() {
    return response()->json(Category::with('image:id,savedFileName')->get());
  }

  public function showAllCategoriesOfType($id, Request $request) {
    if($request->has('withProducts')) {
      return response()->json(Category::where('shop_id',$id)->with(['image:id,savedFileName','products.image:id,savedFileName'])->get());
    }
    return response()->json(Category::where('shop_id',$id)->with('image:id,savedFileName')->get());
  }

  public function showOneCategory($id) {
    return response()->json(Category::with('image:id,savedFileName')->find($id));
  }

  public function showProducts($id) {
    $category = Category::findOrFail($id);
    return response()->json($category->products()->with('image:id,savedFileName','partitions')->get());
  }

  public function addProduct($id, Request $request) {
    $this->validate($request, [
      'product' => 'required|numeric|exists:products,id'
    ]);
    $category = Category::findOrFail($id);
    $category->products()->attach($request->input('product'));

    return response()->json($category->products()->with('image:id,savedFileName','partitions')->get());
  }

  public function removeProduct($category_id, $product_id) {
    $category = Category::findOrFail($category_id);
    $category->products()->detach($product_id);

    return response()->json($category->products()->with('image:id,savedFileName','partitions')->get());
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
      'image_id' => 'numeric',
      'shop_id' => 'numeric'
    ]);

    $category = Category::findOrFail($id);
    $category->update($request->except('image_id'));
    if($request->has('image_id') && ($category->image == null || $request->input('image_id') != $category->image->id)) {
      $img = Image::find($request->input('image_id'));
      if($img) {
        $category->image()->associate($img);
      }
      else {
        $category->image()->dissociate();
      }
      $category->save();
    }

    return response()->json(Category::with('image:id,savedFileName')->findOrFail($id), 200);
  }

  public function delete($id) {
    $category = Category::findOrFail($id);
    $category->delete();
    return response('Deleted Successfully', 200);
  }

  public function getAvailableMeat() {
    $shops = ['Rind' => 11, 'Kalb' => 12, 'Schwein' => 13];
    $productList = array(['status' => 'in_stock', 'stocks' => []],
      ['status' => 'running_low', 'stocks' => []],
      ['status' => 'out_of_stock', 'stocks' => []]);
    $availabilityList = array();
    foreach ($productList as $status) {
      foreach ($shops as $shop => $shop_id) {
        $status['stocks'][] = ['meatName' => $shop,
        'categories' => Category::with(['products' => function($query) use ($status) {
          return $query->where('status',$status)->select(['category_id', 'name', 'stock']);
        }])->where('shop_id', $shop_id)->select('id','name')->get()];
      }
      $availabilityList[] = $status;
      unset($shop);
      unset($shop_id);
    }
    unset($status);
    return response()->json($availabilityList);
  }

}

?>
