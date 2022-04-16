<?php
declare(strict_types = 1);

namespace Innmind\Doctrine;

use Innmind\Doctrine\Exception\NestedMutationNotSupported;
use Innmind\Immutable\Either;
use Doctrine\ORM\EntityManagerInterface;

final class Manager
{
    private EntityManagerInterface $entityManager;
    private bool $mutating = false;

    private function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public static function of(EntityManagerInterface $entityManager): self
    {
        return new self($entityManager);
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
        return new Repository(
            $this->entityManager,
            $class,
            fn(): bool => $this->mutating,
        );
    }

    /**
     * @template L
     * @template R
     *
     * @param callable(self): Either<L, R> $mutate
     *
     * @throws NestedMutationNotSupported
     *
     * @return Either<L, R>
     */
    public function mutate(callable $mutate): Either
    {
        $this->enterMutation();

        try {
            return $mutate($this)
                ->map(function(mixed $right): mixed {
                    $this->entityManager->flush();
                    $this->leaveMutation();

                    return $right;
                })
                ->leftMap(function(mixed $left): mixed {
                    $this->entityManager->close();
                    $this->leaveMutation();

                    return $left;
                });
        } catch (\Throwable $e) {
            $this->entityManager->close();
            $this->leaveMutation();

            throw $e;
        }
    }

    /**
     * @template L
     * @template R
     *
     * @param callable(self, callable(): void): Either<L, R> $mutate The second argument allow to perform periodic flushes
     *
     * @throws NestedMutationNotSupported
     *
     * @return Either<L, R>
     */
    public function transaction(callable $mutate): Either
    {
        $this->enterMutation();

        try {
            $this->entityManager->beginTransaction();

            return $mutate(
                $this,
                function(): void {
                    // this is fine in a transaction as the flushed entities can
                    // be rolled back
                    $this->entityManager->flush();
                    $this->entityManager->clear();
                },
            )
                ->map(function(mixed $right): mixed {
                    $this->entityManager->flush();
                    $this->entityManager->commit();
                    $this->leaveMutation();

                    return $right;
                })
                ->leftMap(function(mixed $left): mixed {
                    $this->entityManager->rollback();
                    $this->leaveMutation();

                    return $left;
                });
        } catch (\Throwable $e) {
            $this->entityManager->rollback();
            $this->leaveMutation();

            throw $e;
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
