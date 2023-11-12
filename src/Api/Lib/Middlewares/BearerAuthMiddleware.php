<?php
namespace App\Api\Lib\Middlewares;

use App\Api\Lib\RequestClass;
use App\Api\Lib\ResponseClass;
use App\Libs\JwtLib;
use Closure;
use Exception;

class BearerAuthMiddleware{

    /**
     * @param RequestClass $request
     * @param Closure $next
     * @return ResponseClass
     */
    public function handle(RequestClass $request, Closure $next){

        if (!JwtLib::verifyTokenJWT()){
            throw new Exception("Token inválido!", 401);
        }

        return $next($request);
    }

}