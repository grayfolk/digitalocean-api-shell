<?php

declare(strict_types=1);

namespace App\actions;

use App\App;
use Exception;

/**
 * @author grayfolk
 */
class CacheAction extends AbstractAction
{
    /**
     * @var CacheAction
     */
    private static CacheAction $instance;

    /**
     * @var App
     */
    public App $app;

    /**
     * @param App $app
     */
    protected function __construct(App $app)
    {
        $this->app = $app;
    }

    /**
     * @param App $app
     * @return CacheAction
     */
    public static function getInstance(App $app): self
    {
        if (!isset(self::$instance)) {
            self::$instance = new static($app);
        }

        return self::$instance;
    }

    /**
     * @throws Exception
     */
    public function run(): void
    {
        foreach (App::ACTIONS as $class) {
            $class::getInstance($this->app)->clearCache();
        }

        try {
            $this->app->selectAction();
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }
}
