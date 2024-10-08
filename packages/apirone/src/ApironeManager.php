<?php

namespace Apirone;

use Apirone\API\Exceptions\ValidationFailedException;
use Apirone\SDK\Invoice;
use Apirone\SDK\Model\Settings;
use Apirone\SDK\Model\UserData;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;

class ApironeManager
{
    /**
     * @var Settings|null
     */
    protected ?Settings $settings;

    /**
     * @var string
     */
    protected string $settingPath;

    protected \Closure $logger;

    protected \Closure $db_handler;

    /**
     * @throws \ReflectionException|ValidationFailedException
     */
    public function __construct()
    {
        $this->initializeSettings();
        $this->initializeLogger();
        $this->initializeDbHandler();
    }

    /**
     * Set currencies and destinations.
     *
     * @param array $currencies
     * @return ApironeManager
     * @throws ValidationFailedException
     */
    public function setCurrencies(array $currencies): ApironeManager
    {
        foreach ($currencies as $currency) {
            if (!isset($currency['name'], $currency['destination'], $currency['fee'])) {
                throw new ValidationFailedException('Each currency must have a name, destination, and fee.');
            }

            $this->settings->getCurrency($currency['name'])
                ->setAddress($currency['destination'])
                ->setPolicy($currency['fee']);
        }

        $this->settings->saveCurrencies();

        $this->settings->toFile($this->settingPath);

        return $this;
    }


    /**
     * Create a new invoice.
     * @param string $currency
     * @param int $amount
     * @param ?UserData $userData
     * @return Invoice
     */
    public function createInvoice(string $currency, int $amount, ?UserData $userData = null, ?string $linkback = null): Invoice
    {
        $this->setInvoiceSettings();

        $invoice = Invoice::init($currency, $amount);

        $invoice = $invoice->lifetime(config('apirone.lifetime'));
        $invoice = $invoice->callbackUrl(config('apirone.callback_url'));

        if($userData) {
            $invoice->userData($userData);
        }

        if($linkback) {
            $invoice->linkback($linkback);
        }

        return $invoice->create();
    }

    /**
     * Get invoice info and status
     */
    public function getInvoice($invoice, $private = false)
    {
        $this->setInvoiceSettings();

        $invoice = Invoice::getInvoice($invoice);

        return $invoice->info($private);
    }

    /**
     * @param $invoice
     * @return never|string
     * @throws ValidationFailedException
     */
    public function renderInvoice($invoice)
    {
        $this->setInvoiceSettings();

        $invoice = Invoice::getInvoice($invoice);

        return Invoice::renderLoader($invoice);
    }

    /**
     * Callback handler
     * @param callable|null $orderHandler
     * @return void
     * @throws ValidationFailedException
     * @throws \ReflectionException
     */
    public function callbackHandler(callable $orderHandler = null)
    {
        return Invoice::callbackHandler($orderHandler);
    }

    /**
     * Initialize logger
     * @return void
     */
    protected function initializeLogger(): void
    {
        $this->logger = static function ($level, $message, $context) {
            file_put_contents(storage_path('logs/apirone.log'), print_r([$level, $message, $context], true) . "\r\n", FILE_APPEND);
        };

        Invoice::setLogger($this->logger);
    }

    /**
     * Initialize settings
     * @return void
     * @throws ValidationFailedException
     * @throws \ReflectionException
     */
    protected function initializeSettings()
    {
        $settingsPath = config('apirone.settings_file_path');

        if (!file_exists($settingsPath)) {
            $this->settings = Settings::init();

            $this->settings->createAccount();
        } else {
            $this->settings = Settings::fromFile($settingsPath);
        }

        $this->settingPath = $settingsPath;
    }

    /**
     * Initialize database handler
     * @return void
     */
    protected function initializeDbHandler(): void
    {
        $this->db_handler = function ($query) {
            $query = str_replace('\u', '\\\u', $query);
            try {
                return $this->executeQuery($query) ?: true;
            } catch (QueryException $e) {
                return $e->getMessage();
            }
        };
    }

    /**
     * Set invoice settings
     * @return void
     */
    private function setInvoiceSettings(): void
    {
        Invoice::db($this->db_handler, config('apirone.table_prefix'));
        Invoice::settings($this->settings);
    }

    /**
     * Execute database query
     * @param string $query
     * @param array $params
     * @return array|bool|int|null
     */
    private function executeQuery(string $query, array $params = []): array|bool|int|null
    {
        $queryType = strtoupper(substr(trim($query), 0, 6));

        return match ($queryType) {
            'SELECT' => json_decode(json_encode(DB::select($query, $params)), true),
            'INSERT' => DB::insert($query, $params),
            'UPDATE' => DB::update($query, $params),
            'DELETE' => DB::delete($query, $params),
            default => throw new \InvalidArgumentException("Unsupported query type: $queryType"),
        };
    }
}
