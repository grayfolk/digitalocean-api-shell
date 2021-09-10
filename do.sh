#!/usr/bin/env php
<?php

declare(strict_types=1);

use App\App;

require_once('vendor/autoload.php');

$app = new App();

try {
    $app->selectAccount();
    $app->selectAction();
} catch (Exception $e) {
    return $app->climate->error($e->getMessage());
}
