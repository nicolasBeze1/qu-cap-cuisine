<?php

namespace App\Repository;

use App\Entity\Category;
use App\Entity\SubCategory;
use App\Entity\SurveyUser;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Collections\Collection;
use Doctrine\Persistence\ManagerRegistry;

class SurveyUserRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, SurveyUser::class);
    }

    public function findByCategory(Category $category, User $user): array
    {
        $qb = $this->createQueryBuilder('su')
            ->leftJoin('su.survey', 's')
            ->leftJoin('s.subCategory', 'subC')
            ->where('subC.category <= :category')
            ->andWhere('su.user <= :user')
            ->andWhere('s.status = true')
            ->setParameter('user',$user)
            ->setParameter('category',$category)
        ;

        return $qb
            ->getQuery()
            ->getResult();
    }

    public function findBySubCategory(SubCategory $subCategory, User $user): array
    {
        $qb = $this->createQueryBuilder('su')
            ->leftJoin('su.survey', 's')
            ->where('s.subCategory <= :subCategory')
            ->andWhere('su.user <= :user')
            ->andWhere('s.status = true')
            ->setParameter('user',$user)
            ->setParameter('subCategory',$subCategory)
        ;

        return $qb
            ->getQuery()
            ->getResult();
    }
}
