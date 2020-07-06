<?php
declare(strict_types = 1);

namespace Fixtures\Innmind\Doctrine;

use Innmind\BlackBox\Set;

final class Element
{
    /**
     * @return Set<object> Objects with a single method 'prop' returning integers
     */
    public static function any(): Set
    {
        return Set\Decorate::mutable(
            static fn(int $value): object => new class($value) {
                public int $value;

                public function __construct(int $value)
                {
                    $this->value = $value;
                }

                public function prop(): int
                {
                    return $this->value;
                }
            },
            Set\Integers::any(),
        );
    }
}
