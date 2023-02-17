<?php
declare(strict_types = 1);

namespace Tests\Innmind\Doctrine;

use Innmind\Doctrine\{
    Manager,
    Sort,
};
use Innmind\Immutable\Either;
use PHPUnit\Framework\TestCase;
use Innmind\BlackBox\{
    PHPUnit\BlackBox,
    Set,
};
use Fixtures\Innmind\Doctrine\User as FUser;
use Example\Innmind\Doctrine\{
    User,
    Username,
};

class FunctionalTest extends TestCase
{
    use BlackBox;

    public function testAllPagination()
    {
        $entityManager = require __DIR__.'/../config/entity-manager.php';
        $this->reset($entityManager);
        $manager = Manager::of($entityManager);
        $repository = $manager->repository(User::class);
        $this
            ->forAll(FUser::any())
            ->take(10)
            ->then(static function($user) use ($manager, $repository) {
                $manager->mutate(static fn() => Either::right($repository->add($user)));
            });

        $this->assertCount(
            5,
            $repository
                ->all()
                ->take(5)
                ->fetch(),
        );
        $this->assertCount(
            5,
            $repository
                ->all()
                ->drop(5)
                ->take(5)
                ->fetch(),
        );
        $this->assertCount(
            0,
            $repository
                ->all()
                ->drop(10)
                ->take(5)
                ->fetch(),
        );
        $this->assertCount(
            3,
            $repository
                ->all()
                ->drop(7)
                ->take(5)
                ->fetch(),
        );
        $this->assertCount(
            4,
            $repository
                ->all()
                ->drop(3)
                ->drop(3)
                ->take(5)
                ->fetch(),
        );
    }

    public function testAllSorting()
    {
        $entityManager = require __DIR__.'/../config/entity-manager.php';
        $this->reset($entityManager);
        $manager = Manager::of($entityManager);
        $repository = $manager->repository(User::class);
        $this
            ->forAll(FUser::any())
            ->take(100)
            ->then(static function($user) use ($manager, $repository) {
                $manager->mutate(static fn() => Either::right($repository->add($user)));
            });

        $users = $repository
            ->all()
            ->sort('username', Sort::asc)
            ->fetch();

        $this->assertSame(
            'alice',
            $users->first()->match(
                static fn($user) => $user->username(),
                static fn() => null,
            ),
        );
        $this->assertSame(
            'john',
            $users->last()->match(
                static fn($user) => $user->username(),
                static fn() => null,
            ),
        );

        $users = $repository
            ->all()
            ->sort('username', Sort::desc)
            ->fetch();

        $this->assertSame(
            'john',
            $users->first()->match(
                static fn($user) => $user->username(),
                static fn() => null,
            ),
        );
        $this->assertSame(
            'alice',
            $users->last()->match(
                static fn($user) => $user->username(),
                static fn() => null,
            ),
        );
    }

    public function testMatchingPagination()
    {
        $entityManager = require __DIR__.'/../config/entity-manager.php';
        $this->reset($entityManager);
        $manager = Manager::of($entityManager);
        $repository = $manager->repository(User::class);
        $this
            ->forAll(FUser::any())
            ->take(100)
            ->then(static function($user) use ($manager, $repository) {
                $manager->mutate(static fn() => Either::right($repository->add($user)));
            });

        $allAlices = $repository
            ->matching(Username::of('alice'))
            ->fetch();

        if ($allAlices->empty()) {
            return;
        }

        $alices = $repository
            ->matching(Username::of('alice'))
            ->take(5)
            ->fetch();

        $this->assertLessThanOrEqual(
            5,
            $alices->size(),
        );

        $alices = $repository
            ->matching(Username::of('alice'))
            ->drop(3)
            ->drop(3)
            ->fetch();

        $this->assertSame(
            \max(0, $allAlices->size() - 6),
            $alices->size(),
        );

        $alices = $repository
            ->matching(Username::of('alice'))
            ->drop(3)
            ->drop(3)
            ->take(5)
            ->fetch();

        $this->assertLessThanOrEqual(
            5,
            $alices->size(),
        );
    }

    public function testMatchingSorting()
    {
        $entityManager = require __DIR__.'/../config/entity-manager.php';
        $this->reset($entityManager);
        $manager = Manager::of($entityManager);
        $repository = $manager->repository(User::class);
        $this
            ->forAll(FUser::any())
            ->take(100)
            ->then(static function($user) use ($manager, $repository) {
                $manager->mutate(static fn() => Either::right($repository->add($user)));
            });

        $users = $repository
            ->matching(Username::startsWith('j'))
            ->sort('username', Sort::asc)
            ->fetch();

        $this->assertSame(
            'jane',
            $users->first()->match(
                static fn($user) => $user->username(),
                static fn() => null,
            ),
        );
        $this->assertSame(
            'john',
            $users->last()->match(
                static fn($user) => $user->username(),
                static fn() => null,
            ),
        );

        $users = $repository
            ->matching(Username::startsWith('j'))
            ->sort('username', Sort::desc)
            ->fetch();

        $this->assertSame(
            'john',
            $users->first()->match(
                static fn($user) => $user->username(),
                static fn() => null,
            ),
        );
        $this->assertSame(
            'jane',
            $users->last()->match(
                static fn($user) => $user->username(),
                static fn() => null,
            ),
        );
    }

    public function testCount()
    {
        $entityManager = require __DIR__.'/../config/entity-manager.php';
        $this->reset($entityManager);
        $manager = Manager::of($entityManager);
        $repository = $manager->repository(User::class);
        $this
            ->forAll(FUser::any())
            ->take(100)
            ->then(static function($user) use ($manager, $repository) {
                $manager->mutate(static fn() => Either::right($repository->add($user)));
            });

        $expected = $repository
            ->all()
            ->fetch()
            ->filter(static fn($user) => $user->username() === 'alice')
            ->size();

        $alices = $repository->count(Username::of('alice'));

        $this->assertGreaterThanOrEqual(0, $alices);
        $this->assertSame($expected, $alices);
    }

    private function reset($entityManager): void
    {
        $entityManager
            ->getConnection()
            ->executeUpdate('SET FOREIGN_KEY_CHECKS=0');
        $entityManager
            ->getConnection()
            ->executeUpdate('TRUNCATE TABLE user_addresses');
        $entityManager
            ->getConnection()
            ->executeUpdate('TRUNCATE TABLE address');
        $entityManager
            ->getConnection()
            ->executeUpdate('TRUNCATE TABLE user');
        $entityManager
            ->getConnection()
            ->executeUpdate('SET FOREIGN_KEY_CHECKS=1');
    }
}
