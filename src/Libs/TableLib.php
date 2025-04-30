<?php
namespace App\Libs;

/**
 * Classe que cria e exibe uma grid com dados MySQL
 *
 * @author Bruno Morais
 * @version 04 out 2023, 11h28
 * @copyright bmorais.com
 */


/**
 * EXEMPLO DE STYLE PARA A GRID
 */
class TableLib
{
    protected $qtd = 15;

    protected $headers_name;

    protected $headers_width;

    protected $has_checkbox = false;

    protected $has_img = false;

    protected $total;

    protected $msg_zero="<i class='mdi mdi-file-search-outline pt-2 pb-2 d-none d-md-block' style='font-size: 48px'></i><br><h6>Nenhuma informação encontrada!</h6>";

    protected $pg;

    protected $npages;

    protected $inicioPg;

    protected $fimPg;

    protected $busca = array();

    protected $linhas;

    protected $espacos = array("0", "190", "170", "150", "130", "110", "90", "70", "50", "30");

    protected $data = [];

    protected $link = '';

    protected array $col = [];

    protected $dataTable = "";

    public function __construct($qtd = 15)
    {
        $this->qtd = $qtd;
        $this->dataTable = "";
        return $this;
    }

    public function checkPagination($index)
    {
        if (($index >= $this->getInicioPg()) and ($index < $this->getFimPg())) {
            return true;
        }
        return false;
    }
    public function setHasCheckbox(bool $value)
    {
        $this->has_checkbox = $value;
    }

    public function setHasImage(bool $value)
    {
        $this->has_img = $value;
    }

    public function setQtd(int $qtd )
    {
        if (!empty($qtd))
            $this->qtd = $qtd;
    }
    public function getQtd( )
    {
      
        return $this->qtd ;
    }

    public function render()
    {
        $this->setFooter();
        return $this->dataTable;
    }

    protected function getInicioPg()
    {
        return $this->inicioPg;
    }

    protected function getFimPg()
    {
        return $this->fimPg;
    }


    public function init($data, array $header, array $getParams = [])
    {
        $header = $header;
        $this->headers_name = $header;
        $this->busca = $getParams;

        $this->data = $data;
        $this->total = !empty($this->data)?count($this->data):0;
        //$this->mod = $mod;

        $this->pg = empty($_GET['pg']) ? 1 : $_GET["pg"];

        $this->npages = floor($this->total / $this->qtd);

        if ((($this->total % $this->qtd) > 0) or ($this->npages == 0)) {
            $this->npages++;
        }

        if ($this->pg > $this->npages) {
            $this->pg = 1;
        }

        $this->inicioPg = ($this->pg - 1) * $this->qtd;
        $this->fimPg = $this->inicioPg + $this->qtd;

        $this->addHeader();
        return $this;
    }


    private function addHeader()
    {
        $this->linhas = 0;

        $this->dataTable .= "<div>\n";
        $this->dataTable .= "<hr/>\n";
        $this->dataTable .= "<div class='table-responsive'>\n";
        $this->dataTable .= "<table class='table table-striped' id='table-1'>\n";
        $this->dataTable .= "<thead class='cf'>\n";
        $this->dataTable .= "<tr>\n";


        $i = 0;
        if ($this->has_checkbox) {
            $this->dataTable .= "<td style='width:{$this->headers_width[$i]}%' class='form-check'> <input class='form-check-input' type='checkbox' id='ckbselectall' name='ckbselectall' onclick='selectAll(`checkbox`)'></td>\n";
            $i++;
        }

        for ($i = $i; $i < count($this->headers_name); $i++) {
            if (($i == count($this->headers_name) - 2) and ($this->has_img)) {
                $this->dataTable .= "<th style='width:" . $this->headers_width[$i] . "%; border-right:0'>" . $this->headers_name[$i] . "</th>\n";
            } else {
                if (!empty($this->headers_width))
                    $this->dataTable .= "<th class=\"actions\" style='width:" . $this->headers_width[$i] . "%'>" . $this->headers_name[$i] . "</th>\n";
                else
                    $this->dataTable .= "<th class=\"actions\">" . $this->headers_name[$i] . "</th>\n";

            }
        }
        $this->dataTable .= "</tr>\n";
        $this->dataTable .= "</thead>\n";

        // fim do header


        // adiciona o vazio se a consulta retorna vazio
        if ($this->total == 0) {
            $this->addNull();
        }
    }


    private function addNull()
    {
        $this->dataTable .= "<tr>\n";
        $this->dataTable .= "<td class=\"text-center semtitle\" style=\"padding: 60px\" colspan='" . count($this->headers_name) . "'>" . $this->msg_zero . "</td>\n";
        $this->dataTable .= "</tr>\n";
    }


    public function addColLine($content)
    {
        return '<div class="td-break">
                    '.$content.'
                </div>';
    }

    public function addCol(string $col)
    {
        $this->col[] = $col;
        return $this;
    }

    /**
     * @param $link
     * @param $style
     * @return $this
     * @example $table->addRow("/modulo/servico-cadastro/?id=1","nomeFuncaoJS()", "style: css");
     */
    public function addRow($link = "", $onclick = "", $style = "")
    {
        if (!empty($link)) {
            //$this->link = str_replace("'", '', $this->link);
            $parameter = "&pg={$this->pg}&";
            foreach ($this->busca as $key => $value) {
                $parameter .= $key . "=" . str_replace(" ", "+", htmlentities((string)$value??""));
            }
            $this->link = "onclick='location.href=`{$link}{$parameter}`'";
            $style = "style='cursor: pointer;{$style}'";
        }

        if (!empty($onclick)) {
            $this->link = "onclick='{$onclick}'";
            $style = "style='cursor: pointer;{$style}'";
        }

        $this->dataTable .= "<tr id='linec" . $this->linhas . "' {$style}>\n";

        // contador de colunas
        $j = 0;

        // escreve o checkbox, com seu nome e valor, caso houver
        if ($this->has_checkbox) {
            $this->dataTable .= "<td data-title='Selecione' style='width:" . $this->headers_width[$j] . "%'><div class=\"form-check\"><input type=\"checkbox\" value='" . $this->col[$j] . "' id='c" . $this->linhas . "' data-checkboxes=\"mygroup\" class=\"form-check-input\"><label for='c" . $this->linhas . "'>&nbsp;</label></div></td>\n";
            $class = "";

            // incrementa o $j para entrar o for a partir do segundo registro
            $j++;
        } else {
            $class = 'nocheck';
        }

        // escreve os valores, vindos do banco de dados
        for ($j = $j; $j < count($this->col); $j++) {

            $classLinha = $class;

            if (($this->has_img) and ($j == count($this->col) - 1)) {
                if (isset($this->headers_width[$j]))
                    $this->dataTable .= "<td data-title='".$this->headers_name[$j]."' class='" . $classLinha . "' style='width:" . $this->headers_width[$j] . "%'>" . $this->col[$j] . "</td>\n";
                else
                    $this->dataTable .= "<td data-title='".$this->headers_name[$j]."' class='" . $classLinha . "'>" . $this->col[$j] . "</td>\n";
            } else {
                if (isset($this->headers_width[$j]))
                    $this->dataTable .= "<td data-title='".$this->headers_name[$j]."' class='" . $classLinha ."' style='width:" . $this->headers_width[$j] . "%' {$this->link}>" . $this->col[$j] . "</td>\n";
                else {
                    if (empty($this->headers_name[$j]))
                        $classLinha .= " semtitle";
                    $this->dataTable .= "<td data-title='" . ($this->headers_name[$j]??'') . "' class='" . $classLinha . "' {$this->link}>" . $this->col[$j] . "</td>\n";
                }

            }
        }

        // fecha a linha
        $this->dataTable .= "</tr>";

        $this->linhas++;
        $this->col = [];
        return $this;
    }


    private function setFooter()
    {
        /**
         * ADICIONA A LINHA EM BRANCO PARA GRID COM MENOS DE 10 ITENS
         */
        if (($this->linhas > 0) and ($this->linhas < 10)) {
            $this->dataTable .= "<tr>";
            $this->dataTable .= "<td style='height:" . $this->espacos[$this->linhas] . "px' class='espaco' colspan='" . count($this->headers_name) . "'></td>";
            $this->dataTable .= "</tr>";
        }


        // fim da linha do rodap?

        // fim da grid
        $this->dataTable .= "</table></div>\n";
        $this->dataTable .= "<hr/>";


        $this->dataTable .= "<form id='formTable' action='' method='get' style='margin:0'>\n";
        //$this->dataTable .= "<input type='hidden' name='busca' value=\"".$this->busca2."\" />\n";
        $this->dataTable .= "<input type='hidden' id='pg' name='pg' value='" . $this->pg . "' />";
        foreach ($this->busca as $key => $value) {
            $this->dataTable .= "<input type='hidden' name='{$key}' value='" . htmlentities($value??"") . "' />\n";
        }
        $this->dataTable .= "<div class=\"row m-0\">\n";
        $this->dataTable .= "<div class='text-left col-4'><p class=\"fw-medium\">Total: ".$this->total."</p></div>\n";
        $this->dataTable .= "<div class='text-center col-4'><p class=\"fw-medium\">Pg: ". $this->pg."/".$this->npages."</p></div>\n";
        $this->dataTable .= "<div class='col-4 p-0' style='text-align: right'>\n";
        $this->dataTable .= "<nav class=\"d-inline-block\">
                                      <ul class=\"pagination pagination-sm\">
                                        <li class='page-item ".($this->pg==1?'disabled':'')."'>
                                          <a class=\"page-link\" href=\"javascript:void(0)\" onclick=formTableAction('".($this->pg - 1)."') tabindex=\"-1\"><i class=\"mdi mdi-chevron-left\"></i></a>
                                        </li>
                                        <li class=\"page-item active\"><a class=\"page-link\" href=\"#\">".$this->pg."</a></li>
                                        <li class='page-item ".($this->pg==$this->npages?'disabled':'')."'>
                                          <a class=\"page-link\" href=\"javascript:void(0)\" onclick=formTableAction('".($this->pg + 1)."')><i class=\"mdi mdi-chevron-right\"></i></a>
                                        </li>
                                      </ul>
                              </nav>\n";
        $this->dataTable .= "</div></div>\n";
        $this->dataTable .= "</form>\n";


        $this->dataTable .= "<script>
                function formTableAction(pg){
                    document.getElementById('pg').value = pg;
                    document.getElementById('formTable').submit();
                }
                </script>";
        $this->dataTable .= "</div>\n";

    }
}
