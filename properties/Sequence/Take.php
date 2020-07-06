<?php
declare(strict_types = 1);

namespace Properties\Innmind\Doctrine\Sequence;

use Innmind\BlackBox\Property;
use PHPUnit\Framework\Assert;

final class Take implements Property
{
    private int $number;

    public function __construct(int $number)
    {
        $this->number = $number;
    }

    public function name(): string
    {
        return "Take {$this->number} elements";
    }

    public function applicableTo(object $sequence): bool
    {
        return true;
    }

    public function ensureHeldBy(object $sequence): object
    {
        $sequence2 = $sequence->take($this->number);
        Assert::assertNotSame($sequence, $sequence2);
        Assert::assertLessThanOrEqual($sequence->size(), $sequence2->size());
        $sequence2->foreach(fn($element) => Assert::assertTrue($sequence->contains($element)));
        Assert::assertTrue(
            $sequence2->equals($sequence->take($this->number)),
            'take() is not idempotent',
        );

        return $sequence2;
    }
}
