<?php
declare(strict_types = 1);

namespace Properties\Innmind\Doctrine\Sequence;

use Innmind\Doctrine\Sequence;
use Example\Innmind\Doctrine\User;
use Innmind\BlackBox\Property;
use PHPUnit\Framework\Assert;

final class Sort implements Property
{
    private User $min;
    private User $max;

    public function __construct(array $elements)
    {
        [$this->min, $this->max] = $elements;
    }

    public function name(): string
    {
        return 'Sort';
    }

    public function applicableTo(object $sequence): bool
    {
        return $sequence->size() >= 2;
    }

    public function ensureHeldBy(object $sequence): object
    {
        $sequence2 = $sequence->sort('prop', 'asc');
        $sequence3 = $sequence->sort('prop', 'desc');
        Assert::assertFalse($sequence3->equals($sequence2));
        Assert::assertTrue(
            $sequence2
                ->sort('prop', 'asc')
                ->equals($sequence2),
            'sort() is not idempotent',
        );
        Assert::assertTrue(
            $sequence2
                ->sort('prop', 'desc')
                ->sort('prop', 'asc')
                ->equals($sequence2),
            'sort() is not idempotent',
        );
        $elements = $this->unwrap($sequence2);
        Assert::assertLessThan(\reset($elements)->prop(), \end($elements)->prop());
        $elements = $this->unwrap($sequence3);
        Assert::assertGreaterThan(\reset($elements)->prop(), \end($elements)->prop());

        return $sequence;
    }

    private function unwrap(Sequence $sequence): array
    {
        return $sequence->reduce(
            [],
            function($elements, $element) {
                $elements[] = $element;

                return $elements;
            },
        );
    }
}
