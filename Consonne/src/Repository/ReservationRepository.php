<?php

namespace App\Repository;

use App\Entity\Reservation;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method Reservation|null find($id, $lockMode = null, $lockVersion = null)
 * @method Reservation|null findOneBy(array $criteria, array $orderBy = null)
 * @method Reservation[]    findAll()
 * @method Reservation[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ReservationRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Reservation::class);
    }

    public function getByDay(){
      return $this->createQueryBuilder('r')
                  ->andWhere("DATE_DIFF(CURRENT_DATE(), r.createdAt) = 0")
                  ->orderBy('r.startAt + r.duree', 'DESC')
                  ->getQuery()
                  ->getResult()
                  ;
    }

    public function getByDayLimited(){
      return $this->createQueryBuilder('r')
                  ->andWhere("DATE_DIFF(CURRENT_DATE(), r.createdAt) = 0")
                  ->orderBy('r.startAt + r.duree', 'DESC')
                  ->setMaxResults(5)
                  ->getQuery()
                  ->getResult()
                  ;
    }

    public function getByUser($user){
      return $this->createQueryBuilder('r')
                  ->andWhere('r.user = :user')
                  ->setParameter('user', $user)
                  ->andWhere("DATE_DIFF(CURRENT_DATE(), r.createdAt) = 0")
                  ->orderBy('r.startAt + r.duree', 'DESC')
                  ->setMaxResults(10)
                  ->getQuery()
                  ->getResult()
                  ;
    }

    public function getByUserLimited($user){
      return $this->createQueryBuilder('r')
                  ->andWhere('r.user = :user')
                  ->setParameter('user', $user)
                  ->setMaxResults(10)
                  ->getQuery()
                  ->getResult()
                  ;
    }




    // /**
    //  * @return Reservation[] Returns an array of Reservation objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('r')
            ->andWhere('r.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('r.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Reservation
    {
        return $this->createQueryBuilder('r')
            ->andWhere('r.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
