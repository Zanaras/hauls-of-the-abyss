<?php

namespace App\Entity;

use Doctrine\DBAL\Types\Types;

class RoomType {
	private ?string $name = null;
	private array $modifiers = [];
	private ?int $id = null;

    private ?bool $spawn = null;

    private ?bool $teleporter = null;

    private ?bool $portal = null;

    private ?bool $allowUp = null;

    private ?bool $allowDown = null;

    private ?string $alternate = null;

	public function getName(): ?string {
                                                         		return $this->name;
                                                         	}

	public function setName(string $name): static {
                                                         		$this->name = $name;
                                                         
                                                         		return $this;
                                                         	}

	public function getModifiers(): array {
                                                         		return $this->modifiers;
                                                         	}

	public function setModifiers(array $modifiers): static {
                                                         		$this->modifiers = $modifiers;
                                                         
                                                         		return $this;
                                                         	}

	public function getId(): ?int {
                                                         		return $this->id;
                                                         	}

    public function isSpawn(): ?bool
    {
        return $this->spawn;
    }

    public function setSpawn(bool $spawn): static
    {
        $this->spawn = $spawn;

        return $this;
    }

    public function isTeleporter(): ?bool
    {
        return $this->teleporter;
    }

    public function setTeleporter(bool $teleporter): static
    {
        $this->teleporter = $teleporter;

        return $this;
    }

    public function isPortal(): ?bool
    {
        return $this->portal;
    }

    public function setPortal(bool $portal): static
    {
        $this->portal = $portal;

        return $this;
    }

    public function isAllowUp(): ?bool
    {
        return $this->allowUp;
    }

    public function setAllowUp(bool $allowUp): static
    {
        $this->allowUp = $allowUp;

        return $this;
    }

    public function isAllowDown(): ?bool
    {
        return $this->allowDown;
    }

    public function setAllowDown(bool $allowDown): static
    {
        $this->allowDown = $allowDown;

        return $this;
    }

    public function getAlternate(): ?string
    {
        return $this->alternate;
    }

    public function setAlternate(?string $alternate): static
    {
        $this->alternate = $alternate;

        return $this;
    }
}
