<?php
declare(strict_types = 1);

namespace Fixtures\Innmind\Doctrine;

use Innmind\Doctrine\Id as Model;
use Innmind\BlackBox\Set;

final class Id
{
    /**
     * @return Set<Model>
     */
    public static function any(): Set
    {
        return Set\Decorate::mutable(
            static fn(string $uuid): Model => new Model($uuid),
            Set\Uuid::any(),
        );
    }
}
