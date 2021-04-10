<?php
declare(strict_types = 1);

namespace Tests\Innmind\Doctrine\Sequence;

use Innmind\Doctrine\{
    Sequence\DeferFindBy,
    Sequence,
};
use Innmind\Specification\Specification;
use Doctrine\Persistence\ObjectRepository;
use PHPUnit\Framework\TestCase;
use Innmind\BlackBox\{
    PHPUnit\BlackBox,
    Set,
};
use Fixtures\Innmind\Doctrine\User;
use Properties\Innmind\Doctrine\Sequence as Properties;
use Example\Innmind\Doctrine\{
    User as Entity,
    Username,
};

class DeferFindByTest extends TestCase
{
    use BlackBox;

    private $entityManager;

    public function setUp(): void
    {
        $this->entityManager = require __DIR__.'/../../config/entity-manager.php';
        $this->load();
    }

    public function testInterface()
    {
        $this->assertInstanceOf(
            Sequence::class,
            new DeferFindBy(
                $this->createMock(ObjectRepository::class),
                $this->createMock(Specification::class),
            ),
        );
    }

    public function testHoldProperties()
    {
        $this
            ->forAll(
                Properties::properties(),
                Set\Elements::of('alice', 'bob', 'jane', 'john'),
            )
            ->then(function($properties, $username) {
                $properties->ensureHeldBy(new DeferFindBy(
                    $this->entityManager->getRepository(Entity::class),
                    Username::of($username),
                ));
            });
    }

    /**
     * @dataProvider properties
     */
    public function testHoldProperty($property)
    {
        $this
            ->forAll(
                $property,
                Set\Elements::of('alice', 'bob', 'jane', 'john'),
            )
            ->then(function($property, $username) {
                $sequence = new DeferFindBy(
                    $this->entityManager->getRepository(Entity::class),
                    Username::of($username),
                );

                if (!$property->applicableTo($sequence)) {
                    return;
                }

                $property->ensureHeldBy($sequence);
            });
    }

    public function testDropIsCumulativeWhileUnfetched()
    {
        $this
            ->forAll(
                Set\Integers::between(0, 100),
                Set\Integers::between(0, 100),
                Set\Elements::of('alice', 'bob', 'jane', 'john'),
            )
            ->then(function($toDrop1, $toDrop2, $username) {
                $repository = $this->createMock(ObjectRepository::class);
                $repository
                    ->expects($this->once())
                    ->method('findBy')
                    ->with(
                        ['username' => $username],
                        [],
                        null,
                        $toDrop1 + $toDrop2,
                    )
                    ->willReturn([]);
                $sequence = new DeferFindBy($repository, Username::of($username));

                $sequence = $sequence
                    ->drop($toDrop1)
                    ->drop($toDrop2);
                $sequence->size(); // call to size is here to trigger the findBy
                $sequence->size(); // assert findBy is called only once
            });
    }

    public function testSortIsCumulativeWhileUnfetched()
    {
        $this
            ->forAll(
                $this->name(),
                Set\Elements::of('asc', 'desc'),
                $this->name(),
                Set\Elements::of('asc', 'desc'),
                Set\Elements::of('alice', 'bob', 'jane', 'john'),
            )
            ->filter(fn($property1, $direction1, $property2) => $property1 !== $property2)
            ->then(function($property1, $direction1, $property2, $direction2, $username) {
                $repository = $this->createMock(ObjectRepository::class);
                $repository
                    ->expects($this->once())
                    ->method('findBy')
                    ->with(
                        ['username' => $username],
                        [
                            $property1 => $direction1,
                            $property2 => $direction2,
                        ],
                    )
                    ->willReturn([]);
                $sequence = new DeferFindBy($repository, Username::of($username));

                $sequence = $sequence
                    ->sort($property1, $direction1)
                    ->sort($property2, $direction2);
                $sequence->size(); // call to size is here to trigger the findBy
                $sequence->size(); // assert findBy is called only once
            });
    }

    public function properties(): iterable
    {
        foreach (Properties::list() as $property) {
            yield [$property];
        }
    }

    private function reset(): void
    {
        $this
            ->entityManager
            ->getConnection()
            ->executeUpdate('SET FOREIGN_KEY_CHECKS=0');
        $this
            ->entityManager
            ->getConnection()
            ->executeUpdate('TRUNCATE TABLE user_addresses');
        $this
            ->entityManager
            ->getConnection()
            ->executeUpdate('TRUNCATE TABLE address');
        $this
            ->entityManager
            ->getConnection()
            ->executeUpdate('TRUNCATE TABLE user');
        $this
            ->entityManager
            ->getConnection()
            ->executeUpdate('SET FOREIGN_KEY_CHECKS=1');
    }

    private function load(): void
    {
        $this->reset();

        $this
            ->forAll(User::any())
            ->disableShrinking()
            ->take($this->seeder()(Set\Integers::between(0, 100)))
            ->then(function($user) {
                $this->entityManager->persist($user);
            });

        $this->entityManager->flush();
    }

    private function name(): Set
    {
        return Set\Decorate::immutable(
            static fn(array $letters): string => \implode('', $letters),
            Set\Sequence::of(
                Set\Elements::of(...\range('a', 'z'), ...\range('A', 'Z')),
                Set\Integers::between(1, 10),
            ),
        );
    }
}
