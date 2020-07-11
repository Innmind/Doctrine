<?php
declare(strict_types = 1);

namespace Tests\Innmind\Doctrine;

use Innmind\Doctrine\{
    Repository,
    Exception\EntityNotFound,
};
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ObjectRepository;
use PHPUnit\Framework\TestCase;
use Innmind\BlackBox\{
    PHPUnit\BlackBox,
    Set,
};
use Fixtures\Innmind\Doctrine\{
    Id,
    Element,
};

class RepositoryTest extends TestCase
{
    use BlackBox;

    public function testThrowWhenGettingUnknownValue()
    {
        $this
            ->forAll(
                Id::any(),
                Set\Unicode::strings(),
            )
            ->then(function($id, $entityClass) {
                $repository = new Repository(
                    $doctrine = $this->createMock(EntityManagerInterface::class),
                    $entityClass,
                );
                $doctrine
                    ->expects($this->once())
                    ->method('find')
                    ->with($entityClass, $id)
                    ->willReturn(null);

                $this->expectException(EntityNotFound::class);

                $repository->get($id);
            });
    }

    public function testGet()
    {
        $this
            ->forAll(
                Id::any(),
                Set\Unicode::strings(),
                Element::any(),
            )
            ->then(function($id, $entityClass, $entity) {
                $repository = new Repository(
                    $doctrine = $this->createMock(EntityManagerInterface::class),
                    $entityClass,
                );
                $doctrine
                    ->expects($this->once())
                    ->method('find')
                    ->with($entityClass, $id)
                    ->willReturn($entity);

                $this->assertSame($entity, $repository->get($id));
            });
    }

    public function testContains()
    {
        $this
            ->forAll(
                Id::any(),
                Set\Unicode::strings(),
                Element::any(),
            )
            ->then(function($id, $entityClass, $entity) {
                $repository = new Repository(
                    $doctrine = $this->createMock(EntityManagerInterface::class),
                    $entityClass,
                );
                $doctrine
                    ->expects($this->once())
                    ->method('find')
                    ->with($entityClass, $id)
                    ->willReturn($entity);

                $this->assertTrue($repository->contains($id));
            });
        $this
            ->forAll(
                Id::any(),
                Set\Unicode::strings(),
            )
            ->then(function($id, $entityClass) {
                $repository = new Repository(
                    $doctrine = $this->createMock(EntityManagerInterface::class),
                    $entityClass,
                );
                $doctrine
                    ->expects($this->once())
                    ->method('find')
                    ->with($entityClass, $id)
                    ->willReturn(null);

                $this->assertFalse($repository->contains($id));
            });
    }

    public function testAdd()
    {
        $this
            ->forAll(
                Set\Unicode::strings(),
                Element::any(),
            )
            ->then(function($entityClass, $entity) {
                $repository = new Repository(
                    $doctrine = $this->createMock(EntityManagerInterface::class),
                    $entityClass,
                );
                $doctrine
                    ->expects($this->once())
                    ->method('persist')
                    ->with($entity);

                $this->assertNull($repository->add($entity));
            });
    }

    public function testRemove()
    {
        $this
            ->forAll(
                Set\Unicode::strings(),
                Element::any(),
            )
            ->then(function($entityClass, $entity) {
                $repository = new Repository(
                    $doctrine = $this->createMock(EntityManagerInterface::class),
                    $entityClass,
                );
                $doctrine
                    ->expects($this->once())
                    ->method('remove')
                    ->with($entity);

                $this->assertNull($repository->remove($entity));
            });
    }

    public function testAll()
    {
        $this
            ->forAll(
                Set\Unicode::strings(),
                Set\Sequence::of(
                    Element::any(),
                ),
            )
            ->then(function($entityClass, $entities) {
                $repository = new Repository(
                    $doctrine = $this->createMock(EntityManagerInterface::class),
                    $entityClass,
                );
                $doctrine
                    ->expects($this->once())
                    ->method('getRepository')
                    ->willReturn($innerRepository = $this->createMock(ObjectRepository::class));
                $innerRepository
                    ->expects($this->once())
                    ->method('findAll')
                    ->willReturn($entities);

                $this->assertSame(
                    $entities,
                    $repository
                        ->all()
                        ->reduce(
                            [],
                            function($entities, $entity) {
                                $entities[] = $entity;

                                return $entities;
                            },
                        ),
                );
            });
    }
}
