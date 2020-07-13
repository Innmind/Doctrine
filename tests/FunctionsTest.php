<?php
declare(strict_types = 1);

namespace Tests\Innmind\Doctrine;

use Innmind\Doctrine\Sequence\Concrete;
use function Innmind\Doctrine\unwrap;
use PHPUnit\Framework\TestCase;
use Innmind\BlackBox\{
    PHPUnit\BlackBox,
    Set,
};
use Fixtures\Innmind\Doctrine\User;

class FunctionsTest extends TestCase
{
    use BlackBox;

    public function testUnwrap()
    {
        $this
            ->forAll(
                Set\Sequence::of(
                    User::any(),
                    Set\Integers::between(0, 10),
                ),
            )
            ->then(function($users) {
                $this->assertSame(
                    $users,
                    unwrap(Concrete::of(...$users)),
                );
            });
    }
}
