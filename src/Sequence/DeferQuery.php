<?php
declare(strict_types = 1);

namespace Innmind\Doctrine\Sequence;

use Innmind\Doctrine\{
    Sequence,
    Exception\NoElementMatchingPredicateFound,
};
use Doctrine\ORM\QueryBuilder;

/**
 * @template T
 * @psalm-immutable
 */
final class DeferQuery implements Sequence
{
    private QueryBuilder $queryBuilder;
    /** @var array<string, string> */
    private array $sort;
    private int $toDrop;
    private ?int $toTake;
    /**
     * @psalm-allow-private-mutation
     * @var ?Sequence<T>
     */
    private ?Sequence $fetched = null;

    /**
     * @param array<string, string> $sort
     */
    public function __construct(
        QueryBuilder $queryBuilder,
        array $sort = [],
        int $toDrop = 0,
        ?int $toTake = null,
    ) {
        /** @psalm-suppress ImpurePropertyAssignment */
        $this->queryBuilder = $queryBuilder;
        $this->sort = $sort;
        $this->toDrop = $toDrop;
        $this->toTake = $toTake;
    }

    public function size(): int
    {
        return $this->unwrap()->size();
    }

    /**
     * @return Sequence<T>
     */
    public function drop(int $size): Sequence
    {
        if ($this->fetched) {
            return $this->unwrap()->drop($size);
        }

        return new self(
            $this->queryBuilder,
            $this->sort,
            $this->toDrop + $size,
            $this->toTake,
        );
    }

    /**
     * @return Sequence<T>
     */
    public function take(int $size): Sequence
    {
        if ($this->fetched) {
            return $this->unwrap()->take($size);
        }

        return new self(
            $this->queryBuilder,
            $this->sort,
            $this->toDrop,
            $size,
        );
    }

    /**
     * @param Sequence<T> $other
     */
    public function equals(Sequence $other): bool
    {
        return $this->unwrap()->equals($other);
    }

    /**
     * @param callable(T): bool $predicate
     *
     * @return Sequence<T>
     */
    public function filter(callable $predicate): Sequence
    {
        return $this->unwrap()->filter($predicate);
    }

    /**
     * @param callable(T): void $function
     */
    public function foreach(callable $function): void
    {
        /** @psalm-suppress UnusedMethodCall */
        $this->unwrap()->foreach($function);
    }

    /**
     * @param T $element
     */
    public function contains($element): bool
    {
        return $this->unwrap()->contains($element);
    }

    /**
     * @template V
     *
     * @param callable(T): V $function
     *
     * @return Sequence<V>
     */
    public function map(callable $function): Sequence
    {
        return $this->unwrap()->map($function);
    }

    /**
     * @param Sequence<T> $other
     *
     * @return Sequence<T>
     */
    public function append(Sequence $other): Sequence
    {
        return $this->unwrap()->append($other);
    }

    /**
     * @param T $element
     *
     * @return Sequence<T>
     */
    public function add($element): Sequence
    {
        return $this->unwrap()->add($element);
    }

    /**
     * This method only works for sequences of objects having a method with the
     * same name as the given property name
     *
     * @param string $property Name of the property of the objects to filter by
     * @param 'asc'|'desc' $direction
     *
     * @return Sequence<T>
     */
    public function sort(string $property, string $direction): Sequence
    {
        if ($this->fetched) {
            return $this->unwrap()->sort($property, $direction);
        }

        return new self(
            $this->queryBuilder,
            \array_merge(
                $this->sort,
                ["entity.$property" => $direction], // @see ToQueryBuilder::expression()
            ),
            $this->toDrop,
            $this->toTake,
        );
    }

    /**
     * @template C
     *
     * @param C $initial
     * @param callable(C, T): C $reducer
     *
     * @return C
     */
    public function reduce($initial, callable $reducer)
    {
        return $this->unwrap()->reduce($initial, $reducer);
    }

    /**
     * Return an empty sequence of the same type
     *
     * @return Sequence<T>
     */
    public function clear(): Sequence
    {
        /** @var Sequence<T> */
        return Concrete::of();
    }

    public function empty(): bool
    {
        return $this->unwrap()->empty();
    }

    /**
     * Find first value matching the predicate
     *
     * @param callable(T): bool $predicate
     *
     * @throws NoElementMatchingPredicateFound
     *
     * @return T
     */
    public function find(callable $predicate)
    {
        return $this->unwrap()->find($predicate);
    }

    /**
     * @return Sequence<T>
     */
    private function unwrap(): Sequence
    {
        /**
         * @psalm-suppress ImpureFunctionCall
         * @var Sequence<T>
         */
        return $this->fetched ??= Concrete::defer((static function(
            QueryBuilder $queryBuilder,
            array $sort,
            int $toDrop,
            ?int $toTake,
        ): \Generator {
            /**
             * @var string $property
             * @var 'asc'|'desc' $direction
             */
            foreach ($sort as $property => $direction) {
                /** @psalm-suppress ImpureMethodCall */
                $queryBuilder->addOrderBy($property, $direction);
            }

            if ($toDrop !== 0) {
                /** @psalm-suppress ImpureMethodCall */
                $queryBuilder->setFirstResult($toDrop);
            }

            if (\is_int($toTake)) {
                /** @psalm-suppress ImpureMethodCall */
                $queryBuilder->setMaxResults($toTake);
            }

            /**
             * @psalm-suppress ImpureMethodCall
             * @var list<T>
             */
            $entities = $queryBuilder->getQuery()->getResult();

            foreach ($entities as $entity) {
                yield $entity;
            }
        })(
            clone $this->queryBuilder,
            $this->sort,
            $this->toDrop,
            $this->toTake,
        ));
    }
}
