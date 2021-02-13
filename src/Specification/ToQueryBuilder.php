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
use Doctrine\ORM\{
    EntityRepository,
    QueryBuilder,
};
use Doctrine\Common\Collections\{
    Criteria,
    Expr\Expression,
};

/**
 * @psalm-immutable
 */
final class ToQueryBuilder
{
    private EntityRepository $repository;
    /** @psalm-allow-private-mutation */
    private int $count = 0;

    public function __construct(EntityRepository $repository)
    {
        /** @psalm-suppress ImpurePropertyAssignment */
        $this->repository = $repository;
    }

    public function __invoke(Specification $specification): QueryBuilder
    {
        /** @psalm-suppress InaccessibleProperty */
        $this->count = 0;

        /** @psalm-suppress ImpureMethodCall */
        $qb = $this->repository->createQueryBuilder('entity');
        /** @var mixed */
        $expression = $this->visit($specification, $qb);

        /** @psalm-suppress ImpureMethodCall */
        return $qb->where($expression);
    }

    /**
     * @return mixed
     */
    private function visit(Specification $specification, QueryBuilder $qb)
    {
        if ($specification instanceof Comparator) {
            /** @psalm-suppress ImpureMethodCall */
            return $this->expression($specification, $qb);
        }

        if ($specification instanceof Not) {
            /** @psalm-suppress ImpureMethodCall */
            return $qb->expr()->not(
                $this->visit($specification->specification(), $qb),
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
                ->add($this->visit($specification->left(), $qb))
                ->add($this->visit($specification->right(), $qb));
        }

        /** @psalm-suppress ImpureMethodCall */
        return $qb
            ->expr()
            ->orX()
            ->add($this->visit($specification->left(), $qb))
            ->add($this->visit($specification->right(), $qb));
    }

    /**
     * @psalm-suppress ImpureMethodCall
     *
     * @return mixed
     */
    private function expression(Comparator $specification, QueryBuilder $qb)
    {
        $property = "entity.{$specification->property()}";

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

                return $qb->expr()->in($property, [$placeholder]);
        }

        throw new ComparisonNotSupported((string) $specification->sign());
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
}
