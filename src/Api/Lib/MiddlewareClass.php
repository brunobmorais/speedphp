<?php


namespace App\Api\Lib;

use Closure;
use Exception;

class MiddlewareClass
{
    /**
     * @var array
     */
    private $middlewares = [];

    /**
     * @var array
     */
    private static $map = [];

    /**
     * @var array
     */
    private static $default = [];

    /**
     * @var Closure
     */
    private $controller;

    /**
     * @var array
     */
    private $controllerArgs = [];

    /**
     * Middleware constructor.
     * @param array $middlewares
     * @param Closure $controller
     * @param array $controllerArgs
     */
    public function __construct($middlewares, $controller, $controllerArgs){
        $this->middlewares = array_merge(self::$default,$middlewares);
        $this->controller = $controller;
        $this->controllerArgs = $controllerArgs;
    }

    /**
     * @param RequestClass $request
     * @return ResponseClass
     */
    public function next($request){

        if (empty($this->middlewares))
            return call_user_func_array($this->controller,$this->controllerArgs);

        $middleware = array_shift($this->middlewares);

        if (!isset(self::$map[$middleware])){
            throw new Exception("Erro ao processar middleware", 500);        }

        $myClass = $this;
        $next = function ($request) use ($myClass){
            return $myClass->next($request);
        };

        return (new self::$map[$middleware])->handle($request,$next);
    }

    /**
     * @param array $map
     */
    public static function setMap($map){
        self::$map = $map;
    }

    public static function setDefault($default)
    {
        self::$default = $default;
    }


}