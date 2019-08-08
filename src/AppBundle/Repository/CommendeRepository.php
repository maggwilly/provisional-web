<?php

namespace AppBundle\Repository;
use AppBundle\Entity\PointVente; 
use AppBundle\Entity\User; 
/**
 * CommendeRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class CommendeRepository extends \Doctrine\ORM\EntityRepository
{
	  	
      /*Liste des commendes pqr critères*/

   public function findCommendes(User $user=null,PointVente $pointVente=null,$alls=[""],$keys=[""]){
           $qb = $this->createQueryBuilder('c')->join('c.pointVente','p');
          if($user!=null){
            $qb->join('c.user','u');
         if (!$user->isMe()) {
             $qb->andWhere('c.user=:user')->setParameter('user', $user);
        }else
             $qb->andWhere('u.parent=:parent')->setParameter('parent', $user);
            } 
          if($pointVente!=null){
            $qb->andWhere('c.pointVente=:pointVente')
             ->setParameter('pointVente', $pointVente);
            } 
         if(array_key_exists('doneBy',$alls)&&$alls['doneBy']){
             $user= $this->_em->getRepository('AppBundle:User')->find($alls['doneBy']);
              $qb->andWhere('c.user=:doneBy')->setParameter('doneBy', $user);
            }      
         if(array_key_exists('secteur',$alls)){
             $qb->andWhere('p.secteur=:secteur')
             ->setParameter('secteur',$alls['secteur']);
            } 
         if(array_key_exists('terminated',$alls)){
             $qb->andWhere('c.terminated=:terminated')
             ->setParameter('terminated',$alls['terminated']==="true"?true:false);
            } 
         if(array_key_exists('ville',$alls)){
             $qb->andWhere('p.ville=:ville')
             ->setParameter('ville',$alls['ville']);
            }
 
         if(array_key_exists('afterdate',$alls)){
             $qb->andWhere('c.date>=:afterdate')
             ->setParameter('afterdate',new \DateTime($alls['afterdate']));
            }
         if(array_key_exists('beforedate',$alls)){
             $qb->andWhere('c.date<=:beforedate')
             ->setParameter('beforedate',new \DateTime($alls['beforedate']));
            }
            $qb->andWhere($qb->expr()->notIn('c.id', $keys))
            ->orderBy('c.date','desc');
            return $qb->getQuery()->getResult();  
  }

      /*Dernier commende dans un point de vente*/

   public function findLast(PointVente $pointVente,$endDate=null,$alls=[""]){
           $qb = $this->createQueryBuilder('c'); 
          if($pointVente!=null){
            $qb->andWhere('c.pointVente=:pointVente')
             ->setParameter('pointVente', $pointVente);
            }
         if(array_key_exists('afterdate',$alls)){
             $qb->andWhere('c.date>=:afterdate')
             ->setParameter('afterdate',new \DateTime($alls['afterdate']));
            }
         if(array_key_exists('beforedate',$alls)){
             $qb->andWhere('c.date<=:beforedate')
             ->setParameter('beforedate',new \DateTime($alls['beforedate']));
            }
          if($endDate!=null){
             $qb->andWhere('c.date<=:endDate')
             ->setParameter('endDate',new \DateTime($endDate));
          }            
            $qb->orderBy('c.date','desc');
            return $qb->getQuery()->setMaxResults(1)->getOneOrNullresult();  
      }

      /*Dernier commende dans un point de vente*/

   public function findFirst(PointVente $pointVente,  $endDate=null){
           $qb = $this->createQueryBuilder('c'); 
          if($pointVente!=null){
            $qb->andWhere('c.pointVente=:pointVente')
             ->setParameter('pointVente', $pointVente);
            }            

          if($endDate!=null){
             $qb->andWhere('c.date<=:endDate')
             ->setParameter('endDate',new \DateTime($endDate));
          }
            $qb->orderBy('c.date','asc');
            return $qb->getQuery()->setMaxResults(1)->getOneOrNullresult();  
      }

/*Commende ou passage effectués pqr un utilisateur*/
    public function countVisitedByWeekByUser(User $user, $alls=[""]){
          $qb = $this->createQueryBuilder('c')->join('c.pointVente','p')->leftJoin('c.lignes','l');
        if ($user->isMe()) {
             $qb->where('p.user=:user')->setParameter('user', $user);
        }else
           $qb->where($qb->expr()->in('p.id', $this->getPointVenteIds($user))); 
          
          if(array_key_exists('user',$alls)){
             $qb->andWhere('p.createdBy=:user')->setParameter('user', $alls['user']);
            }
         if(array_key_exists('secteur',$alls)){
             $qb->andWhere('p.secteur=:secteur')
             ->setParameter('secteur',$alls['secteur']);
            } 
         if(array_key_exists('type',$alls)){
             $qb->andWhere('p.type=:type')
             ->setParameter('type',$alls['type']);
            } 
         if(array_key_exists('ville',$alls)){
             $qb->andWhere('p.ville=:ville')
             ->setParameter('ville',$alls['ville']);
            }
         if(array_key_exists('quartier',$alls)){
             $qb->andWhere('p.quartier=:quartier')
             ->setParameter('quartier',$alls['quartier']);
            }
          if(array_key_exists('afterdate',$alls)){
                 $qb->andWhere('c.date>=:afterdate')
             ->setParameter('afterdate',new \DateTime($alls['afterdate']));
            }                    
         if(array_key_exists('beforedate',$alls)){
             $qb->andWhere('c.date<=:beforedate')
             ->setParameter('beforedate',new \DateTime($alls['beforedate']));
            } 
          $qb->addOrderBy('c.week','asc')
           ->select('c.weekText as weekText')
           ->addSelect('c.week as week')
           ->addSelect('count(DISTINCT p.id) as visited')
            ->addSelect('sum(l.quantite) as quantite')
            ->addSelect('sum(l.quantite*l.pu) as montant')
            ->addGroupBy('c.week')
            ->addGroupBy('c.weekText');
         return $qb->getQuery()->getArrayResult();  
  }


}
