<?php

namespace App\Http\Controllers;

use Validator;
use App\User;
use App\Employee;
use App\Mail\AccountCreated;
use Firebase\JWT\JWT;
use Illuminate\Http\Request;
use Firebase\JWT\ExpiredException;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\DB;
use Laravel\Lumen\Routing\Controller as BaseController;

class AuthController extends BaseController {

  /**
   * The request instance.
   *
   * @var \Illuminate\Http\Request
   */
  private $request;

  /**
   * Create a new controller instance.
   *
   * @param \Illuminate\Http\Request $request
   * @return void
   */
  public function __construct(Request $request) {
    $this->request = $request;
  }

  /**
   * Create a new refresh token.
   *
   * @param $request
   * @return string
   */
   public function refreshToken(Request $request) {
     $this->validate($request, [
       'token' => 'required',
       'id' => 'required|numeric'
     ]);

     $user_id = $request->input('id');
     $user_token = $request->input('token');

     $decodedToken = null;
     try {
       $decodedToken = JWT::decode($user_token, env('JWT_SECRET'), array('HS256'));
     }
     catch(ExpiredException $ex) {
       list($header, $payload, $signature) = explode('.', $user_token);
       $payload = base64_decode($payload);
       $decodedToken = json_decode($payload);
     }

     if($decodedToken->sub != $user_id) {
       return response()->json([
         'error' => 'Invalid token.'
       ],400);
     }

     $payload = [
        'iss' => '',      // issuer of the token
        'sub' => $user_id, // subject of the token
        'iat' => time(),    // time when jwt was issued
        'exp' => time() + 60*60*24*7   // expiration time --> 1 week
     ];

     return response()->json([
         'token' => JWT::encode($payload, env('JWT_SECRET')),
         'id' => $user_id
     ],200);
   }

  /**
   * Create a new user token.
   *
   * @param \App\User $user
   * @return string
   */
   public function jwt(User $user) {
     $payload = [
        'iss' => '',      // issuer of the token
        'sub' => $user->id, // subject of the token
        'iat' => time(),    // time when jwt was issued
        'exp' => time() + 60*60*24*7   // expiration time
     ];

     return JWT::encode($payload, env('JWT_SECRET'));
   }

   /**
    * Create a new employee token.
    *
    * @param \App\Employee $employee
    * @return string
    */
    public function jwtEmp(Employee $employee) {
      $payload = [
         'iss' => '',      // issuer of the token
         'sub' => $employee->id, // subject of the token
         'iat' => time(),    // time when jwt was issued
         'exp' => time() + 60*60*24   // expiration time
      ];

      return JWT::encode($payload, env('JWT_SECRET'));
    }

   /**
    * Authenticate a user and return the token if the provided credentials are correct
    *
    * @param \App\User $user
    * @return mixed
    */
    public function authenticate(User $user) {
      $this->validate($this->request, [
          'email' => 'required|email',
          'password' => 'required'
      ]);

      //Find the user per mail
      $user = User::where('email', $this->request->input('email'))->first();

      if(!$user) {
        return response()->json([
          'error' => 'Email does not exist.'
        ],400);
      }

      //Verify the password and generate the token
      if(Hash::check($this->request->input('password'), $user->password)) {
        return response()->json([
            'token' => $this->jwt($user),
            'id' => $user->id
        ],200);
      }

      //Bad Request response
      return response()->json([
        'error' => 'Email or password is wrong.'
      ], 400);
    }

    /**
     * Authenticate an employee and return the token if the provided credentials are correct
     *
     * @param \App\Employee $employee
     * @return mixed
     */
     public function authenticateEmp(Employee $employee) {
       $this->validate($this->request, [
           'email' => 'required|email',
           'password' => 'required'
       ]);

       //Find the user per mail
       $employee = Employee::where('email', $this->request->input('email'))->first();

       if(!$employee) {
         return response()->json([
           'error' => 'Email does not exist.'
         ],400);
       }

       //Verify the password and generate the token
       if(Hash::check($this->request->input('password'), $employee->password)) {
         return response()->json([
             'token' => $this->jwtEmp($employee),
             'id' => $employee->id
         ],200);
       }

       //Bad Request response
       return response()->json([
         'error' => 'Email or password is wrong.'
       ], 400);
     }

     /**
      * Create an Employee
      *
      * @param Illuminate\Http\Request $request
      * @return mixed
      */
     public function createEmployee(Request $request) {
       $this->validate($request, [
         'prename' => 'required',
         'surname' => 'required',
         'email' => 'required|email:rfc,dns|unique:employees,email',
         'password' => 'required'
       ]);

       $requestData = $request->all();

       $employee = Employee::create([
         'prename' => $requestData['prename'],
         'surname' => $requestData['surname'],
         'email' => $requestData['email'],
         'password' => Hash::make($requestData['password'])
       ]);

       return response()->json(['employee' => $employee, 'token' => $this->jwtEmp($employee)], 201);
     }

     /**
      * Create a User
      *
      * @param Illuminate\Http\Request $request
      * @return mixed
      */
     public function createUser(Request $request) {
       $this->validate($request, [
         'prename' => 'required',
         'surname' => 'required',
         'email' => 'required|email:rfc,dns|unique:App\User,email',
         'password' => 'required'
       ]);

       $requestData = $request->all();

       $user = null;
       $requestToken = null;
       DB::transaction(function() use ($requestData) {
         $user = User::create([
           'prename' => $requestData['prename'],
           'surname' => $requestData['surname'],
           'email' => $requestData['email'],
           'password' => Hash::make($requestData['password']),
           'temp_hash' => Hash::make(Str::random(32))
         ]);

         $requestToken = $this->jwt($user);
         Mail::to($user->email)->send(new AccountCreated($user, $requestToken));
       });
       return response()->json(['user' => $user, 'token' => $requestToken], 201);
     }

     public function verifyMail($user_id, Request $request) {
       $this->validate($request, [
         'hash' => 'required'
       ]);
       $user = User::findOrFail($user_id);
       if(!empty($user->email_verified_at)) {
         return view('mail.verified', ['term' => 'bereits']);
       }
       if(!empty($user->temp_hash) && $user->temp_hash == $request->input('hash')) {
         $user->update(['temp_hash' => '', 'email_verified_at' => DB::raw('now()')]);
         return view('mail.verified', ['term' => 'erfolgreich']);
       }
       else {
         return view('mail.error');
       }

       return view('mail.verified');

     }

}

?>
