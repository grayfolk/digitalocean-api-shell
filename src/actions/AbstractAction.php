<?php

declare(strict_types=1);

namespace App\actions;

abstract class AbstractAction
{
    public bool $alert = false;

    public function clearCache(): void
    {
    }
}
