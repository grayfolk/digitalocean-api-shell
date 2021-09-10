<?php

declare(strict_types=1);

namespace App\actions;

interface ActionInterface
{
    public function clearCache(): void;
}
