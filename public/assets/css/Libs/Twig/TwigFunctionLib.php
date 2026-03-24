<?php

namespace App\Libs\Twig;

use Twig\Environment;
use Twig\Error\Error;
use Twig\TwigFunction;
use Twig\TwigFilter;

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


        $enums = new TwigFunction('getEnums', function ($class, $func, $value = null, array $params = []) {
            $fqcn = "App\\Enums\\{$class}";

            // Se veio um valor, tenta resolver como Enum
            if ($value !== null && method_exists($fqcn, 'fromValue')) {
                $enum = $fqcn::fromValue($value);

                if ($enum instanceof $fqcn) {
                    if (method_exists($enum, $func)) {
                        return $enum->$func(...$params);
                    }
                    throw new Error("Método {$func} não existe em instância de {$fqcn}");
                }

                throw new Error("Valor '{$value}' não corresponde a nenhum case de {$fqcn}");
            }

            // fallback para métodos estáticos
            if (method_exists($fqcn, $func)) {
                return $fqcn::$func(...$params);
            }

            throw new Error("Erro twig function enum {$fqcn}::{$func}");
        });
        $twig->addFunction($enums);

        $diaDaSemanaFilter = new TwigFilter('diaDaSemana', function ($dataEvento) {
            $dias = ['Domingo', 'Segunda', 'Terça', 'Quarta', 'Quinta', 'Sexta', 'Sábado'];
            $timestamp = strtotime($dataEvento);
            $indice = date('w', $timestamp);
            return $dias[$indice];
        });
        $twig->addFilter($diaDaSemanaFilter);

        return $twig;
    }
}
