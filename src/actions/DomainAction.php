<?php

declare(strict_types=1);

namespace App\actions;

use App\App;
use DigitalOceanV2\Exception\ExceptionInterface;
use Exception;
use Yiisoft\Arrays\ArrayHelper;

/**
 * @author grayfolk
 */
class DomainAction extends AbstractAction
{
    /**
     * @var DomainAction
     */
    private static DomainAction $instance;

    /**
     * @var App
     */
    public App $app;

    /**
     * @var string
     */
    public string $domain;

    /**
     * @var array
     */
    public array $domains = [];

    /**
     * @var array
     */
    public array $domainRecords = [];

    /**
     * @var bool
     */
    public bool $alert = true;

    /**
     * @param App $app
     */
    protected function __construct(App $app)
    {
        $this->app = $app;
    }

    /**
     * @param App $app
     * @return DomainAction
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
        if (!$this->domains) {
            try {
                $this->domains = $this->app->client->domain()->getAll();
            } catch (ExceptionInterface $e) {
                throw new Exception($e->getMessage());
            }
        }

        $this->domain = $this->app->radio('Select domain:', ArrayHelper::getColumn($this->domains, 'name'));

        try {
            // Save zone file
            $file = sprintf('./tmp/%s-%s.conf', $this->domain, microtime(true));
            $domain = $this->app->client->domain()->getByName($this->domain);
            file_put_contents($file, $domain->zoneFile);
            $this->app->climate->info("Zone file backuped: {$file}");
        } catch (ExceptionInterface $e) {
            throw new Exception($e->getMessage());
        }

        if (!$this->domainRecords) {
            try {
                $this->domainRecords = $this->app->client->domainRecord()->getAll($this->domain);
            } catch (ExceptionInterface $e) {
                throw new Exception($e->getMessage());
            }
        }

        unset($this->app->accounts[$this->app->account]);

        if (!\count($this->app->accounts)) {
            throw new Exception("No accounts to move {$this->domain}");
        }

        $newAccount = $this->app->radio("Select account to move {$this->domain}", array_keys($this->app->accounts));

        $ips = [];

        foreach ($this->domainRecords as $value) {
            switch ($value->type) {
                default:
                    break;
                case 'A':
                case 'AAAA':
                    $ips[] = $value->data;
                    break;
            }
        }

        $ip = $this->app->radio('Select domain ip (A or AAAA):', $ips);

        try {
            if (!$this->app->isDryRun) {
                $this->app->client->domain()->remove($this->domain);
            }
        } catch (ExceptionInterface $e) {
            throw new Exception($e->getMessage());
        }

        $this->app->auth($this->app->accounts[$newAccount]);

        try {
            if (!$this->app->isDryRun) {
                $this->app->client->domain()->create($this->domain, $ip);
            }
        } catch (ExceptionInterface $e) {
            throw new Exception($e->getMessage());
        }

        $data = [];

        foreach ($this->domainRecords as $value) {
            if ($this->app->isDryRun) {
                $data[] = [
                    'Type' => $value->type,
                    'Name' => $value->name,
                    'Data' => \strlen($value->data) > 50 ? substr($value->data, 0, 50) . '...' : $value->data,
                ];
            }

            if ('SOA' === $value->type) {
                continue;
            }

            if (\in_array($value->type, ['A', 'AAAA'], true)) {
                if ($value->data === $ip) {
                    continue;
                }
            }

            if ('NS' === $value->type) {
                if (false !== stripos($value->data, '.digitalocean.com')) {
                    continue;
                }
            }

            if (\in_array($value->type, ['CNAME', 'MX', 'NS'], true) && '@' !== $value->data) {
                $value->data = sprintf('%s.', trim($value->data, '.'));
            }

            if (!$this->app->isDryRun) {
                $this->app->climate->green(sprintf('<light_blue>Adding:</light_blue> %s: %s <bold><black><background_light_gray>%s</background_light_gray></black></bold>', $value->type, $value->name, $value->data));
            }

            try {
                if (!$this->app->isDryRun) {
                    $this->app->client->domainRecord()->create($this->domain, $value->type, $value->name, $value->data, $value->priority, $value->port, $value->weight, $value->flags, $value->tag, $value->ttl);
                }
            } catch (ExceptionInterface $e) {
                throw new Exception($e->getMessage());
            }
        }

        if ($this->app->isDryRun) {
            $this->app->climate->lightCyan()->table($data);
        } else {
            try {
                $domainRecords = $this->app->client->domainRecord()->getAll($this->domain);

                foreach ($domainRecords as $value) {
                    $data[] = [
                        'Type' => $value->type,
                        'Name' => $value->name,
                        'Data' => \strlen($value->data) > 50 ? substr($value->data, 0, 50) . '...' : $value->data,
                    ];
                }

                $this->app->climate->green("{$this->domain} moved.");
                $this->app->climate->lightCyan()->table($data);
            } catch (ExceptionInterface $e) {
                throw new Exception($e->getMessage());
            }
        }

        try {
            $this->app->selectAction();
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }

    public function clearCache(): void
    {
        $this->domains = $this->domainRecords = [];
    }
}
