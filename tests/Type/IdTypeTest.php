<?php
declare(strict_types = 1);

namespace Tests\Innmind\Doctrine\Type;

use Innmind\Doctrine\{
    Type\IdType,
    Id,
    Exception\LogicException,
};
use Doctrine\DBAL\{
    Types\Type,
    Platforms\AbstractPlatform,
};
use PHPUnit\Framework\TestCase;
use Innmind\BlackBox\{
    PHPUnit\BlackBox,
    Set,
};

class IdTypeTest extends TestCase
{
    use BlackBox;

    public function testInterface()
    {
        $this->assertInstanceOf(
            Type::class,
            new IdType,
        );
    }

    public function testConvertNullToDatabase()
    {
        $type = new IdType;

        $this->assertNull($type->convertToDatabaseValue(
            null,
            $this->createMock(AbstractPlatform::class),
        ));
    }

    public function testConvertIdToDatabase()
    {
        $this
            ->forAll(Set\Uuid::any())
            ->then(function($uuid) {
                $type = new IdType;

                $this->assertSame(
                    $uuid,
                    $type->convertToDatabaseValue(
                        new Id($uuid),
                        $this->createMock(AbstractPlatform::class),
                    ),
                );
            });
    }

    public function testThrowWhenConvertingUnknownValueToDatabase()
    {
        $this
            ->forAll(
                new Set\Either( // here to use different types
                    Set\Uuid::any(),
                    Set\Integers::above(0),
                ),
            )
            ->then(function($id) {
                $type = new IdType;

                $this->expectException(LogicException::class);

                $type->convertToDatabaseValue(
                    $id,
                    $this->createMock(AbstractPlatform::class),
                );
            });
    }

    public function testConvertNullToPHP()
    {
        $type = new IdType;

        $this->assertNull($type->convertToPHPValue(
            null,
            $this->createMock(AbstractPlatform::class),
        ));
    }

    public function testConvertIdToPHP()
    {
        $this
            ->forAll(Set\Uuid::any())
            ->then(function($uuid) {
                $type = new IdType;

                $this->assertEquals(
                    new Id($uuid),
                    $type->convertToPHPValue(
                        $uuid,
                        $this->createMock(AbstractPlatform::class),
                    ),
                );
            });
    }

    public function testThrowWhenConvertingUnknownValueToPHP()
    {
        $this
            ->forAll(
                new Set\Either( // here to use different types
                    Set\Uuid::any(),
                    Set\Integers::above(0),
                ),
            )
            ->then(function($id) {
                $type = new IdType;

                $this->expectException(LogicException::class);

                $type->convertToPHPValue(
                    $id,
                    $this->createMock(AbstractPlatform::class),
                );
            });
    }
}
