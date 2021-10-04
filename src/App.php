<?php

declare(strict_types=1);

namespace App;

use App\actions\AccountAction;
use App\actions\CacheAction;
use App\actions\ChangeAccountAction;
use App\actions\DomainAction;
use App\actions\ExitAction;
use DigitalOceanV2\Client;
use Exception;
use League\CLImate\CLImate;
use Yiisoft\Arrays\ArrayHelper;

/**
 * @author grayfolk
 */
class App
{
    /**
     * Available API actions.
     */
    public const ACTIONS = [
        'View account info' => AccountAction::class,
        'Move domain to another account' => DomainAction::class,
        'Change account' => ChangeAccountAction::class,
        'Clear cache' => CacheAction::class,
        'Exit' => ExitAction::class,
    ];

    /**
     * @var string
     */
    public string $account;

    /**
     * @var array
     */
    public array $accounts;

    /**
     * @var string
     */
    public string $action;

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

        $this->climate->clear();

        $this->options = getopt('wf::');

        $this->isDryRun = !isset($this->options['w']);
    }

    /**
     * @param mixed $excludes
     * @throws Exception
     */
    public function selectAccount($excludes = []): void
    {
        if (!file_exists('accounts.json') || !is_file('accounts.json') || !is_readable('accounts.json')) {
            throw new Exception('accounts.json not exist');
        }

        $json = file_get_contents('accounts.json');

        $this->accounts = json_decode($json, true);

        if (!\is_array($this->accounts)) {
            throw new Exception('No accounts found in accounts.json');
        }

        if ($excludes) {
            $this->accounts = ArrayHelper::remove($this->accounts, $excludes);
        }

        if (!\count($this->accounts)) {
            throw new Exception('No accounts found in accounts.json');
        }

        $this->account = $this->radio('Select DigitalOcean account:', array_keys($this->accounts));

        $this->auth($this->accounts[$this->account]);
    }

    /**
     * @throws Exception
     */
    public function auth(string $apiKey): void
    {
        if (!$this->client) {
            $this->client = new Client();
        }

        $this->client->authenticate($apiKey);

        AccountAction::getInstance($this)->getInfo();
    }

    /**
     * @throws Exception
     */
    public function selectAction(): void
    {
        $action = $this->radio('Select what do you want:', array_keys(self::ACTIONS));

        /** @var $className AccountAction|CacheAction|ChangeAccountAction|DomainAction */
        $className = self::ACTIONS[$action];

        try {
            if ($className::getInstance($this)->alert) {
                if ($this->isDryRun) {
                    $this->climate->info('Dry run mode active. No changes will be applied.')->red('If you want to apply changes run file with -w option.');
                } else {
                    $this->climate->red('Working mode active. All changes will be applied.');
                }
            }

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
