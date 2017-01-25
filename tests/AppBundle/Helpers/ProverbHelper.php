<?php
/**
 * Created by PhpStorm.
 * User: awemo
 * Date: 23.01.17
 * Time: 22:57
 */

namespace Tests\AppBundle\Helpers;


use AppBundle\Entity\Proverb;

class ProverbHelper
{
    public function getProverb()
    {
        $title = 'All is good that ends well';
        $explanation = 'An event with a good outcome is good, irrespective of the wrongs along the way.';
        $origin = 'It came from somewhere';
        $date = new \DateTime('2017-01-23 20:00:00');
        return (new Proverb())
            ->setTitle($title)
            ->setExplanation($explanation)
            ->setOrigin($origin)
            ->setCreated($date)
            ->setModified($date)
            ->setPublished(new \DateTime('2017-01-24 20:00:00'));
    }

}
