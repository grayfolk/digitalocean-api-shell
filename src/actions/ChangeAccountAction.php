<?php

declare(strict_types=1);

namespace App\actions;

use App\App;
use Exception;

/**
 * @author grayfolk
 */
class ChangeAccountAction extends AbstractAction
{
    /**
     * @var ChangeAccountAction
     */
    private static ChangeAccountAction $instance;

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
     * @return ChangeAccountAction
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
        try {
            $this->app->selectAccount();
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }

        CacheAction::getInstance($this->app)->run();

        try {
            $this->app->selectAction();
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }
}
