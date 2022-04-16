<?php
declare(strict_types = 1);

namespace Example\Innmind\Doctrine;

use Innmind\Specification\{
    Comparator,
    Sign,
    Composable,
};

final class Username implements Comparator
{
    use Composable;

    private string|array $value;
    private Sign $sign;

    private function __construct(string|array $value, Sign $sign)
    {
        $this->value = $value;
        $this->sign = $sign;
    }

    public static function of(string $username): self
    {
        return new self($username, Sign::equality);
    }

    public static function in(string ...$usernames): self
    {
        return new self($usernames, Sign::in);
    }

    public static function startsWith(string $username): self
    {
        return new self($username, Sign::startsWith);
    }

    public function property(): string
    {
        return 'username';
    }

    public function sign(): Sign
    {
        return $this->sign;
    }

    public function value(): string|array
    {
        return $this->value;
    }
}
