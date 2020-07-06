<?php
declare(strict_types = 1);

namespace Properties\Innmind\Doctrine\Sequence;

use Innmind\Doctrine\Sequence;
use Innmind\BlackBox\Property;
use PHPUnit\Framework\Assert;

final class AppendAtTheEnd implements Property
{
    private Sequence $sequence;

    public function __construct(Sequence $sequence)
    {
        $this->sequence = $sequence;
    }

    public function name(): string
    {
        return 'Append at the end';
    }

    public function applicableTo(object $sequence): bool
    {
        return true;
    }

    public function ensureHeldBy(object $sequence): object
    {
        $sequence2 = $sequence->append($this->sequence);
        Assert::assertNotSame($sequence, $sequence2);
        Assert::assertNotSame($this->sequence, $sequence2);
        Assert::assertSame(
            $this->sequence->size() + $sequence->size(),
            $sequence2->size(),
        );
        Assert::assertTrue(
            $sequence2
                ->drop($sequence->size())
                ->equals($this->sequence),
        );
        Assert::assertTrue(
            $sequence2
                ->take($sequence->size())
                ->equals($sequence),
        );

        return $sequence2;
    }
}
