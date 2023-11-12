<?php

namespace App\Libs\twig;

use App\Daos\intranet\BBoletimDao;
use App\Daos\intranet\FCriterioPromocaoDao;
use App\Daos\intranet\RhFuncoesDao;
use App\Daos\intranet\RhPostoGraduacaoDao;
use App\Daos\intranet\RhQuadrosDao;
use App\Daos\intranet\RhSituacaoCertificadoDao;
use App\Daos\intranet\RhTipoAgregacaoDao;
use App\Daos\intranet\RhTipoArmaFogoDao;
use App\Daos\intranet\RhTipoBancosDao;
use App\Daos\intranet\RhTipoComissaoDao;
use App\Daos\intranet\RhTipoCursoDao;
use App\Daos\intranet\RhTipoDiversosDao;
use App\Daos\intranet\RhTipoDocumentoDao;
use App\Daos\intranet\RhTipoElogiosDao;
use App\Daos\intranet\RhTipoJuntaMedicaDao;
use App\Daos\intranet\RhTipoLicencaDao;
use App\Daos\intranet\RhTipoPunicoesDao;
use App\Daos\intranet\RhTipoTccDao;
use App\Daos\intranet\RhTipoTransferenciaDao;
use App\Daos\intranet\View_UnidadesDao;
use App\Enums\DadosComplementares_Funcionario;
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
            'SESSION' => $_SESSION,
            'GET'=>$_GET,
            'POST'=>$_POST
        ];
    }

    public function getFunctions()
    {
        return [

        ];
    }
}
