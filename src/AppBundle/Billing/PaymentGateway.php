<?php

namespace AppBundle\Billing;

use Cake\Collection\Collection;

interface PaymentGateway
{
    public function charge($amount, $token);

    public function getValidTestToken() : string ;

    public function newChargesDuring($callback) : Collection ;
}
