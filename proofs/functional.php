<?php
declare(strict_types = 1);
declare(ticks = 1);

use Innmind\Doctrine\Manager;
use Innmind\Immutable\Either;
use Innmind\BlackBox\{
    Application,
    Set,
    Runner\IO\Collect,
};
use Fixtures\Innmind\Doctrine\User as FUser;
use Example\Innmind\Doctrine\{
    User,
    Address,
};

return static function() {
    $reset = static function($entityManager) {
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
    };

    yield test(
        'Lazy fetch doesnt load everything in memory',
        static function($assert) use ($reset) {
            $entityManager = require __DIR__.'/../config/entity-manager.php';
            $reset($entityManager);
            $manager = Manager::of($entityManager);
            $repository = $manager->repository(User::class);

            $collect = Collect::new();
            Application::new([])
                ->scenariiPerProof(10_000)
                ->displayOutputVia($collect)
                ->displayErrorVia($collect)
                ->tryToProve(static function() use ($manager, $repository) {
                    yield proof(
                        'create user',
                        given(FUser::any(null, Set\Sequence::of(
                            Set\Composite::immutable(
                                static fn(...$args) => new Address(...$args),
                                Set\Elements::of(true, false),
                                Set\Strings::madeOf(Set\Chars::alphanumerical()),
                            ),
                        )->between(0, 2))),
                        static function($assert, $user) use ($manager, $repository) {
                            $manager->mutate(static fn() => Either::right($repository->add($user)));
                            $manager->clear();
                        },
                    );
                });

            $memory = \memory_get_peak_usage();
            // Without the lazy loading combined with the clear of the manager it
            // takes between 20Mo and 36Mo, when lazy it ranges between 6Mo and 16Mo
            $assert
                ->memory(static function() use ($assert, $repository, $manager) {
                    $count = $repository
                        ->all()
                        ->lazy()
                        ->fetch()
                        ->reduce(
                            0,
                            static function(int $count, $user) use ($manager) {
                                $_ = $user->addresses(); // to make sure sub entities are loadable
                                $manager->clear();

                                return $count + 1;
                            },
                        );

                    $assert->same(10_000, $count);
                })
                ->inLessThan()
                ->megaBytes(18);
        },
    );
};
