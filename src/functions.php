<?php
declare(strict_types = 1);

namespace Innmind\Doctrine;

/**
 * @template T
 *
 * @param Sequence<T> $sequence
 *
 * @return list<T>
 */
function unwrap(Sequence $sequence): array
{
    /** @psalm-suppress MissingClosureParamType */
    return $sequence->reduce(
        [],
        static function(array $elements, $element): array {
            /**
             * @var T $element
             * @var list<T> $elements
             */
            $elements[] = $element;

            return $elements;
        },
    );
}
