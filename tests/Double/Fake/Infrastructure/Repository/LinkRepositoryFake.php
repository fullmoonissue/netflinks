<?php

declare(strict_types=1);

namespace Tests\Double\Fake\Infrastructure\Repository;

use App\Domain\Entity\Link;
use App\Domain\Link\RangeLinkDates;
use App\Domain\Repository\LinkRepositoryInterface;
use DateTimeImmutable;
use Exception;

class LinkRepositoryFake implements LinkRepositoryInterface
{
    /**
     * @param array<int, Link> $links
     */
    public function __construct(
        private array $links = [],
    ) {
    }

    public function add(Link $entity, bool $flush = false): void
    {
        $this->links[$entity->getId()] = $entity;
    }

    public function remove(Link $entity, bool $flush = false): void
    {
        unset($this->links[$entity->getId()]);
    }

    public function getNextLink(DateTimeImmutable $date): Link
    {
        $links = $this->getAllOrderedLinks();

        foreach ($links as $link) {
            if ($link->getDate() > $date) {
                return $link;
            }
        }

        throw new Exception('No next link found');
    }

    public function getLastLink(): Link
    {
        $links = $this->getAllOrderedLinks();

        return $links[count($links) - 1];
    }

    public function getRangedLinks(RangeLinkDates $rangeLinkDates): array
    {
        $links = $this->getAllOrderedLinks();

        return array_values(
            array_filter(
                $links,
                function (Link $link) use ($rangeLinkDates) {
                    return $link->getDate() >= $rangeLinkDates->getStartDate()
                        && $link->getDate() <= $rangeLinkDates->getEndDate();
                }
            )
        );
    }

    public function isUrlAlreadyRegistered(string $url): bool
    {
        foreach ($this->links as $link) {
            if (rtrim($link->getUrl(), '/') === rtrim($url, '/')) {
                return true;
            }
        }

        return false;
    }

    /**
     * @return Link[]
     */
    private function getAllOrderedLinks(): array
    {
        $links = $this->links;

        usort(
            $links,
            function (Link $a, Link $b) {
                return $a->getDate() <=> $b->getDate();
            }
        );

        return $links;
    }
}
