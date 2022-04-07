<?php
declare(strict_types = 1);

namespace Innmind\Doctrine\Specification;

use Innmind\Doctrine\Exception\{
    ComparisonNotSupported,
    SpecificationNotSuported,
};
use Innmind\Specification\{
    Specification,
    Comparator,
    Sign,
    Operator,
    Composite,
    Not,
};
use Innmind\Immutable\Map;
use Doctrine\ORM\{
    EntityRepository,
    EntityManagerInterface,
    QueryBuilder,
    Query\Expr\Join,
};
use Doctrine\Common\Collections\{
    Criteria,
    Expr\Expression,
};
use Doctrine\DBAL\Types\{
    Type,
    JsonType,
};

/**
 * @psalm-immutable
 */
final class ToQueryBuilder
{
    private EntityRepository $repository;
    private EntityManagerInterface $manager;
    /** @psalm-allow-private-mutation */
    private int $count = 0;
    /**
     * @psalm-allow-private-mutation
     * @var list<string>
     */
    private array $relations = [];
    /**
     * @psalm-allow-private-mutation
     * @var Map<array{0: string, 1: string, 2: mixed}, string>
     */
    private Map $children;

    public function __construct(
        EntityRepository $repository,
        EntityManagerInterface $manager
    ) {
        /** @psalm-suppress ImpurePropertyAssignment */
        $this->repository = $repository;
        /** @psalm-suppress ImpurePropertyAssignment */
        $this->manager = $manager;
        /** @var Map<array{0: string, 1: string, 2: mixed}, string> */
        $this->children = Map::of('array', 'mixed');
    }

    public function __invoke(Specification $specification): QueryBuilder
    {
        /** @psalm-suppress InaccessibleProperty */
        $this->count = 0;
        /** @psalm-suppress InaccessibleProperty */
        $this->relations = [];
        /**
         * @psalm-suppress ImpureMethodCall
         * @var Map<array{0: string, 1: string, 2: mixed}, string>
         */
        $this->children = Map::of('array', 'string');

        /** @psalm-suppress ImpureMethodCall */
        $qb = $this->repository->createQueryBuilder('entity');
        /** @var mixed */
        $expression = $this->visit(
            $specification,
            $qb,
            function(string $property): string {
                [$relation] = \explode('.', $property);
                $this->relations[] = $relation;

                return $relation;
            },
        );
        $relations = \array_unique($this->relations);

        /** @var string $relation */
        foreach ($relations as $relation) {
            /** @psalm-suppress ImpureMethodCall */
            $qb->leftJoin("entity.$relation", $relation);
        }

        /** @psalm-suppress ImpureMethodCall */
        $this->children->foreach(function(array $key, string $alias) use ($qb): void {
            /** @var mixed $value */
            [$relation, $field, $value] = $key;

            $placeholder = $this->placeholder($value, $qb);
            $qb->leftJoin(
                "entity.{$relation}",
                $alias,
                Join::WITH,
                "$alias.$field = $placeholder",
            );
        });

        /** @psalm-suppress ImpureMethodCall */
        return $qb->where($expression);
    }

    /**
     * @param callable(string): string $alias
     *
     * @return mixed
     */
    private function visit(
        Specification $specification,
        QueryBuilder $qb,
        callable $alias
    ) {
        if ($specification instanceof Child) {
            return $this->child($specification, $qb);
        }

        if ($specification instanceof Comparator) {
            /** @psalm-suppress ImpureMethodCall */
            return $this->expression($specification, $qb, $alias);
        }

        if ($specification instanceof Not) {
            /** @psalm-suppress ImpureMethodCall */
            return $qb->expr()->not(
                $this->visit($specification->specification(), $qb, $alias),
            );
        }

        if (!$specification instanceof Composite) {
            throw new SpecificationNotSuported(\get_class($specification));
        }

        if ($specification->operator()->equals(Operator::and())) {
            /** @psalm-suppress ImpureMethodCall */
            return $qb
                ->expr()
                ->andX()
                ->add($this->visit($specification->left(), $qb, $alias))
                ->add($this->visit($specification->right(), $qb, $alias));
        }

        /** @psalm-suppress ImpureMethodCall */
        return $qb
            ->expr()
            ->orX()
            ->add($this->visit($specification->left(), $qb, $alias))
            ->add($this->visit($specification->right(), $qb, $alias));
    }

    /**
     * @psalm-suppress ImpureMethodCall
     *
     * @param callable(string): string $alias
     *
     * @return mixed
     */
    private function expression(
        Comparator $specification,
        QueryBuilder $qb,
        callable $alias
    ) {
        $property = "entity.{$specification->property()}";
        $relation = null;
        $field = $specification->property();

        if (\strpos($specification->property(), '.') !== false) {
            [$relation, $field] = \explode('.', $specification->property());
            /** @psalm-suppress ImpureFunctionCall */
            $relationAlias = $alias($specification->property());
            $property = "$relationAlias.$field";
        }

        if ($specification instanceof JsonArray) {
            return $this->matchJson($qb, $property, $specification);
        }

        $property = $this->decodeJson($property, $field, $relation);

        switch ($specification->sign()) {
            case Sign::equality():
                $placeholder = $this->placeholder($specification->value(), $qb);

                return $qb->expr()->eq($property, $placeholder);

            case Sign::inequality():
                $placeholder = $this->placeholder($specification->value(), $qb);

                return $qb->expr()->neq($property, $placeholder);

            case Sign::lessThan():
                $placeholder = $this->placeholder($specification->value(), $qb);

                return $qb->expr()->lt($property, $placeholder);

            case Sign::moreThan():
                $placeholder = $this->placeholder($specification->value(), $qb);

                return $qb->expr()->gt($property, $placeholder);

            case Sign::lessThanOrEqual():
                $placeholder = $this->placeholder($specification->value(), $qb);

                return $qb->expr()->lte($property, $placeholder);

            case Sign::moreThanOrEqual():
                $placeholder = $this->placeholder($specification->value(), $qb);

                return $qb->expr()->gte($property, $placeholder);

            case Sign::isNull():
                return $qb->expr()->isNull($property);

            case Sign::isNotNull():
                return $qb->expr()->isNotNull($property);

            case Sign::startsWith():
                /** @psalm-suppress MixedOperand */
                $placeholder = $this->placeholder(
                    $specification->value().'%',
                    $qb,
                );

                return $qb->expr()->like($property, $placeholder);

            case Sign::endsWith():
                /** @psalm-suppress MixedOperand */
                $placeholder = $this->placeholder(
                    '%'.$specification->value(),
                    $qb,
                );

                return $qb->expr()->like($property, $placeholder);

            case Sign::contains():
                /** @psalm-suppress MixedOperand */
                $placeholder = $this->placeholder(
                    '%'.$specification->value().'%',
                    $qb,
                );

                return $qb->expr()->like($property, $placeholder);

            case Sign::in():
                $placeholder = $this->placeholder(
                    $specification->value(),
                    $qb,
                );

                return $qb->expr()->in($property, $placeholder);
        }

        throw new ComparisonNotSupported((string) $specification->sign());
    }

    /**
     * @psalm-suppress ImpureMethodCall
     *
     * @return mixed
     */
    private function child(Child $child, QueryBuilder $qb)
    {
        /** @psalm-suppress ArgumentTypeCoercion */
        $alias = $this->planJoin($child->left());

        return $this->visit($child->right(), $qb, static fn() => $alias);
    }

    /**
     * @psalm-suppress ImpureMethodCall
     *
     * @param mixed $value
     */
    private function placeholder($value, QueryBuilder $qb): string
    {
        /** @psalm-suppress InaccessibleProperty */
        ++$this->count;
        $qb->setParameter($this->count, $value);

        return "?{$this->count}";
    }

    /**
     * @psalm-suppress ImpureMethodCall
     */
    private function decodeJson(
        string $property,
        string $field,
        ?string $relation
    ): string {
        if ($this->type($field, $relation) instanceof JsonType) {
            return "json_value($property, '$')";
        }

        return $property;
    }

    /**
     * @psalm-suppress ImpureMethodCall
     * @psalm-suppress UndefinedDocblockClass
     */
    private function type(string $field, ?string $relation): Type
    {
        if (\is_string($relation)) {
            /** @var array{targetEntity:string} */
            $association = $this
                ->manager
                ->getClassMetadata($this->repository->getClassName())
                ->getAssociationMapping($relation);

            $type = $this
                ->manager
                ->getClassMetadata($association['targetEntity'])
                ->getFieldMapping($field)['type'];

            return Type::getType($type);
        }

        $type = $this
            ->manager
            ->getClassMetadata($this->repository->getClassName())
            ->getFieldMapping($field)['type'];

        return Type::getType($type);
    }

    /**
     * @psalm-suppress ImpureMethodCall
     */
    private function planJoin(Comparator $specification): string
    {
        [$relation, $field] = \explode('.', $specification->property());
        $key = [$relation, $field, $specification->value()];

        if ($this->children->contains($key)) {
            return $this->children->get($key);
        }

        $alias = "$relation{$this->children->size()}";
        $this->children = ($this->children)($key, $alias);

        return $alias;
    }

    /**
     * @psalm-suppress ImpureMethodCall
     *
     * @return mixed
     */
    private function matchJson(
        QueryBuilder $qb,
        string $property,
        JsonArray $specification
    ) {
        // the sql json_contains function expects the value to be searched to be
        // json encoded otherwise the value will never be found
        $placeholder = $this->placeholder(
            \json_encode($specification->value()),
            $qb,
        );

        // we don't check the sign of the specification as for now it is always
        // a contains sign allowing to apply the equality below, the 1 below
        // means true
        return $qb->expr()->eq(
            "json_contains($property, $placeholder, '$')",
            1,
        );
    }
}
