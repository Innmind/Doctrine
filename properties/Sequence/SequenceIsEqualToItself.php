<?php
declare(strict_types = 1);

namespace Properties\Innmind\Doctrine\Sequence;

use Innmind\BlackBox\Property;
use PHPUnit\Framework\Assert;

final class SequenceIsEqualToItself implements Property
{
    public function name(): string
    {
        return 'Sequence is equal to itself';
    }

    public function applicableTo(object $sequence): bool
    {
        return true;
    }

    public function ensureHeldBy(object $sequence): object
    {
        Assert::assertTrue($sequence->equals($sequence));

        return $sequence;
    }
}
