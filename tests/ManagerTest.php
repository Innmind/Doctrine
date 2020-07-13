<?php
declare(strict_types = 1);

namespace Tests\Innmind\Doctrine;

use Innmind\Doctrine\{
    Manager,
    Repository,
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
}
