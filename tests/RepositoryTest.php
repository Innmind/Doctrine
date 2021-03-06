<?php
declare(strict_types = 1);

namespace Tests\Innmind\Doctrine;

use Innmind\Doctrine\{
    Repository,
    Sequence,
    Exception\EntityNotFound,
    Exception\MutationOutsideOfContext,
};
use Doctrine\ORM\{
    EntityManagerInterface,
    EntityRepository,
    QueryBuilder,
};
use Doctrine\Persistence\ObjectRepository;
use PHPUnit\Framework\TestCase;
use Innmind\BlackBox\{
    PHPUnit\BlackBox,
    Set,
};
use Fixtures\Innmind\Doctrine\{
    Id,
    User,
};
use Example\Innmind\Doctrine\{
    User as Entity,
    Username,
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
                User::any(),
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
                User::any(),
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
                User::any(),
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
                User::any(),
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
                User::list(),
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
                            static function($entities, $entity) {
                                $entities[] = $entity;

                                return $entities;
                            },
                        ),
                );
            });
    }

    public function testAdvancedAll()
    {
        $this
            ->forAll(
                Set\Unicode::strings(),
                User::list(),
            )
            ->then(function($entityClass, $entities) {
                $repository = new Repository(
                    $doctrine = $this->createMock(EntityManagerInterface::class),
                    $entityClass,
                );
                $doctrine
                    ->expects($this->once())
                    ->method('getRepository')
                    ->willReturn($innerRepository = $this->createMock(EntityRepository::class));
                $innerRepository
                    ->expects($this->once())
                    ->method('createQueryBuilder')
                    ->with('entity')
                    ->willReturn($this->createMock(QueryBuilder::class));

                $this->assertInstanceOf(
                    Sequence\DeferQuery::class,
                    $repository->all(),
                );
            });
    }

    public function testMatchingSimpleSpecification()
    {
        $this
            ->forAll(
                Set\Unicode::strings(),
                Set\Unicode::strings(),
            )
            ->then(function($entityClass, $username) {
                $repository = new Repository(
                    $doctrine = $this->createMock(EntityManagerInterface::class),
                    $entityClass,
                );
                $doctrine
                    ->expects($this->once())
                    ->method('getRepository')
                    ->willReturn($this->createMock(ObjectRepository::class));

                $this->assertInstanceOf(
                    Sequence\DeferFindBy::class,
                    $repository->matching(Username::of($username)),
                );
            });
    }

    public function testMatchingAdvancedSpecification()
    {
        $this
            ->forAll(
                Set\Unicode::strings(),
            )
            ->then(function($username) {
                $doctrine = require __DIR__.'/../config/entity-manager.php';

                $repository = new Repository(
                    $doctrine,
                    Entity::class,
                );

                $this->assertInstanceOf(
                    Sequence\DeferQuery::class,
                    $repository->matching(Username::of($username)),
                );
            });
    }

    public function testPreventAddingWhenNotAllowed()
    {
        $this
            ->forAll(
                Set\Strings::any(),
                User::any()
            )
            ->then(function($entityClass, $entity) {
                $repository = new Repository(
                    $em = $this->createMock(EntityManagerInterface::class),
                    $entityClass,
                    static fn() => false,
                );
                $em
                    ->expects($this->never())
                    ->method('persist');

                $this->expectException(MutationOutsideOfContext::class);

                $repository->add($entity);
            });
    }

    public function testPreventRemovingWhenNotAllowed()
    {
        $this
            ->forAll(
                Set\Strings::any(),
                User::any()
            )
            ->then(function($entityClass, $entity) {
                $repository = new Repository(
                    $em = $this->createMock(EntityManagerInterface::class),
                    $entityClass,
                    static fn() => false,
                );
                $em
                    ->expects($this->never())
                    ->method('remove');

                $this->expectException(MutationOutsideOfContext::class);

                $repository->remove($entity);
            });
    }
}
