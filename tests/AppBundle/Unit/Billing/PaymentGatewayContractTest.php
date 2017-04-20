<?php

namespace Tests\AppBundle\Unit\Billing;

use AppBundle\Billing\PaymentGateway;

trait PaymentGatewayContractTest
{
   abstract  protected function getPaymentGateway() : PaymentGateway;

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
        $this->assertEquals([5000, 4000], $newCharges->toList());
    }
}
