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
        $paymentGateway = new FakePaymentGateway([]);

        $paymentGateway->charge(999, $paymentGateway->getValidToken());

        $this->assertEquals(999, $paymentGateway->totalCharges());
    }

    /**
     * @test
     */
    function purchases_with_an_invalid_token_fail() {
        $paymentGateway = new FakePaymentGateway([]);
        
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
        $paymentGateway = new FakePaymentGateway();
        $timesCallbackRan = 0;

        $paymentGateway->beforeFirstCharge(function (FakePaymentGateway $paymentGateway) use (&$timesCallbackRan) {
            $paymentGateway->charge(1000, $paymentGateway->getValidToken());
            $timesCallbackRan++;
            $this->assertEquals(1000, $paymentGateway->totalCharges());
        });

        $paymentGateway->charge(1000, $paymentGateway->getValidToken());
        $this->assertEquals(2000, $paymentGateway->totalCharges());
        $this->assertEquals(1, $timesCallbackRan);
    }
}
