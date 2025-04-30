<?php

namespace App\Libs\Twig;

use Twig\Environment;
use Twig\Error\Error;
use Twig\TwigFunction;

class TwigFunctionLib
{
    public static function getFunctions(Environment $twig)
    {
        // FUNCOES GERAIS
        $callFunc = new TwigFunction('getFunction', function ($class, $func, array $params = []) {
            $controller = new $class();
            $return = call_user_func_array([$controller, $func], $params);
            return $return;
        });
        $twig->addFunction($callFunc);

        $intranetDao = new TwigFunction('getLibs', function ($class, $func, array $params = []) {
            $class = "App\\Libs\\{$class}";
            $controller = new $class();
            $return = call_user_func_array([$controller, $func], $params);
            return $return;
        });
        $twig->addFunction($intranetDao);

        $prevenirDao = new TwigFunction('getDaos', function ($class, $func, array $params = []) {
            $class = "App\\Daos\\{$class}";
            $controller = new $class();
            $return = call_user_func_array([$controller, $func], $params);
            return $return;
        });
        $twig->addFunction($prevenirDao);


        $intranetModels = new TwigFunction('getModels', function ($class, $func, array $params = []) {
            $class = "App\\Models\\{$class}";
            $controller = new $class();
            $return = call_user_func_array([$controller, $func], $params);
            return $return;
        });
        $twig->addFunction($intranetModels);

     
        $enums = new TwigFunction('getEnums', function ($class, $func, array $params = []) {
            $class = "App\\Enums\\{$class}";
            $return = call_user_func_array("{$class}::{$func}", $params);
            if($return){
                return $return;

            }
            throw new Error('Erro twig function enum ');
        });
        $twig->addFunction($enums);

        return $twig;
    }
}
