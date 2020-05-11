<?php

declare(strict_types=1);

namespace Tests\Functional\Infrastructure\Repository;

use App\Infrastructure\Repository\CategoryRepository;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Tests\Builder\CategoryBuilder;

class CategoryRepositoryTest extends KernelTestCase
{
    #[Test]
    public function it_adds_and_remove_a_category(): void
    {
        $sut = self::getContainer()->get(CategoryRepository::class);

        $sut->add(
            $category = (new CategoryBuilder())->build(),
            flush: true
        );

        $this->assertCount(1, $sut->findAll());

        $sut->remove($category, flush: true);

        $this->assertCount(0, $sut->findAll());
    }

    #[Test]
    public function it_sorts_for_association_field(): void
    {
        $sut = self::getContainer()->get(CategoryRepository::class);

        $zCategory = 'z';
        $sut->add(
            (new CategoryBuilder())
                ->withName($zCategory)
                ->build(),
            flush: true
        );

        $bCategory = 'b';
        $sut->add(
            (new CategoryBuilder())
                ->withName($bCategory)
                ->build(),
            flush: true
        );

        $categories = $sut->orderedForAssociationField()->getQuery()->getResult();
        $this->assertSame($bCategory, $categories[0]->getName());
        $this->assertSame($zCategory, $categories[1]->getName());
    }
}
