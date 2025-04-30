<?php

namespace App\Api\Controllers;

use App\Api\Lib\RequestClass;
use App\Libs\Twig\TwigLib;
use stdClass;

class SistemaApiController
{

    public function component(RequestClass $request)
    {
        $component = $request->getJsonParams()["component"];
        $body = $request->getJsonParams()["body"];

        return [
            'error' => false,
            'data'  => (new TwigLib())->renderComponent($component, $body, false),
        ];
    }

}
