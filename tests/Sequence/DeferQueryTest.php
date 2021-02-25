<?php
declare(strict_types = 1);

namespace Tests\Innmind\Doctrine\Sequence;

use Innmind\Doctrine\{
    Sequence\DeferQuery,
    Sequence,
    Specification\ToQueryBuilder,
};
use Doctrine\ORM\{
    QueryBuilder,
    AbstractQuery,
};
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
    Child,
};

class DeferQueryTest extends TestCase
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
            new DeferQuery(
                $this->createMock(QueryBuilder::class),
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
                $qb = new ToQueryBuilder(
                    $this->entityManager->getRepository(Entity::class),
                );

                $properties->ensureHeldBy(new DeferQuery(
                    $qb(new Username($username)),
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
                $qb = new ToQueryBuilder(
                    $this->entityManager->getRepository(Entity::class),
                );
                $sequence = new DeferQuery(
                    $qb(new Username($username)),
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
                Set\Integers::between(1, 100),
            )
            ->then(function($toDrop1, $toDrop2) {
                $queryBuilder = $this->createMock(QueryBuilder::class);
                $queryBuilder
                    ->expects($this->once())
                    ->method('setFirstResult')
                    ->with($toDrop1 + $toDrop2);
                $queryBuilder
                    ->expects($this->once())
                    ->method('getQuery')
                    ->willReturn($query = $this->createMock(AbstractQuery::class));
                $query
                    ->expects($this->once())
                    ->method('getResult')
                    ->willReturn([]);
                $sequence = new DeferQuery($queryBuilder);

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
            )
            ->filter(fn($property1, $direction1, $property2) => $property1 !== $property2)
            ->then(function($property1, $direction1, $property2, $direction2) {
                $queryBuilder = $this->createMock(QueryBuilder::class);
                $queryBuilder
                    ->expects($this->exactly(2))
                    ->method('addOrderBy')
                    ->withConsecutive(
                        ["entity.$property1", $direction1],
                        ["entity.$property2", $direction2],
                    );
                $queryBuilder
                    ->expects($this->once())
                    ->method('getQuery')
                    ->willReturn($query = $this->createMock(AbstractQuery::class));
                $query
                    ->expects($this->once())
                    ->method('getResult')
                    ->willReturn([]);
                $sequence = new DeferQuery($queryBuilder);

                $sequence = $sequence
                    ->sort($property1, $direction1)
                    ->sort($property2, $direction2);
                $sequence->size(); // call to size is here to trigger the findBy
                $sequence->size(); // assert findBy is called only once
            });
    }

    public function testCorrectlyLoadRelations()
    {
        $this->load(User::list());

        $this
            ->forAll(
                Set\Elements::of('alice', 'bob', 'jane', 'john'),
                Set\Elements::of('alice', 'bob', 'jane', 'john'),
            )
            ->then(function($username1, $username2) {
                $qb = new ToQueryBuilder(
                    $this->entityManager->getRepository(Entity::class),
                );

                $sequence = new DeferQuery(
                    $qb(Child::of($username1)->or(Child::of($username2))),
                );

                $sequence->foreach(function($user) use ($username1, $username2) {
                    $this->assertTrue($user->hasChild($username1, $username2));
                });
            });
    }

    public function properties(): iterable
    {
        foreach (Properties::list() as $property) {
            yield [$property];
        }
    }

    private function load(Set $children = null): void
    {
        $this
            ->entityManager
            ->getConnection()
            ->executeUpdate('TRUNCATE TABLE user');

        $this
            ->forAll(User::any($children))
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
