<?php

namespace Tests\AppBundle\Billing;

use AppBundle\Billing\FakePaymentGateway;
use AppBundle\Billing\PaymentFailedException;

class FakePaymentGatewayTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @test
     */
    function charges_with_a_valid_token_are_successful() {
        $paymentGateway = $this->getPaymentGateway();

        $paymentGateway->charge(999, $paymentGateway->getValidTestToken());

        $this->assertEquals(999, $paymentGateway->totalCharges());
    }

    /**
     * @test
     */
    function charges_with_an_invalid_token_fail() {
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
     * @return FakePaymentGateway
     */
    protected function getPaymentGateway()
    {
        return new FakePaymentGateway([]);
    }
}
