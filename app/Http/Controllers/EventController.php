<?php

namespace App\Http\Controllers;

use App\Event;
use App\ImageController;
use Illuminate\Http\Request;

class EventController extends Controller {

  public function showAllEvents() {
    return response()->json(Event::all());
  }

  public function showOneEvent($id) {
    return response()->json(Event::find($id));
  }

  public function create(Request $request) {
    $this->validate($request, [
      'name' => 'required',
      'description' => 'required',
      'location' => 'required',
      'day' => 'required|date',
      'startTime' => 'required|date_format:G:i',
      'endTime' => 'required|date_format:G:i|after:startTime',
      'image_id' => 'numeric|exists:images,id',
      'image' => 'image',
      'image_name' => 'required_with:image'
    ]);

    $event = Event::create($request->all());

    if($request->hasFile('image')) {
      $createdImg = ImageController::createImage($request->file('image'), $request->input('image_name'));
      if($createdImg->success == 1) {
        $event->image()->associate($createdImg->data->image->id);
        $event->save();
      }
    }
    $event = Event::with('image:id,savedFileName')->find($event->id);

    return response()->json($event, 201);
  }

  public function update($id, Request $request) {
    $this->validate($request, [
      'day' => 'date',
      'startTime' => 'date_format:G:i',
      'endTime' => 'date_format:G:i|after:startTime',
      'image_id' => 'numeric|exists:images,id'
    ]);
    $event = Event::findOrFail($id);
    $event->update($request->all());

    return response()->json(Event::with('image:id,savedFileName')->find($event->id), 200);
  }

  public function delete($id) {
    $event = Event::findOrFail($id);
    $event->delete();
    return response('Deleted Successfully', 200);
  }

}

?>
