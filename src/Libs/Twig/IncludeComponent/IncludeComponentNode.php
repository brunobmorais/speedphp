<?php

namespace App\Libs\Twig\IncludeComponent;

use Twig\Compiler;
use Twig\Node\Node;

/**
 * Node que compila a tag {% include_component %}
 * Gera o PHP que renderiza css + html + js do componente
 */
class IncludeComponentNode extends Node
{
    public function __construct(Node $path, ?Node $variables, int $lineno, string $tag)
    {
        $nodes = ['path' => $path];
        if ($variables !== null) {
            $nodes['variables'] = $variables;
        }

        parent::__construct($nodes, [], $lineno, $tag);
    }

    public function compile(Compiler $compiler): void
    {
        $compiler->addDebugInfo($this);

        $compiler
            ->write('$__component_path = ')
            ->subcompile($this->getNode('path'))
            ->raw(";\n");

        if ($this->hasNode('variables')) {
            $compiler
                ->write('$__component_vars = ')
                ->subcompile($this->getNode('variables'))
                ->raw(";\n");
        } else {
            $compiler->write('$__component_vars = [];' . "\n");
        }

        $compiler->write(<<<'PHP'
$__segments = explode('/', $__component_path);
$__component_name = end($__segments);
$__file_name = "_{$__component_name}";
$__merged_vars = array_merge($context, $__component_vars);
$__loader = $this->env->getLoader();

// CSS (opcional)
$__css_file = "{$__component_path}/{$__file_name}.css.twig";
if ($__loader->exists($__css_file)) {
    echo '<style>';
    $this->env->display($__css_file, $__merged_vars);
    echo '</style>' . "\n";
}

// HTML (obrigatório)
$__html_file = "{$__component_path}/{$__file_name}.html.twig";
$this->env->display($__html_file, $__merged_vars);

// JS (opcional)
$__js_file = "{$__component_path}/{$__file_name}.js.twig";
if ($__loader->exists($__js_file)) {
    echo "\n" . '<script>';
    $this->env->display($__js_file, $__merged_vars);
    echo '</script>';
}

PHP
        );
    }
}
