<?php

namespace App\Http\Controllers;

use Log;
use App\Image;
use Illuminate\Http\Request;

class ImageController extends Controller {

  public static $dirPath = 'storage/images/';

  public function ShowAllImages() {
    return response()->json(Image::all());
  }

  public function ShowOneImage($id) {
    return response()->json(Image::find($id));
  }

  public function uploadImage(Request $request) {
    $this->validate($request, [
      'image' => 'required|image',
      'name' => 'required'
    ]);

    if($request->hasFile('image')) {
      $createdImg = self::createImage($request->file('image'), $request->input('name'), $request->input('originalFileName'));

      if($createdImg->success == 1) {
        return $this->responseSuccess($createdImg->data);
      }
      else {
        return $this->responseError('Cannot upload file');
      }
    }
    else {
      return $this->responseError('File not found');
    }
  }

  public static function createImage($image, $imgName, $imgFileName = null) {
    $original_filename = $imgFileName ?? $image->getClientOriginalName();
    Log::info('Creating image: ' . $imgName . ' - ' . $original_filename);
    $original_filename_arr = explode('.', $original_filename);
    $file_ext = end($original_filename_arr);
    $destination_path = self::$dirPath;
    $savedName = 'I-' . time() . '.' . $file_ext;

    if($image->move($destination_path, $savedName)) {
      $dbImg = Image::create([
        'name' => $imgName,
        'originalFileName' => $original_filename,
        'savedFileName' => $savedName
      ]);

      $fullPath = self::$dirPath . $savedName;
      return (object)['success' => 1, 'data' => (object)['fullPath' => $fullPath, 'image' => $dbImg]];
    }
    else {
      return (object)['success' => 0];
    }
  }

  protected function responseSuccess($ret) {
    return response()->json(['status' => 'success', 'data' => $ret], 200)
      ->header('Access-Control-Allow-Origin', '*')
      ->header('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS');
  }

  protected function responseError($message = 'Bad request', $statusCode = 200) {
    return response()->json(['status' => 'error', 'error' => $message], $statusCode)
      ->header('Access-Control-Allow-Origin', '*')
      ->header('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS');
  }

  public function delete($id) {
    $image = Image::findOrFail($id);

    $fullPath = self::$dirPath . $image->savedFileName;

    $refDeleted = file_exists($fullPath) && is_writable($fullPath) && unlink(realpath($fullPath));

    $image->delete();

    return response(['message' => 'Deleted Successfully', 'refDeleted' => $refDeleted], 200);
  }

}

?>
