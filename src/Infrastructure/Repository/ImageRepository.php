<?php

declare(strict_types=1);

namespace App\Infrastructure\Repository;

use App\Domain\Entity\Image;
use App\Domain\Repository\ImageRepositoryInterface;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Image>
 *
 * @method Image|null find($id, $lockMode = null, $lockVersion = null)
 * @method Image|null findOneBy(array $criteria, array $orderBy = null)
 * @method Image[]    findAll()
 * @method Image[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ImageRepository extends ServiceEntityRepository implements ImageRepositoryInterface
{
    use RecipientWorkaroundTrait;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Image::class);
    }

    public function add(Image $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function update(Image $entity): void
    {
        $this->getEntityManager()->persist($entity);
        $this->getEntityManager()->flush();
    }

    public function remove(Image $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function exist(string $filename): bool
    {
        return null !== $this->findOneBy(['filename' => $filename]);
    }

    public function orderedForAssociationField(): QueryBuilder
    {
        return $this
            ->createQueryBuilder('u')
            ->orderBy('u.filename', 'ASC');
    }

    public function findIdsUsedByEveryone(): array
    {
        $sql = <<<SQL
SELECT i.id
FROM image i
INNER JOIN image_recipient ir
    ON i.id = ir.image_id
INNER JOIN recipient r
    ON ir.recipient_id = r.id
GROUP BY filename
HAVING COUNT(filename) = (SELECT COUNT(id) FROM recipient);
SQL;

        $stmt = $this->getEntityManager()->getConnection()->prepare($sql);
        $exec = $stmt->executeQuery();

        $results = $exec->fetchAllAssociative();
        if ([] === $results) {
            return $results;
        }

        return array_map(
            function (array $row) {
                return $row['id'];
            },
            $results
        );
    }
}
