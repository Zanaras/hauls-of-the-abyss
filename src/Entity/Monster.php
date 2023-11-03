<?php

namespace App\Entity;

class Monster
{
    private ?string $name = null;

    private ?int $playerKills = null;

    private ?string $size = null;

    private ?float $constitution = null;

    private ?float $spirit = null;

    private ?int $id = null;

    private ?MonsterType $type = null;

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getPlayerKills(): ?int
    {
        return $this->playerKills;
    }

    public function setPlayerKills(int $playerKills): static
    {
        $this->playerKills = $playerKills;

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

    public function getConstitution(): ?float
    {
        return $this->constitution;
    }

    public function setConstitution(float $constitution): static
    {
        $this->constitution = $constitution;

        return $this;
    }

    public function getSpirit(): ?float
    {
        return $this->spirit;
    }

    public function setSpirit(float $spirit): static
    {
        $this->spirit = $spirit;

        return $this;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getType(): ?MonsterType
    {
        return $this->type;
    }

    public function setType(?MonsterType $type): static
    {
        $this->type = $type;

        return $this;
    }
}
