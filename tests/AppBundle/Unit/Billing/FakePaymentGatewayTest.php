<?php

namespace Tests\AppBundle\Billing;

use AppBundle\Billing\FakePaymentGateway;
use AppBundle\Billing\PaymentFailedException;
use AppBundle\Billing\PaymentGateway;

class FakePaymentGatewayTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @test
     */
    function charges_with_a_valid_token_are_successful()
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
            return;
        }

        $this->fail();
    }

    /**
     * @test
     */
    function running_a_hook_before_the_first_charge()
    {
        $paymentGateway = $this->getPaymentGateway();
        $timesCallbackRan = 0;

        $paymentGateway->beforeFirstCharge(function (FakePaymentGateway $paymentGateway) use (&$timesCallbackRan) {
            $paymentGateway->charge(1000, $paymentGateway->getValidTestToken());
            $timesCallbackRan++;
            $this->assertEquals(1000, $paymentGateway->totalCharges());
        });

        $paymentGateway->charge(1000, $paymentGateway->getValidTestToken());
        $this->assertEquals(2000, $paymentGateway->totalCharges());
        $this->assertEquals(1, $timesCallbackRan);
    }

    /**
     * @test
     */
    public function can_fetch_charges_created_during_a_callback()
    {
        $paymentGateway = $this->getPaymentGateway();
        $paymentGateway->charge(2000, $paymentGateway->getValidTestToken());
        $paymentGateway->charge(3000, $paymentGateway->getValidTestToken());

        $newCharges = $paymentGateway->newChargesDuring(function (PaymentGateway $paymentGateway) {
            $paymentGateway->charge(4000, $paymentGateway->getValidTestToken());
            $paymentGateway->charge(5000, $paymentGateway->getValidTestToken());
        });

        $this->assertCount(2, $newCharges);
        $this->assertEquals([4000, 5000], $newCharges->toList());
    }

    /**
     * @return FakePaymentGateway
     */
    protected function getPaymentGateway()
    {
        return new FakePaymentGateway([]);
    }
}
