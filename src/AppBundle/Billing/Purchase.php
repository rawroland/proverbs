<?php

namespace AppBundle\Billing;

use AppBundle\Entity\Account;
use AppBundle\Entity\Licence;
use AppBundle\Entity\Releasable;
use AppBundle\Repository\AccountRepository;

class Purchase
{
    /**
     * @var int
     */
    private $totalCost;

    /**
     * @var Releasable
     */
    private $article;

    /**
     * @var Account
     */
    private $account;

    private function __construct()
    {

    }

    public static function fromLicence(Licence $licence, Account $account)
    {
        $purchase = new Purchase();
        $purchase->article = $licence;
        $purchase->account = $account;
        $purchase->totalCost = $licence->getPrice();

        return $purchase;
    }

    public function totalCost()
    {
        return $this->totalCost;
    }

    public function article()
    {
        return $this->article;
    }

    public function account()
    {
        return $this->account;
    }

    public function complete(AccountRepository $accounts, PaymentGateway $paymentGateway, $paymentToken)
    {
        $paymentGateway->charge($this->totalCost(), $paymentToken);
        $accounts->createFromPurchase($this);

        return $this->account();
    }
}