<?php
declare(strict_types = 1);

namespace Properties\Innmind\Doctrine\Sequence;

use Innmind\BlackBox\Property;
use PHPUnit\Framework\Assert;

final class FilteringEmptySequenceHasNoEffect implements Property
{
    public function name(): string
    {
        return 'Filtering empty sequence has no effect';
    }

    public function applicableTo(object $sequence): bool
    {
        return $sequence->empty();
    }

    public function ensureHeldBy(object $sequence): object
    {
        Assert::assertTrue($sequence->filter(fn() => false)->empty());
        Assert::assertTrue($sequence->filter(fn() => true)->empty());

        return $sequence;
    }
}
