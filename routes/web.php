<?php

/** @var \Laravel\Lumen\Routing\Router $router */

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
$router->group(['prefix' => 'w'], function () use ($router) {
    $router->post('/goods', 'Web/GoodsController@create');
    $router->get('/addresses', 'Web\HousesController@list');
    $router->post('/address', 'Web\HousesController@store');
    $router->get('/user/{uuid}', 'Web\HousesController@getUserInfo');
    $router->post('/upload', 'Web\UploadController@store');
});

$router->get('/posts', 'ExampleController@store_with_mq');
$router->get('/test', 'ExampleController@test');

