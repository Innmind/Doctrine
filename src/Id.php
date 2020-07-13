<?php
declare(strict_types = 1);

namespace Innmind\Doctrine;

use Ramsey\Uuid\Uuid;

/**
 * @template T of object
 * @psalm-immutable
 */
class Id
{
    private string $id;

    final public function __construct(string $id)
    {
        if (!Uuid::isValid($id)) {
            throw new Exception\DomainException("'$id' is not a valid uuid");
        }

        $this->id = $id;
    }

    public static function new(): self
    {
        return new self(Uuid::uuid4()->toString());
    }

    /**
     * @param self<T> $other
     */
    public function equals(self $other): bool
    {
        return $this->toString() === $other->toString();
    }

    final public function toString(): string
    {
        return $this->id;
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
}
