<?php

namespace AppBundle\Repository;

use AppBundle\Entity\Licence;
use Doctrine\ORM\NoResultException;

/**
 * LicenceRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class LicenceRepository extends BaseRepository
{
    /**
     * @param int $licenceId
     * @return \Doctrine\ORM\Query
     */
    public function purchasable($licenceId = 0)
    {
        $queryBuilder = $this->createQueryBuilder('Licence')
          ->select('licence')
          ->from('AppBundle:Licence', 'licence')
          ->where('licence.type IN (:purchasable_types)')
          ->andWhere('licence.remaining > 0')
          ->setParameter('purchasable_types', ['ad_free', 'free']);
        if($licenceId) {
            $queryBuilder->andWhere('licence.id = :id')
              ->setParameter('id', $licenceId);
        }

        return $queryBuilder->getQuery();
    }

    public function increaseRemaining(Licence $licence, $amount)
    {
        $this->save($licence->increaseRemaining($amount));
    }

    /**
     * @param int $licenceId
     * @return Licence
     */
    public function reserve($licenceId)
    {
        /** @var Licence $licence */
        $licence = $this->purchasable($licenceId)->getSingleResult();
        $licence->reduceRemaining(1);
        $this->save($licence);

        return $licence;
    }

    public function cancel(Licence $reserved)
    {
        $reserved->increaseRemaining(1);
        $this->save($reserved);
    }
}
