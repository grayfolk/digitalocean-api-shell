<?php

declare(strict_types=1);

namespace App;

use App\actions\AccountAction;
use App\actions\CacheAction;
use App\actions\ChangeAccountAction;
use App\actions\ExitAction;
use App\actions\MoveDomainAction;
use DigitalOceanV2\Client;
use Exception;
use League\CLImate\CLImate;

/**
 * @author grayfolk
 * @version 1.0.3
 */
class App
{
    public const VERSION = '1.0.3';

    /**
     * Available API actions.
     */
    public const ACTIONS = [
        'View account info' => AccountAction::class,
        'Move domain to another account' => MoveDomainAction::class,
        'Change account' => ChangeAccountAction::class,
        'Clear cache' => CacheAction::class,
        'Exit (or Ctrl+C)' => ExitAction::class,
    ];

    /**
     * @var string|null
     */
    public ?string $account = null;

    /**
     * @var array
     */
    public array $accounts = [];

    /**
     * @var array|false|false[]|string[]|null
     */
    public ?array $options = null;

    /**
     * @var bool
     */
    public bool $isDryRun = true;

    /**
     * @var CLImate
     */
    public CLImate $climate;

    /**
     * @var Client|null
     */
    public ?Client $client = null;

    public function __construct()
    {
        $this->climate = new CLImate();

        $this->drawHeader();

        $this->options = getopt('wf::');

        $this->isDryRun = !isset($this->options['w']);
    }

    public function drawHeader(): void
    {
        $this->climate->clear();

        $this->climate->bold()->green()->flank(sprintf('DigitalOcean API Console wrapper. v%s', self::VERSION));

        if ($this->account) {
            $this->climate->bold()->green()->flank(sprintf('You\'re logged as %s', $this->account));
        }

        $this->climate->br();
    }

    /**
     * @param string $message
     * @param array $excludes
     * @param bool $forceAuth
     * @return string
     * @throws Exception
     */
    public function selectAccount(string $message = 'Select DigitalOcean account:', array $excludes = [], bool $forceAuth = true): string
    {
        if (!$this->accounts) {
            if (!file_exists('accounts.json') || !is_file('accounts.json') || !is_readable('accounts.json')) {
                throw new Exception('accounts.json not exist');
            }

            $json = file_get_contents('accounts.json');

            $this->accounts = json_decode($json, true);
        }

        if (!\is_array($this->accounts)) {
            throw new Exception('No accounts found in accounts.json');
        }

        $accounts = $this->accounts;

        if ($excludes) {
            foreach ($excludes as $exclude) {
                unset($accounts[$exclude]);
            }
        }

        if (!\count($accounts)) {
            throw new Exception('No accounts found in accounts.json');
        }

        $account = $this->radio($message, array_keys($accounts));

        if ($forceAuth) {
            $this->account = $account;

            $this->auth($this->accounts[$this->account]);
        }

        return $account;
    }

    /**
     * @param string $apiKey
     * @param bool $drawHeader
     * @throws Exception
     */
    public function auth(string $apiKey, bool $drawHeader = true): void
    {
        if (!$this->client) {
            $this->client = new Client();
        }

        $this->client->authenticate($apiKey);

        AccountAction::getInstance($this)->getInfo();

        if ($drawHeader) {
            $this->drawHeader();
        }
    }

    /**
     * @throws Exception
     */
    public function selectAction(): void
    {
        $action = $this->radio('Select what do you want:', array_keys(self::ACTIONS));

        /** @var $className AccountAction|CacheAction|ChangeAccountAction|MoveDomainAction|ExitAction */
        $className = self::ACTIONS[$action];

        try {
            if ($className::getInstance($this)->alert) {
                if ($this->isDryRun) {
                    $this->climate->info('Dry run mode active. No changes will be applied.')->red('If you want to apply changes run file with -w option.');
                } else {
                    $this->climate->red('Working mode active. All changes will be applied.');
                }
            }

            $this->drawHeader();

            $className::getInstance($this)->run();
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }

    /**
     * @param string $message
     * @param array $values
     * @return string
     */
    public function radio(string $message, array $values): string
    {
        return $this->climate->radio($message, $values)->prompt();
    }
}
