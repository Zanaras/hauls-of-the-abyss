<?php

namespace App\Entity;

use Doctrine\DBAL\Types\Types;

class MonsterType {
	private ?string $name = null;
	private ?string $size = null;
	private ?bool $neutral = null;
	private ?string $image = null;
	private ?string $imageDead = null;
	private array $attackTypes = [];
	private ?int $id = null;

	public function getName(): ?string {
   		return $this->name;
   	}

	public function setName(string $name): static {
   		$this->name = $name;
   
   		return $this;
   	}

	public function getSize(): ?string {
   		return $this->size;
   	}

	public function setSize(string $size): static {
   		$this->size = $size;
   
   		return $this;
   	}

	public function getNeutral(): ?bool {
   		return $this->neutral;
   	}

	public function setNeutral(bool $neutral): static {
   		$this->neutral = $neutral;
   
   		return $this;
   	}

	public function getImage(): ?string {
   		return $this->image;
   	}

	public function setImage(string $image): static {
   		$this->image = $image;
   
   		return $this;
   	}

	public function getImageDead(): ?string {
   		return $this->imageDead;
   	}

	public function setImageDead(string $imageDead): static {
   		$this->imageDead = $imageDead;
   
   		return $this;
   	}

	public function getId(): ?int {
   		return $this->id;
   	}

	public function getAttackTypes(): array {
		return $this->attackTypes;
	}

	public function setAttackTypes(array $attackTypes): static {
		$this->attackTypes = $attackTypes;

		return $this;
	}
}
