<?php

declare(strict_types=1);

namespace Tests\Functional\Infrastructure\Repository;

use App\Domain\Entity\Recipient;
use App\Infrastructure\Repository\ImageRepository;
use App\Infrastructure\Repository\RecipientRepository;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Tests\Builder\ImageBuilder;
use Tests\Builder\RecipientBuilder;

class ImageRepositoryTest extends KernelTestCase
{
    #[Test]
    public function it_adds_then_update_and_remove_an_image(): void
    {
        // Add

        $sut = self::getContainer()->get(ImageRepository::class);

        $sut->add(
            $image = (new ImageBuilder())->build(),
            flush: true
        );

        $this->assertCount(1, $sut->findAll());

        // Update

        $filename = 'plop.png';
        $image->setFilename($filename);

        $sut->update($image);

        $this->assertSame($filename, $sut->findAll()[0]->getFilename());

        // Remove

        $sut->remove($image, flush: true);

        $this->assertCount(0, $sut->findAll());
    }

    #[Test]
    public function it_checks_the_existence_of_an_image(): void
    {
        /** @var ImageRepository $sut */
        $sut = self::getContainer()->get(ImageRepository::class);

        $zFilename = 'z';
        $this->assertFalse($sut->exist($zFilename));

        $sut->add(
            (new ImageBuilder())
                ->withFilename($zFilename)
                ->build(),
            flush: true
        );
        $this->assertTrue($sut->exist($zFilename));
    }

    #[Test]
    public function it_sorts_for_association_field(): void
    {
        $sut = self::getContainer()->get(ImageRepository::class);

        $zFilename = 'z';
        $sut->add(
            (new ImageBuilder())
                ->withFilename($zFilename)
                ->build(),
            flush: true
        );

        $bFilename = 'b';
        $sut->add(
            (new ImageBuilder())
                ->withFilename($bFilename)
                ->build(),
            flush: true
        );

        $images = $sut->orderedForAssociationField()->getQuery()->getResult();
        $this->assertSame($bFilename, $images[0]->getFilename());
        $this->assertSame($zFilename, $images[1]->getFilename());
    }

    #[Test]
    public function it_finds_images_used_by_everyone(): void
    {
        $recipient1 = $this->createRecipient('recipient1');
        $recipient2 = $this->createRecipient('recipient2');

        $sut = self::getContainer()->get(ImageRepository::class);

        $sut->add(
            (new ImageBuilder())->build()->addRecipient($recipient1),
            flush: true
        );

        $sut->add(
            (new ImageBuilder())->build()
                ->addRecipient($recipient1)
                ->addRecipient($recipient2),
            flush: true
        );

        $imageIds = $sut->findIdsUsedByEveryone();

        $this->assertCount(1, $imageIds);
    }

    private function createRecipient(string $name): Recipient
    {
        $recipientRepository = self::getContainer()->get(RecipientRepository::class);
        $recipientRepository->add(
            $recipient = (new RecipientBuilder())
                ->withName($name)
                ->build()
        );

        return $recipient;
    }
}
