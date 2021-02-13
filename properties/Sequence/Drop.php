<?php
declare(strict_types = 1);

namespace Properties\Innmind\Doctrine\Sequence;

use Innmind\BlackBox\Property;
use PHPUnit\Framework\Assert;

final class Drop implements Property
{
    private int $number;

    public function __construct(int $number)
    {
        $this->number = $number;
    }

    public function name(): string
    {
        return "Drop {$this->number} elements";
    }

    public function applicableTo(object $sequence): bool
    {
        return !$sequence->empty();
    }

    public function ensureHeldBy(object $sequence): object
    {
        $sequence2 = $sequence->drop($this->number);
        Assert::assertNotSame($sequence, $sequence2);
        Assert::assertFalse($sequence->equals($sequence2));
        Assert::assertLessThan($sequence->size(), $sequence2->size());
        $sequence2->foreach(static fn($element) => Assert::assertTrue($sequence->contains($element)));
        Assert::assertTrue(
            $sequence2->equals($sequence->drop($this->number)),
            'drop() is not idempotent',
        );

        return $sequence2;
    }
}
