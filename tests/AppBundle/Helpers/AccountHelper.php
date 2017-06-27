<?php
/**
 * Created by PhpStorm.
 * User: awemo
 * Date: 22.02.17
 * Time: 21:32
 */

namespace Tests\AppBundle\Helpers;

use AppBundle\Entity\Account;


/**
 * Class AccountHelper
 * @package Tests\AppBundle\Helpers
 * @author awemo
 * @copyright Copyright (c) 2017, publicplan GmbH
 */
class AccountHelper
{
    /**
     * @var Account
     */
    protected $account;

    public function __construct()
    {
        $this->account = (new Account())
          ->setEmail('foo@bar.com')
          ->setName('Jane')
          ->setSurname('Doe')
          ->setPassword('password')
          ->setAmount(999)
          ->setLicence(
            (new LicenceHelper())
              ->getLicence('ad_free')
              ->setPrice(999)
          );
    }

    public function getAccount()
    {
        return $this->account;
    }

}