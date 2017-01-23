<?php
/**
 * Created by PhpStorm.
 * User: awemo
 * Date: 23.01.17
 * Time: 22:49
 */

namespace Tests\AppBundle\Unit;

use Tests\AppBundle\Helpers\ProverbHelper;

class ProverbTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    function can_get_formatted_created_date()
    {
        $proverb = (new ProverbHelper())->getProverb();
        $this->assertEquals('January 23, 2017', $proverb->getFormattedCreatedDate());

    }

}
