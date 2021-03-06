<?php

namespace App\Http\Middleware;

use Closure;
use Exception;
use App\User;
use App\Employee;
use Firebase\JWT\JWT;
use Firebase\JWT\ExpiredException;

class JwtMiddleware {

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
      ], 401);
    }

    try {
      $credentials = JWT::decode($token, env('JWT_SECRET'), ['HS256']);
    }
    catch (ExpiredException $e) {
      return response()->json([
        'error' => 'An error occurred while decoding token.',
        'expired' => true
      ], 401);
    }

    $user = User::find($credentials->sub);
    $isUser = true;
    if(!$user) {
      $user = Employee::find($credentials->sub);
      $isUser = false;
      if(!$user) {
        return response()->json([
          'error' => 'You must be a registered user to view this!'
        ], 401);
      }
    }

    if(($isUser && empty($user->email_verified_at)) && !$request->is('r/api/users/*/verifyMail')) {
      return response()->json([
        'error' => 'You need to verify your email first!'
      ], 401);
    }

    //Put user in request
    $request->auth = $user;

    return $next($request);

  }

}

?>
