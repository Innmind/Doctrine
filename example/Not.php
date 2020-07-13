<?php
declare(strict_types = 1);

namespace Example\Innmind\Doctrine;

use Innmind\Specification\{
    Specification,
    Not as NotInterface,
};

class Not implements NotInterface
{
    use Composable;

    private Specification $specification;

    public function __construct(Specification $specification)
    {
        $this->specification = $specification;
    }

    public function specification(): Specification
    {
        return $this->specification;
    }
}
