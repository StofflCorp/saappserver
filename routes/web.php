<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It is a breeze. Simply tell Lumen the URIs it should respond to
| and give it the Closure to call when that URI is requested.
|
*/

$router->get('/', function () use ($router) {
    return $router->app->version();
});

$router->post('r/auth/login', ['uses' => 'AuthController@authenticate']);
$router->post('r/auth/loginEmp', ['uses' => 'AuthController@authenticateEmp']);
$router->post('r/auth/refreshToken', ['uses' => 'AuthController@refreshToken']);

$router->group(['prefix' => 'r/api'], function() use ($router) {

  $router->post('users', ['uses' => 'AuthController@createUser']);
  $router->post('employees', ['uses' => 'AuthController@createEmployee']);

  $router->get('news', ['uses' => 'NewsController@showAllNews']);
  $router->get('news/{id}', ['uses' => 'NewsController@showOneNews']);
  $router->get('events', ['uses' => 'EventController@showAllEvents']);
  $router->get('events/{id}', ['uses' => 'EventController@showOneEvent']);
  $router->get('jokes', ['uses' => 'JokeController@showAllJokes']);
  $router->get('jokes/{id}', ['uses' => 'JokeController@showOneJoke']);

  $router->group(['middleware' => 'jwt.auth'], function() use ($router) {
    $router->get('users/{id}', ['uses' => 'UserController@showOneUser']);
    $router->get('users/{id}/events', ['uses' => 'UserController@showEvents']);
    $router->post('users/{id}/events', ['uses' => 'UserController@addEvent']);
    $router->delete('users/{user_id}/events/{event_id}', ['uses' => 'UserController@removeEvent']);
    $router->get('users/{id}/jokes', ['uses' => 'UserController@showJokes']);
    $router->post('users/{id}/jokes', ['uses' => 'UserController@addJoke']);
    $router->delete('users/{user_id}/jokes/{joke_id}', ['uses' => 'UserController@removeJoke']);
    $router->get('users/{id}/shoppingCart', ['uses' => 'UserController@showShoppingCartProducts']);
    $router->post('users/{id}/shoppingCart', ['uses' => 'UserController@addShoppingCartProduct']);
    $router->put('users/{user_id}/shoppingCart/{product_id}', ['uses' => 'UserController@changeShoppingCartProductQuantity']);
    $router->delete('users/{user_id}/shoppingCart/{product_id}', ['uses' => 'UserController@removeShoppingCartProduct']);
    $router->get('users/{user_id}/orders', ['uses' => 'UserController@showOrders']);
    $router->get('users/{user_id}/preparingOrders', ['uses' => 'UserController@showPreparingOrders']);
    $router->get('users/{user_id}/latestOrders', ['uses' => 'UserController@showLatestOrders']);
    $router->post('users/{user_id}/shoppingCart/order', ['uses' => 'UserController@order']);
    $router->get('users/{user_id}/statistics', ['uses' => 'UserController@showStatistics']);

    $router->get('products/{id}', ['uses' => 'ProductController@showOneProduct']);

    $router->get('categories', ['uses' => 'CategoryController@showAllCategories']);
    $router->get('categories/shop/{id}', ['uses' => 'CategoryController@showAllCategoriesOfType']);
    $router->get('categories/{id}', ['uses' => 'CategoryController@showOneCategory']);
    $router->get('categories/{id}/products', ['uses' => 'CategoryController@showProducts']);

    $router->get('orders/{id}/products', ['uses' => 'OrderController@showProducts']);

  });

  $router->group(['middleware' => 'jwt.emp.auth'], function() use ($router) {
    $router->get('employees', ['uses' => 'EmployeeController@showAllEmployees']);
    $router->get('employees/{id}', ['uses' => 'EmployeeController@showOneEmployee']);
    $router->delete('employees/{id}', ['uses' => 'EmployeeController@delete']);
    $router->put('employees/{id}', ['uses' => 'EmployeeController@update']);

    $router->get('users', ['uses' => 'UserController@showAllUsers']);
    $router->delete('users/{id}', ['uses' => 'UserController@delete']);
    $router->put('users/{id}', ['uses' => 'UserController@update']);

    $router->delete('news/{id}', ['uses' => 'NewsController@delete']);
    $router->put('news/{id}', ['uses' => 'NewsController@update']);
    $router->post('news', ['uses' => 'NewsController@create']);

    $router->delete('events/{id}', ['uses' => 'EventController@delete']);
    $router->put('events/{id}', ['uses' => 'EventController@update']);
    $router->post('events', ['uses' => 'EventController@create']);

    $router->delete('jokes/{id}', ['uses' => 'JokeController@delete']);
    $router->put('jokes/{id}', ['uses' => 'JokeController@update']);
    $router->post('jokes', ['uses' => 'JokeController@create']);

    $router->delete('categories/{id}', ['uses' => 'CategoryController@delete']);
    //$router->delete('categories/{category_id}/products/{product_id}', ['uses' => 'CategoryController@removeProduct']);
    $router->put('categories/{id}', ['uses' => 'CategoryController@update']);
    $router->post('categories', ['uses' => 'CategoryController@create']);
    $router->post('categories/{id}/products', ['uses' => 'CategoryController@addProduct']);

    $router->get('products', ['uses' => 'ProductController@showAllProducts']);
    $router->post('products', ['uses' => 'ProductController@create']);
    $router->put('products/{id}', ['uses' => 'ProductController@update']);
    $router->delete('products/{id}', ['uses' => 'ProductController@delete']);
    $router->post('products/{id}/partitions', ['uses' => 'ProductController@addMeatPartition']);
    $router->delete('products/{product_id}/partitions/{partition_id}', ['uses' => 'ProductController@removeMeatPartition']);

    $router->get('meatPartitions', ['uses' => 'MeatPartitionController@showAllMeatPartitions']);
    $router->get('meatPartitions/{id}', ['uses' => 'MeatPartitionController@showOneMeatPartition']);
    $router->post('meatPartitions', ['uses' => 'MeatPartitionController@create']);
    $router->put('meatPartitions/{id}', ['uses' => 'MeatPartitionController@update']);
    $router->delete('meatPartitions/{id}', ['uses' => 'MeatPartitionController@delete']);

    $router->get('orders', ['uses' => 'OrderController@showAllOrders']);
    $router->get('orders/{id}', ['uses' => 'OrderController@showOneOrder']);
    $router->delete('orders/{id}', ['uses' => 'OrderController@delete']);
    $router->post('orders/{id}/products', ['uses' => 'OrderController@addProduct']);
    $router->put('orders/{id}', ['uses' => 'OrderController@update']);
    $router->put('orders/{order_id}/products/{product_id}', ['uses' => 'OrderController@changeProductAmount']);
    $router->delete('orders/{order_id}/products/{product_id}', ['uses' => 'OrderController@removeProduct']);
    $router->post('orders', ['uses' => 'OrderController@create']);

    //Image Handling
    $router->get('images', ['uses' => 'ImageController@ShowAllImages']);
    $router->get('images/{id}', ['uses' => 'ImageController@ShowOneImage']);
    $router->post('images', ['uses' => 'ImageController@uploadImage']);
    $router->delete('images/{id}', ['uses' => 'ImageController@delete']);
  });

});
