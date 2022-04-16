<?php
declare(strict_types = 1);

namespace Innmind\Doctrine;

use Innmind\Doctrine\Exception\NestedMutationNotSupported;
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
     * @template R
     *
     * @param callable(self): R $mutate
     *
     * @throws NestedMutationNotSupported
     *
     * @return R
     */
    public function mutate(callable $mutate)
    {
        $this->enterMutation();

        try {
            $return = $mutate($this);
            $this->entityManager->flush();

            return $return;
        } catch (\Throwable $e) {
            $this->entityManager->close();

            throw $e;
        } finally {
            $this->leaveMutation();
        }
    }

    /**
     * @template R
     *
     * @param callable(self, callable(): void): R $mutate The second argument allow to perform periodic flushes
     *
     * @throws NestedMutationNotSupported
     *
     * @return R
     */
    public function transaction(callable $mutate)
    {
        $this->enterMutation();

        try {
            $this->entityManager->beginTransaction();
            $return = $mutate(
                $this,
                function(): void {
                    // this is fine in a transaction as the flushed entities can
                    // be rolled back
                    $this->entityManager->flush();
                    $this->entityManager->clear();
                },
            );
            $this->entityManager->flush();
            $this->entityManager->commit();

            return $return;
        } catch (\Throwable $e) {
            $this->entityManager->rollback();

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
