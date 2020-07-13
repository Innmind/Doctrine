<?php
declare(strict_types = 1);

namespace Properties\Innmind\Doctrine\Sequence;

use Innmind\Doctrine\Exception\NoElementMatchingPredicateFound;
use Example\Innmind\Doctrine\User;
use Innmind\BlackBox\Property;
use PHPUnit\Framework\Assert;

final class Find implements Property
{
    private User $element;

    public function __construct(User $element)
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
            $sequence2->find(fn(User $element) => $element === $this->element),
        );

        try {
            $sequence->find(fn(User $element) => $element === $this->element);
            Assert::fail('it should throw');
        } catch (NoElementMatchingPredicateFound $e) {
            // as intended
        }

        return $sequence2;
    }
}
