<?php

require_once __DIR__.'/../vendor/autoload.php';

(new Laravel\Lumen\Bootstrap\LoadEnvironmentVariables(
    dirname(__DIR__)
))->bootstrap();

date_default_timezone_set(env('APP_TIMEZONE', 'UTC'));

/*
|--------------------------------------------------------------------------
| Create The Application
|--------------------------------------------------------------------------
|
| Here we will load the environment and create the application instance
| that serves as the central piece of this framework. We'll use this
| application as an "IoC" container and router for this framework.
|
*/

$app = new Laravel\Lumen\Application(
    dirname(__DIR__)
);

// $app->withFacades();

// $app->withEloquent();

/*
|--------------------------------------------------------------------------
| Register Container Bindings
|--------------------------------------------------------------------------
|
| Now we will register a few bindings in the service container. We will
| register the exception handler and the console kernel. You may add
| your own bindings here if you like or you can make another file.
|
*/

$app->singleton(
    Illuminate\Contracts\Debug\ExceptionHandler::class,
    App\Exceptions\Handler::class
);

$app->singleton(
    Illuminate\Contracts\Console\Kernel::class,
    App\Console\Kernel::class
);

/*
|--------------------------------------------------------------------------
| Register Config Files
|--------------------------------------------------------------------------
|
| Now we will register the "app" configuration file. If the file exists in
| your configuration directory it will be loaded; otherwise, we'll load
| the default version. You may register other files below as needed.
|
*/

$app->configure('app');
$app->configure('auth');
$app->configure('amqp');
$app->configure('debugbar');
//$app->configure('cors');
$app->configure('jwt');
/*
|--------------------------------------------------------------------------
| Register Middleware
|--------------------------------------------------------------------------
|
| Next, we will register the middleware with the application. These can
| be global middleware that run before and after each request into a
| route or middleware that'll be assigned to some specific routes.
|
*/

 $app->middleware([
    // Fruitcake\Cors\HandleCors::class,
     App\Http\Middleware\CorsMiddleware::class
 ]);

 $app->routeMiddleware([
      'auth' => App\Http\Middleware\Authenticate::class,
      'checkIfLandlord' => \App\Http\Middleware\CheckIfLandlord::class,
      'checkHasIdentity' => \App\Http\Middleware\CheckHasIdentity::class,
 ]);

/*
|--------------------------------------------------------------------------
| Register Service Providers
|--------------------------------------------------------------------------
|
| Here we will register all of the application's service providers which
| are used to bind services into the container. Service providers are
| totally optional, so you are not required to uncomment this line.
|
*/

$app->register(App\Providers\AppServiceProvider::class);
$app->register(App\Providers\EventServiceProvider::class);
$app->register(App\Providers\AuthServiceProvider::class); //auth
$app->register(Hhxsv5\LaravelS\Illuminate\LaravelSServiceProvider::class); //laravels 开启

$app->register(Pearl\RequestValidate\RequestServiceProvider::class); //请求参数验证

//ide helper
$app->register(Barryvdh\LaravelIdeHelper\IdeHelperServiceProvider::class); //ideHelper
//$app->register(Tymon\JWTAuth\Providers\LumenServiceProvider::class); // 启用jwt
$app->register(PHPOpenSourceSaver\JWTAuth\Providers\LumenServiceProvider::class);
$app->register(Jenssegers\Mongodb\MongodbServiceProvider::class); //mongodb
$app->register(Illuminate\Redis\RedisServiceProvider::class); //redis
$app->register(\Vinhson\LumenGeneratorServiceProvider::class);
$app->register(Matchory\Elasticsearch\ElasticsearchServiceProvider::class);  //es
// mq注册
//$app->register(VladimirYuldashev\LaravelQueueRabbitMQ\LaravelQueueRabbitMQServiceProvider::class);
$app->register(Bschmitt\Amqp\LumenServiceProvider::class);

//$app->register(Fruitcake\Cors\CorsServiceProvider::class);

if (env('APP_DEBUG')) {
    $app->register(Barryvdh\Debugbar\LumenServiceProvider::class);
}
$app->withFacades(true, [
    \Illuminate\Support\Facades\Event::class => 'LumenEvent',
    Toplan\PhpSms\Facades\Sms::class => 'PhpSms',
 //   Bschmitt\Amqp\Facades\Amqp::class => 'Amqp',
]);
$app->withEloquent();

/*
|--------------------------------------------------------------------------
| Load The Application Routes
|--------------------------------------------------------------------------
|
| Next we will include the routes file so that they can all be added to
| the application. This will provide all of the URLs the application
| can respond to, as well as the controllers that may handle them.
|
*/


$app->router->group([
    'namespace' => 'App\Http\Controllers',
], function ($router) {
    require __DIR__.'/../routes/web.php';
    require __DIR__.'/../routes/mobile.php';
    require __DIR__.'/../routes/websocket.php';
});



return $app;
