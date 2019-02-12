<?php

namespace App\Repository;

use App\Entity\LastUpdate;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method LastUpdate|null find($id, $lockMode = null, $lockVersion = null)
 * @method LastUpdate|null findOneBy(array $criteria, array $orderBy = null)
 * @method LastUpdate[]    findAll()
 * @method LastUpdate[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class LastUpdateRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, LastUpdate::class);
    }

}
