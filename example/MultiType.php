<?php
declare(strict_types = 1);

namespace Example\Innmind\Doctrine;

use Innmind\Specification\{
    Comparator,
    Sign,
    Composable,
};

final class MultiType implements Comparator
{
    use Composable;

    private string $property;
    private Sign $sign;
    private mixed $value;

    private function __construct(string $property, Sign $sign, mixed $value)
    {
        $this->property = $property;
        $this->sign = $sign;
        $this->value = $value;
    }

    public static function of(mixed $value): self
    {
        return new self('multiType', Sign::equality, $value);
    }

    public static function contains(mixed $value): self
    {
        return new self('multiType', Sign::contains, $value);
    }

    public static function child(mixed $value): self
    {
        return new self('children.multiType', Sign::equality, $value);
    }

    public function property(): string
    {
        return $this->property;
    }

    public function sign(): Sign
    {
        return $this->sign;
    }

    public function value(): mixed
    {
        return $this->value;
    }
}
