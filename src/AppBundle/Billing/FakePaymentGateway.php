<?php

namespace AppBundle\Billing;


use Cake\Collection\Collection;

class FakePaymentGateway implements PaymentGateway
{
    protected $charges;

    private $beforeFirstChargeCallback = null;

    /**
     * FakePaymentGateway constructor.
     * @param $charges
     */
    public function __construct(array $charges = [])
    {
        $this->charges = new Collection($charges);
    }

    public function getValidToken()
    {
        return 'valid_token';
    }

    public function charge($amount, $token)
    {
        if ($this->beforeFirstChargeCallback !== null) {
            $callback = $this->beforeFirstChargeCallback;
            $this->beforeFirstChargeCallback = null;
            $callback($this);
        }

        if ($token !== $this->getValidToken()) {
            throw new PaymentFailedException();
        }
        $this->charges = $this->charges->append([$amount]);
    }

    public function totalCharges()
    {
        return $this->charges->reduce(function ($sum, $value) {
            return $sum + $value;
        }, 0);
    }

    public function beforeFirstCharge($callback)
    {
        $this->beforeFirstChargeCallback = $callback;
    }
}