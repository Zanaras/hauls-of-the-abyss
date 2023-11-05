<?php

namespace App\Entity;

class Monster {
	private ?string $name = null;
	private int $playerKills = 0;
	private ?string $size = null;
	private ?float $constitution = null;
	private ?float $spirit = null;
	private ?int $id = null;
	private ?float $health = null;
	private ?MonsterType $type = null;
	private ?Floor $floor = null;
	private ?Room $room = null;
	private ?Dungeon $dungeon = null;
	private ?string $image = null;
	private ?string $imageDead = null;
	private array $attackTypes = [];
	private ?bool $alive = true;

	public function getName(): ?string {
		return $this->name;
	}

	public function setName(?string $name): static {
		$this->name = $name;

		return $this;
	}

	public function getPlayerKills(): ?int {
		return $this->playerKills;
	}

	public function setPlayerKills(int $playerKills): static {
		$this->playerKills = $playerKills;

		return $this;
	}

	public function getSize(): ?string {
		return $this->size;
	}

	public function setSize(string $size): static {
		$this->size = $size;

		return $this;
	}

	public function getConstitution(): ?float {
		return $this->constitution;
	}

	public function setConstitution(float $constitution): static {
		$this->constitution = $constitution;

		return $this;
	}

	public function getSpirit(): ?float {
		return $this->spirit;
	}

	public function setSpirit(float $spirit): static {
		$this->spirit = $spirit;

		return $this;
	}

	public function getId(): ?int {
		return $this->id;
	}

	public function getType(): ?MonsterType {
		return $this->type;
	}

	public function setType(?MonsterType $type): static {
		$this->type = $type;

		return $this;
	}

	public function getHealth(): ?float {
		return $this->health;
	}

	public function setHealth(float $health): static {
		$this->health = $health;

		return $this;
	}

	public function getDungeon(): ?Dungeon {
		return $this->dungeon;
	}

	public function setDungeon(?Dungeon $dungeon): static {
		$this->dungeon = $dungeon;

		return $this;
	}

	public function getFloor(): ?Floor {
		return $this->floor;
	}

	public function setFloor(?Floor $floor): static {
		$this->floor = $floor;

		return $this;
	}

	public function getRoom(): ?Room {
		return $this->room;
	}

	public function setRoom(?Room $room): static {
		$this->room = $room;

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

	public function setImageDead(string $image): static {
		$this->imageDead = $image;

		return $this;
	}

	public function getAlive(): bool {
		return $this->alive;
	}

	public function setAlive(bool $alive): static {
		$this->alive = $alive;

		return $this;
	}
}
