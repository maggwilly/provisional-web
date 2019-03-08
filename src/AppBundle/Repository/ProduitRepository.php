<?php

namespace AppBundle\Repository;
use AppBundle\Entity\User; 
/**
 * ProduitRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class ProduitRepository extends \Doctrine\ORM\EntityRepository
{

      public function findByUser(User $user,$start=0){
           $qb = $this->createQueryBuilder('p')
           ->where('p.user=:user')
           ->setParameter('user', $user);
         return $qb->getQuery()->setFirstResult($start)->setMaxResults(100)->getResult(); 
  }

}