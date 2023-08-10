<?php
declare(strict_types = 1);

namespace Fixtures\Innmind\Doctrine;

use Innmind\Doctrine\Id;
use Example\Innmind\Doctrine\User as Entity;
use Innmind\BlackBox\Set;

final class User
{
    /**
     * @return Set<Entity>
     */
    public static function any(
        Set $children = null,
        Set $addresses = null,
    ): Set {
        return Set\Composite::mutable(
            static fn($uuid, $name, $registerIndex, $children, $addresses): Entity => new Entity(
                new Id($uuid),
                $name,
                $registerIndex,
                $children,
                $addresses,
            ),
            Set\Uuid::any(),
            Set\Elements::of('alice', 'bob', 'jane', 'john'),
            Set\Integers::any(),
            $children ? $children : Set\Elements::of([]),
            $addresses ? $addresses : Set\Elements::of([]),
        );
    }

    /**
     * @return Set<list<Entity>>
     */
    public static function list(int $min = 0): Set
    {
        return Set\Sequence::of(
            new Set\Randomize(self::any()),
            Set\Integers::between($min, 10),
        );
    }
}
