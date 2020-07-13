<?php
declare(strict_types = 1);

namespace Innmind\Doctrine;

use Innmind\Doctrine\Exception\NestedMutationNotSupported;
use Doctrine\ORM\EntityManagerInterface;

final class Manager
{
    private EntityManagerInterface $entityManager;
    private bool $mutating = false;

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

    /**
     * @param callable(self): void $mutate
     *
     * @throws NestedMutationNotSupported
     */
    public function mutate(callable $mutate): void
    {
        $this->enterMutation();
        try {
            $mutate($this);
            $this->entityManager->flush();
        } catch (\Throwable $e) {
            $this->entityManager->close();

            throw $e;
        } finally {
            $this->leaveMutation();
        }
    }

    private function enterMutation(): void
    {
        if ($this->mutating) {
            throw new NestedMutationNotSupported;
        }

        $this->mutating = true;
    }

    private function leaveMutation(): void
    {
        $this->mutating = false;
    }
}
