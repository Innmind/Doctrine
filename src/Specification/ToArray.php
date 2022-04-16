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
            return match ($specification->sign()) {
                Sign::equality => [$specification->property() => $specification->value()],
                default => throw new ComparisonNotSupported($specification->sign()->name),
            };
        }

        if (!$specification instanceof Composite) {
            throw new OnlyAndCompositeSupported;
        }

        return match ($specification->operator()) {
            Operator::and => \array_merge(
                $this($specification->left()),
                $this($specification->right()),
            ),
            default => throw new OnlyAndCompositeSupported,
        };
    }
}
