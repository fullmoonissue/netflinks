<?php

declare(strict_types=1);

namespace Tests\Builder;

use App\Domain\Entity\Image;
use App\Domain\Entity\Recipient;
use Ramsey\Uuid\Uuid;

class ImageBuilder
{
    /**
     * @var Recipient[]
     */
    private array $recipients = [];

    private ?string $filename = null;

    public function withFilename(string $filename): self
    {
        $this->filename = $filename;

        return $this;
    }

    /**
     * @param Recipient[] $recipients
     */
    public function withRecipients(array $recipients): self
    {
        $this->recipients = $recipients;

        return $this;
    }

    public function build(): Image
    {
        $image = (new Image())
            ->setId(Uuid::uuid4()->toString())
            ->setFilename($this->filename ?? Uuid::uuid4()->toString());

        foreach ($this->recipients as $recipient) {
            $image->addRecipient($recipient);
        }

        return $image;
    }
}
