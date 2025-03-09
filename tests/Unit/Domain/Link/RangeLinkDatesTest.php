<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\Link;

use App\Domain\Link\RangeLinkDates;
use DateTimeImmutable;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Throwable;

class RangeLinkDatesTest extends TestCase
{
    #[Test]
    public function it_does_not_throw_exception_when_link_dates_range_is_correct(): void
    {
        $isExceptionThrown = false;
        try {
            new RangeLinkDates(
                new DateTimeImmutable('- 3 days'),
                new DateTimeImmutable('now')
            );
        } catch (Throwable) {
            $isExceptionThrown = true;
        }

        $this->assertFalse($isExceptionThrown);
    }

    #[Test]
    public function it_does_throw_exception_when_link_dates_range_is_incorrect(): void
    {
        $isExceptionThrown = false;
        try {
            new RangeLinkDates(
                new DateTimeImmutable('now'),
                new DateTimeImmutable('- 3 days'),
            );
        } catch (Throwable) {
            $isExceptionThrown = true;
        }

        $this->assertTrue($isExceptionThrown);
    }
}
