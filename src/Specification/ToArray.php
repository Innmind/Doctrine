<?php
declare(strict_types = 1);

namespace Innmind\Doctrine\Specification;

use Innmind\Doctrine\Exception\{
    ComparisonNotSupported,
    OnlyAndCompositeSupported,
};
use Innmind\Specification\{
    Specification,
    Comparator,
    Sign,
    Operator,
    Composite,
};

/**
 * @psalm-immutable
 */
final class ToArray
{
    /**
     * @return array<string, mixed>
     */
    public function __invoke(Specification $specification): array
    {
        if ($specification instanceof Comparator) {
            if (!$specification->sign()->equals(Sign::equality())) {
                throw new ComparisonNotSupported((string) $specification->sign());
            }

            return [$specification->property() => $specification->value()];
        }

        if (!$specification instanceof Composite) {
            throw new OnlyAndCompositeSupported;
        }

        if (!$specification->operator()->equals(Operator::and())) {
            throw new OnlyAndCompositeSupported;
        }

        return \array_merge(
            $this($specification->left()),
            $this($specification->right()),
        );
    }
}
