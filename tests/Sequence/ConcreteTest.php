<?php
declare(strict_types = 1);

namespace Tests\Innmind\Doctrine\Sequence;

use Innmind\Doctrine\{
    Sequence,
    Sequence\Concrete,
};
use PHPUnit\Framework\TestCase;
use Innmind\BlackBox\{
    PHPUnit\BlackBox,
    Set,
};
use Fixtures\Innmind\Doctrine\User;
use Properties\Innmind\Doctrine\Sequence as Properties;

class ConcreteTest extends TestCase
{
    use BlackBox;

    public function testInterface()
    {
        $this->assertInstanceOf(Sequence::class, Concrete::of());
    }

    public function testHoldProperties()
    {
        $this
            ->forAll(
                Properties::properties(),
                User::list(),
            )
            ->then(function($properties, $elements) {
                $properties->ensureHeldBy(Concrete::of(...$elements));
            });
    }

    public function testDeferHoldProperties()
    {
        $this
            ->forAll(
                Properties::properties(),
                User::list(),
            )
            ->then(function($properties, $elements) {
                $properties->ensureHeldBy(Concrete::defer((function($elements) {
                    yield from $elements;
                })($elements)));
            });
    }
}
