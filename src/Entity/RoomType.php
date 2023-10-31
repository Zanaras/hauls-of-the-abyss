<?php

namespace App\Entity;

class RoomType
{
    private ?string $name = null;

    private array $modifiers = [];

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

    public function getModifiers(): array
    {
        return $this->modifiers;
    }

    public function setModifiers(array $modifiers): static
    {
        $this->modifiers = $modifiers;

        return $this;
    }

    public function getId(): ?int
    {
        return $this->id;
    }
}
