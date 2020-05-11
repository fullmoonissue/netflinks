<?php

declare(strict_types=1);

namespace App\Domain;

/**
 * @method static array cases()
 */
trait BackedEnumValuesTrait
{
    /**
     * @return string[]
     */
    public static function values(): array
    {
        return array_map(
            fn (self $backedEnum) => $backedEnum->value,
            self::cases()
        );
    }
}
