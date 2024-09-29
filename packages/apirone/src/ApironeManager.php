<?php

namespace Apirone;

use Apirone\API\Endpoints\Account;
use Apirone\API\Exceptions\ValidationFailedException;
use Apirone\API\Log\LoggerWrapper;
use Apirone\SDK\Invoice;
use Apirone\SDK\Model\Settings;
use Apirone\SDK\Service\Utils;
use Apirone\Services\InvoiceService;

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

    /**
     * @throws ValidationFailedException
     * @throws \ReflectionException
     */
    public function __construct()
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
     * Configure currencies and destinations.
     *
     * @param array $currencies
     * @return ApironeManager
     * @throws ValidationFailedException
     */
    public function currencies(array $currencies): ApironeManager
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
     * @return InvoiceService
     * @throws \ReflectionException
     */
    public function createInvoice(string $currency, int $amount): InvoiceService
    {
        $invoiceService = new InvoiceService([
            'currency' => $currency,
            'amount' => $amount,
            'lifetime' => config('apirone.lifetime'),
            'callback_url' => config('apirone.callback_url'),
        ], $this->settings->getAccount());

        return $invoiceService->updateOrCreate();
    }

    /**
     * Get invoice info and status
     * @throws ValidationFailedException
     */
    public function getInvoiceInfo($invoice, $private = false)
    {
        $account = Account::init($this->settings->getAccount());

        return $account->invoiceInfo($invoice, $private);
    }

    /**
     * @param $invoice
     * @return never|string
     * @throws ValidationFailedException
     */
    public function renderInvoice($invoice)
    {
        return Invoice::renderLoader($invoice);
    }

    //callback handler

    /**
     * @throws ValidationFailedException
     * @throws \ReflectionException
     */
    public function callbackHandler($data = null)
    {
        if(!$data) {
            return ['status' => 'error', 'message' => 'No data provided'];
        }

        if(isset($data['invoice'])) {
            $invoice = $this->getInvoiceInfo($data['invoice'], true);
            if($invoice) {
                return ['status' => 'ok', 'invoice' => $invoice];
            }

            return ['status' => 'error', 'message' => 'Invoice not found'];
        }

        $invoice = new InvoiceService($data, $this->settings->getAccount());
        $invoice->updateOrCreate();

        return ['status' => 'success'];
    }
}
