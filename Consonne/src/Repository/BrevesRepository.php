<?php

namespace App\Repository;

use App\Entity\Breves;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method Breves|null find($id, $lockMode = null, $lockVersion = null)
 * @method Breves|null findOneBy(array $criteria, array $orderBy = null)
 * @method Breves[]    findAll()
 * @method Breves[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class BrevesRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Breves::class);
    }

    // /**
    //  * @return Breves[] Returns an array of Breves objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('b')
            ->andWhere('b.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('b.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Breves
    {
        return $this->createQueryBuilder('b')
            ->andWhere('b.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
