<?php
declare(strict_types = 1);

namespace Innmind\Doctrine;

use Innmind\Doctrine\{
    Specification\ToQueryBuilder,
    Exception\EntityNotFound,
    Exception\MutationOutsideOfContext,
};
use Innmind\Specification\Specification;
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
        \Closure $allowMutation = null
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
        /** @var ?T */
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

    public function matching(Specification $specification): Sequence
    {
        $repository = $this->doctrine->getRepository($this->entityClass);

        if ($repository instanceof EntityRepository) {
            return new Sequence\DeferQuery(
                (new ToQueryBuilder($repository))($specification),
            );
        }

        return new Sequence\DeferFindBy(
            $repository,
            $specification,
        );
    }

    /**
     * @return Sequence<T>
     */
    public function all(): Sequence
    {
        $repository = $this->doctrine->getRepository($this->entityClass);

        if ($repository instanceof EntityRepository) {
            /** @var Sequence<T> */
            return new Sequence\DeferQuery(
                $repository->createQueryBuilder('entity'),
            );
        }

        /** @var Sequence<T> */
        return Sequence\Concrete::of(
            ...$repository->findAll(),
        );
    }
}
