<?php
/** @var \Laravel\Lumen\Routing\Router $router */

$router->group(['middleware' => 'auth'],function()use($router) {
    $router->get('/ws', function () {
        // Respond any content with status code 200
        return 'websocket';
    });
});
