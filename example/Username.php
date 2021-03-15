<?php
declare(strict_types = 1);

namespace Example\Innmind\Doctrine;

use Innmind\Specification\{
    Comparator,
    Sign,
};

final class Username implements Comparator
{
    use Composable;

    private $value;
    private Sign $sign;

    private function __construct($value, Sign $sign)
    {
        $this->value = $value;
        $this->sign = $sign;
    }

    public static function of(string $username): self
    {
        return new self($username, Sign::equality());
    }

    public static function in(string ...$usernames): self
    {
        return new self($usernames, Sign::in());
    }

    public function property(): string
    {
        return 'username';
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
