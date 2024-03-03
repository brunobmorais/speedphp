<?php

namespace App\Libs\Twig\Components;

use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent]
class Alert
{
    public string $type = 'success';
    public string $message;

    public function __construct(UxPackageRepository $packageRepository)
    {
    }

    public function getIcon(): string
    {
        return match ($this->type) {
            'success' => 'circle-check',
            'danger' => 'circle-exclamation',
        };
    }

    public function getPackageCount(): int
    {
        return $this->packageRepository->count();
    }
}