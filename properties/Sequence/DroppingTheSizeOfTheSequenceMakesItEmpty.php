<?php
declare(strict_types = 1);

namespace Properties\Innmind\Doctrine\Sequence;

use Innmind\BlackBox\Property;
use PHPUnit\Framework\Assert;

final class DroppingTheSizeOfTheSequenceMakesItEmpty implements Property
{
    public function name(): string
    {
        return 'Dropping the size of the sequence makes it empty';
    }

    public function applicableTo(object $sequence): bool
    {
        return true;
    }

    public function ensureHeldBy(object $sequence): object
    {
        $sequence2 = $sequence->drop($sequence->size());
        Assert::assertNotSame($sequence, $sequence2);
        Assert::assertTrue($sequence2->empty());

        return $sequence2;
    }
}
