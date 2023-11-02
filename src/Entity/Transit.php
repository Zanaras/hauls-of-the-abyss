<?php

namespace App\Entity;

class Transit {
	private ?string $transits = null;
	private ?int $id = null;
	private ?Dungeon $dungeon = null;
	private ?TransitType $type = null;
	private ?Room $fromRoom = null;
	private ?Room $toRoom = null;

	public function getTransits(): ?string {
		return $this->transits;
	}

	public function setTransits(string $transits): static {
		$this->transits = $transits;

		return $this;
	}

	public function getId(): ?int {
		return $this->id;
	}

	public function getDungeon(): ?Dungeon {
		return $this->dungeon;
	}

	public function setDungeon(?Dungeon $dungeon): static {
		$this->dungeon = $dungeon;

		return $this;
	}

	public function getType(): ?TransitType {
		return $this->type;
	}

	public function setType(?TransitType $type): static {
		$this->type = $type;

		return $this;
	}

	public function getFromRoom(): ?Room {
		return $this->fromRoom;
	}

	public function setFromRoom(?Room $fromRoom): static {
		$this->fromRoom = $fromRoom;

		return $this;
	}

	public function getToRoom(): ?Room {
		return $this->toRoom;
	}

	public function setToRoom(?Room $toRoom): static {
		$this->toRoom = $toRoom;

		return $this;
	}
}
