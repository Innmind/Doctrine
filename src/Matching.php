<?php
declare(strict_types = 1);

namespace Innmind\Doctrine;

use Innmind\Doctrine\Specification\ToQueryBuilder;
use Innmind\Specification\Specification;
use Doctrine\ORM\{
    EntityManagerInterface,
    EntityRepository,
};
use Doctrine\Persistence\ObjectRepository;

/**
 * @psalm-immutable
 * @template T of object
 */
final class Matching
{
    private EntityManagerInterface $manager;
    /** @var EntityRepository<T>|ObjectRepository<T> */
    private EntityRepository|ObjectRepository $repository;
    private ?Specification $specification;
    /** @var array<non-empty-string, Sort> */
    private array $sort;
    private int $toDrop;
    private ?int $toTake;

    /**
     * @param EntityRepository<T>|ObjectRepository<T> $repository
     * @param array<non-empty-string, Sort> $sort
     */
    private function __construct(
        EntityManagerInterface $manager,
        EntityRepository|ObjectRepository $repository,
        ?Specification $specification,
        array $sort,
        int $toDrop,
        ?int $toTake,
    ) {
        $this->manager = $manager;
        $this->repository = $repository;
        $this->specification = $specification;
        $this->sort = $sort;
        $this->toDrop = $toDrop;
        $this->toTake = $toTake;
    }

    /**
     * @psalm-pure
     * @template A of object
     *
     * @param EntityRepository<A>|ObjectRepository<A> $repository
     *
     * @return self<A>
     */
    public static function of(
        EntityManagerInterface $manager,
        EntityRepository|ObjectRepository $repository,
        Specification $specification,
    ): self {
        return new self($manager, $repository, $specification, [], 0, null);
    }

    /**
     * @psalm-pure
     * @template A of object
     *
     * @param EntityRepository<A>|ObjectRepository<A> $repository
     *
     * @return self<A>
     */
    public static function all(
        EntityManagerInterface $manager,
        EntityRepository|ObjectRepository $repository,
    ): self {
        return new self($manager, $repository, null, [], 0, null);
    }

    /**
     * @param positive-int $size
     */
    public function drop(int $size): self
    {
        return new self(
            $this->manager,
            $this->repository,
            $this->specification,
            $this->sort,
            $this->toDrop + $size,
            $this->toTake,
        );
    }

    /**
     * @param positive-int $size
     */
    public function take(int $size): self
    {
        return new self(
            $this->manager,
            $this->repository,
            $this->specification,
            $this->sort,
            $this->toDrop,
            $size,
        );
    }

    /**
     * @param non-empty-string $property
     */
    public function sort(string $property, Sort $direction): self
    {
        return new self(
            $this->manager,
            $this->repository,
            $this->specification,
            \array_merge(
                $this->sort,
                ["entity.$property" => $direction], // @see ToQueryBuilder::expression() for the prefix "entity."
            ),
            $this->toDrop,
            $this->toTake,
        );
    }

    /**
     * @return Sequence<T>
     */
    public function fetch(): Sequence
    {
        if (\is_null($this->specification)) {
            return $this->fetchAll();
        }

        if ($this->repository instanceof EntityRepository) {
            /** @var Sequence<T> */
            $sequence = new Sequence\DeferQuery(
                (new ToQueryBuilder($this->repository, $this->manager))($this->specification),
            );
        } else {
            /** @var Sequence<T> */
            $sequence = new Sequence\DeferFindBy(
                $this->repository,
                $this->specification,
            );
        }

        foreach ($this->sort as $property => $direction) {
            /** @psalm-suppress ArgumentTypeCoercion Sort cases are asc and desc */
            $sequence = $sequence->sort($property, $direction->name);
        }

        $sequence = $sequence->drop($this->toDrop);

        if (\is_int($this->toTake)) {
            $sequence = $sequence->take($this->toTake);
        }

        return $sequence;
    }

    /**
     * @return Sequence<T>
     */
    private function fetchAll(): Sequence
    {
        if ($this->repository instanceof EntityRepository) {
            /**
             * @psalm-suppress ImpureMethodCall
             * @var Sequence<T>
             */
            return new Sequence\DeferQuery(
                $this->repository->createQueryBuilder('entity'),
            );
        }

        /**
         * @psalm-suppress MixedArgument
         * @psalm-suppress ImpureMethodCall
         * @var Sequence<T>
         */
        return Sequence\Concrete::of(
            ...$this->repository->findAll(),
        );
    }
}
