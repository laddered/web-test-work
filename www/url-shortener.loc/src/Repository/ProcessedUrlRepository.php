<?php

namespace App\Repository;

use App\Entity\ProcessedUrl;
use DateTime;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method ProcessedUrl|null find($id, $lockMode = null, $lockVersion = null)
 * @method ProcessedUrl|null findOneBy(array $criteria, array $orderBy = null)
 * @method ProcessedUrl[]    findAll()
 * @method ProcessedUrl[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ProcessedUrlRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ProcessedUrl::class);
    }

    // /**
    //  * @return ProcessedUrl[] Returns an array of ProcessedUrl objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('p.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?ProcessedUrl
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */

    public function getUniqueUrlsCountByDateRange(DateTime $startDateTime, DateTime $endDateTime): int
    {
        $qb = $this->createQueryBuilder('p');

        $qb->select('COUNT(DISTINCT p.url)')
            ->where('p.createdDate BETWEEN :startDate AND :endDate')
            ->setParameter('startDate', $startDateTime)
            ->setParameter('endDate', $endDateTime);

        return (int) $qb->getQuery()->getSingleScalarResult();
    }

    public function getUniqueUrlsCountByDomain(string $domain): int
    {
        $qb = $this->createQueryBuilder('p');

        $qb->select('COUNT(DISTINCT p.url)')
            ->where('(p.url LIKE :httpDomain OR p.url LIKE :httpsDomain)')
            ->setParameter('httpDomain', 'http://' . $domain . '%')
            ->setParameter('httpsDomain', 'https://' . $domain . '%');

        return (int) $qb->getQuery()->getSingleScalarResult();
    }

}
