<?php

namespace App\Http\Controllers;

use App\Joke;
use Illuminate\Http\Request;

class JokeController extends Controller {

  public function showAllJokes() {
    return response()->json(Joke::all());
  }

  public function showOneJoke($id) {
    return response()->json(Joke::find($id));
  }

  public function create(Request $request) {
    $this->validate($request, [
      'content' => 'required'
    ]);

    $joke = Joke::create($request->all());

    return response()->json($joke, 201);
  }

  public function update($id, Request $request) {
    $joke = Joke::findOrFail($id);
    $joke->update($request->all());

    return response()->json($joke, 200);
  }

  public function delete($id) {
    Joke::findOrFail($id)->delete();
    return response('Deleted Successfully', 200);
  }

}

?>
