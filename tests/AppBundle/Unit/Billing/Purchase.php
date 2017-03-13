<?php
use AppBundle\Billing\Purchase;
use Tests\AppBundle\Helpers\LicenceHelper;

class PurchaseTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    function calculation_the_total_cost_of_a_licence_purchase()
    {
        $licence = (new LicenceHelper())->getLicence('ad_free')->setPrice(999);

        $purchase = Purchase::fromLicence($licence);

        $this->assertEquals(999, $purchase->totalCost());
    }

}