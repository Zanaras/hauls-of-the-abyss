<?php

namespace App\Entity;

class MonsterType {
	private ?string $name = null;
	private ?string $size = null;
	private ?bool $neutral = null;
	private ?string $image = null;
	private ?string $imageDead = null;
	private array $attackTypes = [];
	private ?int $id = null;
	private ?float $agility = 1;
	private ?float $charisma = 1;
	private ?float $constitution = 1;
	private ?float $endurance = 1;
	private ?float $intelligence = 1;
	private ?float $spirit = 1;
	private ?float $perception = 1;

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

	public function isNeutral(): ?bool {
		return $this->neutral;
	}

	public function getAgility(): ?float {
		return $this->agility;
	}

	public function setAgility(float $agility): static {
		$this->agility = $agility;

		return $this;
	}

	public function getCharisma(): ?float {
		return $this->charisma;
	}

	public function setCharisma(float $charisma): static {
		$this->charisma = $charisma;

		return $this;
	}

	public function getConstitution(): ?float {
		return $this->constitution;
	}

	public function setConstitution(float $constitution): static {
		$this->constitution = $constitution;

		return $this;
	}

	public function getEndurance(): ?float {
		return $this->endurance;
	}

	public function setEndurance(float $endurance): static {
		$this->endurance = $endurance;

		return $this;
	}

	public function getIntelligence(): ?float {
		return $this->intelligence;
	}

	public function setIntelligence(float $intelligence): static {
		$this->intelligence = $intelligence;

		return $this;
	}

	public function getSpirit(): ?float {
		return $this->spirit;
	}

	public function setSpirit(float $spirit): static {
		$this->spirit = $spirit;

		return $this;
	}

	public function getPerception(): ?float {
		return $this->perception;
	}

	public function setPerception(float $perception): static {
		$this->perception = $perception;

		return $this;
	}
}
