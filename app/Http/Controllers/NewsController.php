<?php

namespace App\Http\Controllers;

use App\News;
use App\Http\Controllers\ImageController;
use Illuminate\Http\Request;

class NewsController extends Controller {

  public function showAllNews() {
    return response()->json(News::with('author:id,prename,surname','image:id,savedFileName')->get());
  }

  public function showOneNews($id) {
    return response()->json(News::with('author:id,prename,surname','image:id,savedFileName')->find($id));
  }

  public function create(Request $request) {
    $this->validate($request, [
      'name' => 'required',
      'description' => 'required',
      'author' => 'required|numeric|exists:employees,id',
      'image_id' => 'numeric|exists:images,id',
      'image' => 'image',
      'image_name' => 'required_with:image'
    ]);

    $news = News::create($request->all());

    if($request->hasFile('image')) {
      $createdImg = ImageController::createImage($request->file('image'), $request->input('image_name'));
      if($createdImg->success == 1) {
        $news->image()->associate($createdImg->data->image->id);
        $news->save();
        $news = News::with('image:id,savedFileName')->find($news->id);
      }
    }
    $news = News::with('author:id,prename,surname','image:id,savedFileName')->find($news->id);

    return response()->json($news, 201);
  }

  public function update($id, Request $request) {
    $this->validate($request, [
      'author' => 'numeric|exists:employees,id',
      'image_id' => 'numeric|exists:images,id'
    ]);
    $news = News::findOrFail($id);
    $news->update($request->all());

    return response()->json(News::with('author:id,prename,surname','image:id,savedFileName')->find($news->id), 200);
  }

  public function delete($id) {
    $news = News::findOrFail($id);
    if($news->image != null) {
      (new ImageController())->delete($news->image->id);
    }
    $news->delete();
    return response('Deleted Successfully', 200);
  }

}

?>
