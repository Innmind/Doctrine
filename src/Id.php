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
}
