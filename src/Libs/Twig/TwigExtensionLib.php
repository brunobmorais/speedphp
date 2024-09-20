<?php

namespace App\Libs\Twig;

use Twig\TwigFilter;
use Twig\TwigFunction;

/**
 * BIBLIOTECA DE EXTENSÕES ADICIONAIS PARA O TWIG
 * @author athus <athusfelipe1995@gmail.com>
 */
class TwigExtensionLib extends \Twig\Extension\AbstractExtension implements \Twig\Extension\GlobalsInterface
{
    public function getFilters()
    {
        return [
            new TwigFilter('number_format', 'number_format'),
            new TwigFilter('cpf', function ($cpf) {

                if (strlen($cpf) == 11) {
                    if ((strpos($cpf, '.')) or (strpos($cpf, '-'))) {
                        return $cpf;
                    } else {
                        $new = "";

                        for ($i = 0; $i < 11; $i++) {
                            if (in_array($i, array(3, 6))) {
                                $new .= ".";
                            }

                            if ($i == 9) {
                                $new .= "-";
                            }

                            $new .= $cpf[$i];
                        }

                        return $new;
                    }
                } else {
                    return $cpf;
                }
            })
        ];
    }


    public function getGlobals(): array
    {
        return [
            "URL" => CONFIG_URL,
            'SESSION' => $_SESSION??[],
            'GET'=>$_GET??[],
            'POST'=>$_POST??[],
            'SERVER'=>$_SERVER??[],
            'CONFIG_COLOR'=>CONFIG_COLOR??[]

        ];
    }

    public function getFunctions()
    {
        return [

        ];
    }
}
