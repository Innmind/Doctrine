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
    public $multiType;
    private ?self $parent = null;
    public Collection $children;
    public Collection $addresses;

    public function __construct(
        Id $id,
        string $username,
        int $registerIndex = 0,
        array $children = [],
        array $addresses = []
    ) {
        $this->id = $id;
        $this->username = $username;
        $this->registerIndex = $registerIndex;
        $this->children = new ArrayCollection([]);
        $this->addresses = new ArrayCollection($addresses);

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

    public function addresses(): array
    {
        return $this->addresses->getValues();
    }
}
