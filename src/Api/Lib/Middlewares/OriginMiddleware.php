<?php
namespace App\Api\Lib\Middlewares;

use App\Api\Lib\RequestClass;
use App\Api\Lib\ResponseClass;
use App\Libs\FuncoesLib;
use App\Libs\JwtLib;
use Closure;
use Exception;

class OriginMiddleware{

    /**
     * @param RequestClass $request
     * @param Closure $next
     * @return ResponseClass
     */
    public function handle(RequestClass $request, Closure $next){

        $allowed = CONFIG_SECURITY["permission_domains"];

        if(isset($_SERVER['HTTP_ORIGIN']) && in_array($_SERVER['HTTP_ORIGIN'], $allowed)){
            throw new Exception("Origem não permitido!", 401);
        }

        return $next($request);
    }

}