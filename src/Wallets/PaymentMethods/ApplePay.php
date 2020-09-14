<?php

namespace Kanvas\Packages\Wallets\PaymentMethods;

use Gewaer\Models\Users;
use Kanvas\Packages\MobilePayments\Contracts\AppleReceipts;
use Kanvas\Packages\MobilePayments\Contracts\ReceiptValidatorTrait;

class ApplePay implements PaymentMethodsInterface
{
    /**
     * Receipt Validator Trait
     */
    use ReceiptValidatorTrait;

    public ?Users $users = null;
    public ?string $customer_id = null;

    public function __construct(Users $users)
    {
        $this->users = $users;
        $this->customer_id = $users->getDefaultCompany()->get('payment_gateway_customer_id');
    }

    /**
     * Can be charged to user
     * @return bool
     */
    public function canCharge() : bool
    {
        return is_null($this->customer_id) ? false : true;
    }

    /**
     * charge the user
     * @param float $amount
     * @return Transactions
     */
    public function charge(float $amount) : bool
    {
        return true;
    }

    /**
     * Validate Apple Pay payment via receipt
     *
     * @param string $receipt
     *
     * @return array
     */
    public function validatePayment(string $receipt) : array
    {
        $receipt = $this->validateReceipt($receipt);

        if (gettype($receipt) == "string") {
            throw new Exception($receipt);
        }

        return $this->parseReceiptData($receipt, 'apple');
    }
}
