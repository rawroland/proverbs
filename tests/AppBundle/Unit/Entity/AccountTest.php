<?php
/**
 * Created by PhpStorm.
 * User: awemo
 * Date: 22.02.17
 * Time: 21:29
 */

namespace Tests\AppBundle\Unit;


use Tests\AppBundle\Helpers\AccountHelper;

class AccountTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    function converting_to_array()
    {
        $account = (new AccountHelper())->getAccount();

        $this->assertEquals([
          'name' => 'Jane',
          'surname' => 'Doe',
          'email' => 'foo@bar.com',
          'amount' => 999,
        ], $account->toArray());
    }
}
