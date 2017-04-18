<?php

namespace Tests\AppBundle\Billing;

use AppBundle\Billing\FakePaymentGateway;
use AppBundle\Billing\PaymentFailedException;
use AppBundle\Billing\StripePaymentGateway;
use Stripe\Charge;
use Stripe\Token;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/**
 * @group network-dependent
 */
class StripePaymentGatewayTest extends KernelTestCase
{

    /**
     * @var Charge
     */
    private $lastCharge;

    public function setUp()
    {
        parent::setUp();
        static::bootKernel();
        $this->lastCharge = $this->lastCharge();
    }

    public function tearDown()
    {

    }

    /**
     * @test
     */
    function charges_with_a_valid_token_are_valid()
    {
        $paymentGateway = new StripePaymentGateway(static::$kernel->getContainer()->getParameter('stripe.secret'));

        $paymentGateway->charge(999, $this->validToken());

        $this->assertCount(1, $this->newCharges());
        $this->assertEquals(999, $this->lastCharge()->amount);
    }
    /**
     * @test
     */
    function purchases_with_an_invalid_token_fail() {
        $paymentGateway = new StripePaymentGateway(static::$kernel->getContainer()->getParameter('stripe.secret'));

        try {
            $paymentGateway->charge(999, 'invalid-token');
        } catch (PaymentFailedException $exception) {
            $this->assertCount(0, $this->newCharges());
            return;
        }

        $this->fail('Charging with an invalid token does not throw a PaymentFailedException.');
    }

    /**
     * @return string
     */
    protected function validToken()
    {
        return Token::create([
            "card" => [
                "number" => "4242424242424242",
                "exp_month" => date('m'),
                "exp_year" => date('Y') + 1,
                "cvc" => "123"
            ]
        ], ['api_key' => static::$kernel->getContainer()->getParameter('stripe.secret')])->id;
    }

    /**
     * @return Charge
     */
    protected function lastCharge()
    {
        $charges = Charge::all(
            ['limit' => 1],
            ['api_key' => static::$kernel->getContainer()->getParameter('stripe.secret')]
        )['data'];
        $charge = array_shift($charges);

        return $charge;
    }

    protected function newCharges()
    {
        return Charge::all(
            ['ending_before' => $this->lastCharge ? $this->lastCharge->id : null],
            ['api_key' => static::$kernel->getContainer()->getParameter('stripe.secret')]
        )['data'];
    }

}
