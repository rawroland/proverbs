<?php

namespace AppBundle\Billing;


use Cake\Collection\Collection;
use Stripe\Charge;
use Stripe\Error\InvalidRequest;

class StripePaymentGateway implements PaymentGateway
{
    /**
     * @var Collection
     */
    protected $charges;

    /**
     * @var string
     */
    private $apiKey;

    /**
     * FakePaymentGateway constructor.
     * @param $charges
     */
    public function __construct($apiKey, array $charges = [])
    {
        $this->apiKey = $apiKey;
        $this->charges = new Collection($charges);
    }

    public function charge($amount, $token)
    {
        try {
            Charge::create([
                'amount' => $amount,
                'currency' => 'eur',
                'source' => $token,
            ], ['api_key' => $this->apiKey]);
            $this->charges = $this->charges->append([$amount]);
        } catch (InvalidRequest $exception) {
            throw new PaymentFailedException();
        }
    }
}
