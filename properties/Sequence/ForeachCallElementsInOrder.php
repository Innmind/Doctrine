<?php
declare(strict_types = 1);

namespace Properties\Innmind\Doctrine\Sequence;

use Innmind\BlackBox\Property;
use PHPUnit\Framework\Assert;

final class ForeachCallElementsInOrder implements Property
{
    public function name(): string
    {
        return 'foreach() call elements in order';
    }

    public function applicableTo(object $sequence): bool
    {
        return true;
    }

    public function ensureHeldBy(object $sequence): object
    {
        $expected = $sequence->reduce(
            [],
            static function($elements, $element) {
                $elements[] = $element;

                return $elements;
            },
        );
        $elements = [];
        $sequence->foreach(static function($element) use (&$elements) {
            $elements[] = $element;
        });
        Assert::assertSame($expected, $elements);

        return $sequence;
    }
}
