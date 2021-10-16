<?php

declare(strict_types=1);

namespace App\actions;

use App\App;

abstract class AbstractAction
{
    public bool $alert = false;

    public function clearCache(): void
    {
    }

    abstract public static function getInstance(App $app): self;

    abstract public function run(): void;
}
