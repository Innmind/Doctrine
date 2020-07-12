<?php
declare(strict_types = 1);

namespace Innmind\Doctrine\Sequence;

use Innmind\Doctrine\{
    Sequence,
    Exception\NoElementMatchingPredicateFound,
};
use Innmind\Immutable;

/**
 * @template T
 * @psalm-immutable
 */
final class Concrete implements Sequence
{
    /** @var Immutable\Sequence<T> */
    private Immutable\Sequence $sequence;

    /**
     * @param Immutable\Sequence<T> $sequence
     */
    private function __construct(Immutable\Sequence $sequence)
    {
        /** @psalm-suppress ImpurePropertyAssignment */
        $this->sequence = $sequence;
    }

    /**
     * @template V
     * @psalm-pure
     *
     * @param V $elements
     *
     * @return self<V>
     */
    public static function of(...$elements): self
    {
        /**
         * @psalm-suppress ImpureMethodCall
         * @var Immutable\Sequence<V>
         */
        $sequence = Immutable\Sequence::mixed(...$elements);

        return new self($sequence);
    }

    /**
     * @template V
     * @psalm-pure
     *
     * @param \Generator<V> $elements
     *
     * @return self<V>
     */
    public static function defer(\Generator $elements): self
    {
        /**
         * @psalm-suppress ImpureMethodCall
         * @var Immutable\Sequence<V>
         */
        $sequence = Immutable\Sequence::defer('mixed', $elements);

        return new self($sequence);
    }

    public function size(): int
    {
        /** @psalm-suppress ImpureMethodCall */
        return $this->sequence->size();
    }

    /**
     * @return self<T>
     */
    public function drop(int $size): self
    {
        /** @psalm-suppress ImpureMethodCall */
        return new self($this->sequence->drop($size));
    }

    /**
     * @return self<T>
     */
    public function take(int $size): self
    {
        /** @psalm-suppress ImpureMethodCall */
        return new self($this->sequence->take($size));
    }

    /**
     * @param Sequence<T> $other
     */
    public function equals(Sequence $other): bool
    {
        /** @psalm-suppress ImpureFunctionCall */
        return Immutable\unwrap($this->sequence) === $other->reduce(
            [],
            static function(array $elements, $element): array {
                /** @psalm-suppress MixedAssignment */
                $elements[] = $element;

                return $elements;
            },
        );
    }

    /**
     * @param callable(T): bool $predicate
     *
     * @return self<T>
     */
    public function filter(callable $predicate): self
    {
        /** @psalm-suppress ImpureMethodCall */
        return new self($this->sequence->filter($predicate));
    }

    /**
     * @param callable(T): void $function
     */
    public function foreach(callable $function): void
    {
        /** @psalm-suppress ImpureMethodCall */
        $this->sequence->foreach($function);
    }

    /**
     * @param T $element
     */
    public function contains($element): bool
    {
        /** @psalm-suppress ImpureMethodCall */
        return $this->sequence->contains($element);
    }

    /**
     * @template V
     *
     * @param callable(T): V $function
     *
     * @return self<V>
     */
    public function map(callable $function): self
    {
        /**
         * @psalm-suppress ImpureMethodCall
         * @psalm-suppress InvalidArgument It's ok since we use Immutable\Sequence<mixed>
         * @var self<V>
         */
        return new self($this->sequence->map($function));
    }

    /**
     * @param Sequence<T> $other
     *
     * @return self<T>
     */
    public function append(Sequence $other): self
    {
        /** @psalm-suppress ImpureMethodCall */
        return $other->reduce(
            $this,
            static fn(self $new, $element): self => $new->add($element),
        );
    }

    /**
     * @param T $element
     *
     * @return self<T>
     */
    public function add($element): self
    {
        /** @psalm-suppress ImpureMethodCall */
        return new self(($this->sequence)($element));
    }

    /**
     * @param string $property Name of the property of the objects to filter by
     * @param 'asc'|'desc' $direction
     *
     * @return self<T>
     */
    public function sort(string $property, string $direction): self
    {
        /**
         * @psalm-suppress MissingClosureParamType
         * @psalm-suppress MixedMethodCall
         * @var callable(T, T): int
         */
        $compare = static fn($a, $b): int => (int) ($a->$property() < $b->$property());

        if ($direction === 'desc') {
            /**
             * @psalm-suppress MissingClosureParamType
             * @psalm-suppress MixedArgument
             * @var callable(T, T): int
             */
            $compare = static fn($a, $b): int => $compare($b, $a);
        }

        /** @psalm-suppress ImpureMethodCall */
        return new self($this->sequence->sort($compare));
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
        /** @psalm-suppress ImpureMethodCall */
        return $this->sequence->reduce($initial, $reducer);
    }

    /**
     * Return an empty sequence of the same type
     *
     * @return self<T>
     */
    public function clear(): self
    {
        /** @psalm-suppress ImpureMethodCall */
        return new self($this->sequence->clear());
    }

    public function empty(): bool
    {
        /** @psalm-suppress ImpureMethodCall */
        return $this->sequence->empty();
    }

    /**
     * Find first value matching the predicate
     *
     * @throws NoElementMatchingPredicateFound
     *
     * @param callable(T): bool $predicate
     *
     * @return T
     */
    public function find(callable $predicate)
    {
        try {
            /** @psalm-suppress ImpureMethodCall */
            return $this->sequence->find($predicate);
        } catch (Immutable\Exception\NoElementMatchingPredicateFound $e) {
            throw new NoElementMatchingPredicateFound;
        }
    }
}
