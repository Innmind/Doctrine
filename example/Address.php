<?php
declare(strict_types = 1);

namespace Example\Innmind\Doctrine;

use Innmind\Doctrine\Id;

final class Address
{
    private Id $id;
    private bool $main;
    private string $address;

    public function __construct(bool $main, string $address)
    {
        $this->id = Id::new();
        $this->main = $main;
        $this->address = $address;
    }
}
