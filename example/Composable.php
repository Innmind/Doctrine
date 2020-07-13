<?php
declare(strict_types = 1);

namespace Example\Innmind\Doctrine;

use Innmind\Specification\{
    Specification,
    Composite,
    Not as NotInterface,
};

trait Composable
{
    public function and(Specification $specification): Composite
    {
        return new AndSpecification($this, $specification);
    }

    public function or(Specification $specification): Composite
    {
        return new OrSpecification($this, $specification);
    }

    public function not(): NotInterface
    {
        return new Not($this);
    }
}
