<?php
declare(strict_types = 1);

namespace Properties\Innmind\Doctrine\Sequence;

use Innmind\BlackBox\Property;
use PHPUnit\Framework\Assert;

final class Add implements Property
{
    private object $element;

    public function __construct(object $element)
    {
        $this->element = $element;
    }

    public function name(): string
    {
        return 'Add';
    }

    public function applicableTo(object $sequence): bool
    {
        return true;
    }

    public function ensureHeldBy(object $sequence): object
    {
        $sequence2 = $sequence->add($this->element);
        Assert::assertFalse($sequence2->empty());
        Assert::assertNotSame($sequence, $sequence2);
        Assert::assertFalse($sequence->equals($sequence2));
        Assert::assertSame($sequence->size() + 1, $sequence2->size());
        Assert::assertFalse($sequence->contains($this->element));
        Assert::assertTrue($sequence2->contains($this->element));
        Assert::assertTrue(
            $sequence2->drop($sequence->size())->contains($this->element),
            'Element must be added last',
        );
        Assert::assertSame(
            1,
            $sequence2->drop($sequence->size())->size(),
            'Element must be added only once',
        );
        Assert::assertTrue(
            $sequence2->take($sequence->size())->equals($sequence),
            'Initial part of the sequence must not be altered',
        );
        $element = $sequence2->reduce(null, fn($_, $element) => $element);
        Assert::assertSame($this->element, $element);

        return $sequence2;
    }
}
