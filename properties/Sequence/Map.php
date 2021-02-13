<?php
declare(strict_types = 1);

namespace Properties\Innmind\Doctrine\Sequence;

use Example\Innmind\Doctrine\User;
use Innmind\BlackBox\Property;
use PHPUnit\Framework\Assert;

final class Map implements Property
{
    private User $element;

    public function __construct(User $element)
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
        $sequence2 = $sequence->map(static function(User $element) use (&$called) {
            ++$called;

            return $element->registerIndex();
        });
        Assert::assertNotSame($sequence, $sequence2);
        Assert::assertSame($sequence->size(), $sequence2->size());
        Assert::assertFalse($sequence2->equals($sequence));
        Assert::assertTrue(
            $sequence2->equals(
                $sequence->map(static fn(User $element) => $element->registerIndex()),
            ),
            'map() is not idempotent',
        );
        Assert::assertTrue(
            $sequence
                ->clear()
                ->add($this->element)
                ->map(fn(User $element) => $element->registerIndex())
                ->contains($this->element->registerIndex()),
        );
        Assert::assertTrue(
            $sequence
                ->map(static fn(User $element) => $element)
                ->equals($sequence),
            'identity map must not have side effects',
        );

        return $sequence;
    }
}
