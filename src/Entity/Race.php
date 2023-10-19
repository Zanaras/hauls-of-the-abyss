<?php

namespace App\Entity;

class Race
{
    private ?string $name = null;

    private ?int $id = null;

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getId(): ?int
    {
        return $this->id;
    }
}
