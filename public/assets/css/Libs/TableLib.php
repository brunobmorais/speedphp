<?php
namespace App\Libs;

/**
 * Classe que cria e exibe uma grid com dados MySQL
 *
 * @author Bruno Morais
 * @version 3.0 com ordenação via GET (sort=campo&order=asc|desc)
 * @date 2026-03-15
 * @copyright bmorais.com
 */
class TableLib
{
    protected $qtd = 15;

    protected $headers_name;

    protected $headers_width;

    protected $has_checkbox = false;

    protected $has_img = false;

    protected $total;

    protected $msg_zero = "<i class='mdi mdi-file-search-outline pt-2 pb-2 d-none d-md-block' style='font-size: 48px'></i><br><h6>Nenhuma informação encontrada!</h6>";

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

    /**
     * Chaves dos campos para ordenação (índice corresponde ao cabeçalho)
     * Ex: ['nome', 'email', null, 'criado_em']
     * null = coluna não ordenável
     */
    protected array $sortKeys = [];

    /**
     * Coluna atualmente ordenada (nome da chave)
     */
    protected string $currentSort = '';

    /**
     * Direção da ordenação: 'asc' ou 'desc'
     */
    protected string $currentOrder = 'asc';

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

    public function setQtd(int $qtd)
    {
        if (!empty($qtd))
            $this->qtd = $qtd;
    }

    public function getQtd()
    {
        return $this->qtd;
    }

    /**
     * Define as chaves de campo usadas para ordenação de cada coluna.
     * Use null para colunas que não devem ser ordenáveis (ex: botões de ação).
     *
     * @param array $keys Ex: ['nome', 'email', null, 'criado_em']
     * @return $this
     */
    private function setSortKeys(array $keys): static
    {
        $this->sortKeys = $keys;
        return $this;
    }

    /**
     * Retorna a coluna atual de ordenação lida do GET.
     */
    public function getSortColumn(): string
    {
        return $this->currentSort;
    }

    /**
     * Retorna a direção atual de ordenação lida do GET ('asc' ou 'desc').
     */
    public function getSortOrder(): string
    {
        return $this->currentOrder;
    }

    /**
     * Ordena o array de dados com base nos parâmetros de ordenação do GET.
     * Chame este método ANTES de iterar sobre os dados, se quiser
     * ordenação client-side (PHP) sobre o array já carregado.
     *
     * @param array $data
     * @return array
     */
    public function sortData(array $data): array
    {
        if (empty($this->currentSort)) {
            return $data;
        }

        $key   = $this->currentSort;
        $order = $this->currentOrder;

        usort($data, function ($a, $b) use ($key, $order) {
            $valA = is_array($a) ? ($a[$key] ?? '') : ($a->$key ?? '');
            $valB = is_array($b) ? ($b[$key] ?? '') : ($b->$key ?? '');

            // Comparação numérica ou string
            if (is_numeric($valA) && is_numeric($valB)) {
                $cmp = $valA <=> $valB;
            } else {
                $cmp = strnatcasecmp((string)$valA, (string)$valB);
            }

            return $order === 'desc' ? -$cmp : $cmp;
        });

        return $data;
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

    public function init($data, array $header, array $getParams = [], array $sortKeys = [])
    {
        $this->setSortKeys($sortKeys);
        $this->headers_name = $header;
        $this->busca        = $getParams;
        $this->data         = $data;
        $this->total        = !empty($this->data) ? count($this->data) : 0;

        // Lê os parâmetros de ordenação do GET
        $this->currentSort  = isset($_GET['sort'])  ? trim(strip_tags($_GET['sort']))  : '';
        $this->currentOrder = (isset($_GET['order']) && strtolower($_GET['order']) === 'desc') ? 'desc' : 'asc';

        $this->pg = empty($_GET['pg']) ? 1 : $_GET["pg"];

        $this->npages = floor($this->total / $this->qtd);

        if ((($this->total % $this->qtd) > 0) or ($this->npages == 0)) {
            $this->npages++;
        }

        if ($this->pg > $this->npages) {
            $this->pg = 1;
        }

        $this->inicioPg = ($this->pg - 1) * $this->qtd;
        $this->fimPg    = $this->inicioPg + $this->qtd;

        $this->addHeader();
        return $this;
    }

    /**
     * Monta a URL de ordenação para o cabeçalho de uma coluna.
     */
    private function buildSortUrl(string $key): string
    {
        // Inverte a direção se já está ordenando por esta coluna
        $newOrder = ($this->currentSort === $key && $this->currentOrder === 'asc') ? 'desc' : 'asc';

        $params = array_merge($this->busca, [
            'pg'    => 1,
            'sort'  => $key,
            'order' => $newOrder,
        ]);

        return '?' . http_build_query($params);
    }

    public static function getOrderByDatabase(string $sort, string $order): string{
        $sort  = isset($_GET['sort'])  ? trim(strip_tags($_GET['sort']))  : $sort;
        $asc = (isset($_GET['order']) && strtolower($_GET['order']) === 'desc') ? 'desc' : $order;

        return " ORDER BY {$sort} {$asc}";
    }

    /**
     * Retorna o ícone de ordenação adequado para a coluna.
     */
    private function getSortIcon(string $key): string
    {
        if ($this->currentSort !== $key) {
            // Coluna não está sendo ordenada — mostra ícone neutro
            return "<i class='mdi mdi-unfold-more-horizontal sort-icon sort-icon--neutral ms-1'></i>";
        }

        if ($this->currentOrder === 'asc') {
            return "<i class='mdi mdi-sort-ascending sort-icon sort-icon--active ms-1'></i>";
        }

        return "<i class='mdi mdi-sort-descending sort-icon sort-icon--active ms-1'></i>";
    }

    private function addHeader()
    {
        $this->linhas = 0;

        $this->dataTable .= "<div>\n";
        $this->dataTable .= "<hr/>\n";
        $this->dataTable .= "<div class='table-responsive'>\n";
        $this->dataTable .= "<table class='tableLib table table-striped' id='table-1'>\n";
        $this->dataTable .= "<thead class='cf'>\n";
        $this->dataTable .= "<tr>\n";

        // CSS inline para os ícones e th clicáveis
        $this->dataTable .= "<style>
            .th-sortable {
                cursor: pointer;
                user-select: none;
                white-space: nowrap;
            }
            .th-sortable:hover {
                background-color: rgba(0,0,0,.06);
            }
            .sort-icon {
                font-size: .90em;
                vertical-align: middle;
                opacity: .4;
            }
            .sort-icon--active {
                opacity: 1;
                color: var(--mycolor-secondary);
            }
            .sort-icon--neutral {
                opacity: 1;
            }
        </style>\n";

        $i = 0;
        if ($this->has_checkbox) {
            $this->dataTable .= "<td style='width:{$this->headers_width[$i]}%' class='form-check'>"
                . "<input class='form-check-input' type='checkbox' id='ckbselectall' name='ckbselectall'"
                . " onclick='selectAll(`checkbox`)'></td>\n";
            $i++;
        }

        for ($i = $i; $i < count($this->headers_name); $i++) {
            $label     = $this->headers_name[$i];
            $sortKey = array_key_exists($i, $this->sortKeys) ? $this->sortKeys[$i] : null;
            $widthAttr = '';

            if (!empty($this->headers_width[$i])) {
                $widthAttr = "style='width:{$this->headers_width[$i]}%'";
            }

            if (($i == count($this->headers_name) - 2) && $this->has_img) {
                $widthAttr = "style='width:{$this->headers_width[$i]}%; border-right:0'";
            }

            if (!empty($sortKey)) {
                // Coluna ordenável
                $url  = $this->buildSortUrl($sortKey);
                $icon = $this->getSortIcon($sortKey);

                $this->dataTable .= "<th class='actions th-sortable' {$widthAttr}>"
                    . "<a href='{$url}' class='text-decoration-none text-reset d-inline-flex align-items-center gap-1 fw-bold'>"
                    . htmlspecialchars($label) . $icon
                    . "</a></th>\n";
            } else {
                // Coluna sem ordenação
                $this->dataTable .= "<th class='actions' {$widthAttr}>"
                    . htmlspecialchars($label ?? '')
                    . "</th>\n";
            }
        }

        $this->dataTable .= "</tr>\n";
        $this->dataTable .= "</thead>\n";

        if ($this->total == 0) {
            $this->addNull();
        }
    }

    private function addNull()
    {
        $this->dataTable .= "<tr>\n";
        $this->dataTable .= "<td class=\"text-center semtitle\" style=\"padding: 60px\" colspan='"
            . count($this->headers_name) . "'>" . $this->msg_zero . "</td>\n";
        $this->dataTable .= "</tr>\n";
    }

    public function addColLine($content)
    {
        return '<div class="td-break" style="font-size: 11px">' . $content . '</div>';
    }

    public function addCol(string $col)
    {
        $this->col[] = $col;
        return $this;
    }

    /**
     * @param $link
     * @param $onclick
     * @param $style
     * @return $this
     * @example $table->addRow("/modulo/servico-cadastro/?id=1", "nomeFuncaoJS()", "style: css");
     */
    public function addRow($link = "", $onclick = "", $style = "")
    {
        if (!empty($link)) {
            $parameter = "&pg={$this->pg}&";
            foreach ($this->busca as $key => $value) {
                $parameter .= $key . "=" . str_replace(" ", "+", htmlentities((string)($value ?? "")));
            }
            $this->link = "onclick='location.href=`{$link}{$parameter}`'";
            $style      = "style='cursor: pointer;{$style}'";
        }

        if (!empty($onclick)) {
            $this->link = "onclick='{$onclick}'";
            $style      = "style='cursor: pointer;{$style}'";
        }

        $this->dataTable .= "<tr id='linec" . $this->linhas . "' {$style}>\n";

        $j = 0;

        if ($this->has_checkbox) {
            $this->dataTable .= "<td data-title='Selecione' style='width:" . $this->headers_width[$j] . "%'>"
                . "<div class=\"form-check\">"
                . "<input type=\"checkbox\" value='" . $this->col[$j] . "' id='c" . $this->linhas
                . "' data-checkboxes=\"mygroup\" class=\"form-check-input\">"
                . "<label for='c" . $this->linhas . "'>&nbsp;</label>"
                . "</div></td>\n";
            $j++;
        } else {
            $class = 'nocheck';
        }

        $class = $this->has_checkbox ? '' : 'nocheck';

        for ($j = $j; $j < count($this->col); $j++) {
            $classLinha = $class;

            if (($this->has_img) and ($j == count($this->col) - 1)) {
                if (isset($this->headers_width[$j]))
                    $this->dataTable .= "<td data-title='" . ($this->headers_name[$j] ?? '') . "' class='" . $classLinha . "' style='width:" . $this->headers_width[$j] . "%'><span class='td-main'>" . $this->col[$j] . "</span></td>\n";
                else
                    $this->dataTable .= "<td data-title='" . ($this->headers_name[$j] ?? '') . "' class='" . $classLinha . "'><span class='td-main'>" . $this->col[$j] . "</span></td>\n";
            } else {
                if (isset($this->headers_width[$j]))
                    $this->dataTable .= "<td data-title='" . ($this->headers_name[$j] ?? '') . "' class='" . $classLinha . "' style='width:" . $this->headers_width[$j] . "%' {$this->link}><span class='td-main'>" . $this->col[$j] . "</span></td>\n";
                else {
                    if (empty($this->headers_name[$j]))
                        $classLinha .= " semtitle";
                    $this->dataTable .= "<td data-title='" . ($this->headers_name[$j] ?? '') . "' class='" . $classLinha . "' {$this->link}><span class='td-main'>" . $this->col[$j] . "</span></td>\n";
                }
            }
        }

        $this->dataTable .= "</tr>";

        $this->linhas++;
        $this->col = [];
        return $this;
    }

    private function setFooter()
    {
        if (($this->linhas > 0) and ($this->linhas < 10)) {
            $this->dataTable .= "<tr>";
            $this->dataTable .= "<td style='height:" . $this->espacos[$this->linhas] . "px' class='espaco' colspan='" . count($this->headers_name) . "'></td>";
            $this->dataTable .= "</tr>";
        }

        $this->dataTable .= "</table></div>\n";
        $this->dataTable .= "<hr/>";

        $this->dataTable .= "<form id='formTable' action='' method='get' style='margin:0'>\n";
        $this->dataTable .= "<input type='hidden' id='pg' name='pg' value='" . $this->pg . "' />";

        // Preserva sort e order no formulário de paginação
        if (!empty($this->currentSort)) {
            $this->dataTable .= "<input type='hidden' name='sort' value='" . htmlspecialchars($this->currentSort) . "' />\n";
            $this->dataTable .= "<input type='hidden' name='order' value='" . htmlspecialchars($this->currentOrder) . "' />\n";
        }

        foreach ($this->busca as $key => $value) {
            $this->dataTable .= "<input type='hidden' name='{$key}' value='" . htmlentities($value ?? "") . "' />\n";
        }

        $this->dataTable .= "<div class=\"row m-0\">\n";
        $this->dataTable .= "<div class='text-left col-4'><p class=\"fw-medium\">Total: " . $this->total . "</p></div>\n";
        $this->dataTable .= "<div class='text-center col-4'><p class=\"fw-medium\">Pg: " . $this->pg . "/" . $this->npages . "</p></div>\n";
        $this->dataTable .= "<div class='col-4 p-0' style='text-align: right'>\n";
        $this->dataTable .= "<nav class=\"d-inline-block\">
                                  <ul class=\"pagination pagination-sm\">
                                    <li class='page-item " . ($this->pg == 1 ? 'disabled' : '') . "'>
                                      <a class=\"page-link\" href=\"javascript:void(0)\" onclick=formTableAction('" . ($this->pg - 1) . "') tabindex=\"-1\"><i class=\"mdi mdi-chevron-left\"></i></a>
                                    </li>
                                    <li class=\"page-item active\"><a class=\"page-link\" href=\"#\">" . $this->pg . "</a></li>
                                    <li class='page-item " . ($this->pg == $this->npages ? 'disabled' : '') . "'>
                                      <a class=\"page-link\" href=\"javascript:void(0)\" onclick=formTableAction('" . ($this->pg + 1) . "')><i class=\"mdi mdi-chevron-right\"></i></a>
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