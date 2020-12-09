<?php

namespace App\Http\Controllers;

use App\MeatPartition;
use Illuminate\Http\Request;

class MeatPartitionController extends Controller {

  public function showAllMeatPartitions() {
    return response()->json(MeatPartition::all());
  }

  public function showOneMeatPartition($id) {
    return response()->json(MeatPartition::find($id));
  }

  public function create(Request $request) {
    $this->validate($request, [
      'name' => 'required',
      'type' => 'required|numeric',
      'partition_weight' => 'numeric'
    ]);

    $partition = MeatPartition::create($request->all());

    return response()->json($partition, 201);
  }

  public function update($id, Request $request) {
    $partition = MeatPartition::findOrFail($id);
    $this->validate($request, [
      'type' => 'numeric',
      'partition_weight' => 'numeric'
    ]);
    $partition->update($request->all());

    return response()->json($partition, 200);
  }

  public function delete($id) {
    $partition = MeatPartition::findOrFail($id);
    $partition->delete();
    return response('Deleted Successfully', 200);
  }

}

?>
