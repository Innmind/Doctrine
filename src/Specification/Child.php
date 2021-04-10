<?php
declare(strict_types = 1);

namespace Innmind\Doctrine\Specification;

use Innmind\Specification\{
    AndSpecification,
    Comparator,
    Specification,
};

/**
 * This specification is a helper to tell doctrine to create a new left join
 * for every composite so you can use multiple criterias on the child entity
 *
 * The left specification is used to build the condition for the join
 *
 * @psalm-immutable
 */
final class Child extends AndSpecification
{
    public function __construct(Comparator $left, Specification $right)
    {
        parent::__construct($left, $right);
    }
}
