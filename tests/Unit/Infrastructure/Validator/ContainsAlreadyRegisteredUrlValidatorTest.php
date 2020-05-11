<?php

declare(strict_types=1);

namespace Tests\Unit\Infrastructure\Validator;

use App\Domain\Entity\Link;
use App\Infrastructure\Validator\ContainsAlreadyRegisteredUrl;
use App\Infrastructure\Validator\ContainsAlreadyRegisteredUrlValidator;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Component\Validator\Test\ConstraintValidatorTestCase;
use Tests\Builder\LinkBuilder;
use Tests\Double\Fake\Infrastructure\Repository\LinkRepositoryFake;

class ContainsAlreadyRegisteredUrlValidatorTest extends ConstraintValidatorTestCase
{
    private const string URL = 'https://www.perdu.com';

    private const string OTHER_URL = 'https://www.youtube.com';

    #[Test]
    public function it_validates_that_it_contains_already_registered_url_during_creation(): void
    {
        // No LinkBuilder() because of the id (wanted to not be set)
        $link = (new Link())->setUrl(self::URL);

        $this->validator->validate(
            $link,
            new ContainsAlreadyRegisteredUrl()
        );

        $this
            ->buildViolation('link.url_already_registered')
            ->assertRaised();
    }

    #[Test]
    public function it_validates_that_it_does_not_contain_already_registered_url_during_update(): void
    {
        $link = (new LinkBuilder())
            ->withUrl(self::URL)
            ->build();

        $this->validator->validate(
            $link,
            new ContainsAlreadyRegisteredUrl()
        );

        $this->assertNoViolation();
    }

    #[Test]
    public function it_validates_that_it_does_not_contain_already_registered_url_during_creation(): void
    {
        // No LinkBuilder() because of the id to not be set
        $link = (new Link())->setUrl(self::OTHER_URL);

        $this->validator->validate(
            $link,
            new ContainsAlreadyRegisteredUrl()
        );

        $this->assertNoViolation();
    }

    protected function createValidator(): ContainsAlreadyRegisteredUrlValidator
    {
        $link = (new LinkBuilder())
            ->withUrl(self::URL)
            ->build();

        return new ContainsAlreadyRegisteredUrlValidator(
            new LinkRepositoryFake([$link])
        );
    }
}
