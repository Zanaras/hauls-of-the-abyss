<?php

namespace App\Entity;

class MonsterType
{
    private ?string $name = null;

    private ?string $size = null;

    private ?bool $neutral = null;

    private ?bool $alive = null;

    private ?string $image = null;

    private ?string $imageDead = null;

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

    public function isNeutral(): ?bool
    {
        return $this->neutral;
    }

    public function setNeutral(bool $neutral): static
    {
        $this->neutral = $neutral;

        return $this;
    }

    public function isAlive(): ?bool
    {
        return $this->alive;
    }

    public function setAlive(bool $alive): static
    {
        $this->alive = $alive;

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

    public function getImageDead(): ?string
    {
        return $this->imageDead;
    }

    public function setImageDead(string $imageDead): static
    {
        $this->imageDead = $imageDead;

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
