<?php

namespace Tests\AppBundle\Unit\Billing;

use AppBundle\Billing\FakePaymentGateway;
use AppBundle\Billing\PaymentFailedException;
use AppBundle\Billing\PaymentGateway;

class FakePaymentGatewayTest extends \PHPUnit_Framework_TestCase
{
    use PaymentGatewayContractTest;

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
     * @return PaymentGateway
     */
    protected function getPaymentGateway(): PaymentGateway
    {
        return new FakePaymentGateway([]);
    }
}
