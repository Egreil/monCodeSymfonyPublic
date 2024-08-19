<?php

namespace App\Repository;

use App\Entity\Lieu;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Lieu>
 */
class LieuRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Lieu::class);
    }
    public function getVillesDeFrance($ville){
            return $this->createQueryBuilder('l')
                ->where('l.ville = :ville')
                ->setParameter('ville', $ville)
                ->getQuery()
                ->getResult();

    }

}
