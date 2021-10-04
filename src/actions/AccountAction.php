<?php

declare(strict_types=1);

namespace App\actions;

use App\App;
use DigitalOceanV2\Entity\Account as AccountEntity;
use DigitalOceanV2\Exception\ExceptionInterface;
use Exception;

/**
 * @author grayfolk
 */
class AccountAction extends AbstractAction
{
    /**
     * @var AccountAction
     */
    private static AccountAction $instance;

    /**
     * @var AccountEntity|null
     */
    public ?AccountEntity $info = null;

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
     * @return AccountAction
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
        $this->getInfo();

        $this->app->climate->lightCyan()->table([
            [
                'Email' => $this->info->email,
                'Email Verified' => $this->info->emailVerified ? 'Yes' : 'No',
                'Status' => $this->info->status,
                'Droplet Limit' => $this->info->dropletLimit,
                'Floating Ip Limit' => $this->info->floatingIpLimit,
            ],
        ]);

        $response = $this->app->client->getLastResponse();

        $this->app->climate->lightRed()->table([
            [
                'API Rate Limit' => $response->getHeaders()['ratelimit-limit'][0] ?? 'unknown',
                'API Rate Limit Remaining' => $response->getHeaders()['ratelimit-remaining'][0] ?? 'unknown',
            ],
        ]);

        try {
            $this->app->selectAction();
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }

    public function getInfo(): void
    {
        if (!$this->info) {
            try {
                $this->info = $this->app->client->account()->getUserInformation();
            } catch (ExceptionInterface $e) {
                throw new Exception($e->getMessage());
            }
        }
    }

    public function clearCache(): void
    {
        $this->info = null;
    }
}
