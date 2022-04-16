<?php
declare(strict_types = 1);

namespace Example\Innmind\Doctrine;

use Innmind\Specification\{
    Comparator,
    Sign,
    Composable,
};

final class Child implements Comparator
{
    use Composable;

    private string $username;

    public function __construct(string $username)
    {
        $this->username = $username;
    }

    public static function of(string $username): self
    {
        return new self($username);
    }

    public function property(): string
    {
        return 'children.username';
    }

    public function sign(): Sign
    {
        return Sign::equality;
    }

    public function value(): string
    {
        return $this->username;
    }
}
