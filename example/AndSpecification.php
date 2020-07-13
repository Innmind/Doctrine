<?php
declare(strict_types = 1);

namespace Example\Innmind\Doctrine;

use Innmind\Specification\{
    Specification,
    Composite,
    Operator,
};

class AndSpecification implements Composite
{
    use Composable;

    private Specification $left;
    private Specification $right;
    private Operator $operator;

    public function __construct(
        Specification $left,
        Specification $right
    ) {
        $this->left = $left;
        $this->right = $right;
        $this->operator = Operator::and();
    }

    public function left(): Specification
    {
        return $this->left;
    }

    public function right(): Specification
    {
        return $this->right;
    }

    public function operator(): Operator
    {
        return $this->operator;
    }
}
