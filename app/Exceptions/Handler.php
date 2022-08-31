<?php

namespace App\Exceptions;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Validation\ValidationException;
use Laravel\Lumen\Exceptions\Handler as ExceptionHandler;
use PHPOpenSourceSaver\JWTAuth\Exceptions\TokenExpiredException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Throwable;


class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that should not be reported.
     *
     * @var array
     */
    protected $dontReport = [
        AuthorizationException::class,
        HttpException::class,
        ModelNotFoundException::class,
        ValidationException::class,
    ];

    /**
     * Report or log an exception.
     *
     * This is a great spot to send exceptions to Sentry, Bugsnag, etc.
     *
     * @param  \Throwable  $exception
     * @return void
     *
     * @throws \Exception
     */
    public function report(Throwable $exception)
    {
        parent::report($exception);
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Throwable  $exception
     * @return \Illuminate\Http\Response|\Illuminate\Http\JsonResponse
     *
     * @throws \Throwable
     */
    public function render($request, Throwable $exception)
    {
    //TokenExpiredException
        if($exception instanceof  ModelNotFoundException) {
            $exception = new NotFoundHttpException();
            return $this->fail( $exception->getMessage(), $exception->getCode());
        } else if ($exception instanceof NotFoundHttpException) {
            return $this->fail('路由未找到',104,$exception->getStatusCode());
        } else if ($exception instanceof MethodNotAllowedHttpException) {
            return $this->fail('请求方法不存在',105,$exception->getStatusCode());
        }elseif($exception instanceof TokenExpiredException){
            return $this->fail('无效的访问令牌',106,401);
        } else if ($exception instanceof UnauthorizedHttpException) { //这个在jwt.auth 中间件中抛出
            return $this->fail('无效的访问令牌',106,401);
        }elseif ($exception instanceof AuthenticationException) { //这个异常在 auth:api 中间件中抛出
            return $this->fail('无效的访问令牌',106,401);
        } elseif ($exception instanceof \Symfony\Component\HttpKernel\Exception\HttpException &&
            $exception->getStatusCode() == 403){
            return $this->fail('没有访问权限，请联系管理员',107,$exception->getStatusCode());
        }
        //业务异常
        if($exception instanceof  BusinessException) {
            return $this->fail( $exception->getMessage(), $exception->getCode(), 200);
        }

        return parent::render($request, $exception);
    }

    public function fail($message='error',$code=500,$http=500)
    {
        return response()->json([
            'success' => false,
            'data' =>[],
            'errorCode' => $code,
            'errorMessage' =>$message],$http);
    }
}
