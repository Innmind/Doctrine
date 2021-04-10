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
    MultiType,
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
                    $this->entityManager,
                );

                $properties->ensureHeldBy(new DeferQuery(
                    $qb(Username::of($username)),
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
                    $this->entityManager,
                );
                $sequence = new DeferQuery(
                    $qb(Username::of($username)),
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
                    $this->entityManager,
                );

                $sequence = new DeferQuery(
                    $qb(Child::of($username1)->or(Child::of($username2))),
                );

                $sequence->foreach(function($user) use ($username1, $username2) {
                    $this->assertTrue($user->hasChild($username1, $username2));
                });
            });
    }

    public function testSearchInUsername()
    {
        $this
            ->forAll(Set\Sequence::of(
                Set\Elements::of('alice', 'bob', 'jane', 'john'),
                Set\Integers::between(1, 4),
            ))
            ->then(function($usernames) {
                $qb = new ToQueryBuilder(
                    $this->entityManager->getRepository(Entity::class),
                    $this->entityManager,
                );
                $sequence = new DeferQuery(
                    $qb(Username::in(...$usernames)),
                );

                $sequence->foreach(function($user) use ($usernames) {
                    $this->assertContains($user->username(), $usernames);
                });
            });
    }

    public function testSearchJsonField()
    {
        $this
            ->forAll(
                User::any(),
                Set\Elements::of(35, 'foo', false),
                Set\Elements::of(55, 'bar', true),
            )
            ->then(function($user, $value, $random) {
                $this->reset();

                $user->multiType = $value;
                $this->entityManager->persist($user);

                $this->entityManager->flush();
                $qb = new ToQueryBuilder(
                    $this->entityManager->getRepository(Entity::class),
                    $this->entityManager,
                );
                $sequence = new DeferQuery(
                    $qb(MultiType::of($value)),
                );

                $this->assertSame(1, $sequence->size());

                $sequence = new DeferQuery(
                    $qb(MultiType::of($random)),
                );

                $this->assertSame(0, $sequence->size());
            });
    }

    public function testSearchInJsonField()
    {
        $this
            ->forAll(
                User::any(),
            )
            ->then(function($user) {
                $this->reset();

                $user->multiType = 'foobar';
                $this->entityManager->persist($user);

                $this->entityManager->flush();
                $qb = new ToQueryBuilder(
                    $this->entityManager->getRepository(Entity::class),
                    $this->entityManager,
                );
                $sequence = new DeferQuery(
                    $qb(MultiType::contains('oba')),
                );

                $this->assertSame(1, $sequence->size());

                $sequence = new DeferQuery(
                    $qb(MultiType::contains('abo')),
                );

                $this->assertSame(0, $sequence->size());
            });
    }

    public function testSearchRelationJsonField()
    {
        $this
            ->forAll(
                User::any(User::list(1)),
                Set\Elements::of(35, 'foo', false),
                Set\Elements::of(55, 'bar', true),
            )
            ->then(function($user, $value, $random) {
                $this->reset();

                $user->children->first()->multiType = $value;
                $this->entityManager->persist($user);

                $this->entityManager->flush();
                $qb = new ToQueryBuilder(
                    $this->entityManager->getRepository(Entity::class),
                    $this->entityManager,
                );
                $sequence = new DeferQuery(
                    $qb(MultiType::child($value)),
                );

                $this->assertSame(1, $sequence->size());

                $sequence = new DeferQuery(
                    $qb(MultiType::child($random)),
                );

                $this->assertSame(0, $sequence->size());
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

    private function load(Set $children = null): void
    {
        $this->reset();

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
