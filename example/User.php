<?php
declare(strict_types = 1);

namespace Example\Innmind\Doctrine;

use Innmind\Doctrine\Id;
use Doctrine\Common\Collections\{
    Collection,
    ArrayCollection,
};

final class User
{
    private Id $id;
    private string $username;
    private int $registerIndex;
    private ?self $parent = null;
    private Collection $children;

    public function __construct(
        Id $id,
        string $username,
        int $registerIndex = 0,
        array $children = []
    ) {
        $this->id = $id;
        $this->username = $username;
        $this->registerIndex = $registerIndex;
        $this->children = new ArrayCollection([]);

        foreach ($children as $child) {
            $child->parent = $this;
            $this->children->add($child);
        }
    }

    public function username(): string
    {
        return $this->username;
    }

    public function registerIndex(): int
    {
        return $this->registerIndex;
    }

    public function hasChild(string ...$usernames): bool
    {
        foreach ($this->children as $child) {
            if (\in_array($child->username(), $usernames, true)) {
                return true;
            }
        }

        return false;
    }
}
