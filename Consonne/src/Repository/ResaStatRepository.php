<?php

namespace App\Repository;

use App\Entity\ResaStat;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method ResaStat|null find($id, $lockMode = null, $lockVersion = null)
 * @method ResaStat|null findOneBy(array $criteria, array $orderBy = null)
 * @method ResaStat[]    findAll()
 * @method ResaStat[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ResaStatRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, ResaStat::class);
    }

    // /**
    //  * @return ResaStat[] Returns an array of ResaStat objects
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
    public function findOneBySomeField($value): ?ResaStat
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
