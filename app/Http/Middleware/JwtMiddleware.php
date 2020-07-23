<?php

namespace App\Http\Middleware;

use App\ApiCode;
use Closure;
use Exception;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Tymon\JWTAuth\Exceptions\TokenInvalidException;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Http\Middleware\BaseMiddleware;
use MarcinOrlowski\ResponseBuilder\ResponseBuilder as RB;

class JwtMiddleware extends BaseMiddleware
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
        try {
            $user = JWTAuth::parseToken()->authenticate();
        } catch (Exception $e) {
            if ($e instanceof TokenInvalidException){
                return RB::error(
                    ApiCode::CLIENT_UNAUTHORIZED,
                    ['Token is Invalid'],
                    ['error_message'=>trans('api.auth.token_invalid')]
                );
            }else if ($e instanceof TokenExpiredException){
                return RB::error(
                    ApiCode::CLIENT_UNAUTHORIZED,
                    ['Token is Expired'],
                    ['error_message'=>trans('api.auth.token_expired')]
                );
            }else{
                return RB::error(
                    ApiCode::CLIENT_UNAUTHORIZED,
                    ['Authorization Token not found'],
                    ['error_message'=>trans('api.auth.token_not_found')]
                );
            }
        }
        return $next($request);
    }

}
