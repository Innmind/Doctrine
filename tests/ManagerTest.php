<?php
declare(strict_types = 1);

namespace Tests\Innmind\Doctrine;

use Innmind\Doctrine\{
    Manager,
    Repository,
    Exception\NestedMutationNotSupported,
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

class ManagerTest extends TestCase
{
    use BlackBox;

    public function testRepository()
    {
        $this
            ->forAll(Set\Strings::any())
            ->then(function($entityClass) {
                $manager = new Manager(
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
        $manager = new Manager(
            $this->createMock(EntityManagerInterface::class),
        );

        $this->expectException(NestedMutationNotSupported::class);

        $manager->mutate(fn($manager) => $manager->mutate(fn() => null));
    }

    public function testUseSameInstanceOfManagerInMutationContext()
    {
        $manager = new Manager($this->createMock(EntityManagerInterface::class));

        $this->assertNull($manager->mutate(function($inner) use ($manager) {
            $this->assertSame($manager, $inner);
        }));
    }

    public function testFlushOnceTheMutationIsDone()
    {
        $manager = new Manager(
            $em = $this->createMock(EntityManagerInterface::class),
        );
        $em
            ->expects($this->exactly(2))
            ->method('flush');

        $this->assertNull($manager->mutate(fn() => null));
        $this->assertNull(
            $manager->mutate(fn() => null),
            'Multiple mutations should be allowed',
        );
    }

    public function testCloseTheEntityManagerWhenExceptionOccursDuringMutation()
    {
        $manager = new Manager(
            $em = $this->createMock(EntityManagerInterface::class),
        );
        $em
            ->expects($this->once())
            ->method('close');
        $exception = new \Exception;

        try {
            $manager->mutate(function() use ($exception) {
                throw $exception;
            });
            $this->fail('it should throw');
        } catch (\Throwable $e) {
            $this->assertSame($exception, $e);
        }
    }
}
