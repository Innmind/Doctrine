<?php
declare(strict_types = 1);

namespace Innmind\Doctrine;

use Innmind\Doctrine\Specification\{
    ToQueryBuilder,
    ToArray,
};
use Innmind\Specification\Specification;
use Innmind\Immutable\Sequence;
use Doctrine\ORM\{
    EntityManagerInterface,
    EntityRepository,
};
use Doctrine\Persistence\ObjectRepository;

/**
 * @psalm-immutable
 * @template T of object
 */
final class Matching
{
    private EntityManagerInterface $manager;
    /** @var EntityRepository<T>|ObjectRepository<T> */
    private EntityRepository|ObjectRepository $repository;
    private ?Specification $specification;
    /** @var array<non-empty-string, Sort> */
    private array $sort;
    private int $toDrop;
    private ?int $toTake;
    private bool $lazy;

    /**
     * @param EntityRepository<T>|ObjectRepository<T> $repository
     * @param array<non-empty-string, Sort> $sort
     */
    private function __construct(
        EntityManagerInterface $manager,
        EntityRepository|ObjectRepository $repository,
        ?Specification $specification,
        array $sort,
        int $toDrop,
        ?int $toTake,
        bool $lazy,
    ) {
        $this->manager = $manager;
        $this->repository = $repository;
        $this->specification = $specification;
        $this->sort = $sort;
        $this->toDrop = $toDrop;
        $this->toTake = $toTake;
        $this->lazy = $lazy;
    }

    /**
     * @internal
     * @psalm-pure
     * @template A of object
     *
     * @param EntityRepository<A>|ObjectRepository<A> $repository
     *
     * @return self<A>
     */
    public static function of(
        EntityManagerInterface $manager,
        EntityRepository|ObjectRepository $repository,
        Specification $specification,
    ): self {
        return new self($manager, $repository, $specification, [], 0, null, false);
    }

    /**
     * @internal
     * @psalm-pure
     * @template A of object
     *
     * @param EntityRepository<A>|ObjectRepository<A> $repository
     *
     * @return self<A>
     */
    public static function all(
        EntityManagerInterface $manager,
        EntityRepository|ObjectRepository $repository,
    ): self {
        return new self($manager, $repository, null, [], 0, null, false);
    }

    /**
     * @param positive-int $size
     *
     * @return self<T>
     */
    public function drop(int $size): self
    {
        /** @var self<T> */
        return new self(
            $this->manager,
            $this->repository,
            $this->specification,
            $this->sort,
            $this->toDrop + $size,
            $this->toTake,
            $this->lazy,
        );
    }

    /**
     * @param positive-int $size
     *
     * @return self<T>
     */
    public function take(int $size): self
    {
        /** @var self<T> */
        return new self(
            $this->manager,
            $this->repository,
            $this->specification,
            $this->sort,
            $this->toDrop,
            $size,
            $this->lazy,
        );
    }

    /**
     * @param non-empty-string $property
     *
     * @return self<T>
     */
    public function sort(string $property, Sort $direction): self
    {
        /** @var self<T> */
        return new self(
            $this->manager,
            $this->repository,
            $this->specification,
            \array_merge(
                $this->sort,
                ["entity.$property" => $direction], // @see ToQueryBuilder::expression() for the prefix "entity."
            ),
            $this->toDrop,
            $this->toTake,
            $this->lazy,
        );
    }

    /**
     * @param callable(self<T>): self<T> $map
     *
     * @return self<T>
     */
    public function map(callable $map): self
    {
        /** @psalm-suppress ImpureFunctionCall */
        return $map($this);
    }

    /**
     * @return self<T>
     */
    public function lazy(): self
    {
        /** @var self<T> */
        return new self(
            $this->manager,
            $this->repository,
            $this->specification,
            $this->sort,
            $this->toDrop,
            $this->toTake,
            true,
        );
    }

    /**
     * @return Sequence<T>
     */
    public function fetch(): Sequence
    {
        if ($this->repository instanceof EntityRepository) {
            /** @psalm-suppress InvalidArgument */
            return $this->fetchQueryBuilder($this->repository);
        }

        return $this->directFetch($this->repository);
    }

    /**
     * @param EntityRepository<T> $repository
     *
     * @return Sequence<T>
     */
    private function fetchQueryBuilder(EntityRepository $repository): Sequence
    {
        $repository = $repository;
        $manager = $this->manager;
        $specification = $this->specification;
        $sort = $this->sort;
        $toDrop = $this->toDrop;
        $toTake = $this->toTake;
        $lazy = $this->lazy;

        $fetch = static function() use (
            $repository,
            $manager,
            $specification,
            $sort,
            $toDrop,
            $toTake,
            $lazy,
        ): \Generator {
            if (\is_null($specification)) {
                $queryBuilder = $repository->createQueryBuilder('entity');
            } else {
                $queryBuilder = (new ToQueryBuilder($repository, $manager))($specification);
            }

            /**
             * @var string $property
             * @var Sort $direction
             */
            foreach ($sort as $property => $direction) {
                /**
                 * @psalm-suppress ArgumentTypeCoercion Sort cases are asc and desc
                 * @psalm-suppress ImpureMethodCall
                 */
                $queryBuilder->addOrderBy($property, $direction->name);
            }

            if ($toDrop !== 0) {
                /** @psalm-suppress ImpureMethodCall */
                $queryBuilder->setFirstResult($toDrop);
            }

            if (\is_int($toTake)) {
                /** @psalm-suppress ImpureMethodCall */
                $queryBuilder->setMaxResults($toTake);
            }

            $query = $queryBuilder->getQuery();

            /**
             * @psalm-suppress ImpureMethodCall
             * @var iterable<T>
             */
            $entities = match ($lazy) {
                true => $query->toIterable(),
                false => $query->getResult(),
            };

            foreach ($entities as $entity) {
                yield $entity;
            }
        };

        /** @psalm-suppress ImpureFunctionCall */
        return match ($this->lazy) {
            true => Sequence::lazy($fetch),
            false => Sequence::defer($fetch()),
        };
    }

    /**
     * @param ObjectRepository<T> $repository
     *
     * @return Sequence<T>
     */
    private function directFetch(ObjectRepository $repository): Sequence
    {
        /** @psalm-suppress ImpureFunctionCall */
        return Sequence::defer((static function(
            ObjectRepository $repository,
            ?Specification $specification,
            array $sort,
            int $toDrop,
            ?int $toTake,
        ) {
            if (\is_null($specification)) {
                $criteria = [];
            } else {
                $criteria = (new ToArray)($specification);
            }

            /**
             * @psalm-suppress ArgumentTypeCoercion
             * @var iterable<T>
             */
            $entities = $repository->findBy(
                $criteria,
                \array_map(
                    static fn(Sort $sort) => $sort->name,
                    $sort,
                ),
                $toTake,
                $toDrop === 0 ? null : $toDrop,
            );

            foreach ($entities as $entity) {
                yield $entity;
            }
        })(
            $repository,
            $this->specification,
            $this->sort,
            $this->toDrop,
            $this->toTake,
        ));
    }
}
