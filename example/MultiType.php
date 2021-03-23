<?php
declare(strict_types = 1);

namespace Example\Innmind\Doctrine;

use Innmind\Specification\{
    Comparator,
    Sign,
};

final class MultiType implements Comparator
{
    use Composable;

    private string $property;
    private Sign $sign;
    private $value;

    private function __construct(string $property, Sign $sign, $value)
    {
        $this->property = $property;
        $this->sign = $sign;
        $this->value = $value;
    }

    public static function of($value): self
    {
        return new self('multiType', Sign::equality(), $value);
    }

    public static function contains($value): self
    {
        return new self('multiType', Sign::contains(), $value);
    }

    public static function child($value): self
    {
        return new self('children.multiType', Sign::equality(), $value);
    }

    public function property(): string
    {
        return $this->property;
    }

    public function sign(): Sign
    {
        return $this->sign;
    }

    public function value()
    {
        return $this->value;
    }
}
