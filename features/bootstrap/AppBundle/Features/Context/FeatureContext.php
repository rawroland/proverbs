<?php

namespace AppBundle\Features\Context;


use AppKernel;
use Behat\Behat\Context\Context;


use Behat\MinkExtension\Context\MinkContext;

/**
 * Defines application features from the specific context.
 */
class FeatureContext extends MinkContext implements Context
{
    /**
     * Initializes context.
     *
     * Every scenario gets its own context instance.
     * You can also pass arbitrary arguments to the
     * context constructor through behat.yml.
     */
    public function __construct()
    {
        $kernel = new AppKernel('test', true);
        $kernel->boot();
    }
}
