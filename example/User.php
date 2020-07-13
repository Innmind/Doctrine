<?php
declare(strict_types = 1);

namespace Example\Innmind\Doctrine;

use Innmind\Doctrine\Id;

final class User
{
    private Id $id;
    private string $username;
    private int $registerIndex;

    public function __construct(
        Id $id,
        string $username,
        int $registerIndex = 0
    ) {
        $this->id = $id;
        $this->username = $username;
        $this->registerIndex = $registerIndex;
    }

    public function username(): string
    {
        return $this->username;
    }

    public function registerIndex(): int
    {
        return $this->registerIndex;
    }
}
