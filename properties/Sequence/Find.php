<?php
declare(strict_types = 1);

namespace Properties\Innmind\Doctrine\Sequence;

use Innmind\Doctrine\Exception\NoElementMatchingPredicateFound;
use Innmind\BlackBox\Property;
use PHPUnit\Framework\Assert;

final class Find implements Property
{
    private object $element;

    public function __construct(object $element)
    {
        $this->element = $element;
    }

    public function name(): string
    {
        return 'Find';
    }

    public function applicableTo(object $sequence): bool
    {
        return true;
    }

    public function ensureHeldBy(object $sequence): object
    {
        $sequence2 = $sequence->add($this->element);
        Assert::assertSame(
            $this->element,
            $sequence2->find(fn($element) => $element === $this->element),
        );

        try {
            $sequence->find(fn($element) => $element === $this->element);
            Assert::fail('it should throw');
        } catch (NoElementMatchingPredicateFound $e) {
            // as intended
        }

        return $sequence2;
    }
}
