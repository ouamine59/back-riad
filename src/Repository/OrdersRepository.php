<?php

namespace App\Repository;

use App\Entity\Orders;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Orders>
 */
class OrdersRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Orders::class);
    }
    public function findAllByIdUser($idUser): array
    {
    return $this->createQueryBuilder('a')
        ->select('NEW App\\DTO\\OrdersClientListingDTO(a.id, s.states, u.id, a.isCreatedAt)')
        ->innerJoin('a.user', 'u')
        ->innerJoin('a.states', 's')
        ->where('a.user = :id')
        ->setParameter('id', $idUser)
        ->getQuery()
        ->getResult();
    }

     public function findOneByUser( $idUser, $idOrder):array{
         return $this->createQueryBuilder('a')
         ->select('NEW App\\DTO\\OrdersClientListingDTO(a.id, s.states, u.id, a.isCreatedAt)')
         ->innerJoin('a.user', 'u')
         ->innerJoin('a.states', 's')
         ->where('a.user = :user')
        ->andWhere("a.id =:order")
         ->setParameter('order', $idOrder)
         ->setParameter('user', $idUser)
         ->getQuery()
        ->getResult();
     }
    
    //    /**
    //     * @return Orders[] Returns an array of Orders objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('o')
    //            ->andWhere('o.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('o.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Orders
    //    {
    //        return $this->createQueryBuilder('o')
    //            ->andWhere('o.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
