<?php

namespace App\Http\Middleware;

use Closure;

class Cors
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $response = $next($request);
        /**
         *

        $origin = $request->server('HTTP_ORIGIN') ? $request->server('HTTP_ORIGIN') : '';
//        $allow_origin = [
//            'http://localhost:8080',
//        ];
//        if (in_array($origin, $allow_origin)) {
            $response->header('Access-Control-Allow-Origin', $origin);
            $response->header('Access-Control-Allow-Headers', 'Origin, Content-Type, Cookie, X-CSRF-TOKEN, Accept, Authorization, X-XSRF-TOKEN');
            $response->header('Access-Control-Expose-Headers', 'Authorization, authenticated');
            $response->header('Access-Control-Allow-Methods', 'GET, POST, PATCH, PUT, OPTIONS, DELETE');
            $response->header('Access-Control-Allow-Credentials', 'true');
//        }
         * */


        $IlluminateResponse = 'Illuminate\Http\Response';
        $SymfonyResopnse = 'Symfony\Component\HttpFoundation\Response';
        $headers = [
            'Access-Control-Allow-Origin' => '*',
            'Access-Control-Allow-Methods' => 'POST, GET, OPTIONS, PUT, PATCH, DELETE',
            'Access-Control-Allow-Headers' => 'Access-Control-Allow-Headers, Origin,Accept, X-Requested-With, Content-Type, Access-Control-Request-Method, Authorization , Access-Control-Request-Headers, X-CSRF-TOKEN'
        ];
        if ($response instanceof $IlluminateResponse) {
            foreach ($headers as $key => $value) {
                $response->header($key, $value);
            }
            return $response;
        }

        if ($response instanceof $SymfonyResopnse) {
            foreach ($headers as $key => $value) {
                $response->headers->set($key, $value);
            }
            return $response;
        }
        return $response;

//        if ($response instanceof  Response) {
//            return $next($request)->header('Access-Control-Allow-Origin', '*')
//                ->header('Access-Control-Allow-Methods', 'GET,POST,PUT,OPTIONS,PATCH,DELETE,HEAD')
//                ->header('Access-Control-Allow-Headers', 'x-csrf-token,x-requested-with,content-type');
//        }
//
//        //  如果是实列 \Symfony\Component\HttpFoundation\Response::class
//        $response->headers->set('Access-Control-Allow-Origin', '*');
//        $response->headers->set('Access-Control-Allow-Methods', 'GET,POST,PUT,OPTIONS,PATCH,DELETE,HEAD');
//        $response->headers->set('Access-Control-Allow-Headers', 'x-csrf-token,x-requested-with,content-type');
//        return $response;
    }
}
