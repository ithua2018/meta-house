<?php
/** @var \Laravel\Lumen\Routing\Router $router */

//手机端API路由群组
$router->group(['prefix' => 'm'], function () use ($router) {
    $router->post('auth/captcha', 'Mobile\AuthController@regCaptcha'); //验证码
    //登录 | 退出
    $router->post('me', 'Mobile\AuthController@regLog');
    $router->delete('me', 'Mobile\AuthController@logout');
    $router->get('stores', 'Mobile\AuthController@stores');
    //用户组
    $router->group(['middleware' => 'auth'], function () use ($router) {
        $router->patch('user/regPwd', 'Mobile\UserController@regPwd');
        $router->patch('user/resetPwd', 'Mobile\UserController@resetPwd');
        //用户身份
        $router->get('identities','Mobile\IdentityController@list');
        $router->post('identity','Mobile\IdentityController@checked');
        $router->post('upload', 'Mobile\UploadController@store');
        //城市
        $router->get('area/cities', 'Mobile\AreaController@getCities');
        //商圈或者地铁
        $router->get('area/bdsw', 'Mobile\AreaController@getBdSw');
        //检查是否选择了身份
        $router->group(['middleware' => 'checkHasIdentity'],function()use($router) {
            $router->get('me', 'Mobile\UserController@show');
            $router->patch('me', 'Mobile\UserController@update');
            $router->get('me/houses',  'Mobile\MyHouseController@index');
            $router->get('me/house/{id}',  'Mobile\MyHouseController@show');
            $router->post('me/house',  'Mobile\MyHouseController@store');
            $router->put('me/house/{id}',  'Mobile\MyHouseController@update');
            $router->delete('me/house/{id}',  'Mobile\MyHouseController@destory');
            // Search routes
            $router->group(['prefix' => 'search'], static function (): void {
                Route::get('areas','Mobile\Search\AreaSearchController@index');
                Route::get('houses-match','Mobile\Search\HousesSearchController@index');
                Route::get('houses-nearby','Mobile\Search\HousesSearchController@nearby');
            });
            //聊天
            $router->group(['prefix' => 'chat'], static function (): void {
                //用户对话列表
                Route::get('list','Mobile\TalkController@list');
                //用户聊天记录
                Route::get('records','Mobile\TalkController@getChatRecords');
                //发送图片
                Route::post('send-image','Mobile\TalkController@sendImage');
            });

        });
        $router->get('equipments', 'Mobile\MyHouseController@equipments');

    });



});

$router->get('chargePay','Mobile\BrotherPayController@charge');
