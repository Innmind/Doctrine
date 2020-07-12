<?php
declare(strict_types = 1);

namespace Tests\Innmind\Doctrine\Specification;

use Innmind\Doctrine\{
    Specification\ToArray,
    Exception\OnlyAndCompositeSupported,
    Exception\OnlyEqualityComparisonSupported,
};
use Innmind\Specification\{
    Not,
    Composite,
    Operator,
    Sign,
    Comparator,
};
use PHPUnit\Framework\TestCase;
use Innmind\BlackBox\{
    PHPUnit\BlackBox,
    Set,
};

class ToArrayTest extends TestCase
{
    use BlackBox;

    public function testThrowWhenUsingNegatedSpecification()
    {
        $this->expectException(OnlyAndCompositeSupported::class);

        (new ToArray)($this->createMock(Not::class));
    }

    public function testThrowWhenUsingOrOperator()
    {
        $this->expectException(OnlyAndCompositeSupported::class);

        $specification = $this->createMock(Composite::class);
        $specification
            ->expects($this->once())
            ->method('operator')
            ->willReturn(Operator::or());

        (new ToArray)($specification);
    }

    public function testThrowWhenUsingSomethingElseThanEqualityComparison()
    {
        $this
            ->forAll(Set\Elements::of(
                'inequality',
                'lessThan',
                'moreThan',
                'lessThanOrEqual',
                'moreThanOrEqual',
                'isNull',
                'isNotNull',
                'startsWith',
                'endsWith',
                'contains',
                'in',
            ))
            ->then(function($sign) {
                $this->expectException(OnlyEqualityComparisonSupported::class);

                $specification = $this->createMock(Comparator::class);
                $specification
                    ->expects($this->once())
                    ->method('sign')
                    ->willReturn(Sign::$sign());

                (new ToArray)($specification);
            });
    }

    public function testTransformComparator()
    {
        $this
            ->forAll(
                Set\Strings::matching('[a-zA-Z]+'),
                Set\Unicode::strings(),
            )
            ->then(function($property, $value) {
                $specification = $this->createMock(Comparator::class);
                $specification
                    ->expects($this->once())
                    ->method('sign')
                    ->willReturn(Sign::equality());
                $specification
                    ->expects($this->once())
                    ->method('property')
                    ->willReturn($property);
                $specification
                    ->expects($this->once())
                    ->method('value')
                    ->willReturn($value);

                $this->assertSame(
                    [$property => $value],
                    (new ToArray)($specification),
                );
            });
    }

    public function testTransformAndComposite()
    {
        $this
            ->forAll(
                Set\Strings::matching('[a-zA-Z]+'),
                Set\Unicode::strings(),
                Set\Strings::matching('[a-zA-Z]+'),
                Set\Unicode::strings(),
            )
            ->then(function($leftProperty, $leftValue, $rightProperty, $rightValue) {
                $left = $this->createMock(Comparator::class);
                $left
                    ->expects($this->once())
                    ->method('sign')
                    ->willReturn(Sign::equality());
                $left
                    ->expects($this->once())
                    ->method('property')
                    ->willReturn($leftProperty);
                $left
                    ->expects($this->once())
                    ->method('value')
                    ->willReturn($leftValue);
                $right = $this->createMock(Comparator::class);
                $right
                    ->expects($this->once())
                    ->method('sign')
                    ->willReturn(Sign::equality());
                $right
                    ->expects($this->once())
                    ->method('property')
                    ->willReturn($rightProperty);
                $right
                    ->expects($this->once())
                    ->method('value')
                    ->willReturn($rightValue);
                $and = $this->createMock(Composite::class);
                $and
                    ->expects($this->once())
                    ->method('operator')
                    ->willReturn(Operator::and());
                $and
                    ->expects($this->once())
                    ->method('left')
                    ->willReturn($left);
                $and
                    ->expects($this->once())
                    ->method('right')
                    ->willReturn($right);

                $this->assertSame(
                    [
                        $leftProperty => $leftValue,
                        $rightProperty => $rightValue,
                    ],
                    (new ToArray)($and),
                );
            });
    }
}
