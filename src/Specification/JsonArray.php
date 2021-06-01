<?php
declare(strict_types = 1);

namespace Innmind\Doctrine\Specification;

use Innmind\Specification\{
    Comparator,
    Composable,
    Specification,
    Sign,
};

/**
 * This specification is a helper to tell doctrine to use the json_contains
 * function instead of doing a LIKE like any other specification
 *
 * @psalm-immutable
 */
final class JsonArray implements Comparator
{
    use Composable;

    private string $property;
    /** @var mixed */
    private $value;

    /**
     * @param mixed $value
     */
    private function __construct(string $property, $value)
    {
        $this->property = $property;
        $this->value = $value;
    }

    /**
     * @param mixed $value
     */
    public static function contains(string $property, $value): self
    {
        return new self($property, $value);
    }

    public function property(): string
    {
        return $this->property;
    }

    public function sign(): Sign
    {
        return Sign::contains();
    }

    public function value()
    {
        return $this->value;
    }
}
