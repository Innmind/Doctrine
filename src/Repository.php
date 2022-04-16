<?php
declare(strict_types = 1);

namespace Innmind\Doctrine;

use Innmind\Doctrine\{
    Exception\EntityNotFound,
    Exception\MutationOutsideOfContext,
};
use Innmind\Specification\Specification;
use Innmind\Immutable\Maybe;
use Doctrine\ORM\{
    EntityManagerInterface,
    EntityRepository,
};

/**
 * @template T of object
 */
final class Repository
{
    private EntityManagerInterface $doctrine;
    /** @var class-string<T> */
    private string $entityClass;
    /** @var \Closure(): bool */
    private \Closure $allowMutation;

    /**
     * @param class-string<T> $entityClass
     * @param \Closure(): bool $allowMutation
     */
    public function __construct(
        EntityManagerInterface $doctrine,
        string $entityClass,
        \Closure $allowMutation = null,
    ) {
        $this->doctrine = $doctrine;
        $this->entityClass = $entityClass;
        $this->allowMutation = $allowMutation ?? static fn(): bool => true;
    }

    /**
     * @param Id<T> $id
     *
     * @throws EntityNotFound
     *
     * @return T
     */
    public function get(Id $id): object
    {
        $entity = $this->doctrine->find($this->entityClass, $id);

        if (\is_null($entity)) {
            throw new EntityNotFound($id->toString());
        }

        return $entity;
    }

    /**
     * @param Id<T> $id
     */
    public function contains(Id $id): bool
    {
        return !\is_null($this->doctrine->find($this->entityClass, $id));
    }

    /**
     * @param T $entity
     *
     * @throws MutationOutsideOfContext
     */
    public function add(object $entity): void
    {
        if (!($this->allowMutation)()) {
            throw new MutationOutsideOfContext;
        }

        $this->doctrine->persist($entity);
    }

    /**
     * @param T $entity
     *
     * @throws MutationOutsideOfContext
     */
    public function remove(object $entity): void
    {
        if (!($this->allowMutation)()) {
            throw new MutationOutsideOfContext;
        }

        $this->doctrine->remove($entity);
    }

    /**
     * @return Matching<T>
     */
    public function matching(Specification $specification): Matching
    {
        $repository = $this->doctrine->getRepository($this->entityClass);

        return Matching::of($this->doctrine, $repository, $specification);
    }

    /**
     * @return Matching<T>
     */
    public function all(): Matching
    {
        $repository = $this->doctrine->getRepository($this->entityClass);

        return Matching::all($this->doctrine, $repository);
    }
}
