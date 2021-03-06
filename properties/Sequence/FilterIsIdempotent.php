<?php
declare(strict_types = 1);

namespace Properties\Innmind\Doctrine\Sequence;

use Innmind\BlackBox\Property;
use PHPUnit\Framework\Assert;

final class FilterIsIdempotent implements Property
{
    public function name(): string
    {
        return 'Filter is idempotent';
    }

    public function applicableTo(object $sequence): bool
    {
        return !$sequence->empty();
    }

    public function ensureHeldBy(object $sequence): object
    {
        Assert::assertTrue($sequence->filter(static fn() => false)->empty());
        Assert::assertFalse($sequence->filter(static fn() => true)->empty());
        Assert::assertFalse(
            $sequence
                ->filter(static fn() => false)
                ->equals($sequence),
        );
        Assert::assertTrue(
            $sequence
                ->filter(static fn() => true)
                ->equals($sequence),
        );

        return $sequence;
    }
}
