<?php
declare(strict_types = 1);

namespace Innmind\Doctrine;

use Ramsey\Uuid\Uuid;

/**
 * @template T of object
 * @psalm-immutable
 */
final class Id
{
    private string $id;

    public function __construct(string $id)
    {
        if (!Uuid::isValid($id)) {
            throw new Exception\DomainException("'$id' is not a valid uuid");
        }

        $this->id = $id;
    }

    /**
     * This method is required by Doctrine as all ids must be castable to string
     *
     * @internal Never use this method in your code
     */
    final public function __toString(): string
    {
        return $this->id;
    }

    /**
     * @template A
     *
     * @param class-string<A> $class
     *
     * @return self<A>
     */
    public static function new(string $class): self
    {
        /** @var self<A> */
        return new self(Uuid::uuid4()->toString());
    }

    /**
     * @param self<T> $other
     */
    public function equals(self $other): bool
    {
        return $this->toString() === $other->toString();
    }

    public function toString(): string
    {
        return $this->id;
    }
}
