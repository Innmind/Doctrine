<?php
declare(strict_types = 1);

namespace Properties\Innmind\Doctrine;

use Innmind\Doctrine\Sequence\Concrete;
use Fixtures\Innmind\Doctrine\User;
use Innmind\BlackBox\{
    Set,
    Property,
};

final class Sequence
{
    /**
     * @return Set<Property>
     */
    public static function properties(): Set
    {
        return Set\Properties::any(...self::list());
    }

    /**
     * @return list<Set\Property>
     */
    public static function list(): array
    {
        return [
            Set\Property::of(
                Sequence\Add::class,
                User::any(),
            ),
            Set\Property::of(
                Sequence\SizeIsTheNumberOfItsElements::class,
            ),
            Set\Property::of(
                Sequence\DroppingTheSizeOfTheSequenceMakesItEmpty::class,
            ),
            Set\Property::of(
                Sequence\SequenceIsEqualToItself::class,
            ),
            Set\Property::of(
                Sequence\SequenceIsEqualToANewSequenceWithSameElements::class,
            ),
            Set\Property::of(
                Sequence\FilterIsIdempotent::class,
            ),
            Set\Property::of(
                Sequence\FilteringEmptySequenceHasNoEffect::class,
            ),
            Set\Property::of(
                Sequence\ForeachCallElementsInOrder::class,
            ),
            Set\Property::of(
                Sequence\Drop::class,
                Set\Integers::between(1, 100),
            ),
            Set\Property::of(
                Sequence\Take::class,
                Set\Integers::between(1, 100),
            ),
            Set\Property::of(
                Sequence\Map::class,
                User::any(),
            ),
            Set\Property::of(
                Sequence\AppendAtTheEnd::class,
                Set\Decorate::immutable(
                    static fn(array $elements): Concrete => Concrete::of(...$elements),
                    User::list(1),
                ),
            ),
            Set\Property::of(
                Sequence\Clear::class,
            ),
            Set\Property::of(
                Sequence\Find::class,
                User::any(),
            ),
            Set\Property::of(
                Sequence\Sort::class,
                Set\Composite::immutable(
                    static fn($a, $b) => [$a, $b],
                    User::any(),
                    User::any(),
                )->filter(static fn($elements) => $elements[0]->registerIndex() < $elements[1]->registerIndex()),
            ),
        ];
    }
}
