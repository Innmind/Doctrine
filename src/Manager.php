<?php
declare(strict_types = 1);

namespace Innmind\Doctrine;

use Doctrine\ORM\EntityManagerInterface;

final class Manager
{
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * @template T of object
     *
     * @param class-string<T> $class
     *
     * @return Repository<T>
     */
    public function repository(string $class): Repository
    {
        return new Repository($this->entityManager, $class);
    }
}
