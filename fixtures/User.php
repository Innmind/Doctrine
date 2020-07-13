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
    public static function any(): Set
    {
        return Set\Composite::mutable(
            static fn($uuid, $name, $registerIndex): Entity => new Entity(
                new Id($uuid),
                $name,
                $registerIndex,
            ),
            Set\Uuid::any(),
            Set\Elements::of('alice', 'bob', 'jane', 'john'),
            Set\Integers::any(),
        );
    }

    /**
     * @return Set<list<Entity>>
     */
    public static function list(int $min = 0): Set
    {
        return Set\Sequence::of(
            self::any(),
            Set\Integers::between($min, 10),
        );
    }
}
