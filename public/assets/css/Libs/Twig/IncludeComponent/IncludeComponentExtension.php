<?php

namespace App\Libs\Twig\IncludeComponent;

use Twig\Extension\AbstractExtension;

/**
 * Extension que registra a tag {% include_component %} no Twig
 */
class IncludeComponentExtension extends AbstractExtension
{
    public function getTokenParsers(): array
    {
        return [new IncludeComponentTokenParser()];
    }
}
