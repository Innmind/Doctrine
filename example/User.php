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
        int $registerIndex
    ) {
        $this->id = $id;
        $this->username = $username;
        $this->registerIndex = $registerIndex;
    }

    /**
     * For compatibility with the Element fixture, as it is used the the
     * properties to prove the behaviour of sequences
     */
    public function prop(): int
    {
        return $this->registerIndex;
    }
}
