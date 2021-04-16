<?php

namespace App\Http\Controllers;

use App\News;
use App\Image;
use App\Http\Controllers\ImageController;
use Illuminate\Http\Request;

class NewsController extends Controller {

  public function showAllNews() {
    return response()->json(News::with('author:id,prename,surname','image:id,savedFileName')->orderBy('created_at','desc')->get());
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
      'image_id' => 'numeric'
    ]);
    $news = News::findOrFail($id);
    $news->update($request->except('image_id'));

    if($request->has('image_id') && ($news->image == null || $request->input('image_id') != $news->image->id)) {
      $img = Image::find($request->input('image_id'));
      if($img) {
        $news->image()->associate($img);
      }
      else {
        $news->image()->dissociate();
      }
      $news->save();
    }

    return response()->json(News::with('author:id,prename,surname','image:id,savedFileName')->find($news->id), 200);
  }

  public function delete($id) {
    $news = News::findOrFail($id);
    $news->delete();
    return response('Deleted Successfully', 200);
  }

}

?>
