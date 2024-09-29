<?php

namespace Apirone\Services;

use Apirone\API\Endpoints\Account;
use Apirone\API\Exceptions\ValidationFailedException;
use Apirone\Models\Invoice;
use Apirone\SDK\Model\InvoiceDetails;
use Exception;
use Illuminate\Support\Facades\DB;

class InvoiceService
{
    protected string $invoice;
    protected array $createParams;
    protected InvoiceDetails $details;
    protected string $status;
    protected string $order;
    protected string $account;

    public function __construct(array $createParams = [], string $account = null)
    {
        $this->createParams = $createParams;
        $this->account = $account;
    }

    /**
     * @throws \ReflectionException
     * @throws Exception
     */
    public function updateOrCreate(?string $account = null)
    {
        if (!isset($this->createParams)) {
            return $this;
        }

        $this->order = array_key_exists('order', $this->createParams) ? $this->createParams['order'] : 0;
        unset($this->createParams['order']);

        $account = $account ? Account::init($account) : Account::init($this->account);

        $options = $this->createParams;
        $options['currency'] = $this->createParams['currency'];

        try {
            DB::beginTransaction();

            $created = $account->invoiceCreate($options);

            // Parse created invoice details
            $this->details = InvoiceDetails::fromJson($created);
            $this->invoice = $this->details->invoice;
            $this->status = $this->details->status;

            $this->save();

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }

        return $this;
    }

    /**
     * Save invoice into database using Eloquent
     *
     * @return bool
     */
    public function save(): bool
    {
        if (!isset($this->details)) {
            return false;
        }

        $invoice = Invoice::updateOrCreate(
            ['id' => $this->invoice->id ?? null],
            [
                'invoice' => $this->details->invoice,
                'status' => $this->status,
                'currency' => $this->details->currency,
                'amount' => $this->details->amount,
                'order' => $this->order,
                'details' => json_encode($this->createParams),
                'meta' => json_encode($this->createParams['meta'] ?? []),
            ]
        );

        return $invoice !== null;
    }
}
