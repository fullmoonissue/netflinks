<?php

declare(strict_types=1);

namespace App\Infrastructure\Repository;

use App\Domain\Entity\Recipient;
use Doctrine\ORM\QueryBuilder;

/**
 * @method findAll()
 */
trait RecipientWorkaroundTrait
{
    // The "NOT IN" filter doesn't return the wanted results (if "not that one recipient" is chosen),
    // so the recipient filter is handled here when only one recipient is selected and "is not" too.
    // This way, it allows to find entities that are "really" not assigned to the selected recipient.

    // In other words, on the SQL side, here is the section within the WHERE condition :
    // WHERE r1_.id NOT IN (?) OR r1_.id IS NULL
    // and so, if an entity (link, image, ...) is linked to multiple recipients, it will be returned.
    // This is not wanted. Only entities specifically not linked to the selected recipient have to be returned.
    // So, with this workaround, an additional NOT IN is added containing the entities that are not concerned.

    /**
     * @param array{comparison: string, value: string[]} $recipientProperties
     */
    public function recipientQueryBuilderIfNotThatOne(QueryBuilder $queryBuilder, array $recipientProperties): void
    {
        if (
            '!=' === $recipientProperties['comparison']
            && 1 === count($recipientProperties['value'])
        ) {
            $ids = [];
            $entities = $this->findAll();
            foreach ($entities as $entity) {
                $recipientIds = array_map(
                    function (Recipient $recipient) {
                        return $recipient->getId();
                    },
                    $entity->getRecipients()->toArray()
                );

                if (in_array($recipientProperties['value'][0], $recipientIds, true)) {
                    $ids[] = $entity->getId();
                }
            }

            if ([] !== $ids) {
                $queryBuilder->andWhere(
                    $queryBuilder->expr()->notIn('entity.id', $ids)
                );
            }
        }
    }
}
