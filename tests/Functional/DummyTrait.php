<?php

declare(strict_types=1);

namespace Tests\Functional;

use App\Domain\Entity\Category;
use App\Domain\Entity\Link;
use App\Domain\Entity\Recipient;
use App\Infrastructure\Repository\CategoryRepository;
use App\Infrastructure\Repository\LinkRepository;
use App\Infrastructure\Repository\RecipientRepository;
use Symfony\Component\DependencyInjection\Container;
use Tests\Builder\CategoryBuilder;
use Tests\Builder\LinkBuilder;
use Tests\Builder\RecipientBuilder;

/**
 * @method static Container getContainer()
 */
trait DummyTrait
{
    protected function createDummyCategory(): Category
    {
        self::getContainer()->get(CategoryRepository::class)->add(
            $category = (new CategoryBuilder())->build()
        );

        return $category;
    }

    protected function createDummyLink(): Link
    {
        self::getContainer()->get(LinkRepository::class)->add(
            $link = (new LinkBuilder())
                ->withCategory($this->createDummyCategory())
                ->build()
        );

        return $link;
    }

    private function createDummyRecipient(): Recipient
    {
        self::getContainer()->get(RecipientRepository::class)->add(
            $recipient = (new RecipientBuilder())->build()
        );

        return $recipient;
    }
}
