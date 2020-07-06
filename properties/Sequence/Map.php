<?php
declare(strict_types = 1);

namespace Properties\Innmind\Doctrine\Sequence;

use Innmind\BlackBox\Property;
use PHPUnit\Framework\Assert;

final class Map implements Property
{
    private object $element;

    public function __construct(object $element)
    {
        $this->element = $element;
    }

    public function name(): string
    {
        return 'Map';
    }

    public function applicableTo(object $sequence): bool
    {
        return !$sequence->empty();
    }

    public function ensureHeldBy(object $sequence): object
    {
        $called = 0;
        $sequence2 = $sequence->map(function($element) use (&$called) {
            ++$called;

            return $element->prop();
        });
        Assert::assertNotSame($sequence, $sequence2);
        Assert::assertSame($sequence->size(), $sequence2->size());
        Assert::assertFalse($sequence2->equals($sequence));
        Assert::assertTrue(
            $sequence2->equals(
                $sequence->map(fn($element) => $element->prop()),
            ),
            'map() is not idempotent',
        );
        Assert::assertTrue(
            $sequence
                ->clear()
                ->add($this->element)
                ->map(fn($element) => $element->prop())
                ->contains($this->element->prop()),
        );
        Assert::assertTrue(
            $sequence
                ->map(fn($element) => $element)
                ->equals($sequence),
            'identity map must not have side effects',
        );

        return $sequence;
    }
}
