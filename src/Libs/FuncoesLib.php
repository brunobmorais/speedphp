<?php
namespace App\Libs;

use DateTime;
use Firebase\JWT\JWT;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use ReCaptcha\ReCaptcha;


/**
 * CLASSE DE FUNÇÕES GERAIS
 *
 *
 * @author Bruno Morais <brunomoraisti@gmail.com>
 * @version 2
 * @date 17/09/2021
 * @copyright GPL © 2021, bmorais.com
 * @package php
 * @subpackage class
 * @access private
 */
class FuncoesLib
{

    function quantidadeElementos($array)
    {
        return (is_array($array) ? count($array) : 0);
    }

    /*function quantidadeElementosArray($array)
    {
        return count(array($array));
    }*/

    /**
     * FUNÇÃO GERAR SENHA
     *
     * @return String
     */
    function geraSenha($tamanho = 8, $maiusculas = true, $minuscula = true, $numeros = true, $simbolos = false)
    {
        $lmin = 'abcdefghijklmnopqrstuvwxyz';
        $lmai = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $num = '1234567890';
        $simb = '!@#$%*-';
        $retorno = '';
        $caracteres = '';
        if ($minuscula) $caracteres .= $lmin;
        if ($maiusculas) $caracteres .= $lmai;
        if ($numeros) $caracteres .= $num;
        if ($simbolos) $caracteres .= $simb;
        $len = strlen($caracteres);
        for ($n = 1; $n <= $tamanho; $n++) {
            $rand = mt_rand(1, $len);
            $retorno .= $caracteres[$rand - 1];
        }
        return $retorno;
    }

    /**
     * FUNÇÃO PARA GERAR UM TOKEN
     *
     * @return false|string
     */
    function gerarToken($length = 20)
    {
        return bin2hex(random_bytes($length));
    }

    function converteDataEmTimestamp($date)
    {
        return strtotime($date);
    }

    function converteTimestampEmData($timestamp)
    {
        return date('Y-m-d H:i', $timestamp);
    }


    /**
     * FUNÇÃO PEGAR DATA ATUAL NO FORMATO DO BANCO DE DADOS
     *
     * @return false|stringgit
     */
    function pegarDataAtualBanco()
    {
        date_default_timezone_set("America/Araguaina");
        return date("Y-m-d H:i:s");
    }

    /**
     * FUNÇÃO PEGAR DATA ATUAL NO FORMATO DO BANCO DE DADOS
     *
     * @return false|string
     */
    function pegarDataAtualUsuario()
    {
        date_default_timezone_set("America/Araguaina");
        return date("d/m/Y");
    }

    /**
     * FUNÇÃO PEGAR DATA ATUAL NO FORMATO DO BANCO DE DADOS
     *
     * @return false|string
     */
    function pegarDataHoraAtualUsuario()
    {
        date_default_timezone_set("America/Araguaina");
        return date("d/m/Y H:i");
    }

    /**
     * FUNÇÃO PEGAR DATA ATUAL NO FORMATO DO BANCO DE DADOS
     *
     * @return false|string
     */
    function pegarHoraAtualUsuario()
    {
        date_default_timezone_set("America/Araguaina");
        return date("H:i");
    }

    /**
     * FUNÇÃO PEGAR DATA ATUAL NO FORMATO DO BANCO DE DADOS
     *
     * @return false|string
     */
    function pegarNumeroDiaDaSemana()
    {
        $data = date('Y-m-d');
        $diasemana_numero = date('w', strtotime($data));
        return $diasemana_numero;
    }

    function formatMoedaBanco($numero)
    {
        //return number_format($numero, 2, ',', '.');
        $moeda = str_replace('.', '', $numero);
        $moeda = str_replace(',', '.', $moeda);
        return $moeda;
    }

    function formatMoedaUsuario($numero)
    {
        return number_format($numero, 2, '.', ',');
    }

    /**
     * FUNÇÃO PARA FORMATAR A DATA RECEBIDA NO FORMATO DO USUÁRIO
     *
     * @param $data
     * @return string
     */
    function formatDataUsuario($data)
    {
        date_default_timezone_set("America/Araguaina");
        $dataformatada = strtotime($data);
        return date("m/d/Y", $dataformatada);;
    }

        /**
     * FUNÇÃO PARA FORMATAR A DATA RECEBIDA PARA FORMATO BRASILEIRO
     *
     * @param $data
     * @return string
     */
    function formatDataUsuarioAmigavel($data)
    {
        date_default_timezone_set("America/Araguaina");
        $dataformatada = strtotime($data);
        return date("d/m/Y", $dataformatada);;
    }

    /**
     * FUNÇÃO PARA FORMATAR A DATA E HORA NO FORMATO DO USUÁRIO
     *
     * @param $data
     * @return string
     */
    function formatDataHoraUsuario($data)
    {
        date_default_timezone_set("America/Araguaina");
        $dataformatada = new DateTime($data);
        return $dataformatada->format('d/m/Y H:i:s');
    }

    /**
     * FUNÇÃO FORMATAR DATA PARA BANCO DE DADOS
     *
     */
    function formatDataBanco($date)
    {
        $new = substr($date, 6, 4) . "-" . substr($date, 3, 2) . "-" . substr($date, 0, 2);
        return $new;
    }

    /**
     * FUNÇÃO FORMAR CPF PARA USUARIO
     *
     * @param $cpf
     * @return string
     */
    function formatCpfUsuario($cpf)
    {
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
    }

    /**
     * FORMATAR CPF PARA INSERÇÃO NO BANCO
     *
     * @param $cpf
     * @return mixed
     */
    public function formatCpfBanco($cpf)
    {
        $cpf = str_replace(".", "", $cpf);
        $cpf = str_replace("-", "", $cpf);
        return $cpf;
    }

    /**
     * FORMATAR TEXTO PARA COLOCAR NA URL
     * @param $texto
     * @return mixed
     */
    public function formatUrl($texto)
    {
        $texto = trim($texto);
        $texto = $this->textoMinusculo($texto);
        $texto = $this->removeAcentos($texto);

        $texto = str_replace(".", "-", $texto);
        $texto = str_replace("-", "-", $texto);
        $texto = str_replace("/", "-", $texto);
        $texto = str_replace("(", "-", $texto);
        $texto = str_replace(")", "-", $texto);
        $texto = str_replace("&", "", $texto);
        $texto = str_replace("\"", "-", $texto);
        $texto = str_replace("+", "-", $texto);
        $texto = str_replace("_", "-", $texto);
        $texto = str_replace(" ", "-", $texto);
        $texto = str_replace("'", "", $texto);
        $texto = str_replace("´", "", $texto);
        $texto = str_replace("@", "", $texto);
        $texto = str_replace(",", "", $texto);
        $texto = str_replace("|", "", $texto);

        return $texto;
    }

    public function pegarUrlAtual():string{

        return $_SERVER['REQUEST_URI'];
    }

    /**
     * @param $texto
     * @return mixed
     */
    public function removeCaracteres($texto)
    {
        $texto = str_replace(".", "", $texto);
        $texto = str_replace("-", "", $texto);
        $texto = str_replace("/", "", $texto);
        $texto = str_replace(" ", "", $texto);
        $texto = str_replace("(", "", $texto);
        $texto = str_replace(")", "", $texto);
        $texto = str_replace("_", "", $texto);


        return $texto;
    }

    /**
     * @param $texto
     * @return mixed
     */
    public function antiXss($texto)
    {
        $texto = str_replace("<", "", $texto);
        $texto = str_replace(">", "", $texto);
        $texto = str_replace("¼", "", $texto);
        $texto = str_replace("&#32", "", $texto);
        $texto = str_replace("%2F", "", $texto);
        $texto = str_replace("%00", "", $texto);
        $texto = str_replace("%253c", "", $texto);
        $texto = str_replace("%253e", "", $texto);
        $texto = str_replace("&lt", "", $texto);
        $texto = str_replace("&gt", "", $texto);

        return $texto;
    }


    /**
     * FORMATAR TEXTO PARA INSERÇÃO NO BANCO REMOVENDO ASPAS
     *
     * @param $texto
     * @return string
     */
    public function formatTextoAspasBanco($texto)
    {
        return addslashes($texto);
    }

    /**
     * FORMATA TEXTO COM AS BANCOS PARA USUÁRIO
     *
     * @param $texto
     * @return string
     */
    public function formatTextoAspasUsuario($texto)
    {
        return stripcslashes($texto);
    }


    /**
     * FUNÇÃO PARA PEGAR IP DO CLIENTE
     */
    function pegaIpUsuario()
    {
        $ipaddress = '';
        if (isset($_SERVER['HTTP_CLIENT_IP']))
            $ipaddress = $_SERVER['HTTP_CLIENT_IP'];
        else if (isset($_SERVER['HTTP_X_FORWARDED_FOR']))
            $ipaddress = $_SERVER['HTTP_X_FORWARDED_FOR'];
        else if (isset($_SERVER['HTTP_X_FORWARDED']))
            $ipaddress = $_SERVER['HTTP_X_FORWARDED'];
        else if (isset($_SERVER['HTTP_FORWARDED_FOR']))
            $ipaddress = $_SERVER['HTTP_FORWARDED_FOR'];
        else if (isset($_SERVER['HTTP_FORWARDED']))
            $ipaddress = $_SERVER['HTTP_FORWARDED'];
        else if (isset($_SERVER['REMOTE_ADDR']))
            $ipaddress = $_SERVER['REMOTE_ADDR'];
        else
            $ipaddress = 'UNKNOWN';
        return $ipaddress;
    }

    /**
     * FUNÇÃO PARA TRANSFORMAR TEXTO EM MAISCULA
     */
    function textoMaiusculo($str)
    {
        $str = trim($str);
        return mb_strtoupper($str, "UTF-8");
    }

    function textoMinusculo($str)
    {
        $str = trim($str);
        return mb_strtolower($str, "UTF-8");
    }

    /**
     * FUNÇÃO PARA FORMATAR APENAS A PRIMEIRA LETRA DA PALAVRA EM MAÍSCULA
     * @param $str
     * @return mixed|string
     */
    function textoPrimeiraLetraMaiusculoCadaPalavra($str)
    {
        $str = trim($str);
        $texto = mb_convert_case($str, MB_CASE_TITLE, 'UTF-8');
        $texto = str_replace(array("De ", "Do ", "Dos ", "Da ", "Das ", "Com ", "E ", "É "), array("de ", "do ", "dos ", "da ", "das ", "com ", "e ", "é "), ucwords(strtolower($texto)));
        return $texto;
    }

    function textoPrimeiraLetraMaiusculo($str)
    {
        $str = trim($str);
        $nome = strtolower($str); // Converter o nome todo para minúsculo
        $saida = ucfirst($nome);
        return $saida;


    }

    /**
     * FUNÇÃO PARA CRIAR SENHA HASH
     *
     * @param $strPassword
     * @param $numAlgo
     * @param $arrOptions
     *
     * @return string
     */
    function create_password_hash($strPassword, $numAlgo = 1, $arrOptions = array())
    {
        if (function_exists('password_hash')) {
            // php >= 5.5
            $hash = password_hash($strPassword, $numAlgo, $arrOptions);
        } else {
            $salt = mcrypt_create_iv(22, MCRYPT_DEV_URANDOM);
            $salt = base64_encode($salt);
            $salt = str_replace('+', '.', $salt);
            $hash = crypt($strPassword, '$2y$10$' . $salt . '$');
        }
        return $hash;
    }

    /**
     * FUNÇÃO PARA VERIFICAR SENHA
     *
     * @param $strPassword
     * @param $strHash
     *
     * @return bool
     */
    function verify_password_hash($strPassword, $strHash)
    {
        if (function_exists('password_verify')) {
            // php >= 5.5
            $boolReturn = password_verify($strPassword, $strHash);
        } else {
            $strHash2 = crypt($strPassword, $strHash);
            $boolReturn = $strHash == $strHash2;
        }
        return $boolReturn;
    }

    /**
     * FUNÇÃO REMOVE ACENTOS
     *
     * @param $texto
     *
     * @return string
     */
    function removeAcentos($texto)
    {
        return str_replace(array(' ', 'à', 'á', 'â', 'ã', 'ä', 'ç', 'è', 'é', 'ê', 'ë', 'ì', 'í', 'î', 'ï', 'ñ', 'ò', 'ó', 'ô', 'õ', 'ö', 'ù', 'ú', 'û', 'ü', 'ý', 'ÿ', 'À', 'Á', 'Â', 'Ã', 'Ä', 'Ç', 'È', 'É', 'Ê', 'Ë', 'Ì', 'Í', 'Î', 'Ï', 'Ñ', 'Ò', 'Ó', 'Ô', 'Õ', 'Ö', 'Ù', 'Ú', 'Û', 'Ü', 'Ý'), array(' ', 'a', 'a', 'a', 'a', 'a', 'c', 'e', 'e', 'e', 'e', 'i', 'i', 'i', 'i', 'n', 'o', 'o', 'o', 'o', 'o', 'u', 'u', 'u', 'u', 'y', 'y', 'A', 'A', 'A', 'A', 'A', 'C', 'E', 'E', 'E', 'E', 'I', 'I', 'I', 'I', 'N', 'O', 'O', 'O', 'O', 'O', 'U', 'U', 'U', 'U', 'Y'), $texto);
    }

    /**
     * FUNÇÃO PARA RETORNAR A QUANTIDADE DE CARACTERES DE UM TEXTO
     *
     * @param $texto
     *
     * @return int
     */
    function quantidadeCaracteres($texto)
    {
        return strlen($texto);
    }



    /**
     * FUNÇÃO PARA RETORNAR O NUMERO DE ACORDO COM A QUANTIDADE DE ZEROS
     *
     * @param $numero
     * @param int $qtd
     * @return string
     */
    function formataNumeroComZero($numero, $qtd = 2)
    {
        return str_pad((int)$numero, $qtd, '0', STR_PAD_LEFT);
    }

    /**
     * FUNÇÃO PARA QUE RETORNA OS FERIADOS
     *
     * @param $ano
     * @param $posicao
     * @return string
     */
    function Feriados($ano, $posicao)
    {
        $dia = 86400;
        $datas = array();
        $datas['pascoa'] = easter_date($ano);
        $datas['sexta_santa'] = $datas['pascoa'] - (2 * $dia);
        $datas['carnaval'] = $datas['pascoa'] - (47 * $dia);
        $datas['corpus_cristi'] = $datas['pascoa'] + (60 * $dia);
        $feriados = array(
            '01/01',
            date('d/m', $datas['carnaval']),
            date('d/m', $datas['sexta_santa']),
            date('d/m', $datas['pascoa']),
            '21/04',
            '01/05',
            date('d/m', $datas['corpus_cristi']),
            '12/10',
            '02/11',
            '15/11',
            '25/12',
        );

        return $feriados[$posicao] . "/" . $ano;
    }


    /**
     * FUNÇÃO PARA RETORNAR DIA MES E ANO
     *
     * @param $data
     * @return false|int
     */
    function dataToTimestamp($data)
    {
        $ano = substr($data, 6, 4);
        $mes = substr($data, 3, 2);
        $dia = substr($data, 0, 2);
        return mktime(0, 0, 0, $mes, $dia, $ano);
    }

    /**
     * FUNÇÃO PARA SOMAR UM DIA A DATA
     *
     * @param $data
     * @return false|string
     */
    function Soma1dia($data)
    {
        $ano = substr($data, 6, 4);
        $mes = substr($data, 3, 2);
        $dia = substr($data, 0, 2);
        return date("d/m/Y", mktime(0, 0, 0, $mes, $dia + 1, $ano));
    }

    /**
     * FUNÇÃO PARA SOMAR UM DIA A DATA
     *
     * @param $data
     * @return false|string
     */
    function somaHoraDataBanco($data, $addHora)
    {
        $ano = substr($data, 0, 4);
        $mes = substr($data, 5, 2);
        $dia = substr($data, 8, 2);
        $hora = substr($data, 11, 2);
        $minutos = substr($data, 14, 2);
        return date("Y-m-d H:m", mktime($hora + $addHora, $minutos, 0, $mes, $dia, $ano));
    }

    function somaDiasBanco($qtd)
    {
        $dataAtual = date('Y-m-d');
        return date('Y-m-d', strtotime("+" . $qtd . " days", strtotime($dataAtual)));
    }

    /**
     * FUNÇÃO PARA SOMAR DIAS UTEIS
     *
     * @param $xDataInicial
     * @param $xSomarDias
     * @param bool $diasuteis
     * @return false|string
     */
    function somaDiasUsuario($xDataInicial, $xSomarDias, $diasuteis = true)
    {
        for ($ii = 0; $ii <= $xSomarDias; $ii++) {

            $xDataInicial = $this->Soma1dia($xDataInicial); //SOMA DIA NORMAL

            if ($diasuteis == true) {
                //VERIFICANDO SE EH DIA DE TRABALHO
                if (date("w", $this->dataToTimestamp($xDataInicial)) == "0") {
                    //SE DIA FOR DOMINGO OU FERIADO, SOMA +1
                    $xDataInicial = $this->Soma1dia($xDataInicial);

                } else if (date("w", $this->dataToTimestamp($xDataInicial)) == "6") {
                    //SE DIA FOR SABADO, SOMA +2
                    $xDataInicial = $this->Soma1dia($xDataInicial);
                    $xDataInicial = $this->Soma1dia($xDataInicial);

                } else {
                    //senaum vemos se este dia eh FERIADO
                    for ($i = 0; $i <= 12; $i++) {
                        if ($xDataInicial == $this->Feriados(date("Y"), $i)) {
                            $xDataInicial = $this->Soma1dia($xDataInicial);
                        }
                    }
                }
            }
        }
        return $xDataInicial;
    }

    function calcularParcelas($dtVencimento, $nParcelas)
    {

        $dataExplode = explode("/", $dtVencimento);

        $dia = $dataExplode[0];
        $mes = $dataExplode[1];
        $ano = $dataExplode[2];

        for ($i = 1; $i <= $nParcelas; $i++) {
            // outra possibilidade
            // ++$mes;
            $mes = $mes + 1;
            if ($mes > 12) {
                $mes = 1;
                // ++$ano;
                $ano = $ano + 1;
            }
            $data[$i] = date("d/m/Y", mktime(0, 0, 0, $mes, $dia, $ano));
        }
        return $data;
    }

    /**
     * FORMATA CPF OU CNPJ PARA O USUÁRIO
     *
     * @param $campo
     * @param bool $formatado
     * @return bool|null|string|string[]
     */
    function formatCpfCnpjUsuario($campo, $formatado = true)
    {
        //retira formato
        $codigoLimpo = preg_replace("[' '-./ t]", '', $campo);
        // pega o tamanho da string menos os digitos verificadores
        $tamanho = (strlen($codigoLimpo) - 2);
        //verifica se o tamanho do c?digo informado ? v?lido
        if ($tamanho != 9 && $tamanho != 12) {
            return false;
        }

        if ($formatado) {
            // seleciona a m?scara para cpf ou cnpj
            if ($tamanho == 9) {
                $mascara = '###.###.###-##';
            } else {
                $mascara = '##.###.###/####-##';
            }

            //$mascara = ($tamanho == 9) ? '###.###.###-##' : '##.###.###/####-##';

            $indice = -1;
            for ($i = 0; $i < strlen($mascara); $i++) {
                if ($mascara[$i] == '#') $mascara[$i] = $codigoLimpo[++$indice];
            }
            //retorna o campo formatado
            $retorno = $mascara;

        } else {
            //se n?o quer formatado, retorna o campo limpo
            $retorno = $codigoLimpo;
        }
        return $retorno;
    }

    /**
     * FUNÇÃO PARA INSERIR UM POPOVER
     *
     * @param $msg
     * @param string $titulo
     * @return string
     */
    function inserirPopover($msg, $titulo = "")
    {
        return "<span class='mdi mdi-comment-question-outline' data-toggle='popover' data-trigger='hover' title='" . $titulo . "' data-content='" . $msg . "'></span>";
    }

    function formatTextoQtdCaracteres($texto, $quantidade)
    {
        return mb_substr($texto, 0, $quantidade, "UTF-8");
    }

    //echo mask($cnpj,'##.###.###/####-##');

    /**
     * FUNÇÃO PARA RETORNAR O TEXTO DE ACORDO COM O FORMATO DA MASCARA EX: ###.###.###-##
     * @param $val
     * @param $mask
     * @return string
     */
    function formatTextoMask($val, $mask)
    {
        $maskared = '';
        $k = 0;
        for ($i = 0; $i <= strlen($mask) - 1; $i++) {
            if ($mask[$i] == '#') {
                if (isset($val[$k]))
                    $maskared .= $val[$k++];
            } else {
                if (isset($mask[$i]))
                    $maskared .= $mask[$i];
            }
        }
        return $maskared;
    }

    public function pegaArrayEstado()
    {
        $estadosBrasileiros = array(
            'AC' => 'Acre',
            'AL' => 'Alagoas',
            'AP' => 'Amapá',
            'AM' => 'Amazonas',
            'BA' => 'Bahia',
            'CE' => 'Ceará',
            'DF' => 'Distrito Federal',
            'ES' => 'Espírito Santo',
            'GO' => 'Goiás',
            'MA' => 'Maranhão',
            'MT' => 'Mato Grosso',
            'MS' => 'Mato Grosso do Sul',
            'MG' => 'Minas Gerais',
            'PA' => 'Pará',
            'PB' => 'Paraíba',
            'PR' => 'Paraná',
            'PE' => 'Pernambuco',
            'PI' => 'Piauí',
            'RJ' => 'Rio de Janeiro',
            'RN' => 'Rio Grande do Norte',
            'RS' => 'Rio Grande do Sul',
            'RO' => 'Rondônia',
            'RR' => 'Roraima',
            'SC' => 'Santa Catarina',
            'SP' => 'São Paulo',
            'SE' => 'Sergipe',
            'TO' => 'Tocantins'
        );

        return $estadosBrasileiros;
    }

    public function pegarArrayDiasDaSemana($abreviado = false)
    {
        if (!$abreviado)
            $diasemana = array('Domingo', 'Segunda-Feira', 'Terça-Feira', 'Quarta-Feira', 'Quinta-Feira', 'Sexta-Feira', 'Sabado');
        else {
            $diasemana = array('Dom.', 'Seg.', 'Ter.', 'Qua.', 'Qui.', 'Sex.', 'Sab.');
        }
        return $diasemana;
    }

    function setDisplayError()
    {
        ini_set('display_errors', 1);
        ini_set('display_startup_errors', 1);
        error_reporting(E_ALL);
    }

    function validaCampo($campo, $tipo=FILTER_VALIDATE_INT) {
        // FILTER_VALIDATE_URL;
        // FILTER_VALIDATE_EMAIL
        if (filter_var($campo, $tipo))
            return true;
        else
            return false;
    }

    function sanitizaCampo($campo, $tipo=FILTER_SANITIZE_STRING){
        return filter_var($campo, $tipo);
    }

    function verificaCampoVazio($valor, $seVazio=""){
        if (!empty($valor) || ($valor === '0')){
            $retorno = $this->sanitizaCampo($valor);
        } else {
            $retorno = $seVazio;
        }
        return $retorno;
    }

    static function inputGet($name){
        return filter_input(INPUT_GET, $name, FILTER_SANITIZE_STRING);
    }

    static function inputPost($name){
        return filter_input(INPUT_POST, $name, FILTER_SANITIZE_STRING);
    }

    function base64ParaArquivo($textoBase64,$caminho){
        // SALVA ARQUIVO
        $arquivo = base64_decode($textoBase64);
        $nome = $this->geraNomeArquivoBase64($arquivo);
        file_put_contents($caminho.$nome, $arquivo);
        return $nome;
    }

    function arquivoParaBase64($arquivo){
        // SALVA ARQUIVO NA BASE LOCAL
        return base64_encode(file_get_contents($arquivo));
    }

    /**
     * FUNÇÃO PARA GERAR NOME DO ARQUIVO NO FORMATO MD5
     *
     * @param string $arquivo
     * @return string
     */
    function geraNomeArquivoBase64($arquivo)
    {
        $f = finfo_open();
        $mime_type = finfo_buffer($f, $arquivo, FILEINFO_MIME_TYPE);
        $split = explode( '/', $mime_type );
        $extensao = $split[1];
        $nomeArquivo = md5(uniqid(time()) . $this->pegaIpUsuario()) . "." . $extensao;

        return $nomeArquivo;
    }

    function &arrayEmMinusculo(&$obj)
    {
        $type = (int) is_object($obj) - (int) is_array($obj);
        if ($type === 0) return $obj;
        foreach ($obj as $key => &$val)
        {
            $element = $this->arrayEmMinusculo($val);
            switch ($type)
            {
                case 1:
                    if (!is_int($key) && $key !== ($keyLowercase = strtolower($key)))
                    {
                        unset($obj->{$key});
                        $key = $keyLowercase;
                    }
                    $obj->{$key} = $element;
                    break;
                case -1:
                    if (!is_int($key) && $key !== ($keyLowercase = strtolower($key)))
                    {
                        unset($obj[$key]);
                        $key = $keyLowercase;
                    }
                    $obj[$key] = $element;
                    break;
            }
        }
        return $obj;
    }

    function tempoCorrido($time) {

        $now = strtotime(date('m/d/Y H:i:s'));
        $time = strtotime($time);
        $diff = $now - $time;

        $seconds = $diff;
        $minutes = round($diff / 60);
        $hours = round($diff / 3600);
        $days = round($diff / 86400);
        $weeks = round($diff / 604800);
        $months = round($diff / 2419200);
        $years = round($diff / 29030400);

        if ($seconds <= 60) return"1 min atrás";
        else if ($minutes <= 60) return $minutes==1 ?'1 min atrás':$minutes.' min atrás';
        else if ($hours <= 24) return $hours==1 ?'1 hrs atrás':$hours.' hrs atrás';
        else if ($days <= 7) return $days==1 ?'1 dia atras':$days.' dias atrás';
        else if ($weeks <= 4) return $weeks==1 ?'1 semana atrás':$weeks.' semanas atrás';
        else if ($months <= 12) return $months == 1 ?'1 mês atrás':$months.' meses atrás';
        else return $years == 1 ? 'um ano atrás':$years.' anos atrás';
    }

    function verificaRecaptcha($token){

        $secret = CONFIG_RECAPTCHA['chaveSecreta'];
        $remote_ip = $_SERVER["REMOTE_ADDR"];
        $recaptcha = new ReCaptcha($secret);
        $g_recaptcha_response = $token;
        $resposta = $recaptcha->verify($g_recaptcha_response, $remote_ip);
        if ($resposta->isSuccess()) {
            return true;
        } else {
            return false;
        }
    }

    function ofuscaCampo($texto, $inicio, $final){
        $qtd = strlen($texto)-$inicio-$final;
        $asc = str_repeat('*', $qtd);
        return substr_replace($texto, $asc, $inicio, $qtd);
    }

    /** Busca por um valor em uma matriz com base na coluna e retorna o index do array */
    public static function searchArray($value, array $array, string $column_name) {
        $key =  array_search($value, array_column(json_decode(json_encode($array),TRUE), $column_name ));  
        return $key ; 
    }

}

