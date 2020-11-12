<?php

namespace App\Http\Middleware;

use Closure;
use Exception;
use App\Employee;
use Firebase\JWT\JWT;
use Firebase\JWT\ExpiredException;

class JwtEmpMiddleware {

  public function handle($request, Closure $next, $guard=null) {
    $token = $request->get('token');

    if($request->header('Authorization')) {
      $key = explode(' ', $request->header('Authorization'));
      if($key[1]) {
        $token = $key[1];
      }
    }

    if(!$token) {
      //Unauthorized response if no token available
      return response()->json([
        'error' => 'Token not provided.'
      ], 400);
    }

    try {
      $credentials = JWT::decode($token, env('JWT_SECRET'), ['HS256']);
    }
    catch (ExpiredException $e) {
      return response()->json([
        'error' => 'An error occurred while decoding token.',
        'expired' => true
      ], 400);
    }

    $employee = Employee::find($credentials->sub);
    if(!$employee) {
      return response()->json([
        'error' => 'You must be an employee to view this!'
      ], 400);
    }

    //Put user in request
    $request->auth = $employee;

    return $next($request);

  }

}

?>
