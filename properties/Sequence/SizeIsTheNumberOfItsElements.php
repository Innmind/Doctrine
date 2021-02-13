<?php
declare(strict_types = 1);

namespace Properties\Innmind\Doctrine\Sequence;

use Innmind\BlackBox\Property;
use PHPUnit\Framework\Assert;

final class SizeIsTheNumberOfItsElements implements Property
{
    public function name(): string
    {
        return 'Size is the number of its elements';
    }

    public function applicableTo(object $sequence): bool
    {
        return true;
    }

    public function ensureHeldBy(object $sequence): object
    {
        $elements = $sequence->reduce(
            [],
            static function($elements, $element) {
                $elements[] = $element;

                return $elements;
            },
        );
        Assert::assertSame(\count($elements), $sequence->size());

        return $sequence;
    }
}
