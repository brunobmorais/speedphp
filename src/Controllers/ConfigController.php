<?php
namespace App\Controllers;

use App\Core\Controller\ControllerCore;
use App\Core\Controller\ControllerInterface;
use App\Daos\BuildDao;
use App\Daos\PessoaDao;
use App\Libs\FuncoesLib;
use MatthiasMullie\Minify\CSS;
use MatthiasMullie\Minify\JS;

class ConfigController extends ControllerCore implements ControllerInterface
{
    public function index($args = [])
    {
        $this->redirect("/");
    }

    public function build()
    {
        try {
            if ($this->isModeDeveloper()){

                unlink (dirname(__DIR__,2)."/public/assets/css/my-color-root.css");
                unlink(dirname(__DIR__,2)."/public/assets/css/style.min.css");
                unlink(dirname(__DIR__,2)."/public/assets/js/script.min.js");

                $cssString = ":root {
                --cor-bg-principal: ".CONFIG_COLOR['color-primary'].";
                --cor-bg-principal-hover: ".CONFIG_COLOR['color-primary-hover'].";
                --cor-bg-secodary: ".CONFIG_COLOR['color-secondary'].";
                --cor-bg-link: ".CONFIG_COLOR['color-link'].";
                --cor-bg-navbar: ".CONFIG_COLOR['color-navbar'].";
                }";
                file_put_contents(dirname(__DIR__,2)."/public/assets/css/my-color-root.css", $cssString);

                // GERAR ARQUIVOS CSS MINIFICADOS
                $minCss = new CSS();
                $cssDir = scandir(dirname(__DIR__,2).""."/public/assets/css/");
                foreach ($cssDir as $cssItem){
                    $cssFile = dirname(__DIR__,2)."/public/assets/css/{$cssItem}";
                    if (is_file($cssFile) && pathinfo($cssFile)["extension"] ==  "css"){
                        $minCss->add($cssFile);
                    }
                }
                $minCss->minify(dirname(__DIR__,2)."/public/assets/css/style.min.css");


                // GERAR ARQUIVOS JS MINIFICADOS
                $minJs = new JS();
                $jsDir = scandir(dirname(__DIR__,2).""."/public/assets/js/");
                foreach ($jsDir as $jsItem){
                    $jsFile = dirname(__DIR__,2)."/public/assets/js/{$jsItem}";
                    if (is_file($jsFile) && pathinfo($jsFile)["extension"] ==  "js"){
                        $minJs->add($jsFile);
                    }
                }
                $minJs->minify(dirname(__DIR__,2)."/public/assets/js/script.min.js");

                echo "Gerado com sucesso";
            } else {
                $this->redirect("/");
            }
        } catch (\Error $e) {
            return $e;
        }
    }

    public function createpage($args = [])
    {

        try {
            if ($this->isModeDeveloper()){

                $nomeClass = ucfirst($args[0]??"");
                $nomeClassMinusculo = strtolower($args[0]??"");
                $nomeMetodo = strtolower($args[1]??"");

                if (empty($nomeClass)){
                    echo "Informe o nome da class";
                    exit;
                }
                $conteudoMetodo = "
}";
                if (!empty($nomeMetodo)) {

                        $conteudoMetodo = '
    public function ' . $nomeMetodo . '($args = []){}
}';
                }

                if (!file_exists(dirname(__DIR__, 2) . "/src/Controllers/{$nomeClass}Controller.php")) {
                    $conteudoClass = '<?php
    namespace App\Controllers;
    
    class ' .$nomeClass.'Controller extends ControllerCore implements ControllerInterface
    {
        public function index($args  = [])
        {
        
        }'.$conteudoMetodo.'
    ';
                } else {
                    $conteudoClass = file_get_contents(dirname(__DIR__, 2) . "/src/Controllers/{$nomeClass}Controller.php");
                    $conteudoClass = substr($conteudoClass, 0, -1);
                    $conteudoClass .= $conteudoMetodo;
                }

                file_put_contents(dirname(__DIR__, 2) . "/src/Controllers/{$nomeClass}Controller.php", $conteudoClass);
                echo "Classe controller gerada em: /src/Controllers/{$nomeClass}Controller.php<br/>";

                if (!mkdir($concurrentDirectory = dirname(__DIR__, 2) . "/templates/{$nomeClassMinusculo}/", 0777, true) && !is_dir($concurrentDirectory)) {
                    throw new \RuntimeException(sprintf('Directory "%s" was not created', $concurrentDirectory));
                }


                if (!file_exists(dirname(__DIR__, 2) . "/templates/{$nomeClassMinusculo}/{$nomeClassMinusculo}.html.twig") && !empty($nomeClass)) {
                    file_put_contents(dirname(__DIR__, 2) . "/templates/{$nomeClassMinusculo}/{$nomeClassMinusculo}.html.twig", "");
                    file_put_contents(dirname(__DIR__, 2) . "/templates/{$nomeClassMinusculo}/{$nomeClassMinusculo}.css", "");
                    file_put_contents(dirname(__DIR__, 2) . "/templates/{$nomeClassMinusculo}/{$nomeClassMinusculo}.js", "");
                    echo "Arquivos templates controller gerado: /templates/{$nomeClassMinusculo}/{$nomeClassMinusculo}.html.twig<br/>";
                }

                if (!mkdir($concurrentDirectory = dirname(__DIR__, 2) . "/templates/{$nomeClassMinusculo}/{$nomeMetodo}/", 0777, true) && !is_dir($concurrentDirectory)) {
                    throw new \RuntimeException(sprintf('Directory "%s" was not created', $concurrentDirectory));
                }

                if (!file_exists(dirname(__DIR__, 2) . "/templates/{$nomeClassMinusculo}/{$nomeMetodo}/{$nomeMetodo}.html.twig") && !empty($nomeMetodo)) {
                    file_put_contents(dirname(__DIR__, 2) . "/templates/{$nomeClassMinusculo}/{$nomeMetodo}/{$nomeMetodo}.html.twig", "");
                    file_put_contents(dirname(__DIR__, 2) . "/templates/{$nomeClassMinusculo}/{$nomeMetodo}/{$nomeMetodo}.css", "");
                    file_put_contents(dirname(__DIR__, 2) . "/templates/{$nomeClassMinusculo}/{$nomeMetodo}/{$nomeMetodo}.js", "");
                    echo "Arquivos templates método gerado: /templates/{$nomeClassMinusculo}/{$nomeMetodo}/{$nomeMetodo}.html.twig<br/>";
                }

                echo "Gerado com sucesso";
            } else {
                $this->redirect("/");
            }
        } catch (\Exception $e) {
            return $e;
        }
    }

    public function createmodel($args = [])
    {
        try {
            if (!$this->isModeDeveloper()) {
                $this->redirect("/");
                exit();
            }

            $nomeClassModel = (new FuncoesLib())->removeCaracteres(ucfirst($args[0] ?? ""));
            $nomeTable = strtoupper($args[0] ?? "");

            if (empty($nomeClassModel)) {
                echo "Informe o nome da class";
                exit;
            }

            $dao = new BuildDao();
            $columns = $dao->execute("SELECT * FROM $nomeTable");
            if (empty($columns)) {
                echo "Nenhuma tabela encontrada";
                exit;
            }

            $variavel = "";
            foreach ($columns as $col) {
                $variavel .= "Protected $" . $col . ";\n    ";
            }

            $gets = $this->creategets($columns);
            $sets = $this->createsets($columns);


            $conteudoClass = "<?php
namespace App\Models;
use BMorais\Database\ModelAbstract;

class {$nomeClassModel}Model extends ModelAbstract
{
    {$variavel}
    {$gets}
    {$sets}
}";

            file_put_contents(dirname(__DIR__, 2) . "/src/Models/{$nomeClassModel}Model.php", $conteudoClass);

            echo "executado com sucesso";
        } catch (\Exception $e){
            return $e;
        }
    }

    private function createsets($columns){
        if (empty($columns))
            return [];
        $gets = "";
        foreach ($columns as $col) {
            $gets .= 'public function set'.$col.'($'.$col.'):self
    {
        $this->'.$col.' = $'.$col.';
        return $this;
    }
    
    ';
        }
        return $gets;
    }

    private function creategets($columns){
        if (empty($columns))
            return [];
        $sets = "";
        foreach ($columns as $col) {
            $sets .= 'public function get'.$col.'()
    {
        return $this->'.$col.';
    }
    
    ';
        }
        return $sets;
    }

}
