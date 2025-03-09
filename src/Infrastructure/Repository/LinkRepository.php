<?php

declare(strict_types=1);

namespace App\Infrastructure\Repository;

use App\Domain\Entity\Link;
use App\Domain\Link\RangeLinkDates;
use App\Domain\Repository\LinkRepositoryInterface;
use DateTimeImmutable;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Link>
 *
 * @method Link|null find($id, $lockMode = null, $lockVersion = null)
 * @method Link|null findOneBy(array $criteria, array $orderBy = null)
 * @method Link[]    findAll()
 * @method Link[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class LinkRepository extends ServiceEntityRepository implements LinkRepositoryInterface
{
    use RecipientWorkaroundTrait;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Link::class);
    }

    public function add(Link $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Link $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function getNextLink(DateTimeImmutable $date): Link
    {
        return $this
            ->createQueryBuilder('l')
            ->where('l.date > :date')
            ->orderBy('l.date', 'ASC')
            ->setMaxResults(1)
            ->setParameter('date', $date->format('Y-m-d H:i:s'))
            ->getQuery()
            ->getSingleResult();
    }

    public function getLastLink(): Link
    {
        return $this->findOneBy([], ['date' => 'DESC']);
    }

    public function getRangedLinks(RangeLinkDates $rangeLinkDates): array
    {
        return $this
            ->createQueryBuilder('l')
            ->where('l.date BETWEEN :startDate AND :endDate')
            ->orderBy('l.date', 'ASC')
            ->setParameter('startDate', $rangeLinkDates->getStartDate()->format('Y-m-d H:i:s'))
            ->setParameter('endDate', $rangeLinkDates->getEndDate()->format('Y-m-d H:i:s'))
            ->getQuery()
            ->getResult();
    }

    public function isUrlAlreadyRegistered(string $url): bool
    {
        $sql = <<<SQL
SELECT COUNT(id) as nb
FROM link
WHERE RTRIM(url, '/') = :url;
SQL;

        $stmt = $this->getEntityManager()->getConnection()->prepare($sql);
        $stmt->bindValue(':url', rtrim($url, '/'));
        $exec = $stmt->executeQuery();

        return 0 < (int) $exec->fetchOne();
    }
}
