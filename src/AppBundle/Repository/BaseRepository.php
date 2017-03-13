<?php
/**
 * Created by PhpStorm.
 * User: awemo
 * Date: 12.02.17
 * Time: 19:20
 */

namespace AppBundle\Repository;

use Doctrine\ORM\EntityRepository;


/**
 * Class BaseRepository
 * @package AppBundle\Repository
 * @author awemo
 * @copyright Copyright (c) 2017, publicplan GmbH
 */
class BaseRepository extends EntityRepository
{
    /**
     * @param $entity
     */
    public function save($entity)
    {
        $this->getEntityManager()->persist($entity);
        $this->getEntityManager()->flush();
    }

}