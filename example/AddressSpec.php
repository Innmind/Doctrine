<?php
declare(strict_types = 1);

namespace Example\Innmind\Doctrine;

use Innmind\Doctrine\Specification\Child;
use Innmind\Specification\{
    Comparator,
    Sign,
};

final class AddressSpec implements Comparator
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

    public static function primary(string $address): Child
    {
        return new Child(
            new self('addresses.main', Sign::equality(), true),
            new self('addresses.address', Sign::contains(), $address),
        );
    }

    public static function secondary(string $address): Child
    {
        return new Child(
            new self('addresses.main', Sign::equality(), false),
            new self('addresses.address', Sign::contains(), $address),
        );
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
