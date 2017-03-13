<?php
/**
 * Created by PhpStorm.
 * User: awemo
 * Date: 23.01.17
 * Time: 22:49
 */

namespace Tests\AppBundle\Unit;

use Tests\AppBundle\Helpers\LicenceHelper;

class LicenceTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    function can_increase_remaining_licences()
    {
        $licence = (new LicenceHelper())->getLicence('free');

        $increased = $licence->increaseRemaining(5);

        $this->assertEquals(10, $licence->getRemaining());
        $this->assertEquals($licence, $increased);
    }

    /**
     * @test
     */
    function can_reduce_remaining_licences()
    {
        $licence = (new LicenceHelper())->getLicence('free');

        $reduced = $licence->reduceRemaining(1);

        $this->assertEquals(4, $licence->getRemaining());
        $this->assertEquals($licence, $reduced);
    }
}
