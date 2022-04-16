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

    public function testIdEqualsItself()
    {
        $this
            ->forAll(Set\Uuid::any())
            ->then(function($uuid) {
                $id = new Id($uuid);

                $this->assertTrue($id->equals($id));
            });
    }

    public function testIdEqualsAnotherObjectInstanceWithTheSameUuid()
    {
        $this
            ->forAll(Set\Uuid::any())
            ->then(function($uuid) {
                $this->assertTrue((new Id($uuid))->equals(new Id($uuid)));
            });
    }

    public function testTwoDifferentUuidsAreNotEqual()
    {
        $this
            ->forAll(
                Set\Uuid::any(),
                Set\Uuid::any(),
            )
            ->then(function($uuid1, $uuid2) {
                $this->assertFalse((new Id($uuid1))->equals(new Id($uuid2)));
            });
    }

    public function testNewReturnAnId()
    {
        $this->assertInstanceOf(
            Id::class,
            Id::new('stdClass'),
        );
    }

    public function testNewNeverReturnsTheSameValue()
    {
        $this->assertFalse(Id::new('stdClass')->equals(Id::new('stdClass')));
    }
}
