<?php

namespace AppBundle\Billing;


use Cake\Collection\Collection;

class StripePaymentGateway implements PaymentGateway
{
    protected $charges;

    /**
     * FakePaymentGateway constructor.
     * @param $charges
     */
    public function __construct(array $charges = [])
    {
        $this->charges = new Collection($charges);
    }

    public function charge($amount, $token)
    {
        $this->charges = $this->charges->append([$amount]);
    }
}