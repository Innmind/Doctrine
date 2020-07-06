<?php
declare(strict_types = 1);

namespace Properties\Innmind\Doctrine\Sequence;

use Innmind\BlackBox\Property;
use PHPUnit\Framework\Assert;

final class Clear implements Property
{
    public function name(): string
    {
        return 'Clear';
    }

    public function applicableTo(object $sequence): bool
    {
        return true;
    }

    public function ensureHeldBy(object $sequence): object
    {
        $sequence2 = $sequence->clear();
        Assert::assertNotSame($sequence, $sequence2);
        Assert::assertTrue($sequence2->empty());
        Assert::assertSame(0, $sequence2->size());

        return $sequence2;
    }
}
