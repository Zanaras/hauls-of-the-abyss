<?php

namespace App\Entity;

use Doctrine\DBAL\Types\Types;

class MonsterType
{
    private ?string $name = null;

    private ?string $size = null;

    private ?string $image = null;

    private array $attackType = [];

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

    public function getSize(): ?string
    {
        return $this->size;
    }

    public function setSize(string $size): static
    {
        $this->size = $size;

        return $this;
    }

    public function getImage(): ?string
    {
        return $this->image;
    }

    public function setImage(string $image): static
    {
        $this->image = $image;

        return $this;
    }

    public function getAttackType(): array
    {
        return $this->attackType;
    }

    public function setAttackType(array $attackType): static
    {
        $this->attackType = $attackType;

        return $this;
    }

    public function getId(): ?int
    {
        return $this->id;
    }
}
