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
            }),
            new TwigFilter('cpfcnpj', function ($value) {
                $CPF_LENGTH = 11;
                //
                //$cnpj_cpf = preg_replace("/\s/", '', $value);
                $cnpj_cpf = $value;

                if (strlen($cnpj_cpf) === $CPF_LENGTH) {
                    return preg_replace("/(\S{3})(\S{3})(\S{3})(\S{2})/", "\$1.\$2.\$3-\$4", $cnpj_cpf);
                }

                return preg_replace("/(\S{2})(\S{3})(\S{3})(\S{4})(\S{2})/", "\$1.\$2.\$3/\$4-\$5", $cnpj_cpf);
            }),
            new TwigFilter('ucfirst', function ($n) {
                return ucwords(strtolower($n));
            }),
            new TwigFilter(
                'telefone',
                function ($n) {
                    $phoneString = preg_replace('/[()]/', '', $n);


                    $ddi = $matches[1] ?? '';
                    $ddd = preg_replace('/^0/', '', $matches[2] ?? '');
                    $number = $matches[3] ?? '';


                    return $number;

                }
            ),
            new TwigFilter(
                'moeda',
                fn($valor) => preg_replace(
                    '/^\s+|\s+$/u', 
                    '', 
                    str_replace(
                        'R$', 
                        '', 
                        (new \NumberFormatter('pt_BR', \NumberFormatter::CURRENCY))->formatCurrency($valor, 'BRL')
                    )
                )
            ),
            new TwigFilter('object', function (array $value) {
                return (object) $value;
            }),
        ];
    }


    public function getGlobals(): array
    {
        return [
            "URL" => CONFIG_URL,
            'SESSION' => $_SESSION ?? [],
            'GET' => $_GET ?? [],
            'POST' => $_POST ?? [],
            'SERVER' => $_SERVER ?? [],
            'COOKIE' => $_COOKIE ?? [],
            'CONFIG_COLOR' => CONFIG_COLOR ?? [],
            'CONFIG_SITE' => CONFIG_SITE ?? [],
            'CONFIG_PAYMENT' => CONFIG_PAYMENT ?? [],
            'CONFIG_HEADER' => CONFIG_HEADER ?? [],
            'CONFIG_VERSION_CODE' => CONFIG_VERSION_CODE,
        ];
    }

    public function getFunctions()
    {
        return [

        ];
    }
}
