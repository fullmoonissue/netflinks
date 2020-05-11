<?php

declare(strict_types=1);

namespace Tests\Functional\Infrastructure\Repository;

use App\Infrastructure\Repository\TagRepository;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Tests\Builder\TagBuilder;

class TagRepositoryTest extends KernelTestCase
{
    #[Test]
    public function it_adds_and_remove_a_tag(): void
    {
        $sut = self::getContainer()->get(TagRepository::class);

        $sut->add(
            $tag = (new TagBuilder())->build(),
            flush: true
        );

        $this->assertCount(1, $sut->findAll());

        $sut->remove($tag, flush: true);

        $this->assertCount(0, $sut->findAll());
    }

    #[Test]
    public function it_sorts_for_association_field(): void
    {
        $sut = self::getContainer()->get(TagRepository::class);

        $zTag = 'z';
        $sut->add(
            (new TagBuilder())
                ->withName($zTag)
                ->build(),
            flush: true
        );

        $bTag = 'b';
        $sut->add(
            (new TagBuilder())
                ->withName($bTag)
                ->build(),
            flush: true
        );

        $categories = $sut->orderedForAssociationField()->getQuery()->getResult();
        $this->assertSame($bTag, $categories[0]->getName());
        $this->assertSame($zTag, $categories[1]->getName());
    }
}
