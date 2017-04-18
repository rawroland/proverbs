<?php

namespace AppBundle\Billing;


use Cake\Collection\Collection;
use Stripe\Charge;
use Stripe\Error\InvalidRequest;
use Stripe\Token;

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
        } catch (InvalidRequest $exception) {
            throw new PaymentFailedException();
        }
    }

    public function getValidTestToken(): string
    {
        return Token::create([
            "card" => [
                "number" => "4242424242424242",
                "exp_month" => date('m'),
                "exp_year" => date('Y') + 1,
                "cvc" => "123"
            ]
        ], ['api_key' => $this->apiKey])->id;
    }

    public function newChargesDuring($callback)
    {
        $latestCharge = $this->lastCharge();

        $callback($this);

        return $this->newChargesSince($latestCharge)->map(function (Charge $charge) {return $charge->amount;});
    }

    /**
     * @return Charge
     */
    protected function lastCharge()
    {
        $charges = Charge::all(
            ['limit' => 1],
            ['api_key' => $this->apiKey]
        )['data'];
        $charge = array_shift($charges);

        return $charge;
    }

    protected function newChargesSince(Charge $charge = null)
    {
        $lastCharges = Charge::all(
            ['ending_before' => $charge ? $charge->id : null],
            ['api_key' => $this->apiKey]
        )['data'];

        return new Collection($lastCharges);
    }
}
