<?php

namespace OC\PlatformBundle\Entity;

/**
 * AdvertRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class AdvertRepository extends \Doctrine\ORM\EntityRepository
{
    public function myFindAll()
    {
        return $this
          ->createQueryBuilder('a')
          ->getQuery()
          ->getResult()
          ;
    }
}
