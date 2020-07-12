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
                    new Username($username),
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
                    new Username($username),
                );

                if (!$property->applicableTo($sequence)) {
                    return;
                }

                $property->ensureHeldBy($sequence);
            });
    }

    public function properties(): iterable
    {
        foreach (Properties::list() as $property) {
            yield [$property];
        }
    }

    private function load(): void
    {
        $this
            ->entityManager
            ->getConnection()
            ->executeUpdate('DELETE FROM user');

        $this
            ->forAll(User::any())
            ->disableShrinking()
            ->take($this->seeder()(Set\Integers::between(0, 100)))
            ->then(function($user) {
                $this->entityManager->persist($user);
            });

        $this->entityManager->flush();
    }
}
