<?php
/**
 * Created by PhpStorm.
 * User: awemo
 * Date: 11.02.17
 * Time: 12:10
 */

namespace AppBundle\Billing;


interface PaymentGateway
{
    public function charge($amount, $token);

    public function getValidTestToken() : string ;
}
