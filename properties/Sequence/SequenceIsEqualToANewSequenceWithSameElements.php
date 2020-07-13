<?php
declare(strict_types = 1);

namespace Properties\Innmind\Doctrine\Sequence;

use Innmind\Doctrine\Sequence\Concrete;
use Innmind\BlackBox\Property;
use PHPUnit\Framework\Assert;

final class SequenceIsEqualToANewSequenceWithSameElements implements Property
{
    public function name(): string
    {
        return 'Sequence is equal to a new sequence with the same elements';
    }

    public function applicableTo(object $sequence): bool
    {
        return true;
    }

    public function ensureHeldBy(object $sequence): object
    {
        $sequence2 = $sequence->reduce(
            Concrete::of(),
            static fn($sequence, $element) => $sequence->add($element),
        );
        Assert::assertTrue($sequence->equals($sequence2));
        Assert::assertTrue($sequence2->equals($sequence));

        return $sequence;
    }
}
