<?php
declare(strict_types = 1);

namespace Example\Innmind\Doctrine;

final class User
{
    private string $id;
    private string $username;
    private int $registerIndex;

    public function __construct(
        string $id,
        string $username,
        int $registerIndex = 0
    ) {
        $this->id = $id;
        $this->username = $username;
        $this->registerIndex = $registerIndex;
    }

    public function registerIndex(): int
    {
        return $this->registerIndex;
    }
}
