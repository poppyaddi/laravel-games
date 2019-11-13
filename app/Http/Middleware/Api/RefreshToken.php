<?php

namespace App\Http\Middleware\Api;

use Closure;
use Auth;
use Illuminate\Support\Facades\Cache;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Http\Middleware\BaseMiddleware;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;

class RefreshToken extends BaseMiddleware
{
    /**
     * @param $request
     * @param Closure $next
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\Response|mixed
     * @throws JWTException
     */
    public function handle($request, Closure $next)
    {
        $oldToken = $this->auth->parseToken()->setRequest($request)->getToken();
        // 检查此次请求中是否带有 token，如果没有则抛出异常。
        $this->checkForToken($request);
//         使用 try 包裹，以捕捉 token 过期所抛出的 TokenExpiredException  异常
        try {
            // 检测用户的登录状态，如果正常则通过
            if ($this->auth->parseToken()->authenticate()) {
                return $next($request);
            }
            // 没有token则返回401错误
            throw new UnauthorizedHttpException('jwt-auth', '未登录');
        } catch (TokenExpiredException $exception) {
            // 此处捕获到了 token 过期所抛出的 TokenExpiredException 异常，我们在这里需要做的是刷新该用户的 token 并将它添加到响应头中
            try {
                // 刷新用户的 token
                $token = $this->auth->refresh();
                // || 下面是新加的代码
                // 获取旧的token

                // 将旧的token作为键, 新token作为值存在缓存中
                Cache::put('token_blacklist'.$oldToken, $token, 3);
                // || 新加代码结束
                // 使用一次性登录以保证此次请求的成功
                Auth::guard('api')->onceUsingId($this->auth->manager()->getPayloadFactory()->buildClaimsCollection()->toPlainArray()['sub']);
            } catch (JWTException $exception) {
                // 如果捕获到此异常，即代表 refresh 也过期了，用户无法刷新令牌，需要重新登录。
                // 异步请求时，如果旧的token被新的token替换后延缓三分钟, 如果还是旧token, 三分钟内可通过请求
                if($token = Cache::get('token_blacklist'.$oldToken)){
//                    $request->headers->set('authorization', $token); // 好像一点也不行  $request->headers->set('Authorization','Bearer '.$token); huoxukeyi
                    return $this->setAuthenticationHeader($next($request), $token);

                }
                throw new UnauthorizedHttpException('jwt-auth', $exception->getMessage());
            }
        }

        // 在响应头中返回新的 token
        return $this->setAuthenticationHeader($next($request), $token);
    }
}
