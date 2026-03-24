<?php

namespace App\Libs\Twig\IncludeComponent;

use Twig\Token;
use Twig\TokenParser\AbstractTokenParser;

/**
 * Parser para a tag {% include_component %}
 *
 * Sintaxe:
 *   {% include_component "caminho/do/componente" %}
 *   {% include_component "caminho/do/componente" with { var1: "valor", var2: variavel } %}
 */
class IncludeComponentTokenParser extends AbstractTokenParser
{
    public function parse(Token $token): IncludeComponentNode
    {
        $stream = $this->parser->getStream();

        // Pega o caminho do componente (expressão string)
        $path = $this->parser->getExpressionParser()->parseExpression();

        // Verifica se tem "with"
        $variables = null;
        if ($stream->nextIf(Token::NAME_TYPE, 'with')) {
            $variables = $this->parser->getExpressionParser()->parseExpression();
        }

        // Fecha a tag %}
        $stream->expect(Token::BLOCK_END_TYPE);

        return new IncludeComponentNode($path, $variables, $token->getLine(), $this->getTag());
    }

    public function getTag(): string
    {
        return 'include_component';
    }
}
