<?php
// src/OC/PlatformBundle/Entity/AdvertRepository.php

namespace DEVWEB\ColocationBundle\Entity;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;

class AdvertRepository extends EntityRepository
{
  public function getAdvertWithCategories(array $categoryNames)
  {
    $qb = $this->createQueryBuilder('a');

    $qb
      ->innerJoin('a.categories', 'c')
      ->addSelect('c')
    ;

    $qb->where($qb->expr()->in('c.name', $categoryNames));

    return $qb
      ->getQuery()
      ->getResult()
    ;
  }
  public function getAdverts($page, $nbPerPage)
  {
    $query = $this->createQueryBuilder('a')
      ->leftJoin('a.image', 'i')
      ->addSelect('i')
      ->addSelect('c')
      ->orderBy('a.date', 'DESC')
      ->getQuery()
    ;

    $query
      ->setFirstResult(($page-1) * $nbPerPage)
      ->setMaxResults($nbPerPage)
    ;

    return new Paginator($query, true);
  }
}
