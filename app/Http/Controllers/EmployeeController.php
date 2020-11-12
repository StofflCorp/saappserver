<?php

namespace App\Http\Controllers;

use App\Employee;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class EmployeeController extends Controller {

  public function showAllEmployees() {
    return response()->json(Employee::all());
  }

  public function showOneEmployee($id) {
    return response()->json(Employee::find($id));
  }

  public function update($id, Request $request) {
    $employee = Employee::findOrFail($id);

    $requestData = $request->all();
    if($request->has('password')) {
      $requestData['password'] = Hash::make($requestData['password']);
    }

    $employee->update($requestData);

    return response()->json($employee, 200);
  }

  public function delete($id) {
    Employee::findOrFail($id)->delete();
    return response('Deleted Successfully', 200);
  }

}

?>
