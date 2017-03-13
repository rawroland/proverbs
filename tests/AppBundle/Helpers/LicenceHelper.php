<?php
/**
 * Created by PhpStorm.
 * User: awemo
 * Date: 23.01.17
 * Time: 22:57
 */

namespace Tests\AppBundle\Helpers;

use AppBundle\Entity\Licence;
use Doctrine\ORM\EntityManager;

class LicenceHelper
{
    public function __construct()
    {
        $datetime = new \DateTime('2017-02-10 00:00:00');
        $this->licence = (new Licence())
          ->setPrice(999)
          ->setCreated($datetime)
          ->setRemaining(5)
          ->setModified($datetime);
    }

    /**
     * @param string $type
     * @return Licence
     */
    public function getLicence($type)
    {
        return $this->licence->setType($type);
    }

    /**
     * @param string $type
     * @param EntityManager $entityManager
     * @param int $remaining
     * @return Licence
     */
    public function createLicence($type, EntityManager $entityManager, $remaining = 5)
    {
        $licence = clone $this->licence->setType($type)->setRemaining($remaining);
        $entityManager->persist($licence);
        $entityManager->flush();

        return $licence;
    }
}
