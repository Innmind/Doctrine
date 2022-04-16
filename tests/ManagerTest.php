<?php
declare(strict_types = 1);

namespace Tests\Innmind\Doctrine;

use Innmind\Doctrine\{
    Manager,
    Repository,
    Exception\NestedMutationNotSupported,
    Exception\MutationOutsideOfContext,
};
use Doctrine\ORM\{
    EntityManagerInterface,
};
use Doctrine\Persistence\ObjectRepository;
use PHPUnit\Framework\TestCase;
use Innmind\BlackBox\{
    PHPUnit\BlackBox,
    Set,
};
use Fixtures\Innmind\Doctrine\User;

class ManagerTest extends TestCase
{
    use BlackBox;

    public function testRepository()
    {
        $this
            ->forAll(Set\Strings::any())
            ->then(function($entityClass) {
                $manager = Manager::of(
                    $em = $this->createMock(EntityManagerInterface::class),
                );
                $em
                    ->expects($this->once())
                    ->method('getRepository')
                    ->with($entityClass)
                    ->willReturn($innerRepository = $this->createMock(ObjectRepository::class));
                $innerRepository
                    ->method('findAll')
                    ->willReturn([]);

                $repository = $manager->repository($entityClass);

                $this->assertInstanceOf(Repository::class, $repository);
                $repository->all(); // trigger getRepository assertion
            });
    }

    public function testNestedMutationThrows()
    {
        $manager = Manager::of(
            $this->createMock(EntityManagerInterface::class),
        );

        $this->expectException(NestedMutationNotSupported::class);

        $manager->mutate(static fn($manager) => $manager->mutate(static fn() => null));
    }

    public function testUseSameInstanceOfManagerInMutationContext()
    {
        $manager = Manager::of($this->createMock(EntityManagerInterface::class));

        $this->assertNull($manager->mutate(function($inner) use ($manager) {
            $this->assertSame($manager, $inner);
        }));
    }

    public function testFlushOnceTheMutationIsDone()
    {
        $manager = Manager::of(
            $em = $this->createMock(EntityManagerInterface::class),
        );
        $em
            ->expects($this->exactly(2))
            ->method('flush');

        $this->assertNull($manager->mutate(static fn() => null));
        $this->assertNull(
            $manager->mutate(static fn() => null),
            'Multiple mutations should be allowed',
        );
    }

    public function testAllowToReturnAValueFromMutationContext()
    {
        $this
            ->forAll(Set\Integers::any())
            ->then(function($return) {
                $manager = Manager::of(
                    $this->createMock(EntityManagerInterface::class),
                );

                $this->assertSame(
                    $return,
                    $manager->mutate(static fn() => $return),
                );
            });
    }

    public function testCloseTheEntityManagerWhenExceptionOccursDuringMutation()
    {
        $manager = Manager::of(
            $em = $this->createMock(EntityManagerInterface::class),
        );
        $em
            ->expects($this->once())
            ->method('close');
        $exception = new \Exception;

        try {
            $manager->mutate(static function() use ($exception) {
                throw $exception;
            });
            $this->fail('it should throw');
        } catch (\Throwable $e) {
            $this->assertSame($exception, $e);
        }
    }

    public function testPreventMutationOutsideOfContext()
    {
        $this
            ->forAll(
                Set\Strings::any(),
                User::any(),
            )
            ->then(function($entityClass, $entity) {
                $manager = Manager::of($this->createMock(EntityManagerInterface::class));
                $repository = $manager->repository($entityClass);

                $this->expectException(MutationOutsideOfContext::class);

                $repository->add($entity);
            });
    }

    public function testAllowMutationInContext()
    {
        $this
            ->forAll(
                Set\Strings::any(),
                User::any(),
            )
            ->then(function($entityClass, $entity) {
                $manager = Manager::of($this->createMock(EntityManagerInterface::class));

                $manager->mutate(function($manager) use ($entityClass, $entity) {
                    $repository = $manager->repository($entityClass);

                    $this->assertNull($repository->add($entity));
                });
            });
    }

    public function testNestedTransactionsThrows()
    {
        $manager = Manager::of(
            $this->createMock(EntityManagerInterface::class),
        );

        $this->expectException(NestedMutationNotSupported::class);

        $manager->transaction(static fn($manager) => $manager->transaction(static fn() => null));
    }

    public function testRollbackWhenAnExceptionIsThrown()
    {
        $manager = Manager::of(
            $em = $this->createMock(EntityManagerInterface::class),
        );
        $em
            ->expects($this->exactly(2))
            ->method('beginTransaction');
        $em
            ->expects($this->once())
            ->method('rollback');
        $exception = new \Exception;

        try {
            $manager->transaction(static function() use ($exception) {
                throw $exception;
            });
            $this->fail('it should throw');
        } catch (\Throwable $e) {
            $this->assertSame($exception, $e);
            $this->assertNull(
                $manager->transaction(static fn() => null),
                'the manager should be healthy after a failed transaction',
            );
        }
    }

    public function testTransaction()
    {
        $this
            ->forAll(Set\Unicode::strings())
            ->then(function($return) {
                $manager = Manager::of(
                    $em = $this->createMock(EntityManagerInterface::class),
                );
                $em
                    ->expects($this->exactly(2))
                    ->method('beginTransaction');
                $em
                    ->expects($this->exactly(2))
                    ->method('flush');
                $em
                    ->expects($this->exactly(2))
                    ->method('commit');

                $this->assertSame(
                    $return,
                    $manager->transaction(static fn() => $return),
                );
                $this->assertNull(
                    $manager->transaction(static fn() => null),
                    'the manager should be healthy after a transaction',
                );
            });
    }

    public function testUseSameInstanceOfManagerInTransactionContext()
    {
        $manager = Manager::of($this->createMock(EntityManagerInterface::class));

        $this->assertNull($manager->transaction(function($inner) use ($manager) {
            $this->assertSame($manager, $inner);
        }));
    }

    public function testAllowMutationInTransaction()
    {
        $this
            ->forAll(
                Set\Strings::any(),
                User::any(),
            )
            ->then(function($entityClass, $entity) {
                $manager = Manager::of($this->createMock(EntityManagerInterface::class));

                $manager->transaction(function($manager) use ($entityClass, $entity) {
                    $repository = $manager->repository($entityClass);

                    $this->assertNull($repository->add($entity));
                });
            });
    }

    public function testAllowPeriodicFlushesInTransaction()
    {
        $manager = Manager::of(
            $em = $this->createMock(EntityManagerInterface::class),
        );
        $em
            ->expects($this->once())
            ->method('beginTransaction');
        $em
            ->expects($this->exactly(2))
            ->method('flush');
        $em
            ->expects($this->once())
            ->method('clear');
        $em
            ->expects($this->once())
            ->method('commit');

        $manager->transaction(static fn($_, $flush) => $flush());
    }
}
