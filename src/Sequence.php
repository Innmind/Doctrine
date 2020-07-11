<?php
declare(strict_types = 1);

namespace Innmind\Doctrine;

use Innmind\Doctrine\Exception\NoElementMatchingPredicateFound;

/**
 * @template T
 * @psalm-immutable
 */
interface Sequence
{
    public function size(): int;

    /**
     * @return self<T>
     */
    public function drop(int $size): self;

    /**
     * @return self<T>
     */
    public function take(int $size): self;

    /**
     * @param self<T> $other
     */
    public function equals(self $other): bool;

    /**
     * @param callable(T): bool $predicate
     *
     * @return self<T>
     */
    public function filter(callable $predicate): self;

    /**
     * @param callable(T): void $function
     */
    public function foreach(callable $function): void;

    /**
     * @param T $element
     */
    public function contains($element): bool;

    /**
     * @template V
     *
     * @param callable(T): V $function
     *
     * @return self<V>
     */
    public function map(callable $function): self;

    /**
     * @param self<T> $other
     *
     * @return self<T>
     */
    public function append(self $other): self;

    /**
     * @param T $element
     *
     * @return self<T>
     */
    public function add($element): self;

    /**
     * This method only works for sequences of objects having a method with the
     * same name as the given property name
     *
     * @param string $property Name of the property of the objects to filter by
     * @param 'asc'|'desc' $direction
     *
     * @return self<T>
     */
    public function sort(string $property, string $direction): self;

    /**
     * @template C
     *
     * @param C $initial
     * @param callable(C, T): C $reducer
     *
     * @return C
     */
    public function reduce($initial, callable $reducer);

    /**
     * Return an empty sequence of the same type
     *
     * @return self<T>
     */
    public function clear(): self;
    public function empty(): bool;

    /**
     * Find first value matching the predicate
     *
     * @throws NoElementMatchingPredicateFound
     *
     * @param callable(T): bool $predicate
     *
     * @return T
     */
    public function find(callable $predicate);
}
