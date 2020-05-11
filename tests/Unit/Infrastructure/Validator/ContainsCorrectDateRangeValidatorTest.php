<?php

declare(strict_types=1);

namespace Tests\Unit\Infrastructure\Validator;

use App\Infrastructure\Validator\ContainsCorrectDateRange;
use App\Infrastructure\Validator\ContainsCorrectDateRangeValidator;
use DateInterval;
use DateTimeImmutable;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Component\Validator\Test\ConstraintValidatorTestCase;
use Tests\Builder\LinkBuilder;
use Tests\Builder\NewsletterBuilder;
use Tests\Double\Stub\Domain\Newsletter\LastLinkGuesserStub;

class ContainsCorrectDateRangeValidatorTest extends ConstraintValidatorTestCase
{
    private const string DATE = '2000-01-01';

    #[Test]
    public function it_validates_that_it_contains_correct_date_range(): void
    {
        $link = (new LinkBuilder())
            ->withDate((new DateTimeImmutable(self::DATE))->sub(new DateInterval('P1D')))
            ->build();

        $newsletter = (new NewsletterBuilder())
            ->withFirstLink($link)
            ->withLastLink($link)
            ->build();

        $this->validator->validate(
            $newsletter,
            new ContainsCorrectDateRange()
        );

        $this->assertNoViolation();
    }

    #[Test]
    public function it_validates_that_it_does_not_contain_correct_date_range(): void
    {
        $link = (new LinkBuilder())
            ->withDate((new DateTimeImmutable(self::DATE))->add(new DateInterval('P1D')))
            ->build();

        $newsletter = (new NewsletterBuilder())
            ->withFirstLink($link)
            ->withLastLink($link)
            ->build();

        $this->validator->validate(
            $newsletter,
            new ContainsCorrectDateRange()
        );

        $this
            ->buildViolation('newsletter.end_date_before_start_date')
            ->assertRaised();
    }

    protected function createValidator(): ContainsCorrectDateRangeValidator
    {
        $link = (new LinkBuilder())
            ->withDate(new DateTimeImmutable(self::DATE))
            ->build();

        return new ContainsCorrectDateRangeValidator(
            new LastLinkGuesserStub($link)
        );
    }
}
