<?php


namespace App\Api\Lib\Middlewares;


use App\Api\Lib\RequestClass;
use App\Api\Lib\ResponseClass;
use Closure;
use Exception;

class MaintenanceMiddleware{

    /**
     * @param RequestClass $request
     * @param Closure $next
     * @return ResponseClass
     */
    public function handle(RequestClass $request, Closure $next){

        if (CONFIG_MAINTENANCE){
            throw new Exception("Estamos em manutenção, tente novamente mais tarde!", 200);
        }

        return $next($request);
    }

}