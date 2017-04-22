<?php

namespace Tests\AppBundle\Unit\Billing;

use AppBundle\{
    Billing\PaymentGateway,
    Billing\StripePaymentGateway
};
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/**
 * @group network-dependent
 */
class StripePaymentGatewayTest extends KernelTestCase
{

    use PaymentGatewayContractTest;

    public function setUp()
    {
        parent::setUp();
        static::bootKernel();
    }


    /**
     * @return PaymentGateway
     */
    private function getPaymentGateway(): PaymentGateway
    {
        return new StripePaymentGateway(static::$kernel->getContainer()->getParameter('stripe.secret'));
    }

}
