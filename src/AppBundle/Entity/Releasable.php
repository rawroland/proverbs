<?php

namespace AppBundle\Entity;


/**
 * Class Cancellable
 * @package AppBundle\Billing
 * @author awemo
 * @copyright Copyright (c) 2017
 */
interface Releasable
{
    public function release();

}