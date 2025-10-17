<?php

namespace App\Api\Routers;


use App\Api\Controllers\TelegramApiController;
use App\Api\Lib\ResponseClass;
use App\Api\Lib\RouterClass;

class TelegramRouter
{

    public static function start(RouterClass $router): RouterClass
    {
        $router->setGroup('/telegram');

        $router->post('/send', [
            function ($request) {
                return new ResponseClass(200, (new TelegramApiController())->sendMessage($request));
            }
        ]);

        return $router;
    }
}
