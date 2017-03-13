<?php
/**
 * Created by PhpStorm.
 * User: awemo
 * Date: 13.02.17
 * Time: 21:39
 */

namespace Tests\AppBundle;

use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;


/**
 * Class DisableKernelExceptionListener
 * @package Tests\AppBundle
 * @author awemo
 * @copyright Copyright (c) 2017, publicplan GmbH
 */
class DisableKernelExceptionListener
{

    public function onKernelException(GetResponseForExceptionEvent $event)
    {
        throw $event->getException();
    }

}