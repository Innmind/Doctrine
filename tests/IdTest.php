<?php
declare(strict_types = 1);

namespace Tests\Innmind\Doctrine;

use Innmind\Doctrine\{
    Id,
    Exception\DomainException,
};
use PHPUnit\Framework\TestCase;
use Innmind\BlackBox\{
    PHPUnit\BlackBox,
    Set,
};

class IdTest extends TestCase
{
    use BlackBox;

    public function testAcceptsUuids()
    {
        $this
            ->forAll(Set\Uuid::any())
            ->then(function($uuid) {
                $id = new Id($uuid);

                $this->assertSame($uuid, $id->toString());
            });
    }

    public function testRandomStringsAreRejected()
    {
        $this
            ->forAll(Set\Unicode::strings())
            ->then(function($value) {
                $this->expectException(DomainException::class);

                new Id($value);
            });
    }
}
