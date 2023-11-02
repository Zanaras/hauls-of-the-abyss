<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

class Room {
	private ?string $visits = null;
	private ?int $id = null;
	private Collection $characters;
	private Collection $exits;
	private Collection $entrances;
	private ?Dungeon $dungeon = null;
	private ?RoomType $type = null;

	public function __construct() {
		$this->characters = new ArrayCollection();
		$this->exits = new ArrayCollection();
		$this->entrances = new ArrayCollection();
	}

	public function getVisits(): ?string {
		return $this->visits;
	}

	public function setVisits(string $visits): static {
		$this->visits = $visits;

		return $this;
	}

	public function getId(): ?int {
		return $this->id;
	}

	/**
	 * @return Collection<int, Character>
	 */
	public function getCharacters(): Collection {
		return $this->characters;
	}

	public function addCharacter(Character $character): static {
		if (!$this->characters->contains($character)) {
			$this->characters->add($character);
			$character->setRoom($this);
		}

		return $this;
	}

	public function removeCharacter(Character $character): static {
		if ($this->characters->removeElement($character)) {
			// set the owning side to null (unless already changed)
			if ($character->getRoom() === $this) {
				$character->setRoom(null);
			}
		}

		return $this;
	}

	/**
	 * @return Collection<int, Transit>
	 */
	public function getExits(): Collection {
		return $this->exits;
	}

	public function addExit(Transit $exit): static {
		if (!$this->exits->contains($exit)) {
			$this->exits->add($exit);
			$exit->setToRoom($this);
		}

		return $this;
	}

	public function removeExit(Transit $exit): static {
		if ($this->exits->removeElement($exit)) {
			// set the owning side to null (unless already changed)
			if ($exit->getToRoom() === $this) {
				$exit->setToRoom(null);
			}
		}

		return $this;
	}

	/**
	 * @return Collection<int, Transit>
	 */
	public function getEntrances(): Collection {
		return $this->entrances;
	}

	public function addEntrance(Transit $entrance): static {
		if (!$this->entrances->contains($entrance)) {
			$this->entrances->add($entrance);
			$entrance->setFromRoom($this);
		}

		return $this;
	}

	public function removeEntrance(Transit $entrance): static {
		if ($this->entrances->removeElement($entrance)) {
			// set the owning side to null (unless already changed)
			if ($entrance->getFromRoom() === $this) {
				$entrance->setFromRoom(null);
			}
		}

		return $this;
	}

	public function getDungeon(): ?Dungeon {
		return $this->dungeon;
	}

	public function setDungeon(?Dungeon $dungeon): static {
		$this->dungeon = $dungeon;

		return $this;
	}

	public function getType(): ?RoomType {
		return $this->type;
	}

	public function setType(?RoomType $type): static {
		$this->type = $type;

		return $this;
	}
}
