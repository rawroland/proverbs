<?php

namespace Tests\AppBundle\Billing;

use AppBundle\Billing\PaymentFailedException;
use AppBundle\Billing\PaymentGateway;
use AppBundle\Billing\StripePaymentGateway;
use Stripe\Charge;
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
        $paymentGateway = $this->getPaymentGateway();

        $newCharges = $paymentGateway->newChargesDuring(function (PaymentGateway $paymentGateway) {
            $paymentGateway->charge(999, $paymentGateway->getValidTestToken());
        });

        $this->assertCount(1, $newCharges);
        $this->assertEquals(999, $newCharges->sumOf());
    }

    /**
     * @test
     */
    function charges_with_an_invalid_token_fail()
    {
        $paymentGateway = $this->getPaymentGateway();

        try {
            $paymentGateway->charge(999, 'invalid-token');
        } catch (PaymentFailedException $exception) {
            $this->assertCount(0, $this->newCharges());
            return;
        }

        $this->fail('Charging with an invalid token does not throw a PaymentFailedException.');
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

    /**
     * @return StripePaymentGateway
     */
    protected function getPaymentGateway()
    {
        return new StripePaymentGateway(static::$kernel->getContainer()->getParameter('stripe.secret'));
    }

}
