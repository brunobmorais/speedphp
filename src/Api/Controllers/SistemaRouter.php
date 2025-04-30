<?php

namespace App\Api\Controllers;

use App\Api\Lib\ResponseClass;
use App\Api\Lib\RouterClass;

class SistemaRouter
{

    public static function start(RouterClass $router): RouterClass
    {

        $router->post('/sistema/component', [
            function ($request) {
                return new ResponseClass(200, (new SistemaApiController())->component($request));
            }
        ]);

        return $router;
    }
}
