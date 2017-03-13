<?php

namespace AppBundle\Billing;

use AppBundle\Entity\Licence;

class Purchase
{
    /**
     * @var int
     */
    private $totalCost;

    private function __construct()
    {

    }

    public static function fromLicence(Licence $licence)
    {
        $purchase = new Purchase();
        $purchase->totalCost = $licence->getPrice();

        return $purchase;
    }

    public function totalCost()
    {
        return $this->totalCost;
    }
}